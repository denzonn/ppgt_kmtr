<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PeriodeController extends Controller
{
    public function index()
    {
        return view('pages.admin.periode', ['bidangDefault' => Bidang::DEFAULT_NAMA]);
    }

    public function getData(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $paginator = Periode::query()
            ->withCount(['users', 'bidangs', 'pengurus'])
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
        ], [
            'tahun_mulai.required' => 'Tahun mulai wajib diisi.',
            'tahun_mulai.integer' => 'Tahun mulai harus berupa angka.',
            'tahun_mulai.between' => 'Tahun mulai harus antara 1900 dan 2100.',
            'tahun_selesai.required' => 'Tahun selesai wajib diisi.',
            'tahun_selesai.integer' => 'Tahun selesai harus berupa angka.',
            'tahun_selesai.between' => 'Tahun selesai harus antara 1900 dan 2100.',
            'tahun_selesai.gte' => 'Tahun selesai tidak boleh lebih kecil dari tahun mulai.',
        ]);

        $kembar = Periode::where($data)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists();
        if ($kembar) {
            throw ValidationException::withMessages(['tahun_mulai' => 'Periode dengan tahun tersebut sudah ada.']);
        }

        return $data;
    }

    public function store(Request $request)
    {
        $bidangs = $request->validate([
            'bidangs' => ['required', 'array', 'min:1'],
            'bidangs.*' => ['required', 'string', 'distinct', Rule::in(Bidang::DEFAULT_NAMA)],
        ], [
            'bidangs.required' => 'Pilih minimal satu bidang.',
            'bidangs.min' => 'Pilih minimal satu bidang.',
            'bidangs.*.in' => 'Bidang yang dipilih tidak valid.',
            'bidangs.*.distinct' => 'Bidang yang dipilih tidak boleh sama.',
        ])['bidangs'];
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $bidangs) {
            Periode::create($data)->bidangs()->createMany(
                array_map(fn ($nama) => ['nama' => $nama], $bidangs)
            );
        });

        return response()->json(['status' => true, 'message' => 'Periode berhasil ditambahkan.']);
    }

    public function show($id)
    {
        return response()->json(Periode::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $periode = Periode::findOrFail($id);
        $data = $this->validated($request, $periode->id);

        $diLuar = $periode->programKerjas()
            ->where(fn ($q) => $q->where('tahun', '<', $data['tahun_mulai'])->orWhere('tahun', '>', $data['tahun_selesai']))
            ->exists();
        if ($diLuar) {
            return response()->json([
                'status' => false,
                'message' => 'Rentang periode tidak bisa dipersempit karena ada program kerja di luar rentang baru.',
            ], 422);
        }

        $periode->update($data);

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

        if ($periode->inventarisRiwayat()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Periode masih memiliki riwayat inventaris dan belum dapat dihapus.',
            ], 422);
        }

        $periode->delete();

        return response()->json(['status' => true, 'message' => 'Periode berhasil dihapus.']);
    }
}
