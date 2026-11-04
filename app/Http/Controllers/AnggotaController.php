<?php

namespace App\Http\Controllers;

use App\Imports\AnggotaImport;
use App\Models\DataAnggota;
use App\Models\Jemaat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class AnggotaController extends Controller
{
    public function index()
    {
        $jemaat = Jemaat::orderBy('nama')->get(['id', 'nama']);

        return view('pages.admin.anggota', compact('jemaat'));
    }

    public function getData(Request $request)
    {
        $query = DataAnggota::with('jemaat:id,nama');

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

        if ($request->filled('status_wilayah')) {
            $query->where('status_wilayah', $request->status_wilayah);
        }

        $totalAnggota = DataAnggota::count();

        $totalLakiLaki = DataAnggota::where(function ($q) {
            $q->whereRaw("LOWER(TRIM(jenis_kelamin)) LIKE ?", ['%laki%'])
                ->orWhereRaw("LOWER(TRIM(jenis_kelamin)) = ?", ['l']);
        })->count();

        $totalPerempuan = DataAnggota::where(function ($q) {
            $q->whereRaw("LOWER(TRIM(jenis_kelamin)) LIKE ?", ['%perempuan%'])
                ->orWhereRaw("LOWER(TRIM(jenis_kelamin)) = ?", ['p']);
        })->count();

        $totalJemaat = Jemaat::count();

        $jemaatList = Jemaat::orderBy('nama')->get(['id', 'nama']);

        $statusList = DataAnggota::whereNotNull('status_anggota')
            ->where('status_anggota', '!=', '')
            ->distinct()
            ->orderBy('status_anggota')
            ->pluck('status_anggota');

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
            ],
            'filters' => [
                'jemaat' => $jemaatList,
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
        $anggota = DataAnggota::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $anggota,
        ]);
    }

    public function update(Request $request, $id)
    {
        $anggota = DataAnggota::findOrFail($id);

        $validated = $request->validate(
            $this->validationRules($id),
            $this->validationMessages()
        );

        $anggota->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data anggota berhasil diperbarui.',
            'data' => $anggota->fresh(),
        ]);
    }

    public function show($id)
    {
        $anggota = DataAnggota::with('jemaat:id,nama')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $anggota,
        ]);
    }

    public function destroy($id)
    {
        $anggota = DataAnggota::findOrFail($id);

        if (\App\Models\Pengurus::where('data_anggota_id', $anggota->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Anggota tidak bisa dihapus karena tercatat sebagai pengurus.',
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
