<?php

namespace App\Http\Controllers\Admin;

use App\Support\FileUpload;
use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Models\InventarisKondisi;
use App\Models\PeminjamanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class InventarisController extends Controller
{
    public function index()
    {
        return view('pages.admin.inventaris');
    }

    public function getData(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $jenis = trim((string) $request->input('jenis', ''));

        $query = Inventaris::query()->with('kondisi');

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
        ]);
    }

    /**
     * Ringkasan aset untuk kartu statistik.
     */
    private function getStats(): array
    {
        $totalNilai = Inventaris::pluck('harga')
            ->map(fn($harga) => (float) preg_replace('/\D/', '', (string) $harga))
            ->sum();

        $kondisi = [
            'Baik' => 0,
            'Kurang Baik' => 0,
            'Rusak' => 0,
            'Hilang' => 0,
        ];

        InventarisKondisi::get(['kondisi', 'jumlah'])->each(function ($row) use (&$kondisi) {
            $kondisi[$row->kondisi] = ($kondisi[$row->kondisi] ?? 0) + (int) $row->jumlah;
        });

        return [
            'total_inventaris' => Inventaris::count(),
            'total_nilai' => $totalNilai,
            'total_barang' => array_sum($kondisi),
            'kondisi' => $kondisi,
        ];
    }

    public function generateKodeInventaris(string $jenis, ?int $ignoreId = null): string
    {
        $lastInventaris = Inventaris::where('jenis', $jenis)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
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
            Inventaris::where('kode_inventaris', $kode)->exists()
        );

        return $kode;
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis' => [
                'required',
                Rule::in(['PAK', 'PDP', 'PPL']),
            ],
            'nama' => 'required|string|max:255',
            'tanggal_perolehan' => 'required|date',
            'harga' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'kondisi' => 'required|array',
            'kondisi.*' => 'required|integer|min:0',
        ]);

        $imageName = $request->hasFile('foto') ? FileUpload::simpan($request->file('foto'), 'inventaris') : null;

        $inventaris = Inventaris::create([
            'kode_inventaris' => $this->generateKodeInventaris(
                $request->jenis
            ),
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

        return response()->json([
            'status' => true,
            'message' => 'Inventaris berhasil ditambahkan.',
        ]);
    }

    public function show($id)
    {
        $inventaris = Inventaris::with('kondisi')->findOrFail($id);

        return response()->json($inventaris);
    }

    public function update(Request $request, $id)
    {
        $inventaris = Inventaris::findOrFail($id);

        $request->validate([
            'jenis' => [
                'required',
                Rule::in(['PAK', 'PDP', 'PPL']),
            ],
            'nama' => 'required|string|max:255',
            'tanggal_perolehan' => 'required|date',
            'harga' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'kondisi' => 'required|array',
            'kondisi.*' => 'required|integer|min:0',
        ]);

        $data = [
            'jenis' => $request->jenis,
            'nama' => $request->nama,
            'harga' => $request->harga,
            'tanggal_perolehan' => $request->tanggal_perolehan,
        ];

        // Kode inventaris memuat jenis, jadi dibuat ulang saat jenis berubah.
        if ($request->jenis !== $inventaris->jenis) {
            $data['kode_inventaris'] = $this->generateKodeInventaris(
                $request->jenis,
                $inventaris->id
            );
        }

        if ($request->hasFile('foto')) {
            if ($inventaris->foto) {
                FileUpload::hapus($inventaris->foto);
            }

            $data['foto'] = FileUpload::simpan($request->file('foto'), 'inventaris');
        }

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

        return response()->json([
            'status' => true,
            'message' => 'Inventaris berhasil diperbarui.',
        ]);
    }

    public function destroy($id)
    {
        $inventaris = Inventaris::with('kondisi')->findOrFail($id);

        // Inventaris yang sedang dipinjam tidak boleh dihapus karena
        // riwayat peminjaman ikut terhapus (cascade).
        $sedangDipinjam = PeminjamanDetail::where('id_inventaris', $id)
            ->whereHas('peminjaman', fn($q) => $q->where('status', 'dipinjam'))
            ->exists();

        if ($sedangDipinjam) {
            return response()->json([
                'status' => false,
                'message' => 'Inventaris masih dipinjam dan belum dapat dihapus.',
            ], 422);
        }

        if ($inventaris->foto) {
            FileUpload::hapus($inventaris->foto);
        }

        // hapus kondisi
        $inventaris->kondisi->each->delete();

        // hapus inventaris
        $inventaris->delete();

        return response()->json([
            'status' => true,
            'message' => 'Inventaris berhasil dihapus.'
        ]);
    }
}
