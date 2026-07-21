<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Models\PeminjamanDetail;
use App\Models\PeminjamanInventaris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{

    public function index()
    {
        $inventaris = Inventaris::all();

        return view('pages.peminjaman', compact('inventaris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_peminjam'      => 'required',
            'foto_pengambilan'   => 'required|image',
            'inventaris'         => 'required|array|min:1',
            'inventaris.*.id'    => 'required|exists:inventaris,id',
            'inventaris.*.jumlah' => 'required|integer|min:1',
        ]);

        $namaFoto = $request->file('foto_pengambilan')
            ->store('peminjaman', 'public');

        DB::transaction(function () use ($request, $namaFoto) {

            $pinjam = PeminjamanInventaris::create([
                'nama_peminjam'      => $request->nama_peminjam,
                'foto_pengambilan'   => $namaFoto,
                'tanggal_peminjaman' => now(),
                'status'             => 'Dipinjam',
            ]);

            foreach ($request->inventaris as $item) {

                PeminjamanDetail::create([
                    'id_peminjaman' => $pinjam->id,
                    'id_inventaris' => $item['id'],
                    'jumlah'        => $item['jumlah'],
                ]);
            }
        });

        return response()->json([
            'status' => true
        ]);
    }
}
