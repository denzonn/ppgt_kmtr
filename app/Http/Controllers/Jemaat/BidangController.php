<?php

namespace App\Http\Controllers\Jemaat;

use App\Http\Controllers\Controller;
use App\Models\JemaatBidang;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BidangController extends Controller
{
    public function index()
    {
        return view('pages.jemaat.bidang');
    }

    public function getData(Request $request)
    {
        return response()->json([
            'status' => true,
            'data' => JemaatBidang::where('jemaat_id', $request->user()->jemaat_id)->withCount('pengurus')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        JemaatBidang::create($this->validasi($request) + ['jemaat_id' => $request->user()->jemaat_id]);

        return response()->json(['status' => true, 'message' => 'Bidang berhasil ditambahkan.']);
    }

    public function update(Request $request, $id)
    {
        $bidang = $this->milikJemaat($request, $id);
        $bidang->update($this->validasi($request, $bidang->id, $bidang->pengurus()->count()));

        return response()->json(['status' => true, 'message' => 'Bidang berhasil diperbarui.']);
    }

    public function destroy(Request $request, $id)
    {
        $bidang = $this->milikJemaat($request, $id);

        if ($bidang->pengurus()->exists()) {
            return response()->json(['status' => false, 'message' => 'Bidang masih punya pengurus dan belum dapat dihapus.'], 422);
        }

        $bidang->delete();

        return response()->json(['status' => true, 'message' => 'Bidang berhasil dihapus.']);
    }

    private function milikJemaat(Request $request, $id): JemaatBidang
    {
        return JemaatBidang::where('jemaat_id', $request->user()->jemaat_id)->findOrFail($id);
    }

    private function validasi(Request $request, ?int $abaikanId = null, int $terisi = 0): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:150',
                Rule::unique('jemaat_bidang', 'nama')->where('jemaat_id', $request->user()->jemaat_id)->ignore($abaikanId)],
            'jumlah_anggota' => ['required', 'integer', 'min:' . max(1, $terisi), 'max:999'],
        ], [
            'nama.required' => 'Nama bidang wajib diisi.',
            'nama.max' => 'Nama bidang maksimal 150 karakter.',
            'nama.unique' => 'Nama bidang sudah ada di jemaat ini.',
            'jumlah_anggota.required' => 'Jumlah orang wajib diisi.',
            'jumlah_anggota.integer' => 'Jumlah orang harus berupa angka.',
            'jumlah_anggota.min' => $terisi > 1
                ? "Bidang ini sudah punya {$terisi} pengurus, jadi jumlah orang minimal {$terisi}."
                : 'Jumlah orang minimal 1.',
            'jumlah_anggota.max' => 'Jumlah orang maksimal 999.',
        ]);
    }
}
