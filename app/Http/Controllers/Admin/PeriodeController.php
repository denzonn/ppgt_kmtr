<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PeriodeController extends Controller
{
    public function index()
    {
        return view('pages.admin.periode');
    }

    public function getData(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $paginator = Periode::query()
            ->withCount('users')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('tahun_mulai', 'like', '%' . $search . '%')
                ->orWhere('tahun_selesai', 'like', '%' . $search . '%')))
            ->orderByDesc('tahun_mulai')
            ->orderByDesc('tahun_selesai')
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

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'tahun_mulai' => ['required', 'integer', 'between:1900,2100'],
            'tahun_selesai' => ['required', 'integer', 'between:1900,2100', 'gte:tahun_mulai'],
        ]);

        $kembar = Periode::where($data)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists();
        if ($kembar) {
            throw ValidationException::withMessages(['tahun_mulai' => 'Periode dengan tahun tersebut sudah ada.']);
        }

        return $data;
    }

    public function store(Request $request)
    {
        Periode::create($this->validated($request));

        return response()->json(['status' => true, 'message' => 'Periode berhasil ditambahkan.']);
    }

    public function show($id)
    {
        return response()->json(Periode::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $periode = Periode::findOrFail($id);
        $periode->update($this->validated($request, $periode->id));

        return response()->json(['status' => true, 'message' => 'Periode berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        $periode = Periode::findOrFail($id);

        if ($periode->users()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Periode masih dipakai pengguna dan belum dapat dihapus.',
            ], 422);
        }

        if ($periode->bidangs()->exists() || $periode->jabatans()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Periode masih memiliki bidang atau jabatan dan belum dapat dihapus.',
            ], 422);
        }

        $periode->delete();

        return response()->json(['status' => true, 'message' => 'Periode berhasil dihapus.']);
    }
}
