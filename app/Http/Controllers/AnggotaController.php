<?php

namespace App\Http\Controllers;

use App\Imports\AnggotaImport;
use App\Models\DataAnggota;
use App\Models\Jemaat;
use App\Models\Kelompok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class AnggotaController extends Controller
{
    public function index()
    {
        $jemaat = $this->daftarJemaat();

        $kelompok = Kelompok::whereIn('jemaat_id', $jemaat->pluck('id'))->orderBy('nama')->get(['id', 'jemaat_id', 'nama']);

        return view('pages.admin.anggota', compact('jemaat', 'kelompok'));
    }

    /** Pengurus Jemaat hanya boleh menyentuh anggota jemaatnya sendiri. */
    private function anggota()
    {
        return DataAnggota::query()->when(auth()->user()->jemaat_id, fn ($q, $jemaatId) => $q->where('jemaat_id', $jemaatId));
    }

    private function daftarJemaat()
    {
        return Jemaat::query()
            ->when(auth()->user()->jemaat_id, fn ($q, $jemaatId) => $q->whereKey($jemaatId))
            ->orderBy('nama')
            ->get(['id', 'nama']);
    }

    private function kunciJemaat(Request $request): void
    {
        if ($request->user()->jemaat_id) {
            $request->merge(['jemaat_id' => $request->user()->jemaat_id]);
        }
    }

    public function getData(Request $request)
    {
        $query = $this->anggota()->with(['jemaat:id,nama', 'kelompok:id,nama']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('no_anggota', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('telepon', 'like', "%{$search}%")
                    ->orWhereHas('jemaat', function ($jemaat) use ($search) {
                        $jemaat->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status_anggota', $request->status);
        }

        if ($request->filled('jemaat')) {
            $query->where('jemaat_id', $request->jemaat);
        }

        if ($request->input('kelompok') === 'kosong') {
            $query->whereNull('kelompok_id');
        } elseif ($request->filled('kelompok')) {
            $query->where('kelompok_id', $request->kelompok);
        }

        if ($request->filled('status_wilayah')) {
            $query->where('status_wilayah', $request->status_wilayah);
        }

        $totalAnggota = $this->anggota()->count();

        $totalLakiLaki = $this->anggota()->where(function ($q) {
            $q->whereRaw("LOWER(TRIM(jenis_kelamin)) LIKE ?", ['%laki%'])
                ->orWhereRaw("LOWER(TRIM(jenis_kelamin)) = ?", ['l']);
        })->count();

        $totalPerempuan = $this->anggota()->where(function ($q) {
            $q->whereRaw("LOWER(TRIM(jenis_kelamin)) LIKE ?", ['%perempuan%'])
                ->orWhereRaw("LOWER(TRIM(jenis_kelamin)) = ?", ['p']);
        })->count();

        $totalAktif = $this->anggota()->whereRaw("LOWER(TRIM(status_anggota)) = ?", ['aktif'])->count();

        $jemaatList = $this->daftarJemaat();

        $totalJemaat = $jemaatList->count();

        $statusList = $this->anggota()->whereNotNull('status_anggota')
            ->where('status_anggota', '!=', '')
            ->distinct()
            ->orderBy('status_anggota')
            ->pluck('status_anggota');

        // Pilihan kelompok baru berarti setelah jemaatnya jelas.
        $jemaatKelompok = $request->user()->jemaat_id ?: $request->integer('jemaat');
        $kelompokList = $jemaatKelompok
            ? Kelompok::where('jemaat_id', $jemaatKelompok)->orderBy('nama')->get(['id', 'nama'])
            : [];

        $statusWilayahList = [
            'Dalam Wilayah Pelayanan',
            'Luar Wilayah',
        ];

        $perPage = (int) $request->input('per_page', 10);

        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $anggota = $query
            ->select([
                'id',
                'jemaat_id',
                'kelompok_id',
                'no_anggota',
                'nama',
                'jenis_kelamin',
                'status_anggota',
                'status_wilayah',
            ])
            ->orderBy('nama')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $anggota,
            'statistics' => [
                'total_anggota' => $totalAnggota,
                'total_laki_laki' => $totalLakiLaki,
                'total_perempuan' => $totalPerempuan,
                'total_jemaat' => $totalJemaat,
                'total_aktif' => $totalAktif,
            ],
            'filters' => [
                'jemaat' => $jemaatList,
                'kelompok' => $kelompokList,
                'status' => $statusList,
                'status_wilayah' => $statusWilayahList,
            ],
        ]);
    }

    private function generateNomorAnggota()
    {
        $nomorTerbesar = 0;

        $nomorList = DataAnggota::pluck('no_anggota');

        foreach ($nomorList as $nomor) {
            if (preg_match('/^PPGT-(\d+)$/i', $nomor, $match)) {
                $nomorTerbesar = max($nomorTerbesar, (int) $match[1]);
            }
        }

        return 'PPGT-' . str_pad($nomorTerbesar + 1, 4, '0', STR_PAD_LEFT);
    }

    private function validationRules($id = null)
    {
        return [
            'jemaat_id' => ['required', 'exists:jemaat,id'],
            // Jemaat yang belum membuat kelompok tetap bisa mendata anggota.
            'kelompok_id' => [
                Rule::requiredIf(fn () => Kelompok::where('jemaat_id', request()->integer('jemaat_id'))->exists()),
                'nullable',
                'integer',
                Rule::exists('kelompok', 'id')->where('jemaat_id', request()->integer('jemaat_id')),
            ],
            'nik' => [
                'nullable',
                'digits:16',
                Rule::unique('data_anggota', 'nik')->ignore($id),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'status_anggota' => ['required', Rule::in(['Aktif', 'Tidak Aktif'])],
            'status_wilayah' => [
                'required',
                Rule::in(['dalam_wilayah', 'luar_wilayah']),
            ],
            'alamat' => ['nullable', 'string'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'golongan_darah' => ['nullable', Rule::in(['A', 'B', 'AB', 'O'])],
            'golongan_darah_rhesus' => ['nullable', Rule::in(['Positif', 'Negatif'])],
            'pendidikan' => ['nullable', 'string', 'max:50'],
        ];
    }

    private function validationMessages()
    {
        return [
            'jemaat_id.required' => 'Jemaat wajib dipilih.',
            'jemaat_id.exists' => 'Jemaat yang dipilih tidak ditemukan.',
            'kelompok_id.required' => 'Kelompok wajib dipilih.',
            'kelompok_id.exists' => 'Kelompok tidak ditemukan di jemaat ini.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'nama.required' => 'Nama anggota wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'status_anggota.required' => 'Status anggota wajib dipilih.',
            'status_wilayah.required' => 'Status wilayah wajib dipilih.',
            'status_wilayah.in' => 'Status wilayah tidak valid.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'email.email' => 'Format email tidak valid.',
        ];
    }

    public function store(Request $request)
    {
        $this->kunciJemaat($request);

        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        $anggota = DB::transaction(function () use ($validated) {
            Jemaat::query()
                ->orderBy('id')
                ->lockForUpdate()
                ->firstOrFail();

            $validated['no_anggota'] = $this->generateNomorAnggota();

            return DataAnggota::create($validated);
        });

        return response()->json([
            'success' => true,
            'message' => 'Data anggota berhasil ditambahkan dengan nomor ' . $anggota->no_anggota . '.',
            'data' => $anggota,
        ]);
    }

    public function edit($id)
    {
        $anggota = $this->anggota()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $anggota,
        ]);
    }

    public function update(Request $request, $id)
    {
        $anggota = $this->anggota()->findOrFail($id);

        $this->kunciJemaat($request);

        $validated = $request->validate(
            $this->validationRules($id),
            $this->validationMessages()
        );

        // Kelompok lama tidak boleh terbawa saat anggota pindah ke jemaat lain.
        $anggota->update($validated + ['kelompok_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Data anggota berhasil diperbarui.',
            'data' => $anggota->fresh(),
        ]);
    }

    public function show($id)
    {
        $anggota = $this->anggota()->with(['jemaat:id,nama', 'kelompok:id,nama'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $anggota,
        ]);
    }

    public function destroy($id)
    {
        $anggota = $this->anggota()->findOrFail($id);

        if (\App\Models\Pengurus::where('data_anggota_id', $anggota->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Anggota tidak bisa dihapus karena tercatat sebagai pengurus.',
            ], 422);
        }

        if (\App\Models\JemaatPengurus::where('data_anggota_id', $anggota->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Anggota tidak bisa dihapus karena tercatat sebagai pengurus jemaat.',
            ], 422);
        }

        $anggota->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data anggota berhasil dihapus.',
        ]);
    }


    public function import(Request $request)
    {
        abort_if($request->user()->jemaat_id, 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'file.required' => 'Silakan pilih file Excel terlebih dahulu.',
            'file.mimes' => 'File harus berformat XLSX, XLS, atau CSV.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        try {
            $import = new AnggotaImport();

            Excel::import($import, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Proses import data anggota selesai.',
                'hasil' => [
                    'diproses' => $import->diproses,
                    'berhasil' => $import->berhasil,
                    'dilewati' => $import->dilewati,
                    'gagal' => count($import->gagal),
                    'catatan' => array_slice($import->catatan, 0, 100),
                    'detail_gagal' => array_slice($import->gagal, 0, 100),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Import gagal. ' . $e->getMessage(),
            ], 500);
        }
    }
}
