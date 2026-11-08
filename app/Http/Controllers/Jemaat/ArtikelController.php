<?php

namespace App\Http\Controllers\Jemaat;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Support\FileUpload;
use App\Support\KontenArtikel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ArtikelController extends Controller
{
    public function index()
    {
        return view('pages.jemaat.artikel');
    }

    public function getData(Request $request)
    {
        $jemaatId = $request->user()->jemaat_id;

        $data = Artikel::where('jemaat_id', $jemaatId)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest('id')
            ->paginate(12);

        $jumlah = Artikel::where('jemaat_id', $jemaatId)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return response()->json([
            'status' => true,
            'data' => collect($data->items())->map(fn (Artikel $a) => [
                'id' => $a->id,
                'judul' => $a->judul,
                'penulis' => $a->penulis,
                'foto' => $a->foto,
                'status' => $a->status,
                'alasan_tolak' => $a->alasan_tolak,
                'ringkasan' => KontenArtikel::ringkasan($a->isi),
                'tanggal' => $a->created_at->timezone('Asia/Makassar')->translatedFormat('d M Y'),
            ]),
            'statistik' => [
                'semua' => (int) $jumlah->sum(),
                'menunggu' => (int) ($jumlah['menunggu'] ?? 0),
                'disetujui' => (int) ($jumlah['disetujui'] ?? 0),
                'ditolak' => (int) ($jumlah['ditolak'] ?? 0),
            ],
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
        ]);
    }

    public function create()
    {
        return view('pages.jemaat.artikel-form', ['artikel' => null]);
    }

    public function edit(Request $request, $id)
    {
        return view('pages.jemaat.artikel-form', ['artikel' => $this->milikJemaat($request, $id)]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['foto'] = FileUpload::simpan($request->file('foto'), 'artikel');

        Artikel::create($data + ['jemaat_id' => $request->user()->jemaat_id, 'status' => 'menunggu']);

        return response()->json(['status' => true, 'message' => 'Artikel berhasil dikirim dan menunggu persetujuan.']);
    }

    public function update(Request $request, $id)
    {
        $artikel = $this->milikJemaat($request, $id);
        $data = $this->validasi($request, $artikel);
        $fotoLama = $artikel->foto;
        $gambarLama = KontenArtikel::gambar($artikel->isi);

        if ($request->hasFile('foto')) {
            $data['foto'] = FileUpload::simpan($request->file('foto'), 'artikel');
        }

        // Isi yang berubah harus diperiksa lagi sebelum boleh tampil.
        $artikel->update($data + ['status' => 'menunggu', 'alasan_tolak' => null, 'diperiksa_oleh' => null, 'diperiksa_pada' => null]);

        if ($fotoLama !== $artikel->foto) {
            FileUpload::hapus($fotoLama);
        }

        $this->hapusGambar(array_diff($gambarLama, KontenArtikel::gambar($artikel->isi)));

        return response()->json(['status' => true, 'message' => 'Artikel berhasil disimpan dan menunggu persetujuan.']);
    }

    public function destroy(Request $request, $id)
    {
        $artikel = $this->milikJemaat($request, $id);
        $artikel->delete();
        FileUpload::hapus($artikel->foto);
        $this->hapusGambar(KontenArtikel::gambar($artikel->isi));

        return response()->json(['status' => true, 'message' => 'Artikel berhasil dihapus.']);
    }

    /** Dipanggil CKEditor. Editor membaca pesan gagal dari error.message. */
    public function gambar(Request $request)
    {
        $pesanFormat = 'File harus berupa gambar JPG, PNG, atau GIF.';

        $validator = Validator::make($request->all(), [
            'upload' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:4096'],
        ], [
            'upload.required' => $pesanFormat,
            'upload.image' => $pesanFormat,
            'upload.mimes' => $pesanFormat,
            'upload.max' => 'Ukuran gambar maksimal 4 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => ['message' => $validator->errors()->first('upload')]], 422);
        }

        return response()->json(['url' => FileUpload::simpan($request->file('upload'), 'artikel/isi')]);
    }

    private function milikJemaat(Request $request, $id): Artikel
    {
        return Artikel::where('jemaat_id', $request->user()->jemaat_id)->findOrFail($id);
    }

    private function hapusGambar(array $paths): void
    {
        foreach ($paths as $path) {
            if (Artikel::where('isi', 'like', '%' . basename($path) . '%')->doesntExist()) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function validasi(Request $request, ?Artikel $artikel = null): array
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'penulis' => ['required', 'string', 'max:150'],
            'isi' => ['required', 'string', 'max:' . KontenArtikel::MAKS_PANJANG],
            'foto' => [$artikel?->foto ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], [
            'judul.required' => 'Judul wajib diisi.',
            'judul.max' => 'Judul maksimal 200 karakter.',
            'penulis.required' => 'Nama penulis wajib diisi.',
            'penulis.max' => 'Nama penulis maksimal 150 karakter.',
            'isi.required' => 'Isi artikel wajib diisi.',
            'isi.max' => 'Isi artikel terlalu panjang.',
            'foto.required' => 'Foto sampul wajib diunggah.',
            'foto.image' => 'Foto sampul harus berupa gambar.',
            'foto.mimes' => 'Foto sampul harus berformat JPG, PNG, atau GIF.',
            'foto.max' => 'Ukuran foto sampul maksimal 2 MB.',
        ]);

        if (KontenArtikel::kosong(KontenArtikel::bersihkan($data['isi']))) {
            throw ValidationException::withMessages(['isi' => 'Isi artikel wajib diisi.']);
        }

        unset($data['foto']);

        return $data;
    }
}
