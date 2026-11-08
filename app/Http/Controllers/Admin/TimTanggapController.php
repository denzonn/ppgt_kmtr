<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataAnggota;
use App\Models\Jemaat;
use App\Support\AuditLogger;
use App\Support\NomorWa;
use Illuminate\Http\Request;

class TimTanggapController extends Controller
{
    private const GOLONGAN = ['A', 'B', 'AB', 'O'];

    public function index()
    {
        return view('pages.admin.tim-tanggap', [
            'golongan' => self::GOLONGAN,
            'jemaats' => Jemaat::orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function getData(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $golongan = $request->input('golongan');

        // Kolom dibatasi: Tim Tanggap tidak perlu NIK, alamat, atau data pribadi lainnya.
        $data = DataAnggota::query()
            ->with('jemaat:id,nama')
            ->select(['id', 'jemaat_id', 'no_anggota', 'nama', 'jenis_kelamin', 'status_anggota', 'golongan_darah', 'golongan_darah_rhesus', 'telepon'])
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('nama', 'like', "%{$search}%")
                ->orWhere('no_anggota', 'like', "%{$search}%")))
            ->when($golongan === 'kosong', fn ($q) => $q->where(fn ($w) => $w->whereNull('golongan_darah')->orWhere('golongan_darah', '')))
            ->when($golongan && $golongan !== 'kosong', fn ($q) => $q->where('golongan_darah', $golongan))
            ->when($request->filled('rhesus'), fn ($q) => $q->where('golongan_darah_rhesus', $request->input('rhesus')))
            ->when($request->filled('jemaat'), fn ($q) => $q->where('jemaat_id', $request->input('jemaat')))
            ->when($request->boolean('punya_wa'), fn ($q) => $q->whereNotNull('telepon')->whereRaw("TRIM(telepon) <> ''"))
            ->orderBy('nama')
            ->paginate(25);

        return response()->json([
            'status' => true,
            // Kolom string `jemaat` menimpa relasi, jadi relasi dibaca lewat getRelation.
            'data' => collect($data->items())->map(fn (DataAnggota $a) => [
                'id' => $a->id,
                'nama' => $a->nama,
                'no_anggota' => $a->no_anggota,
                'jemaat' => $a->getRelation('jemaat')?->nama,
                'jenis_kelamin' => $a->jenis_kelamin,
                'status' => $a->status_anggota,
                'golongan' => $a->golongan_darah ?: null,
                'rhesus' => $a->golongan_darah_rhesus ?: null,
                'telepon' => $a->telepon ?: null,
                'wa' => NomorWa::dari($a->telepon),
            ]),
            'statistik' => $this->statistik(),
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
        ]);
    }

    /** Dicatat saat WhatsApp dibuka. Sistem tidak bisa tahu apakah pesannya benar-benar dikirim. */
    public function catatPesan(Request $request, DataAnggota $anggota)
    {
        AuditLogger::akses('kirim pesan wa', $request->user(), $anggota->nama);

        return response()->json(['status' => true]);
    }

    private function statistik(): array
    {
        $baris = DataAnggota::query()
            ->selectRaw("COALESCE(golongan_darah, '') as golongan, COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN TRIM(COALESCE(telepon, '')) <> '' THEN 1 ELSE 0 END) as punya_wa")
            ->groupByRaw("COALESCE(golongan_darah, '')")
            ->get()
            ->keyBy('golongan');

        $hasil = [];

        foreach ([...self::GOLONGAN, 'kosong'] as $kunci) {
            $item = $baris->get($kunci === 'kosong' ? '' : $kunci);
            $hasil[$kunci] = ['total' => (int) ($item->total ?? 0), 'punya_wa' => (int) ($item->punya_wa ?? 0)];
        }

        return $hasil;
    }
}
