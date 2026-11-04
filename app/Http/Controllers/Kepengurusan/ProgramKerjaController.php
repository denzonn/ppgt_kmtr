<?php

namespace App\Http\Controllers\Kepengurusan;

use App\Http\Controllers\Controller;
use App\Models\Periode;
use App\Models\ProgramKerja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgramKerjaController extends Controller
{
    public function index(Periode $periode)
    {
        return view('pages.admin.program-kerja', [
            'periode' => $periode,
            'bidangs' => $periode->bidangs()->orderBy('nama')->get(['id', 'nama']),
            'tahuns' => range($periode->tahun_mulai, $periode->tahun_selesai),
        ]);
    }

    public function getData(Request $request, Periode $periode)
    {
        $paginator = ProgramKerja::milikPeriode($periode)
            ->with(['bidang:id,nama', 'kegiatans'])
            ->when($request->filled('tahun'), fn ($q) => $q->where('tahun', (int) $request->input('tahun')))
            ->when($request->filled('bidang_id'), fn ($q) => $q->where('bidang_id', (int) $request->input('bidang_id')))
            ->orderByDesc('tahun')
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
            'tahun' => ['required', 'integer', 'between:' . $periode->tahun_mulai . ',' . $periode->tahun_selesai],
            'bidang_id' => ['required', Rule::exists('bidangs', 'id')->where('periode_id', $periode->id)],
            'nama' => ['required', 'string', 'max:255',
                Rule::unique('program_kerjas', 'nama')
                    ->where('bidang_id', $request->input('bidang_id'))
                    ->where('tahun', $request->input('tahun'))
                    ->ignore($ignoreId)],
        ], ['nama.unique' => 'Program dengan nama tersebut sudah ada pada bidang dan tahun ini.']);
    }

    public function store(Request $request, Periode $periode)
    {
        ProgramKerja::create($this->validated($request, $periode));

        return response()->json(['status' => true, 'message' => 'Program kerja berhasil ditambahkan.']);
    }

    public function show(Periode $periode, $id)
    {
        return response()->json(ProgramKerja::milikPeriode($periode)->findOrFail($id));
    }

    public function update(Request $request, Periode $periode, $id)
    {
        $program = ProgramKerja::milikPeriode($periode)->findOrFail($id);
        $program->update($this->validated($request, $periode, $program->id));

        return response()->json(['status' => true, 'message' => 'Program kerja berhasil diperbarui.']);
    }

    public function destroy(Periode $periode, $id)
    {
        $program = ProgramKerja::milikPeriode($periode)->findOrFail($id);

        if ($program->kegiatans()->exists()) {
            return response()->json(['status' => false, 'message' => 'Program kerja tidak bisa dihapus karena masih memiliki kegiatan.'], 422);
        }

        $program->delete();

        return response()->json(['status' => true, 'message' => 'Program kerja berhasil dihapus.']);
    }
}
