<?php

namespace App\Http\Controllers\Kepengurusan;

use App\Http\Controllers\Controller;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BidangController extends Controller
{
    public function index(Periode $periode)
    {
        return view('pages.admin.bidang', ['periode' => $periode]);
    }

    public function getData(Request $request, Periode $periode)
    {
        $search = trim((string) $request->input('search', ''));

        $paginator = $periode->bidangs()
            ->withCount(['pengurus', 'jabatans', 'programKerjas'])
            ->when($search !== '', fn ($q) => $q->where('nama', 'like', '%' . $search . '%'))
            ->orderBy('id')
            ->paginate(25);

        return response()->json([
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
        ]);
    }

    private function validated(Request $request, Periode $periode, ?int $ignoreId = null, int $terisi = 0): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255',
                Rule::unique('bidangs', 'nama')->where('periode_id', $periode->id)->ignore($ignoreId)],
            'jumlah_anggota' => ['required', 'integer', 'min:' . max(1, $terisi), 'max:999'],
        ], [
            'nama.required' => 'Nama bidang wajib diisi.',
            'nama.max' => 'Nama bidang maksimal 255 karakter.',
            'nama.unique' => 'Bidang dengan nama tersebut sudah ada pada periode ini.',
            'jumlah_anggota.required' => 'Jumlah orang dalam bidang wajib diisi.',
            'jumlah_anggota.integer' => 'Jumlah orang harus berupa angka.',
            'jumlah_anggota.min' => $terisi > 1
                ? "Bidang ini sudah punya {$terisi} pengurus, jadi jumlah orang minimal {$terisi}."
                : 'Jumlah orang minimal 1.',
            'jumlah_anggota.max' => 'Jumlah orang maksimal 999.',
        ]);
    }

    public function store(Request $request, Periode $periode)
    {
        $periode->bidangs()->create($this->validated($request, $periode));

        return response()->json(['status' => true, 'message' => 'Bidang berhasil ditambahkan.']);
    }

    public function show(Periode $periode, $id)
    {
        return response()->json($periode->bidangs()->findOrFail($id));
    }

    public function update(Request $request, Periode $periode, $id)
    {
        $bidang = $periode->bidangs()->findOrFail($id);
        $bidang->update($this->validated($request, $periode, $bidang->id, $bidang->pengurus()->count()));

        return response()->json(['status' => true, 'message' => 'Bidang berhasil diperbarui.']);
    }

    public function destroy(Periode $periode, $id)
    {
        $item = $periode->bidangs()->findOrFail($id);

        if ($item->jabatans()->exists()) {
            return response()->json(['status' => false, 'message' => 'Bidang tidak bisa dihapus karena masih memiliki jabatan.'], 422);
        }

        if ($item->programKerjas()->exists()) {
            return response()->json(['status' => false, 'message' => 'Bidang tidak bisa dihapus karena sudah memiliki program kerja.'], 422);
        }

        $item->delete();

        return response()->json(['status' => true, 'message' => 'Bidang berhasil dihapus.']);
    }
}
