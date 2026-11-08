<?php

namespace App\Http\Controllers\Jemaat;

use App\Http\Controllers\Controller;
use App\Models\DataAnggota;
use App\Models\Kelompok;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelompokController extends Controller
{
    public function index()
    {
        return view('pages.jemaat.kelompok');
    }

    public function getData(Request $request)
    {
        $jemaatId = $request->user()->jemaat_id;
        $anggota = DataAnggota::where('jemaat_id', $jemaatId)->orderBy('nama')->get(['nama', 'jenis_kelamin', 'kelompok_id']);
        $perKelompok = $anggota->groupBy('kelompok_id');

        return response()->json([
            'status' => true,
            'ringkasan' => [
                'total_anggota' => $anggota->count(),
                'belum_berkelompok' => $anggota->whereNull('kelompok_id')->count(),
            ],
            'data' => Kelompok::where('jemaat_id', $jemaatId)->orderBy('nama')->get()->map(function (Kelompok $k) use ($perKelompok) {
                $isi = $perKelompok->get($k->id, collect());
                // Data lama bisa memakai "L"/"P" selain "Laki-laki"/"Perempuan", sama seperti di statistik Data Anggota.
                $laki = $isi->filter(fn ($a) => in_array(strtolower(trim((string) $a->jenis_kelamin)), ['l', 'laki-laki', 'laki laki'], true))->count();
                $perempuan = $isi->filter(fn ($a) => in_array(strtolower(trim((string) $a->jenis_kelamin)), ['p', 'perempuan'], true))->count();

                return [
                    'id' => $k->id,
                    'nama' => $k->nama,
                    'anggota_count' => $isi->count(),
                    'laki' => $laki,
                    'perempuan' => $perempuan,
                    'contoh' => $isi->take(4)->pluck('nama'),
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        Kelompok::create($this->validasi($request) + ['jemaat_id' => $request->user()->jemaat_id]);

        return response()->json(['status' => true, 'message' => 'Kelompok berhasil ditambahkan.']);
    }

    public function update(Request $request, $id)
    {
        $kelompok = $this->milikJemaat($request, $id);
        $kelompok->update($this->validasi($request, $kelompok->id));

        return response()->json(['status' => true, 'message' => 'Kelompok berhasil diperbarui.']);
    }

    public function destroy(Request $request, $id)
    {
        $kelompok = $this->milikJemaat($request, $id);

        if ($kelompok->anggota()->exists()) {
            return response()->json(['status' => false, 'message' => 'Kelompok masih punya anggota dan belum dapat dihapus.'], 422);
        }

        $kelompok->delete();

        return response()->json(['status' => true, 'message' => 'Kelompok berhasil dihapus.']);
    }

    private function milikJemaat(Request $request, $id): Kelompok
    {
        return Kelompok::where('jemaat_id', $request->user()->jemaat_id)->findOrFail($id);
    }

    private function validasi(Request $request, ?int $abaikanId = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:100',
                Rule::unique('kelompok', 'nama')->where('jemaat_id', $request->user()->jemaat_id)->ignore($abaikanId)],
        ], [
            'nama.required' => 'Nama kelompok wajib diisi.',
            'nama.max' => 'Nama kelompok maksimal 100 karakter.',
            'nama.unique' => 'Nama kelompok sudah ada di jemaat ini.',
        ]);
    }
}
