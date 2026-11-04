<?php

namespace App\Http\Controllers\Kepengurusan;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\Periode;
use App\Models\ProgramKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        ]);

        $fotoLama = $kegiatan->foto;

        if ($request->hasFile('foto')) {
            $kegiatan->foto = $request->file('foto')->store('kegiatan', 'public');
        } elseif ($request->boolean('hapus_foto')) {
            $kegiatan->foto = null;
        }

        if ($request->exists('link')) {
            $kegiatan->link = $data['link'] ?? null;
        }
        $kegiatan->save();

        if ($fotoLama && $fotoLama !== $kegiatan->foto) {
            Storage::disk('public')->delete($fotoLama);
        }

        return response()->json(['status' => true, 'message' => 'Dokumentasi berhasil disimpan.']);
    }

    public function destroy(Periode $periode, $id)
    {
        $kegiatan = Kegiatan::milikPeriode($periode)->findOrFail($id);
        $foto = $kegiatan->foto;
        $kegiatan->delete();

        if ($foto) {
            Storage::disk('public')->delete($foto);
        }

        return response()->json(['status' => true, 'message' => 'Kegiatan berhasil dihapus.']);
    }
}
