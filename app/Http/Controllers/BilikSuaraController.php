<?php

namespace App\Http\Controllers;

use App\Models\Pemilihan;
use App\Models\PemilihanPeserta;
use App\Models\PemilihanPutaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Layar di depan ruangan yang didatangi pemilih satu per satu.
 * Kode hanya dipakai untuk memeriksa hak suara, dan tidak pernah disimpan bersama pilihan.
 */
class BilikSuaraController extends Controller
{
    public function index(Pemilihan $pemilihan)
    {
        return view('pages.pemilihan.bilik', ['pemilihan' => $pemilihan, 'putaran' => $pemilihan->putaranDibuka()]);
    }

    public function periksa(Request $request, Pemilihan $pemilihan)
    {
        $putaran = $pemilihan->putaranDibuka();
        $peserta = $this->pesertaDariKode($pemilihan, $request->input('kode'));

        if ($pesan = $this->alasanDitolak($putaran, $peserta)) {
            return redirect()->route('bilik.index', $pemilihan->token)->with('ditolak', $pesan);
        }

        // Tidak disimpan browser, supaya tombol kembali tidak membuka surat suara pemilih sebelumnya.
        return response()->view('pages.pemilihan.surat-suara', [
            'pemilihan' => $pemilihan,
            'putaran' => $putaran,
            'peserta' => $peserta,
            'calon' => $putaran->calon()->with('peserta.jemaat:id,nama')->get()->sortBy(fn ($c) => $c->peserta->nama)->values(),
        ])->header('Cache-Control', 'no-store');
    }

    public function pilih(Request $request, Pemilihan $pemilihan)
    {
        $pesan = DB::transaction(function () use ($request, $pemilihan) {
            // Kunci ini juga mencegah suara masuk bersamaan dengan penutupan.
            $putaran = $pemilihan->putaran()->where('status', 'dibuka')->lockForUpdate()->first();
            $peserta = $this->pesertaDariKode($pemilihan, $request->input('kode'));

            if ($alasan = $this->alasanDitolak($putaran, $peserta)) {
                return $alasan;
            }

            $calon = $putaran->calon()->whereKey($request->integer('calon_id'))->first();

            if (! $calon) {
                return 'Pilihan tidak ditemukan. Ulangi dari awal.';
            }

            // Sengaja lewat DB, bukan model, supaya tidak ikut tercatat di audit log.
            DB::table('pemilihan_sudah_memilih')->insert(['putaran_id' => $putaran->id, 'peserta_id' => $peserta->id]);
            DB::table('pemilihan_surat_suara')->insert(['id' => (string) Str::uuid(), 'putaran_id' => $putaran->id, 'calon_id' => $calon->id]);

            return null;
        });

        return $pesan
            ? redirect()->route('bilik.index', $pemilihan->token)->with('ditolak', $pesan)
            : redirect()->route('bilik.index', $pemilihan->token)->with('terkirim', true);
    }

    private function pesertaDariKode(Pemilihan $pemilihan, mixed $kode): ?PemilihanPeserta
    {
        return is_string($kode)
            ? $pemilihan->peserta()->where('kode', Str::upper(preg_replace('/\s+/', '', $kode)))->first()
            : null;
    }

    private function alasanDitolak(?PemilihanPutaran $putaran, ?PemilihanPeserta $peserta): ?string
    {
        return match (true) {
            ! $putaran => 'Pemungutan suara belum dibuka atau sudah ditutup.',
            ! $peserta => 'Kode tidak ditemukan. Periksa kembali kartu Anda.',
            ! $peserta->bisaMemilih() => 'Kode ini tidak memiliki hak suara.',
            DB::table('pemilihan_sudah_memilih')->where(['putaran_id' => $putaran->id, 'peserta_id' => $peserta->id])->exists() => 'Kode ini sudah digunakan pada pemungutan suara ini.',
            default => null,
        };
    }
}
