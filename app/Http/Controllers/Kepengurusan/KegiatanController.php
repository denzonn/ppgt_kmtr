<?php

namespace App\Http\Controllers\Kepengurusan;

use App\Support\FileUpload;
use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\Periode;
use App\Models\ProgramKerja;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'anggaran' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'bulan' => ['required', 'array', 'min:1'],
            'bulan.*' => ['integer', 'between:1,12', 'distinct'],
        ], [
            'nama.required' => 'Nama kegiatan wajib diisi.',
            'nama.max' => 'Nama kegiatan maksimal 255 karakter.',
            'deskripsi.required' => 'Deskripsi wajib diisi.',
            'anggaran.required' => 'Anggaran wajib diisi.',
            'anggaran.integer' => 'Anggaran harus berupa angka bulat.',
            'anggaran.min' => 'Anggaran tidak boleh negatif.',
            'anggaran.max' => 'Anggaran terlalu besar.',
            'bulan.required' => 'Pilih minimal satu bulan.',
            'bulan.min' => 'Pilih minimal satu bulan.',
            'bulan.*.integer' => 'Bulan tidak valid.',
            'bulan.*.between' => 'Bulan tidak valid.',
            'bulan.*.distinct' => 'Bulan tidak boleh duplikat.',
        ]);
        $data['bulan'] = array_map('intval', $data['bulan']);
        sort($data['bulan']);

        return $data;
    }

    public function store(Request $request, Periode $periode, $programId)
    {
        $program = ProgramKerja::milikPeriode($periode)->findOrFail($programId);
        $program->kegiatans()->create($this->validated($request));

        return response()->json(['status' => true, 'message' => 'Kegiatan berhasil ditambahkan.']);
    }

    public function update(Request $request, Periode $periode, $id)
    {
        $kegiatan = Kegiatan::milikPeriode($periode)->findOrFail($id);
        $kegiatan->update($this->validated($request));

        return response()->json(['status' => true, 'message' => 'Kegiatan berhasil diperbarui.']);
    }

    public function dokumentasi(Request $request, Periode $periode, $id)
    {
        $kegiatan = Kegiatan::milikPeriode($periode)->findOrFail($id);

        $data = $request->validate([
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'link' => ['nullable', 'url:http,https', 'max:2048'],
            'hapus_foto' => ['nullable', 'boolean'],
        ], [
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat JPG atau PNG.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
            'link.url' => 'Link harus berupa URL yang valid (http atau https).',
            'link.max' => 'Link maksimal 2048 karakter.',
        ]);

        $fotoLama = $kegiatan->foto;

        if ($request->hasFile('foto')) {
            $kegiatan->foto = FileUpload::simpan($request->file('foto'), 'kegiatan');
        } elseif ($request->boolean('hapus_foto')) {
            $kegiatan->foto = null;
        }

        if ($request->exists('link')) {
            $kegiatan->link = $data['link'] ?? null;
        }
        $kegiatan->save();

        if ($fotoLama && $fotoLama !== $kegiatan->foto) {
            FileUpload::hapus($fotoLama);
        }

        return response()->json(['status' => true, 'message' => 'Dokumentasi berhasil disimpan.']);
    }

    public function destroy(Periode $periode, $id)
    {
        $kegiatan = Kegiatan::milikPeriode($periode)->findOrFail($id);
        $foto = $kegiatan->foto;
        $kegiatan->delete();

        if ($foto) {
            FileUpload::hapus($foto);
        }

        return response()->json(['status' => true, 'message' => 'Kegiatan berhasil dihapus.']);
    }
}
