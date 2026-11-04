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
        return view('pages.admin.jabatan', ['periode' => $periode]);
    }

    public function getData(Request $request, Periode $periode)
    {
        $search = trim((string) $request->input('search', ''));

        $paginator = $periode->jabatans()
            ->when($search !== '', fn ($q) => $q->where('nama', 'like', '%' . $search . '%'))
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
        return $request->validate([
            'nama' => ['required', 'string', 'max:255',
                Rule::unique('jabatans', 'nama')->where('periode_id', $periode->id)->ignore($ignoreId)],
            'terkait_bidang' => ['required', 'boolean'],
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

        if ($data['terkait_bidang'] !== $jabatan->terkait_bidang && $jabatan->pengurus()->exists()) {
            return response()->json([
                'message' => 'Status terkait bidang tidak bisa diubah karena jabatan sudah punya pengurus.',
                'errors' => ['terkait_bidang' => ['Status terkait bidang tidak bisa diubah karena jabatan sudah punya pengurus.']],
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
