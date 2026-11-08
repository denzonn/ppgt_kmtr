<?php

namespace App\Http\Controllers\Kepengurusan;

use App\Support\FileUpload;
use App\Http\Controllers\Controller;
use App\Models\DataAnggota;
use App\Models\Jabatan;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PengurusController extends Controller
{
    public function index(Periode $periode)
    {
        $bidangs = $periode->bidangs()
            ->with(['jabatans' => fn ($q) => $q->orderBy('nama')])
            ->withCount('pengurus')
            ->orderBy('id')
            ->get();

        return view('pages.admin.pengurus', [
            'periode' => $periode,
            'bidangs' => $bidangs,
            'bidangInfo' => $bidangs->map->only(['id', 'nama', 'jumlah_anggota', 'pengurus_count'])->values(),
        ]);
    }

    public function getData(Request $request, Periode $periode)
    {
        $search = trim((string) $request->input('search', ''));

        $paginator = $periode->pengurus()
            ->with(['jabatan.bidang:id,nama', 'anggota:id,nama,no_anggota'])
            ->when($search !== '', fn ($q) => $q->whereHas('anggota', fn ($a) => $a->where('nama', 'like', '%' . $search . '%')))
            ->when($request->filled('bidang_id'), fn ($q) => $q->whereHas('jabatan', fn ($j) => $j->where('bidang_id', $request->input('bidang_id'))))
            ->orderBy(Jabatan::select('bidang_id')->whereColumn('jabatans.id', 'pengurus.jabatan_id'))
            ->orderBy('jabatan_id')
            ->orderBy('id')
            ->paginate(25);

        return response()->json([
            'data' => collect($paginator->items())->map(fn ($p) => [
                'id' => $p->id,
                'nama' => $p->anggota->nama,
                'no_anggota' => $p->anggota->no_anggota,
                'jabatan' => $p->jabatan->nama,
                'bidang_id' => $p->jabatan->bidang_id,
                'bidang' => $p->jabatan->bidang?->nama,
                'foto' => $p->foto,
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
        $data = $request->validate([
            'jabatan_id' => ['required', 'integer',
                Rule::exists('jabatans', 'id')->where('periode_id', $periode->id)->whereNotNull('bidang_id')],
            'data_anggota_id' => ['required', 'integer', 'exists:data_anggota,id',
                Rule::unique('pengurus', 'data_anggota_id')->where('periode_id', $periode->id)->ignore($ignoreId)],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], [
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat JPG, PNG, atau GIF.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
            'data_anggota_id.required' => 'Anggota wajib dipilih.',
            'data_anggota_id.integer' => 'Anggota tidak valid.',
            'data_anggota_id.exists' => 'Anggota tidak ditemukan.',
            'data_anggota_id.unique' => 'Anggota ini sudah menjadi pengurus di periode ini.',
            'jabatan_id.required' => 'Jabatan wajib dipilih.',
            'jabatan_id.integer' => 'Jabatan tidak valid.',
            'jabatan_id.exists' => 'Jabatan tidak ditemukan atau belum punya bidang.',
        ]);

        // ponytail: tanpa lock, dua penyimpanan serentak bisa melewati batas; pakai lockForUpdate kalau pengisinya banyak.
        $bidang = $periode->jabatans()->findOrFail($data['jabatan_id'])->bidang;
        $terisi = $bidang->pengurus()->when($ignoreId, fn ($q) => $q->where('pengurus.id', '!=', $ignoreId))->count();

        if ($bidang->jumlah_anggota !== null && $terisi >= $bidang->jumlah_anggota) {
            throw ValidationException::withMessages([
                'jabatan_id' => "{$bidang->nama} sudah penuh ({$bidang->jumlah_anggota} orang).",
            ]);
        }

        unset($data['foto']);

        return $data;
    }

    public function store(Request $request, Periode $periode)
    {
        $data = $this->validated($request, $periode);

        if ($request->hasFile('foto')) {
            $data['foto'] = FileUpload::simpan($request->file('foto'), 'pengurus');
        }

        $periode->pengurus()->create($data);

        return response()->json(['status' => true, 'message' => 'Pengurus berhasil ditambahkan.']);
    }

    public function show(Periode $periode, $id)
    {
        $p = $periode->pengurus()->with('anggota:id,nama,no_anggota')->findOrFail($id);

        return response()->json([
            'id' => $p->id,
            'jabatan_id' => $p->jabatan_id,
            'data_anggota_id' => $p->data_anggota_id,
            'nama' => $p->anggota->nama,
            'no_anggota' => $p->anggota->no_anggota,
            'foto' => $p->foto,
        ]);
    }

    public function update(Request $request, Periode $periode, $id)
    {
        $pengurus = $periode->pengurus()->findOrFail($id);
        $data = $this->validated($request, $periode, $pengurus->id);
        $fotoLama = $pengurus->foto;

        if ($request->hasFile('foto')) {
            $data['foto'] = FileUpload::simpan($request->file('foto'), 'pengurus');
        } elseif ($request->boolean('hapus_foto')) {
            $data['foto'] = null;
        }

        $pengurus->update($data);

        if ($fotoLama && $fotoLama !== $pengurus->foto) {
            FileUpload::hapus($fotoLama);
        }

        return response()->json(['status' => true, 'message' => 'Pengurus berhasil diperbarui.']);
    }

    public function destroy(Periode $periode, $id)
    {
        $pengurus = $periode->pengurus()->findOrFail($id);
        $pengurus->delete();

        if ($pengurus->foto) {
            FileUpload::hapus($pengurus->foto);
        }

        return response()->json(['status' => true, 'message' => 'Pengurus berhasil dihapus.']);
    }
}
