<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Linktree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LinktreeController extends Controller
{
    public function index()
    {
        return view('pages.admin.linktree');
    }

    public function getData()
    {
        return response()->json(['status' => true, 'data' => Linktree::terurut()->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request, $request->input('jenis') === 'judul');

        Linktree::create($data + ['urutan' => (int) Linktree::max('urutan') + 1]);

        return response()->json(['status' => true, 'message' => 'Berhasil ditambahkan.']);
    }

    public function update(Request $request, Linktree $linktree)
    {
        $linktree->update($this->validasi($request, $linktree->link === null));

        return response()->json(['status' => true, 'message' => 'Berhasil diperbarui.']);
    }

    public function destroy(Linktree $linktree)
    {
        $linktree->delete();

        return response()->json(['status' => true, 'message' => 'Berhasil dihapus.']);
    }

    public function urutan(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'distinct', 'exists:linktree,id'],
        ], [
            'ids.*.distinct' => 'Daftar urutan tidak valid.',
            'ids.*.exists' => 'Ada item yang sudah dihapus. Silakan muat ulang halaman.',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $posisi => $id) {
                Linktree::find($id)->update(['urutan' => $posisi + 1]);
            }
        });

        return response()->json(['status' => true, 'message' => 'Urutan berhasil disimpan.']);
    }

    private function validasi(Request $request, bool $judul): array
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            // Link tampil di halaman publik, jadi hanya http dan https yang boleh.
            'link' => $judul ? [] : ['required', 'url:http,https', 'max:2048'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 150 karakter.',
            'link.required' => 'Link wajib diisi.',
            'link.url' => 'Masukkan URL yang valid, misalnya https://contoh.com.',
            'link.max' => 'Link maksimal 2.048 karakter.',
        ]);

        return ['nama' => $validated['nama'], 'link' => $judul ? null : $validated['link']];
    }
}
