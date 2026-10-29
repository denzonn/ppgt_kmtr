<?php

namespace App\Http\Controllers;

use App\Models\QRCodeLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateQRLinkController extends Controller
{
    public function index()
    {
        return view('pages.admin.generate-qr');
    }

    public function getData(Request $request)
    {
        $query = QRCodeLink::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('link', 'like', "%{$search}%");
            });
        }

        $data = $query->latest()->paginate(10);

        // Pastikan URL QR Code lengkap dikirim ke frontend.
        $items = collect($data->items())->map(function ($item) {
            $item->qr_code = $item->qr_code
                ? $this->getQrUrl($item->qr_code)
                : null;

            return $item;
        });

        return response()->json([
            'status' => true,
            'data' => $items->values(),
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'per_page' => $data->perPage(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'link' => ['required', 'url', 'max:2048'],
        ], [
            'nama.required' => 'Nama QR Code wajib diisi.',
            'nama.max' => 'Nama QR Code maksimal 150 karakter.',
            'link.required' => 'Link tujuan wajib diisi.',
            'link.url' => 'Masukkan URL yang valid, misalnya https://contoh.com.',
            'link.max' => 'Link maksimal 2048 karakter.',
        ]);

        $item = QRCodeLink::create([
            'nama' => $validated['nama'],
            'link' => $validated['link'],
        ]);

        try {
            $this->generateQrCode($item);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete(
                'qr-codes/' . $item->id . '.svg'
            );

            $item->delete();

            report($e);

            return response()->json([
                'status' => false,
                'message' => 'QR Code gagal dibuat. Silakan coba kembali.',
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'QR Code berhasil dibuat.',
            'data' => $item->fresh(),
        ]);
    }

    public function show($id)
    {
        $item = QRCodeLink::findOrFail($id);

        // Untuk menampilkan URL lengkap jika diperlukan.
        $item->qr_code = $item->qr_code
            ? $this->getQrUrl($item->qr_code)
            : null;

        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = QRCodeLink::findOrFail($id);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'link' => ['required', 'url', 'max:2048'],
        ], [
            'nama.required' => 'Nama QR Code wajib diisi.',
            'nama.max' => 'Nama QR Code maksimal 150 karakter.',
            'link.required' => 'Link tujuan wajib diisi.',
            'link.url' => 'Masukkan URL yang valid, misalnya https://contoh.com.',
            'link.max' => 'Link maksimal 2048 karakter.',
        ]);

        $item->nama = $validated['nama'];
        $item->link = $validated['link'];

        try {
            $this->generateQrCode($item);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'QR Code gagal diperbarui. Silakan coba kembali.',
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'QR Code berhasil diperbarui.',
            'data' => $item->fresh(),
        ]);
    }

    public function download($id)
    {
        $item = QRCodeLink::findOrFail($id);

        $path = $this->getQrPath($item->qr_code);

        if (!$path || !Storage::disk('public')->exists($path)) {
            $this->generateQrCode($item);
            $item->refresh();
        }

        return response()->json([
            'status' => true,
            'url' => $this->getQrUrl($item->qr_code),
            'nama' => $item->nama,
        ]);
    }

    public function destroy($id)
    {
        $item = QRCodeLink::findOrFail($id);

        // Kolom qr_code sekarang menyimpan URL lengkap.
        $path = $this->getQrPath($item->qr_code);

        if ($path) {
            Storage::disk('public')->delete($path);
        }

        $item->delete();

        return response()->json([
            'status' => true,
            'message' => 'QR Code berhasil dihapus.',
        ]);
    }

    /**
     * Membuat file QR dan menyimpan URL lengkap ke database.
     */
    private function generateQrCode(QRCodeLink $item): void
    {
        $path = 'qr-codes/' . $item->id . '.svg';

        $svg = QrCode::format('svg')
            ->size(500)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($item->link);

        Storage::disk('public')->put($path, (string) $svg);

        // Contoh:
        // http://127.0.0.1:8000/storage/qr-codes/1.svg
        $item->qr_code = $this->getQrUrl($path);
        $item->save();
    }

    /**
     * Mengubah path atau URL QR menjadi URL lengkap.
     */
    private function getQrUrl(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        // Jika sudah URL lengkap, gunakan URL tersebut.
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        $path = ltrim($value, '/');

        // Mendukung data lama yang hanya berisi qr-codes/1.svg.
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return url(Storage::disk('public')->url($path));
    }

    /**
     * Mengambil path relatif dari path atau URL lengkap.
     * Dibutuhkan agar Storage dapat membaca dan menghapus file.
     */
    private function getQrPath(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $path = parse_url($value, PHP_URL_PATH) ?: $value;
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        // Batasi operasi file hanya pada folder QR Code.
        if (!str_starts_with($path, 'qr-codes/')) {
            return null;
        }

        return $path;
    }
}
