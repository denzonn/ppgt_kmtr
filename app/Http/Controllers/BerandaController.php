<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\DataAnggota;
use App\Models\Jemaat;
use App\Models\Kegiatan;
use App\Models\Periode;

class BerandaController extends Controller
{
    public function index()
    {
        // Saat dua periode bertemu di satu tahun, yang terbaru dipakai.
        $periode = Periode::where('tahun_mulai', '<=', now()->year)->orderByDesc('tahun_mulai')->first();
        $tahun = $periode?->tahunBerjalan() ?? now()->year;

        $bidangs = $periode
            ? $periode->bidangs()->orderBy('id')
                ->with(['programKerjas' => fn ($q) => $q->where('tahun', $tahun)->orderBy('nama')])
                ->get()
            : collect();

        $kegiatans = $periode
            ? Kegiatan::milikPeriode($periode)
                ->whereHas('programKerja', fn ($q) => $q->where('tahun', $tahun))
                ->with('programKerja.bidang')
                ->get()
                ->sortByDesc(fn ($k) => max($k->bulan))
                ->take(6)
            : collect();

        return view('pages.beranda', [
            'periode' => $periode,
            'tahun' => $tahun,
            'bidangs' => $bidangs,
            'kegiatans' => $kegiatans,
            'jemaats' => Jemaat::orderBy('nama')->pluck('nama'),
            'artikels' => Artikel::tayang()->with('jemaat:id,nama')->terbaru()->take(3)->get(),
            'anggotaAktif' => DataAnggota::where('status_anggota', 'Aktif')->count(),
        ]);
    }
}
