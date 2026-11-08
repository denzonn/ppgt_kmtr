<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\Jemaat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArtikelController extends Controller
{
    public function index(Request $request)
    {
        $cari = is_string($request->query('cari')) ? trim($request->query('cari')) : '';
        $jemaatId = $request->integer('jemaat');

        $artikels = Artikel::tayang()
            ->with('jemaat:id,nama')
            ->when($cari !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('judul', 'like', "%{$cari}%")
                ->orWhere('penulis', 'like', "%{$cari}%")))
            ->when($jemaatId, fn ($q) => $q->where('jemaat_id', $jemaatId))
            ->terbaru()
            ->paginate(9)
            ->withQueryString();

        return view('pages.artikel.daftar', [
            'artikels' => $artikels,
            'cari' => $cari,
            'jemaatId' => $jemaatId,
            'jemaats' => Jemaat::orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function show(int $id, ?string $slug = null)
    {
        $artikel = Artikel::tayang()->with('jemaat:id,nama')->findOrFail($id);

        // Link lama tetap jalan walau judulnya sudah diubah.
        if ($slug !== (Str::slug($artikel->judul) ?: null)) {
            return redirect()->to($artikel->urlBaca(), 301);
        }

        return view('pages.artikel.baca', [
            'artikel' => $artikel,
            'lainnya' => Artikel::tayang()->with('jemaat:id,nama')->whereKeyNot($id)->terbaru()->take(3)->get(),
        ]);
    }
}
