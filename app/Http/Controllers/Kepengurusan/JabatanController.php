<?php

namespace App\Http\Controllers\Kepengurusan;

use App\Http\Controllers\Controller;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JabatanController extends Controller
{
    public function index(Periode $periode)
    {
        return view('pages.admin.jabatan', [
            'periode' => $periode,
            'bidangs' => $periode->bidangs()->orderBy('id')->get(['id', 'nama']),
        ]);
    }

    public function getData(Request $request, Periode $periode)
    {
        $search = trim((string) $request->input('search', ''));

        $paginator = $periode->jabatans()
            ->with('bidang:id,nama')
            ->withCount('pengurus')
            ->when($search !== '', fn ($q) => $q->where('nama', 'like', '%' . $search . '%'))
            ->when($request->filled('bidang_id'), fn ($q) => $q->where('bidang_id', $request->input('bidang_id')))
            ->orderBy('bidang_id') // Jabatan yang belum punya bidang muncul paling atas.
            ->orderBy('nama')
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

    private function validated(Request $request, Periode $periode, ?int $ignoreId = null): array
    {
        $bidangId = $request->input('bidang_id');

        return $request->validate([
            'bidang_id' => ['required', 'integer', Rule::exists('bidangs', 'id')->where('periode_id', $periode->id)],
            'nama' => ['required', 'string', 'max:255',
                Rule::unique('jabatans', 'nama')->where('bidang_id', is_scalar($bidangId) ? $bidangId : null)->ignore($ignoreId)],
        ], [
            'bidang_id.required' => 'Bidang wajib dipilih.',
            'bidang_id.integer' => 'Bidang tidak valid.',
            'bidang_id.exists' => 'Bidang tidak ditemukan pada periode ini.',
            'nama.required' => 'Nama jabatan wajib diisi.',
            'nama.max' => 'Nama jabatan maksimal 255 karakter.',
            'nama.unique' => 'Jabatan dengan nama tersebut sudah ada di bidang ini.',
        ]);
    }

    public function store(Request $request, Periode $periode)
    {
        $periode->jabatans()->create($this->validated($request, $periode));

        return response()->json(['status' => true, 'message' => 'Jabatan berhasil ditambahkan.']);
    }

    public function show(Periode $periode, $id)
    {
        return response()->json($periode->jabatans()->findOrFail($id));
    }

    public function update(Request $request, Periode $periode, $id)
    {
        $jabatan = $periode->jabatans()->findOrFail($id);
        $data = $this->validated($request, $periode, $jabatan->id);

        $pindahBidang = $jabatan->bidang_id !== null && (int) $data['bidang_id'] !== $jabatan->bidang_id;
        if ($pindahBidang && $jabatan->pengurus()->exists()) {
            return response()->json([
                'message' => 'Bidang tidak bisa diubah karena jabatan sudah punya pengurus.',
                'errors' => ['bidang_id' => ['Bidang tidak bisa diubah karena jabatan sudah punya pengurus.']],
            ], 422);
        }

        $jabatan->update($data);

        return response()->json(['status' => true, 'message' => 'Jabatan berhasil diperbarui.']);
    }

    public function destroy(Periode $periode, $id)
    {
        $item = $periode->jabatans()->findOrFail($id);

        if ($item->pengurus()->exists()) {
            return response()->json(['status' => false, 'message' => 'Jabatan tidak bisa dihapus karena sudah dipakai pengurus.'], 422);
        }

        $item->delete();

        return response()->json(['status' => true, 'message' => 'Jabatan berhasil dihapus.']);
    }
}
