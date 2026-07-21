<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeminjamanInventaris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

                        $q2->where('nama', 'like', "%{$request->search}%");
                    });
            });
        }

        $data = $peminjaman
            ->with([
                'details.inventaris'
            ])
            ->latest()
            ->paginate(10);

        return response()->json($data);
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
            Storage::disk('public')->delete($peminjaman->foto_pengembalian);
        }

        $filename = $request->file('foto_pengembalian')
            ->store('pengembalian', 'public');

        $peminjaman->update([
            'foto_pengembalian'   => $filename,
            'tanggal_pengembalian' => now(),
            'status'              => 'dikembalikan'
        ]);

        return response()->json($peminjaman);
    }
}
