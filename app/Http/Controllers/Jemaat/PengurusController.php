<?php

namespace App\Http\Controllers\Jemaat;

use App\Http\Controllers\Controller;
use App\Models\DataAnggota;
use App\Models\JemaatBidang;
use App\Models\JemaatPengurus;
use App\Support\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PengurusController extends Controller
{
    public function index()
    {
        return view('pages.jemaat.pengurus');
    }

    public function getData(Request $request)
    {
        $jemaatId = $request->user()->jemaat_id;

        $data = JemaatPengurus::milikJemaat($jemaatId)
            ->with(['bidang:id,nama', 'anggota:id,nama,no_anggota'])
            ->orderBy('jemaat_bidang_id')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'bidang' => JemaatBidang::where('jemaat_id', $jemaatId)->withCount('pengurus')->orderBy('nama')->get()
                ->map(fn (JemaatBidang $b) => [
                    'id' => $b->id,
                    'nama' => $b->nama,
                    'jumlah_anggota' => $b->jumlah_anggota,
                    'terisi' => $b->pengurus_count,
                ]),
            'data' => $data->map(fn (JemaatPengurus $p) => [
                'id' => $p->id,
                'jemaat_bidang_id' => $p->jemaat_bidang_id,
                'bidang' => $p->bidang->nama,
                'data_anggota_id' => $p->data_anggota_id,
                'nama' => $p->anggota->nama,
                'no_anggota' => $p->anggota->no_anggota,
                'jabatan' => $p->jabatan,
                'foto' => $p->foto,
            ]),
        ]);
    }

    public function anggota(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        return response()->json(
            DataAnggota::where('jemaat_id', $request->user()->jemaat_id)
                ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                    ->where('nama', 'like', "%{$search}%")
                    ->orWhere('no_anggota', 'like', "%{$search}%")))
                ->orderBy('nama')
                ->limit(20)
                ->get(['id', 'nama', 'no_anggota'])
        );
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        if ($request->hasFile('foto')) {
            $data['foto'] = FileUpload::simpan($request->file('foto'), 'jemaat-pengurus');
        }

        JemaatPengurus::create($data);

        return response()->json(['status' => true, 'message' => 'Pengurus berhasil ditambahkan.']);
    }

    public function update(Request $request, $id)
    {
        $pengurus = $this->milikJemaat($request, $id);
        $data = $this->validasi($request, $pengurus->id);
        $fotoLama = $pengurus->foto;

        if ($request->hasFile('foto')) {
            $data['foto'] = FileUpload::simpan($request->file('foto'), 'jemaat-pengurus');
        } elseif ($request->boolean('hapus_foto')) {
            $data['foto'] = null;
        }

        $pengurus->update($data);

        if ($fotoLama !== $pengurus->foto) {
            FileUpload::hapus($fotoLama);
        }

        return response()->json(['status' => true, 'message' => 'Pengurus berhasil diperbarui.']);
    }

    public function destroy(Request $request, $id)
    {
        $pengurus = $this->milikJemaat($request, $id);
        $pengurus->delete();
        FileUpload::hapus($pengurus->foto);

        return response()->json(['status' => true, 'message' => 'Pengurus berhasil dihapus.']);
    }

    private function milikJemaat(Request $request, $id): JemaatPengurus
    {
        return JemaatPengurus::milikJemaat($request->user()->jemaat_id)->findOrFail($id);
    }

    private function validasi(Request $request, ?int $abaikanId = null): array
    {
        $jemaatId = $request->user()->jemaat_id;

        $data = $request->validate([
            'jemaat_bidang_id' => ['required', 'integer', Rule::exists('jemaat_bidang', 'id')->where('jemaat_id', $jemaatId)],
            'data_anggota_id' => ['required', 'integer',
                Rule::exists('data_anggota', 'id')->where('jemaat_id', $jemaatId),
                Rule::unique('jemaat_pengurus', 'data_anggota_id')->ignore($abaikanId)],
            'jabatan' => ['required', 'string', 'max:150'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], [
            'jemaat_bidang_id.required' => 'Bidang wajib dipilih.',
            'jemaat_bidang_id.exists' => 'Bidang tidak ditemukan.',
            'data_anggota_id.required' => 'Anggota wajib dipilih.',
            'data_anggota_id.exists' => 'Anggota tidak ditemukan di jemaat ini.',
            'data_anggota_id.unique' => 'Anggota ini sudah menjadi pengurus.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'jabatan.max' => 'Jabatan maksimal 150 karakter.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat JPG, PNG, atau GIF.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        // ponytail: tanpa lock, dua penyimpanan serentak bisa melewati batas; pakai lockForUpdate kalau pengisinya banyak.
        $bidang = JemaatBidang::find($data['jemaat_bidang_id']);
        $terisi = $bidang->pengurus()->when($abaikanId, fn ($q) => $q->whereKeyNot($abaikanId))->count();

        if ($bidang->jumlah_anggota !== null && $terisi >= $bidang->jumlah_anggota) {
            throw ValidationException::withMessages([
                'jemaat_bidang_id' => "{$bidang->nama} sudah penuh ({$bidang->jumlah_anggota} orang).",
            ]);
        }

        unset($data['foto']);

        return $data;
    }
}
