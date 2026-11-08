<?php

namespace App\Http\Controllers\Kepengurusan;

use App\Http\Controllers\Controller;
use App\Models\PendampinganKegiatan;
use App\Models\Periode;
use App\Support\BotPendampingan;
use App\Support\RekapPendampingan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PendampinganController extends Controller
{
    public function index(Request $request, Periode $periode)
    {
        $semua = PendampinganKegiatan::milikPeriode($periode);
        $daftarBulan = (clone $semua)->orderByDesc('tanggal')->pluck('tanggal')
            ->map(fn ($t) => $t->format('Y-m'))->unique()->values();
        $bulan = $request->query('bulan');
        $bulan = is_string($bulan) && $daftarBulan->contains($bulan) ? $bulan : null;

        $kegiatan = (clone $semua)
            ->when($bulan, fn ($q) => $q->whereYear('tanggal', substr($bulan, 0, 4))->whereMonth('tanggal', substr($bulan, 5, 2)))
            ->orderByDesc('tanggal')->orderBy('id')
            ->get();

        // Nama dihitung tanpa membedakan huruf besar kecil, tetapi ditampilkan seperti tulisan pertamanya.
        $peringkat = $kegiatan->flatMap->hadir
            ->groupBy(fn ($nama) => mb_strtolower($nama))
            ->map(fn ($kelompok) => ['nama' => $kelompok->first(), 'jumlah' => $kelompok->count()])
            ->sortByDesc('jumlah')
            ->values();

        return view('pages.admin.pendampingan', [
            'periode' => $periode,
            'bulan' => $bulan,
            'daftarBulan' => $daftarBulan,
            'perTanggal' => $kegiatan->groupBy(fn ($k) => $k->tanggal->toDateString()),
            'jumlahKegiatan' => $kegiatan->count(),
            'jumlahKehadiran' => $kegiatan->sum(fn ($k) => count($k->hadir)),
            'peringkat' => $peringkat,
            'botAktif' => BotPendampingan::aktif(),
        ]);
    }

    /** Menempel teks rekap langsung di halaman, dengan cara baca yang sama seperti bot Telegram. */
    public function tempel(Request $request, Periode $periode)
    {
        $teks = $request->validate(
            ['teks' => ['required', 'string', 'max:50000']],
            ['teks.required' => 'Tempel teks rekap dulu.', 'teks.max' => 'Teks rekap terlalu panjang.']
        )['teks'];

        $hasil = RekapPendampingan::baca($teks, $periode->tahunBerjalan());

        if ($hasil['kegiatan'] === []) {
            return back()->withInput()->with('error', 'Tidak ada kegiatan yang terbaca. Pastikan ada baris TANGGAL, nama kegiatan diawali *, lalu baris Hadir :');
        }

        $simpan = RekapPendampingan::simpan($hasil['kegiatan'], 'manual');

        return back()
            ->with('success', count($hasil['kegiatan'])." kegiatan tersimpan: {$simpan['baru']} baru, {$simpan['diperbarui']} diperbarui.")
            ->with('abaikan', $hasil['abaikan']);
    }

    public function update(Request $request, Periode $periode, int $id)
    {
        $kegiatan = PendampinganKegiatan::milikPeriode($periode)->findOrFail($id);
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'nama' => ['required', 'string', 'max:255', Rule::unique('pendampingan_kegiatan')->where('tanggal', rescue(fn () => Carbon::parse((string) $request->input('tanggal'))->toDateString(), '', false))->ignore($kegiatan->id)],
            'hadir' => ['nullable', 'string', 'max:5000'],
            'sambutan' => ['nullable', 'string', 'max:1000'],
        ], [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'nama.required' => 'Nama kegiatan wajib diisi.',
            'nama.unique' => 'Kegiatan dengan nama ini sudah ada di tanggal tersebut.',
        ]);

        $kegiatan->update([
            'tanggal' => $data['tanggal'],
            'nama' => $data['nama'],
            'hadir' => array_values(array_unique(RekapPendampingan::pisahNama($data['hadir'] ?? ''))),
            'sambutan' => array_values(array_unique(RekapPendampingan::pisahNama($data['sambutan'] ?? ''))),
        ]);

        return back()->with('success', 'Kegiatan diperbarui.');
    }

    public function destroy(Periode $periode, int $id)
    {
        PendampinganKegiatan::milikPeriode($periode)->findOrFail($id)->delete();

        return back()->with('success', 'Kegiatan dihapus.');
    }
}
