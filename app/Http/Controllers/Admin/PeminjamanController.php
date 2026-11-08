<?php

namespace App\Http\Controllers\Admin;

use App\Support\FileUpload;
use App\Http\Controllers\Controller;
use App\Models\PeminjamanDetail;
use App\Models\PeminjamanInventaris;
use Illuminate\Http\Request;

class PeminjamanController extends Controller
{
    public function index()
    {
        return view('pages.admin.peminjaman');
    }

    public function getData(Request $request)
    {
        $peminjaman = PeminjamanInventaris::query();

        if ($request->filled('search')) {

            $peminjaman->where(function ($q) use ($request) {

                $q->where('nama_peminjam', 'like', "%{$request->search}%")

                    ->orWhereHas('details.inventaris', function ($q2) use ($request) {

                        $q2->where('nama', 'like', "%{$request->search}%")
                            ->orWhere('kode_inventaris', 'like', "%{$request->search}%");
                    });
            });
        }

        if ($request->filled('status')) {

            $peminjaman->where('status', $request->status);
        }

        $data = $peminjaman
            ->with([
                'details.inventaris'
            ])
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => $data->items(),
            'total' => $data->total(),
            'from' => $data->firstItem(),
            'to' => $data->lastItem(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'per_page' => $data->perPage(),
            'stats' => $this->getStats(),
        ]);
    }

    /**
     * Ringkasan peminjaman untuk kartu statistik.
     */
    private function getStats(): array
    {
        $total = PeminjamanInventaris::count();

        $dipinjam = PeminjamanInventaris::where('status', 'dipinjam')->count();

        return [
            'total_peminjaman' => $total,
            'dipinjam' => $dipinjam,
            'dikembalikan' => $total - $dipinjam,
            'total_barang' => (int) PeminjamanDetail::sum('jumlah'),
        ];
    }

    public function pengembalian(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:peminjaman_inventaris,id',
            'foto_pengembalian' => 'required|image'
        ]);

        $peminjaman = PeminjamanInventaris::findOrFail($request->id);

        // Hapus foto lama jika ada
        if ($peminjaman->foto_pengembalian) {
            FileUpload::hapus($peminjaman->foto_pengembalian);
        }


        $filename = FileUpload::simpan($request->file('foto_pengembalian'), 'pengembalian');

        $peminjaman->update([
            'foto_pengembalian'   => $filename,
            'tanggal_pengembalian' => now(),
            'status'              => 'dikembalikan'
        ]);

        return response()->json($peminjaman);
    }
}
