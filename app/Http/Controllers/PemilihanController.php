<?php

namespace App\Http\Controllers;

use App\Models\DataAnggota;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Jemaat;
use App\Models\Pemilihan;
use App\Models\PemilihanCalon;
use App\Models\PemilihanJabatan;
use App\Models\PemilihanPeserta;
use App\Models\Pengurus;
use App\Models\Periode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Dipakai bersama oleh admin (Konferensi klasis) dan akun jemaat (Rapat Anggota).
 * Urutannya: kegiatan, lalu jabatan, lalu tahap demi tahap sampai satu nama ditetapkan.
 */
class PemilihanController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.pemilihan.index', [
            'rute' => $this->rute($request),
            'lingkup' => $this->lingkup($request),
            'daftar' => $this->milikAkun($request)
                ->withCount(['jabatan', 'peserta as hak_suara_count' => fn ($q) => $q->whereIn('peran', PemilihanPeserta::PERAN_MEMILIH)])
                ->latest('id')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(
            ['nama' => ['required', 'string', 'max:255']],
            ['nama.required' => 'Nama kegiatan wajib diisi.', 'nama.max' => 'Nama kegiatan maksimal 255 karakter.']
        );

        $pemilihan = Pemilihan::create($data + [
            'lingkup' => $this->lingkup($request),
            'jemaat_id' => $this->lingkup($request) === 'jemaat' ? $request->user()->jemaat_id : null,
            'token' => Str::random(40),
        ]);

        return redirect()->route($this->rute($request).'.show', [$pemilihan->id, 'tab' => 'peserta'])->with('success', 'Kegiatan dibuat. Isi pesertanya dulu, lalu buat jabatan.');
    }

    public function show(Request $request, int $pemilihan)
    {
        $pemilihan = $this->temukan($request, $pemilihan);
        $peserta = $pemilihan->peserta()->with(['jemaat:id,nama', 'anggota:id,no_anggota'])->orderBy('nama')->get();

        return view('pages.pemilihan.detail', [
            'rute' => $this->rute($request),
            'pemilihan' => $pemilihan,
            'tab' => $request->query('tab') === 'peserta' ? 'peserta' : 'jabatan',
            'peserta' => $peserta,
            'jumlahHakSuara' => $peserta->filter->bisaMemilih()->count(),
            'jumlahCalon' => $peserta->filter->bisaDicalonkan()->count(),
            'daftarJabatan' => $pemilihan->jabatan()->with(['tahap', 'terpilih.jemaat'])->get(),
            'jemaats' => $pemilihan->lingkup === 'klasis' ? Jemaat::orderBy('nama')->get(['id', 'nama']) : collect(),
            'periodes' => $pemilihan->lingkup === 'klasis' ? Periode::orderByDesc('tahun_mulai')->get() : collect(),
        ]);
    }

    public function destroy(Request $request, int $pemilihan): RedirectResponse
    {
        $pemilihan = $this->temukan($request, $pemilihan);

        if ($pemilihan->putaranDibuka()) {
            return back()->with('error', 'Tutup dulu pemungutan suara yang sedang dibuka.');
        }

        $pemilihan->delete();

        return redirect()->route($this->rute($request).'.index')->with('success', 'Kegiatan berhasil dihapus.');
    }

    /** Kartu kode dalam PDF. Tiap jemaat mulai di halaman baru supaya bisa dibagikan per jemaat. */
    public function kartu(Request $request, int $pemilihan)
    {
        $pemilihan = $this->temukan($request, $pemilihan);
        $peserta = $pemilihan->peserta()->with('jemaat:id,nama')
            ->whereIn('peran', PemilihanPeserta::PERAN_MEMILIH)
            ->when($request->integer('jemaat'), fn ($q, $id) => $q->where('jemaat_id', $id))
            ->when($request->integer('peserta'), fn ($q, $id) => $q->whereKey($id))
            ->get()
            ->sortBy(fn ($p) => [$p->jemaat?->nama ?? '', $p->nama]);

        if ($peserta->isEmpty()) {
            return back()->with('error', 'Belum ada peserta yang punya hak suara untuk dicetak.');
        }

        $nama = Str::slug($pemilihan->nama.' kartu kode'.($request->integer('jemaat') ? ' '.$peserta->first()->jemaat?->nama : ''));

        return Pdf::loadView('pages.pemilihan.kartu-pdf', [
            'pemilihan' => $pemilihan,
            'kelompok' => $peserta->groupBy(fn ($p) => $p->jemaat?->nama ?? ($pemilihan->lingkup === 'klasis' ? 'Klasis' : '')),
            'logo' => $this->logoKecil(),
        ])->setPaper('a4')->setOption('isFontSubsettingEnabled', true)->download("{$nama}.pdf");
    }

    /** Pencarian anggota untuk dipilih sebagai peserta. Akun jemaat hanya bisa mencari di jemaatnya sendiri. */
    public function anggota(Request $request, int $pemilihan)
    {
        $pemilihan = $this->temukan($request, $pemilihan);
        $jemaatId = $pemilihan->lingkup === 'jemaat' ? $pemilihan->jemaat_id : $request->integer('jemaat_id');
        $cari = is_string($request->query('cari')) ? trim($request->query('cari')) : '';
        $sudah = $pemilihan->peserta()->whereNotNull('data_anggota_id')->pluck('peran', 'data_anggota_id');

        return response()->json(
            DataAnggota::where('jemaat_id', $jemaatId)
                ->when($cari !== '', fn ($q) => $q->where(fn ($w) => $w->where('nama', 'like', "%{$cari}%")->orWhere('no_anggota', 'like', "%{$cari}%")))
                ->orderBy('nama')
                ->limit(40)
                ->get(['id', 'nama', 'no_anggota', 'status_anggota'])
                ->map(fn ($a) => [
                    'id' => $a->id,
                    'nama' => $a->nama,
                    'keterangan' => collect([$a->no_anggota, $a->status_anggota !== 'Aktif' ? $a->status_anggota : null])->filter()->join(' · '),
                    'sudah' => isset($sudah[$a->id]) ? PemilihanPeserta::PERAN[$sudah[$a->id]] : null,
                ])
        );
    }

    // ---------- Peserta ----------

    public function pesertaStore(Request $request, int $pemilihan): RedirectResponse
    {
        $pemilihan = $this->temukan($request, $pemilihan);
        $data = $request->validate([
            'peran' => ['required', Rule::in(array_keys($pemilihan->pilihanPeran()))],
            'jemaat_id' => [Rule::requiredIf(in_array($request->input('peran'), ['utusan_penuh', 'utusan_cadangan'], true)), 'nullable', 'integer', 'exists:jemaat,id'],
            'anggota' => ['nullable', 'array', 'max:500'],
            'anggota.*' => ['integer'],
            'nama' => ['nullable', 'string', 'max:5000'],
        ], [
            'peran.required' => 'Peran wajib dipilih.',
            'peran.in' => 'Peran tidak tersedia.',
            'jemaat_id.required' => 'Jemaat wajib dipilih untuk utusan.',
            'jemaat_id.exists' => 'Jemaat tidak ditemukan.',
            'nama.max' => 'Daftar nama terlalu panjang. Masukkan bertahap.',
        ]);

        $jemaatId = $pemilihan->lingkup === 'jemaat' ? $pemilihan->jemaat_id : ($data['jemaat_id'] ?? null);

        // Anggota dari jemaat lain diabaikan, jadi pilihan tidak bisa dipalsukan lewat form.
        $orang = DataAnggota::whereIn('id', $data['anggota'] ?? [])
            ->when($jemaatId, fn ($q) => $q->where('jemaat_id', $jemaatId))
            ->get(['id', 'nama', 'jemaat_id'])
            ->map(fn ($a) => ['nama' => $a->nama, 'data_anggota_id' => $a->id, 'jemaat_id' => $a->jemaat_id])
            ->all();

        foreach (preg_split('/\r\n|\r|\n/', $data['nama'] ?? '') as $baris) {
            $orang[] = ['nama' => $baris, 'data_anggota_id' => null, 'jemaat_id' => $jemaatId];
        }

        if (! collect($orang)->contains(fn ($o) => Str::squish($o['nama']) !== '')) {
            return back()->withErrors(['anggota' => 'Pilih minimal satu anggota atau ketik nama.']);
        }

        // Dikunci supaya dua admin yang menambah bersamaan tidak melewati batas utusan penuh.
        $jumlah = DB::transaction(function () use ($pemilihan, $orang, $data, $jemaatId) {
            Pemilihan::whereKey($pemilihan->id)->lockForUpdate()->first();
            $jumlah = $this->tambahPeserta($pemilihan, $orang, $data['peran']);

            if ($data['peran'] === 'utusan_penuh') {
                $this->pastikanSlotUtusanPenuh($pemilihan, $jemaatId);
            }

            return $jumlah;
        });

        return $this->kembaliKePeserta($request, $pemilihan, "{$jumlah} peserta ditambahkan. Nama yang sudah ada dilewati.");
    }

    /** Pengurus klasis periode yang berakhir bisa dicalonkan, tetapi tidak punya hak suara. */
    public function pesertaDemisioner(Request $request, int $pemilihan): RedirectResponse
    {
        $pemilihan = $this->temukan($request, $pemilihan);
        abort_unless($pemilihan->lingkup === 'klasis', 404);

        $data = $request->validate(
            ['periode_id' => ['required', 'integer', 'exists:periodes,id']],
            ['periode_id.required' => 'Periode wajib dipilih.', 'periode_id.exists' => 'Periode tidak ditemukan.']
        );

        $orang = Pengurus::where('periode_id', $data['periode_id'])->with('anggota:id,nama,jemaat_id')->get()
            ->filter->anggota
            ->map(fn ($p) => ['nama' => $p->anggota->nama, 'data_anggota_id' => $p->anggota->id, 'jemaat_id' => $p->anggota->jemaat_id])
            ->all();
        $jumlah = $this->tambahPeserta($pemilihan, $orang, 'demisioner');

        return $this->kembaliKePeserta($request, $pemilihan, "{$jumlah} pengurus demisioner ditambahkan. Nama yang sudah ada dilewati.");
    }

    public function pesertaAnggota(Request $request, int $pemilihan): RedirectResponse
    {
        $pemilihan = $this->temukan($request, $pemilihan);
        abort_unless($pemilihan->lingkup === 'jemaat', 404);

        $orang = DataAnggota::where('jemaat_id', $pemilihan->jemaat_id)->where('status_anggota', 'Aktif')->get(['id', 'nama'])
            ->map(fn ($a) => ['nama' => $a->nama, 'data_anggota_id' => $a->id, 'jemaat_id' => $pemilihan->jemaat_id])
            ->all();
        $jumlah = $this->tambahPeserta($pemilihan, $orang, 'pemilih');

        return $this->kembaliKePeserta($request, $pemilihan, "{$jumlah} anggota aktif ditambahkan. Hapus yang tidak hadir sebelum kartu dicetak.");
    }

    public function pesertaUpdate(Request $request, int $pemilihan, int $peserta): RedirectResponse
    {
        $pemilihan = $this->temukan($request, $pemilihan);
        $peserta = $pemilihan->peserta()->findOrFail($peserta);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'peran' => ['required', Rule::in(array_keys($pemilihan->pilihanPeran()))],
        ], ['nama.required' => 'Nama wajib diisi.', 'peran.in' => 'Peran tidak tersedia.']);

        DB::transaction(function () use ($pemilihan, $peserta, $data) {
            Pemilihan::whereKey($pemilihan->id)->lockForUpdate()->first();
            $peserta->update($data);

            if ($data['peran'] === 'utusan_penuh') {
                $this->pastikanSlotUtusanPenuh($pemilihan, $peserta->jemaat_id);
            }
        });

        return $this->kembaliKePeserta($request, $pemilihan, 'Peserta berhasil diperbarui.');
    }

    public function pesertaKode(Request $request, int $pemilihan, int $peserta): RedirectResponse
    {
        $pemilihan = $this->temukan($request, $pemilihan);
        $peserta = $pemilihan->peserta()->findOrFail($peserta);
        $peserta->update(['kode' => PemilihanPeserta::kodeBaru($pemilihan->id)]);

        return $this->kembaliKePeserta($request, $pemilihan, 'Kode diganti. Kode lama tidak bisa dipakai lagi, cetak ulang kartunya.');
    }

    public function pesertaDestroy(Request $request, int $pemilihan, int $peserta): RedirectResponse
    {
        $pemilihan = $this->temukan($request, $pemilihan);
        $peserta = $pemilihan->peserta()->findOrFail($peserta);

        $sudahDipakai = DB::table('pemilihan_sudah_memilih')->where('peserta_id', $peserta->id)->exists()
            || PemilihanCalon::where('peserta_id', $peserta->id)->whereHas('putaran', fn ($q) => $q->where('status', '!=', 'draft'))->exists()
            || PemilihanJabatan::where('terpilih_id', $peserta->id)->exists();

        if ($sudahDipakai) {
            return back()->with('error', 'Peserta sudah tercatat di pemungutan suara, jadi tidak bisa dihapus. Ubah perannya saja.');
        }

        $peserta->delete();

        return $this->kembaliKePeserta($request, $pemilihan, 'Peserta berhasil dihapus.');
    }

    // ---------- Jabatan ----------

    public function jabatanStore(Request $request, int $pemilihan): RedirectResponse
    {
        $pemilihan = $this->temukan($request, $pemilihan);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('pemilihan_jabatan', 'nama')->where('pemilihan_id', $pemilihan->id)],
        ], [
            'nama.required' => 'Nama jabatan wajib diisi.',
            'nama.max' => 'Nama jabatan maksimal 100 karakter.',
            'nama.unique' => 'Jabatan ini sudah ada di kegiatan ini.',
        ]);

        $jabatan = $pemilihan->jabatan()->create($data);

        return redirect()->route($this->rute($request).'.jabatan.show', [$pemilihan->id, $jabatan->id])->with('success', "Jabatan {$jabatan->nama} dibuat. Mulai tahap pertamanya.");
    }

    public function jabatanShow(Request $request, int $pemilihan, int $jabatan)
    {
        $pemilihan = $this->temukan($request, $pemilihan);
        $jabatan = $pemilihan->jabatan()->with(['tahap.calon.peserta.jemaat', 'terpilih.jemaat'])->findOrFail($jabatan);
        $terakhir = $jabatan->tahapTerakhir();
        $semuaCalon = $pemilihan->peserta()->with('jemaat:id,nama')->whereIn('peran', PemilihanPeserta::PERAN_CALON)->get()
            ->sortBy(fn ($p) => [$p->jemaat?->nama ?? '', $p->nama])->values();

        // Tahap pertama memakai semua yang bisa dicalonkan. Tahap berikutnya hanya dari calon tahap sebelumnya.
        $opsiCalon = $terakhir ? $terakhir->hasil()->pluck('peserta') : $semuaCalon;
        $sebelumDraft = $jabatan->tahap->count() > 1 ? $jabatan->tahap[$jabatan->tahap->count() - 2] : null;
        $idSudahTerpilih = $pemilihan->jabatan()->whereKeyNot($jabatan->id)->whereNotNull('terpilih_id')->pluck('terpilih_id')->all();

        return view('pages.pemilihan.jabatan', [
            'rute' => $this->rute($request),
            'pemilihan' => $pemilihan,
            'jabatan' => $jabatan,
            'terakhir' => $terakhir,
            'jumlahHakSuara' => $pemilihan->jumlahHakSuara(),
            'opsiCalon' => $opsiCalon,
            // Calon tahap yang belum dibuka boleh diubah dari daftar yang sama dengan saat dibuat.
            'opsiDraft' => $sebelumDraft ? $sebelumDraft->calon->pluck('peserta') : $semuaCalon,
            'idSudahTerpilih' => $idSudahTerpilih,
            // Lanjut tahap: semua calon sebelumnya tercentang, tinggal hapus yang tidak sesuai.
            'tercentang' => $terakhir ? $opsiCalon->pluck('id')->all() : $opsiCalon->pluck('id')->diff($idSudahTerpilih)->values()->all(),
            'suaraSebelumnya' => $terakhir ? $terakhir->hasil()->mapWithKeys(fn ($c) => [$c->peserta_id => $c->suara])->all() : [],
        ]);
    }

    public function jabatanDestroy(Request $request, int $pemilihan, int $jabatan): RedirectResponse
    {
        $pemilihan = $this->temukan($request, $pemilihan);
        $jabatan = $pemilihan->jabatan()->findOrFail($jabatan);

        // Tahap yang sudah berjalan adalah catatan sidang, jadi tidak boleh hilang.
        if ($jabatan->tahap()->where('status', '!=', 'draft')->exists()) {
            return back()->with('error', 'Jabatan yang sudah punya pemungutan suara tidak bisa dihapus.');
        }

        $jabatan->delete();

        return redirect()->route($this->rute($request).'.show', $pemilihan->id)->with('success', 'Jabatan dihapus.');
    }

    public function tahapStore(Request $request, int $pemilihan, int $jabatan): RedirectResponse
    {
        $pemilihan = $this->temukan($request, $pemilihan);
        $jabatan = $pemilihan->jabatan()->with('tahap')->findOrFail($jabatan);

        if (! $jabatan->bisaTambahTahap()) {
            return back()->with('error', $jabatan->terpilih_id ? 'Jabatan ini sudah ditetapkan.' : 'Selesaikan dulu tahap yang sedang berjalan.');
        }

        $tahap = $request->validate(
            ['tahap' => ['required', 'string', 'max:100']],
            ['tahap.required' => 'Nama tahap wajib diisi.', 'tahap.max' => 'Nama tahap maksimal 100 karakter.']
        )['tahap'];

        $boleh = $jabatan->tahapTerakhir()
            ? $jabatan->tahapTerakhir()->calon()->pluck('peserta_id')->all()
            : $pemilihan->peserta()->whereIn('peran', PemilihanPeserta::PERAN_CALON)->pluck('id')->all();
        $calon = $this->validasiCalon($request, $boleh);

        DB::transaction(function () use ($pemilihan, $jabatan, $tahap, $calon) {
            $putaran = $jabatan->tahap()->create(['pemilihan_id' => $pemilihan->id, 'tahap' => $tahap]);
            $putaran->calon()->createMany(array_map(fn ($id) => ['peserta_id' => (int) $id], $calon));
        });

        return back()->with('success', "Tahap {$tahap} dibuat. Periksa calonnya, lalu buka saat siap.");
    }

    /** Pimpinan sidang yang menetapkan, karena AD Pasal 12 mengatur suara yang sama. */
    public function jabatanTetapkan(Request $request, int $pemilihan, int $jabatan): RedirectResponse
    {
        $pemilihan = $this->temukan($request, $pemilihan);
        $jabatan = $pemilihan->jabatan()->with('tahap')->findOrFail($jabatan);
        $terakhir = $jabatan->tahapTerakhir();

        if ($jabatan->terpilih_id) {
            return back()->with('error', 'Jabatan ini sudah ditetapkan.');
        }
        if (! $terakhir || $terakhir->status !== 'ditutup') {
            return back()->with('error', 'Tutup dulu pemungutan suara tahap terakhir.');
        }

        $data = $request->validate(
            ['peserta_id' => ['required', 'integer', Rule::in($terakhir->calon()->pluck('peserta_id')->all())]],
            ['peserta_id.required' => 'Pilih nama yang ditetapkan.', 'peserta_id.in' => 'Nama itu bukan calon pada tahap terakhir.']
        );

        $jabatan->update(['terpilih_id' => $data['peserta_id']]);

        return back()->with('success', "{$jabatan->nama} telah ditetapkan.");
    }

    public function jabatanBatalTetapkan(Request $request, int $pemilihan, int $jabatan): RedirectResponse
    {
        $this->temukan($request, $pemilihan)->jabatan()->findOrFail($jabatan)->update(['terpilih_id' => null]);

        return back()->with('success', 'Penetapan dibatalkan.');
    }

    // ---------- Tahap (pemungutan suara) ----------

    public function putaranCalon(Request $request, int $pemilihan, int $putaran): RedirectResponse
    {
        $pemilihan = $this->temukan($request, $pemilihan);
        $putaran = $pemilihan->putaran()->findOrFail($putaran);

        if ($putaran->status !== 'draft') {
            return back()->with('error', 'Calon tidak bisa diubah setelah pemungutan suara dibuka.');
        }

        $sebelumnya = $putaran->jabatan->tahap()->where('id', '<', $putaran->id)->reorder('id', 'desc')->first();
        $boleh = $sebelumnya
            ? $sebelumnya->calon()->pluck('peserta_id')->all()
            : $pemilihan->peserta()->whereIn('peran', PemilihanPeserta::PERAN_CALON)->pluck('id')->all();
        $calon = $this->validasiCalon($request, $boleh);

        DB::transaction(function () use ($putaran, $calon) {
            $putaran->calon()->delete();
            $putaran->calon()->createMany(array_map(fn ($id) => ['peserta_id' => (int) $id], $calon));
        });

        return back()->with('success', 'Daftar calon diperbarui.');
    }

    public function putaranBuka(Request $request, int $pemilihan, int $putaran): RedirectResponse
    {
        $pemilihan = $this->temukan($request, $pemilihan);

        return DB::transaction(function () use ($pemilihan, $putaran) {
            // Baris dikunci supaya dua klik bersamaan tidak membuka dua pemungutan.
            Pemilihan::whereKey($pemilihan->id)->lockForUpdate()->first();
            $putaran = $pemilihan->putaran()->findOrFail($putaran);

            if ($putaran->status !== 'draft') {
                return back()->with('error', 'Pemungutan suara ini sudah pernah dibuka.');
            }
            if ($dibuka = $pemilihan->putaranDibuka()) {
                return back()->with('error', "Pemungutan suara {$dibuka->jabatan->nama} masih dibuka. Tutup dulu.");
            }
            if ($pemilihan->jumlahHakSuara() === 0) {
                return back()->with('error', 'Belum ada peserta yang punya hak suara.');
            }

            $putaran->update(['status' => 'dibuka', 'dibuka_pada' => now()]);

            return back()->with('success', 'Pemungutan suara dibuka. Pemilih sudah bisa memilih di bilik suara.');
        });
    }

    public function putaranTutup(Request $request, int $pemilihan, int $putaran): RedirectResponse
    {
        $pemilihan = $this->temukan($request, $pemilihan);

        return DB::transaction(function () use ($pemilihan, $putaran) {
            $putaran = $pemilihan->putaran()->lockForUpdate()->findOrFail($putaran);

            if ($putaran->status !== 'dibuka') {
                return back()->with('error', 'Pemungutan suara ini tidak sedang dibuka.');
            }

            $putaran->update(['status' => 'ditutup', 'ditutup_pada' => now()]);

            return back()->with('success', 'Pemungutan suara ditutup. Hasilnya sudah tampil.');
        });
    }

    public function putaranStatus(Request $request, int $pemilihan, int $putaran)
    {
        $putaran = $this->temukan($request, $pemilihan)->putaran()->findOrFail($putaran);

        return response()->json([
            'status' => $putaran->status,
            'sudah' => $putaran->jumlahMemilih(),
            'belum' => $putaran->belumMemilih(),
        ]);
    }

    public function putaranDestroy(Request $request, int $pemilihan, int $putaran): RedirectResponse
    {
        $putaran = $this->temukan($request, $pemilihan)->putaran()->findOrFail($putaran);

        if ($putaran->status !== 'draft') {
            return back()->with('error', 'Hanya tahap yang belum dibuka yang bisa dihapus.');
        }

        $putaran->delete();

        return back()->with('success', 'Tahap dihapus.');
    }

    // ---------- Bantuan ----------

    /** Dipanggil di dalam transaksi. Kalau batas terlewati, semua perubahan dibatalkan. */
    private function pastikanSlotUtusanPenuh(Pemilihan $pemilihan, ?int $jemaatId): void
    {
        $jumlah = $pemilihan->peserta()->where('jemaat_id', $jemaatId)->where('peran', 'utusan_penuh')->count();

        if ($jumlah > PemilihanPeserta::MAKS_UTUSAN_PENUH) {
            $nama = Jemaat::whereKey($jemaatId)->value('nama');

            throw ValidationException::withMessages([
                'anggota' => 'Utusan penuh Jemaat '.$nama.' maksimal '.PemilihanPeserta::MAKS_UTUSAN_PENUH.' orang. Jadikan sisanya utusan cadangan.',
            ]);
        }
    }

    /** Logo asli hampir 400 KB. Diperkecil supaya PDF kartu tidak membengkak. */
    private function logoKecil(): string
    {
        $gambar = imagescale(imagecreatefrompng(public_path('images/logo.png')), 160);
        imagesavealpha($gambar, true);
        ob_start();
        imagepng($gambar, null, 9);

        return 'data:image/png;base64,'.base64_encode(ob_get_clean());
    }

    private function lingkup(Request $request): string
    {
        return $request->routeIs('jemaat.*') ? 'jemaat' : 'klasis';
    }

    private function rute(Request $request): string
    {
        return $this->lingkup($request) === 'jemaat' ? 'jemaat.pemilihan' : 'pemilihan';
    }

    private function milikAkun(Request $request)
    {
        return Pemilihan::where('lingkup', $this->lingkup($request))
            ->when($this->lingkup($request) === 'jemaat', fn ($q) => $q->where('jemaat_id', $request->user()->jemaat_id));
    }

    private function temukan(Request $request, int $id): Pemilihan
    {
        return $this->milikAkun($request)->findOrFail($id);
    }

    private function kembaliKePeserta(Request $request, Pemilihan $pemilihan, string $pesan): RedirectResponse
    {
        return redirect()->route($this->rute($request).'.show', [$pemilihan->id, 'tab' => 'peserta'])->with('success', $pesan);
    }

    /** $orang berisi ['nama', 'data_anggota_id', 'jemaat_id']. Anggota atau nama yang sudah terdaftar dilewati. */
    private function tambahPeserta(Pemilihan $pemilihan, array $orang, string $peran): int
    {
        $sudah = $pemilihan->peserta()->get(['nama', 'jemaat_id', 'data_anggota_id']);
        $anggotaAda = $sudah->pluck('data_anggota_id')->filter()->flip();
        $namaAda = $sudah->map(fn ($p) => mb_strtolower($p->nama).'|'.$p->jemaat_id)->flip();
        $jumlah = 0;

        foreach ($orang as $o) {
            $nama = Str::limit(Str::squish((string) $o['nama']), 255, '');
            $kunci = mb_strtolower($nama).'|'.$o['jemaat_id'];

            if ($nama === '' || isset($namaAda[$kunci]) || ($o['data_anggota_id'] && isset($anggotaAda[$o['data_anggota_id']]))) {
                continue;
            }

            $pemilihan->peserta()->create([
                'nama' => $nama,
                'peran' => $peran,
                'jemaat_id' => $o['jemaat_id'],
                'data_anggota_id' => $o['data_anggota_id'],
                'kode' => PemilihanPeserta::kodeBaru($pemilihan->id),
            ]);
            $namaAda[$kunci] = true;
            if ($o['data_anggota_id']) {
                $anggotaAda[$o['data_anggota_id']] = true;
            }
            $jumlah++;
        }

        return $jumlah;
    }

    private function validasiCalon(Request $request, array $boleh): array
    {
        return $request->validate([
            'calon' => ['required', 'array', 'min:2'],
            'calon.*' => ['integer', 'distinct', Rule::in($boleh)],
        ], [
            'calon.required' => 'Pilih minimal 2 calon.',
            'calon.min' => 'Pilih minimal 2 calon.',
            'calon.*.in' => 'Ada calon yang tidak memenuhi syarat.',
        ])['calon'];
    }
}
