<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AbsenController extends Controller
{
    public function index()
    {
        return view('pages.admin.absen');
    }

    public function getData(Request $request)
    {
        $data = Absen::withCount('peserta')
            ->when($request->filled('search'), fn ($q) => $q->where('nama', 'like', "%{$request->search}%"))
            ->latest('id')
            ->paginate(12);

        return response()->json([
            'status' => true,
            'data' => collect($data->items())->map(fn (Absen $absen) => $absen->toArray() + [
                'link' => route('absen.isi', $absen->token),
                'qr_url' => route('absen.qr', $absen),
            ]),
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
        ]);
    }

    public function store(Request $request)
    {
        Absen::create($this->validasi($request) + ['token' => Str::random(16)]);

        return response()->json(['status' => true, 'message' => 'Absen berhasil dibuat.']);
    }

    public function show(Absen $absen)
    {
        return response()->json($absen);
    }

    public function update(Request $request, Absen $absen)
    {
        // Token tidak diubah supaya QR yang sudah dibagikan tetap berlaku.
        $absen->update($this->validasi($request));

        return response()->json(['status' => true, 'message' => 'Absen berhasil diperbarui.']);
    }

    public function destroy(Absen $absen)
    {
        $absen->delete();

        return response()->json(['status' => true, 'message' => 'Absen berhasil dihapus.']);
    }

    // QR dibuat saat diminta, jadi selalu mengikuti alamat web yang sedang dibuka.
    public function qr(Absen $absen)
    {
        $svg = QrCode::format('svg')
            ->size(500)
            ->margin(2)
            ->errorCorrection('H')
            ->generate(route('absen.isi', $absen->token));

        return response((string) $svg)->header('Content-Type', 'image/svg+xml');
    }

    public function peserta(Absen $absen)
    {
        return response()->json([
            'status' => true,
            'data' => $absen->peserta()->latest('id')->get(['nama', 'jenis_kelamin', 'asal_jemaat', 'alamat', 'jabatan']),
        ]);
    }

    private function validasi(Request $request): array
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'jabatan' => ['required', 'string', 'max:1000'],
        ], [
            'nama.required' => 'Nama kegiatan wajib diisi.',
            'nama.max' => 'Nama kegiatan maksimal 150 karakter.',
            'jabatan.required' => 'Pilihan jabatan wajib diisi.',
            'jabatan.max' => 'Pilihan jabatan maksimal 1.000 karakter.',
        ]);

        $jabatan = collect(explode(',', $validated['jabatan']))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->unique()
            ->values();

        if ($jabatan->isEmpty() || $jabatan->contains(fn ($item) => mb_strlen($item) > 100)) {
            throw ValidationException::withMessages([
                'jabatan' => 'Isi minimal satu jabatan, masing-masing maksimal 100 karakter.',
            ]);
        }

        return ['nama' => $validated['nama'], 'jabatan' => $jabatan->all()];
    }
}
