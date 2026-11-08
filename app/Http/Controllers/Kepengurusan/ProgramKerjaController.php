<?php

namespace App\Http\Controllers\Kepengurusan;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
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
            'bidangs' => $periode->bidangs()->orderBy('id')->get(['id', 'nama']),
            'tahuns' => range($periode->tahun_mulai, $periode->tahun_selesai),
            'tahunBerjalan' => $periode->tahunBerjalan(),
        ]);
    }

    public function getData(Request $request, Periode $periode)
    {
        $query = ProgramKerja::milikPeriode($periode)
            ->when($request->filled('tahun'), fn ($q) => $q->where('tahun', (int) $request->input('tahun')))
            ->when($request->filled('bidang_id'), fn ($q) => $q->where('bidang_id', (int) $request->input('bidang_id')));

        // Dihitung dari semua hasil saringan, bukan hanya halaman yang tampil.
        $kegiatan = Kegiatan::whereIn('program_kerja_id', (clone $query)->select('id'));

        $paginator = $query
            ->with(['bidang:id,nama', 'kegiatans'])
            ->orderBy('bidang_id')
            ->orderByDesc('tahun')
            ->orderBy('nama')
            ->paginate(25);

        return response()->json([
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'ringkasan' => [
                'kegiatan' => (clone $kegiatan)->count(),
                'anggaran' => (int) (clone $kegiatan)->sum('anggaran'),
            ],
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
        ]);
    }

    /** Tahun tidak dipilih pengguna: program baru memakai tahun berjalan. */
    private function validated(Request $request, Periode $periode, int $tahun, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'bidang_id' => ['required', 'integer', Rule::exists('bidangs', 'id')->where('periode_id', $periode->id)],
            'nama' => ['required', 'string', 'max:255',
                Rule::unique('program_kerjas', 'nama')
                    ->where('bidang_id', $request->input('bidang_id'))
                    ->where('tahun', $tahun)
                    ->ignore($ignoreId)],
        ], ['bidang_id.required' => 'Bidang wajib dipilih.',
            'bidang_id.integer' => 'Bidang tidak valid.',
            'bidang_id.exists' => 'Bidang tidak ditemukan pada periode ini.',
            'nama.required' => 'Nama program wajib diisi.',
            'nama.max' => 'Nama program maksimal 255 karakter.',
            'nama.unique' => 'Program dengan nama tersebut sudah ada pada bidang dan tahun ini.']);

        return $data + ['tahun' => $tahun];
    }

    public function store(Request $request, Periode $periode)
    {
        ProgramKerja::create($this->validated($request, $periode, $periode->tahunBerjalan()));

        return response()->json(['status' => true, 'message' => 'Program kerja berhasil ditambahkan.']);
    }

    public function show(Periode $periode, $id)
    {
        return response()->json(ProgramKerja::milikPeriode($periode)->findOrFail($id));
    }

    public function update(Request $request, Periode $periode, $id)
    {
        $program = ProgramKerja::milikPeriode($periode)->findOrFail($id);
        $program->update($this->validated($request, $periode, $program->tahun, $program->id));

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
