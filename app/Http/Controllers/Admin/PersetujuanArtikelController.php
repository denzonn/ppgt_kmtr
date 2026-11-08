<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Support\KontenArtikel;
use Illuminate\Http\Request;

class PersetujuanArtikelController extends Controller
{
    public function index()
    {
        return view('pages.admin.persetujuan-artikel');
    }

    public function getData(Request $request)
    {
        $data = Artikel::with('jemaat:id,nama')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest('id')
            ->paginate(12);

        $jumlah = Artikel::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return response()->json([
            'status' => true,
            'data' => collect($data->items())->map(fn (Artikel $a) => [
                'id' => $a->id,
                'judul' => $a->judul,
                'penulis' => $a->penulis,
                'jemaat' => $a->jemaat?->nama,
                'foto' => $a->foto,
                'isi' => $a->isi,
                'ringkasan' => KontenArtikel::ringkasan($a->isi),
                'tanggal' => $a->created_at->timezone('Asia/Makassar')->translatedFormat('d M Y'),
                'status' => $a->status,
                'alasan_tolak' => $a->alasan_tolak,
                'diperiksa_oleh' => $a->diperiksa_oleh,
                'versi' => $a->updated_at->toISOString(),
            ]),
            'statistik' => [
                'semua' => (int) $jumlah->sum(),
                'menunggu' => (int) ($jumlah['menunggu'] ?? 0),
                'disetujui' => (int) ($jumlah['disetujui'] ?? 0),
                'ditolak' => (int) ($jumlah['ditolak'] ?? 0),
            ],
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
        ]);
    }

    public function setujui(Request $request, Artikel $artikel)
    {
        return $this->periksa($request, $artikel, ['status' => 'disetujui', 'alasan_tolak' => null], 'Artikel berhasil disetujui.');
    }

    public function tolak(Request $request, Artikel $artikel)
    {
        $data = $request->validate(['alasan' => ['required', 'string', 'max:1000']], [
            'alasan.required' => 'Alasan penolakan wajib diisi.',
            'alasan.max' => 'Alasan penolakan maksimal 1.000 karakter.',
        ]);

        return $this->periksa($request, $artikel, ['status' => 'ditolak', 'alasan_tolak' => $data['alasan']], 'Artikel ditolak.');
    }

    private function periksa(Request $request, Artikel $artikel, array $hasil, string $pesan)
    {
        if ($artikel->status !== 'menunggu') {
            return response()->json(['status' => false, 'message' => 'Artikel ini sudah diperiksa.'], 422);
        }

        // Mencegah persetujuan atas isi yang belum dibaca karena penulis baru saja mengubahnya.
        if ($request->input('versi') !== $artikel->updated_at->toISOString()) {
            return response()->json(['status' => false, 'message' => 'Artikel baru saja diubah penulis. Silakan baca ulang.'], 409);
        }

        $artikel->update($hasil + ['diperiksa_oleh' => $request->user()->name, 'diperiksa_pada' => now()]);

        return response()->json(['status' => true, 'message' => $pesan]);
    }
}
