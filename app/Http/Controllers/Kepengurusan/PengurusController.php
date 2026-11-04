<?php

namespace App\Http\Controllers\Kepengurusan;

use App\Http\Controllers\Controller;
use App\Models\DataAnggota;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengurusController extends Controller
{
    public function index(Periode $periode)
    {
        return view('pages.admin.pengurus', [
            'periode' => $periode,
            'jabatans' => $periode->jabatans()->orderBy('nama')->get(['id', 'nama', 'terkait_bidang']),
            'bidangs' => $periode->bidangs()->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function getData(Request $request, Periode $periode)
    {
        $search = trim((string) $request->input('search', ''));

        $paginator = $periode->pengurus()
            ->with(['jabatan:id,nama', 'bidang:id,nama', 'anggota:id,nama,no_anggota'])
            ->when($search !== '', fn ($q) => $q->whereHas('anggota', fn ($a) => $a->where('nama', 'like', '%' . $search . '%')))
            ->latest('id')
            ->paginate(25);

        return response()->json([
            'data' => collect($paginator->items())->map(fn ($p) => [
                'id' => $p->id,
                'nama' => $p->anggota->nama,
                'no_anggota' => $p->anggota->no_anggota,
                'jabatan' => $p->jabatan->nama,
                'bidang' => $p->bidang?->nama,
            ])->values(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
        ]);
    }

    public function anggota(Request $request, Periode $periode)
    {
        $search = trim((string) $request->input('search', ''));

        $rows = DataAnggota::with('jemaat:id,nama')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('nama', 'like', '%' . $search . '%')
                ->orWhere('no_anggota', 'like', '%' . $search . '%')))
            ->orderBy('nama')
            ->limit(20)
            ->get();

        // Kolom string `jemaat` menimpa relasi, jadi relasi dibaca lewat getRelation.
        return response()->json($rows->map(fn ($a) => [
            'id' => $a->id,
            'nama' => $a->nama,
            'no_anggota' => $a->no_anggota,
            'jemaat' => $a->getRelation('jemaat')?->nama,
        ])->values());
    }

    private function validated(Request $request, Periode $periode, ?int $ignoreId = null): array
    {
        $jid = $request->input('jabatan_id');
        $jabatan = is_scalar($jid) ? $periode->jabatans()->find($jid) : null;

        return $request->validate([
            'jabatan_id' => ['required', 'integer', Rule::exists('jabatans', 'id')->where('periode_id', $periode->id)],
            'data_anggota_id' => ['required', 'integer', 'exists:data_anggota,id',
                Rule::unique('pengurus', 'data_anggota_id')->where('periode_id', $periode->id)->ignore($ignoreId)],
            'bidang_id' => [
                $jabatan?->terkait_bidang ? 'required' : 'prohibited',
                'nullable',
                'integer',
                Rule::exists('bidangs', 'id')->where('periode_id', $periode->id),
            ],
        ], [
            'bidang_id.required' => 'Bidang wajib diisi untuk jabatan ini.',
            'bidang_id.prohibited' => 'Jabatan ini tidak terkait bidang.',
            'data_anggota_id.required' => 'Anggota wajib dipilih.',
            'data_anggota_id.integer' => 'Anggota tidak valid.',
            'data_anggota_id.exists' => 'Anggota tidak ditemukan.',
            'jabatan_id.required' => 'Jabatan wajib dipilih.',
            'jabatan_id.integer' => 'Jabatan tidak valid.',
            'jabatan_id.exists' => 'Jabatan tidak ditemukan pada periode ini.',
            'bidang_id.integer' => 'Bidang tidak valid.',
            'bidang_id.exists' => 'Bidang tidak ditemukan pada periode ini.',
            'data_anggota_id.unique' => 'Anggota ini sudah menjadi pengurus di periode ini.',
        ]);
    }

    public function store(Request $request, Periode $periode)
    {
        $periode->pengurus()->create($this->validated($request, $periode));

        return response()->json(['status' => true, 'message' => 'Pengurus berhasil ditambahkan.']);
    }

    public function show(Periode $periode, $id)
    {
        $p = $periode->pengurus()->with('anggota:id,nama,no_anggota')->findOrFail($id);

        return response()->json([
            'id' => $p->id,
            'jabatan_id' => $p->jabatan_id,
            'bidang_id' => $p->bidang_id,
            'data_anggota_id' => $p->data_anggota_id,
            'nama' => $p->anggota->nama,
            'no_anggota' => $p->anggota->no_anggota,
        ]);
    }

    public function update(Request $request, Periode $periode, $id)
    {
        $pengurus = $periode->pengurus()->findOrFail($id);
        $data = $this->validated($request, $periode, $pengurus->id);
        $data['bidang_id'] = $data['bidang_id'] ?? null;
        $pengurus->update($data);

        return response()->json(['status' => true, 'message' => 'Pengurus berhasil diperbarui.']);
    }

    public function destroy(Periode $periode, $id)
    {
        $periode->pengurus()->findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Pengurus berhasil dihapus.']);
    }
}
