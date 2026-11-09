<?php

namespace App\Http\Controllers\Kepengurusan;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Models\InventarisKondisi;
use App\Models\InventarisRiwayat;
use App\Models\PeminjamanDetail;
use App\Models\Periode;
use App\Support\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Inventaris dicatat sekali lalu otomatis lanjut ke periode berikutnya.
 * Setiap penambahan, perubahan kondisi, dan penghapusan dicatat di riwayat
 * menurut periode yang melakukannya. Periode lama hanya bisa melihat.
 */
class InventarisController extends Controller
{
    /** Menu sidebar /inventaris: buka periode milik pengguna, atau periode terbaru untuk admin. */
    public function buka(Request $request)
    {
        $periode = $request->user()->periode ?? Periode::orderByDesc('tahun_mulai')->first();

        abort_unless($periode, 404, 'Belum ada periode kepengurusan. Buat periode terlebih dahulu.');

        return redirect()->route('kepengurusan.inventaris.index', $periode);
    }

    public function index(Periode $periode)
    {
        return view('pages.admin.inventaris', [
            'periode' => $periode,
            'hanyaLihat' => $periode->sudahSerahTerima(),
        ]);
    }

    public function getData(Request $request, Periode $periode)
    {
        $search = trim((string) $request->input('search', ''));
        $jenis = trim((string) $request->input('jenis', ''));

        $query = Inventaris::query()->with('kondisi')
            ->when($request->input('status') === 'dihapus', fn ($q) => $q->onlyTrashed());

        // Pencarian mencakup nama, kode inventaris, dan jenis.
        if ($search !== '') {
            $like = '%' . $search . '%';

            $query->where(function ($q) use ($like) {
                $q->where('nama', 'like', $like)
                    ->orWhere('kode_inventaris', 'like', $like)
                    ->orWhere('jenis', 'like', $like);
            });
        }

        if (in_array($jenis, ['PAK', 'PDP', 'PPL'], true)) {
            $query->where('jenis', $jenis);
        }

        $paginator = $query->orderByDesc('created_at')->paginate(25);

        return response()->json([
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'stats' => $this->getStats(),
            'laporan' => $this->laporan($periode),
        ]);
    }

    /**
     * Ringkasan aset untuk kartu statistik (kondisi terkini, tanpa barang terhapus).
     */
    private function getStats(): array
    {
        $totalNilai = Inventaris::pluck('harga')
            ->map(fn ($harga) => (float) preg_replace('/\D/', '', (string) $harga))
            ->sum();

        $kondisi = [
            'Baik' => 0,
            'Kurang Baik' => 0,
            'Rusak' => 0,
            'Hilang' => 0,
        ];

        InventarisKondisi::whereHas('inventaris')->get(['kondisi', 'jumlah'])->each(function ($row) use (&$kondisi) {
            $kondisi[$row->kondisi] = ($kondisi[$row->kondisi] ?? 0) + (int) $row->jumlah;
        });

        return [
            'total_inventaris' => Inventaris::count(),
            'total_nilai' => $totalNilai,
            'total_barang' => array_sum($kondisi),
            'kondisi' => $kondisi,
        ];
    }

    /**
     * Apa yang terjadi pada inventaris selama periode ini: barang ditambah,
     * dihapus, dan selisih unit per kondisi dari setiap perubahan.
     */
    private function laporan(Periode $periode): array
    {
        $riwayat = $periode->inventarisRiwayat()->get(['aksi', 'kondisi_sebelum', 'kondisi_sesudah']);
        $perubahan = [];

        foreach ($riwayat->where('aksi', 'diubah') as $r) {
            foreach (array_keys(($r->kondisi_sebelum ?? []) + ($r->kondisi_sesudah ?? [])) as $k) {
                $perubahan[$k] = ($perubahan[$k] ?? 0) + ($r->kondisi_sesudah[$k] ?? 0) - ($r->kondisi_sebelum[$k] ?? 0);
            }
        }

        return [
            'ditambah' => $riwayat->where('aksi', 'ditambah')->count(),
            'dihapus' => $riwayat->where('aksi', 'dihapus')->count(),
            'perubahan' => array_filter($perubahan),
        ];
    }

    /** Riwayat satu barang (semua periode) atau seluruh riwayat periode ini. */
    public function riwayat(Request $request, Periode $periode)
    {
        $id = $request->integer('inventaris');

        $data = InventarisRiwayat::with(['inventaris:id,nama,kode_inventaris', 'periode', 'user:id,name'])
            ->when(
                $id,
                fn ($q) => $q->where('inventaris_id', $id),
                fn ($q) => $q->where('periode_id', $periode->id)
            )
            ->latest('id')
            ->limit(200) // ponytail: dibatasi 200 baris terbaru, tambah paginasi kalau riwayat sudah ramai
            ->get()
            ->map(fn (InventarisRiwayat $r) => [
                'aksi' => $r->aksi,
                'nama' => $r->inventaris?->nama,
                'kode' => $r->inventaris?->kode_inventaris,
                'periode' => $r->periode->nama,
                'user' => $r->user?->name,
                'kondisi_sebelum' => $r->kondisi_sebelum,
                'kondisi_sesudah' => $r->kondisi_sesudah,
                'keterangan' => $r->keterangan,
                'tanggal' => $r->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json($data);
    }

    public function generateKodeInventaris(string $jenis, ?int $ignoreId = null): string
    {
        // Barang terhapus ikut dihitung karena kodenya tetap terpakai.
        $lastInventaris = Inventaris::withTrashed()
            ->where('jenis', $jenis)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastInventaris) {
            $parts = explode('/', $lastInventaris->kode_inventaris);

            if (isset($parts[0]) && ctype_digit($parts[0])) {
                $nextNumber = (int) $parts[0] + 1;
            }
        }

        do {
            $randomNumber = random_int(1000, 9999);

            $kode = str_pad($nextNumber, 3, '0', STR_PAD_LEFT)
                . '/' . $jenis
                . '/' . $randomNumber;
        } while (
            Inventaris::withTrashed()->where('kode_inventaris', $kode)->exists()
        );

        return $kode;
    }

    private function validasi(Request $request): void
    {
        $request->validate([
            'jenis' => ['required', Rule::in(['PAK', 'PDP', 'PPL'])],
            'nama' => 'required|string|max:255',
            'tanggal_perolehan' => 'required|date',
            'harga' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'kondisi' => 'required|array:Baik,Kurang Baik,Rusak,Hilang',
            'kondisi.*' => 'required|integer|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);
    }

    private function bolehUbah(Periode $periode): void
    {
        abort_if($periode->sudahSerahTerima(), 403, 'Periode ini sudah serah terima. Data inventaris hanya bisa dilihat.');
    }

    private function catat(Periode $periode, Inventaris $inventaris, string $aksi, ?array $sebelum, ?array $sesudah, ?string $keterangan): void
    {
        $inventaris->riwayat()->create([
            'periode_id' => $periode->id,
            'user_id' => auth()->id(),
            'aksi' => $aksi,
            'kondisi_sebelum' => $sebelum,
            'kondisi_sesudah' => $sesudah,
            'keterangan' => $keterangan,
        ]);
    }

    public function store(Request $request, Periode $periode)
    {
        $this->bolehUbah($periode);
        $this->validasi($request);

        $imageName = $request->hasFile('foto') ? FileUpload::simpan($request->file('foto'), 'inventaris') : null;

        DB::transaction(function () use ($request, $periode, $imageName) {
            $inventaris = Inventaris::create([
                'kode_inventaris' => $this->generateKodeInventaris($request->jenis),
                'jenis' => $request->jenis,
                'nama' => $request->nama,
                'tanggal_perolehan' => $request->tanggal_perolehan,
                'harga' => $request->harga,
                'foto' => $imageName,
            ]);

            foreach ($request->kondisi as $kondisi => $jumlah) {
                if ($jumlah > 0) {
                    InventarisKondisi::create([
                        'id_inventaris' => $inventaris->id,
                        'kondisi' => $kondisi,
                        'jumlah' => $jumlah,
                    ]);
                }
            }

            $this->catat($periode, $inventaris, 'ditambah', null, $inventaris->petaKondisi(), $request->keterangan);
        });

        return response()->json([
            'status' => true,
            'message' => 'Inventaris berhasil ditambahkan.',
        ]);
    }

    public function show(Periode $periode, $id)
    {
        return response()->json(Inventaris::with('kondisi')->findOrFail($id));
    }

    public function update(Request $request, Periode $periode, $id)
    {
        $this->bolehUbah($periode);

        $inventaris = Inventaris::findOrFail($id);

        $this->validasi($request);

        $data = [
            'jenis' => $request->jenis,
            'nama' => $request->nama,
            'harga' => $request->harga,
            'tanggal_perolehan' => $request->tanggal_perolehan,
        ];

        // Kode inventaris memuat jenis, jadi dibuat ulang saat jenis berubah.
        if ($request->jenis !== $inventaris->jenis) {
            $data['kode_inventaris'] = $this->generateKodeInventaris($request->jenis, $inventaris->id);
        }

        if ($request->hasFile('foto')) {
            if ($inventaris->foto) {
                FileUpload::hapus($inventaris->foto);
            }

            $data['foto'] = FileUpload::simpan($request->file('foto'), 'inventaris');
        }

        DB::transaction(function () use ($request, $periode, $inventaris, $data, $id) {
            $sebelum = $inventaris->petaKondisi();

            $inventaris->update($data);

            // Hanya kondisi yang berubah yang disentuh, supaya audit log mencatat perubahan sebenarnya.
            $lama = InventarisKondisi::where('id_inventaris', $id)->get()->keyBy('kondisi');

            foreach ($request->kondisi as $kondisi => $jumlah) {
                if ($jumlah > 0) {
                    $baris = $lama->pull($kondisi) ?? new InventarisKondisi(['id_inventaris' => $id, 'kondisi' => $kondisi]);
                    $baris->fill(['jumlah' => $jumlah])->save();
                }
            }

            $lama->each->delete();

            $sesudah = $inventaris->petaKondisi();

            if ($sebelum != $sesudah) {
                $this->catat($periode, $inventaris, 'diubah', $sebelum, $sesudah, $request->keterangan);
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Inventaris berhasil diperbarui.',
        ]);
    }

    /** Barang tidak benar-benar dihapus: hanya ditandai, kondisi dan riwayatnya tetap tersimpan. */
    public function destroy(Request $request, Periode $periode, $id)
    {
        $this->bolehUbah($periode);

        $inventaris = Inventaris::findOrFail($id);

        $request->validate(
            ['keterangan' => 'required|string|max:255'],
            ['keterangan.required' => 'Alasan penghapusan wajib diisi.']
        );

        $sedangDipinjam = PeminjamanDetail::where('id_inventaris', $id)
            ->whereHas('peminjaman', fn ($q) => $q->where('status', 'dipinjam'))
            ->exists();

        if ($sedangDipinjam) {
            return response()->json([
                'status' => false,
                'message' => 'Inventaris masih dipinjam dan belum dapat dihapus.',
            ], 422);
        }

        DB::transaction(function () use ($request, $periode, $inventaris) {
            $this->catat($periode, $inventaris, 'dihapus', $inventaris->petaKondisi(), null, $request->keterangan);
            $inventaris->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'Inventaris berhasil dihapus. Riwayatnya tetap tersimpan.',
        ]);
    }
}
