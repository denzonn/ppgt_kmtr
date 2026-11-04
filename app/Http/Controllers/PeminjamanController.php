<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Models\InventarisKondisi;
use App\Models\PeminjamanDetail;
use App\Models\PeminjamanInventaris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{

    public function index()
    {
        $inventaris = Inventaris::with('kondisi')
            ->orderBy('nama')
            ->get();

        $tersedia = $this->hitungTersedia();

        return view('pages.peminjaman', compact('inventaris', 'tersedia'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_peminjam'      => 'required|string|max:255',
            'foto_pengambilan'   => 'required|image|max:4096',
            'inventaris'         => 'required|array|min:1',
            'inventaris.*.id'    => 'required|exists:inventaris,id',
            'inventaris.*.jumlah' => 'required|integer|min:1',
        ]);

        $tersedia = $this->hitungTersedia();

        // Jumlah yang diminta tidak boleh melebihi unit yang tersedia.
        foreach ($request->inventaris as $item) {

            $id = (int) $item['id'];
            $jumlah = (int) $item['jumlah'];

            if (!isset($tersedia[$id])) {
                continue;
            }

            if ($jumlah > $tersedia[$id]) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Jumlah peminjaman melebihi unit yang tersedia. Silakan perbarui halaman.',
                ], 422);
            }
        }

        $namaFoto = $request->file('foto_pengambilan')
            ->store('peminjaman', 'public');

        DB::transaction(function () use ($request, $namaFoto) {

            $pinjam = PeminjamanInventaris::create([
                'nama_peminjam'      => $request->nama_peminjam,
                'foto_pengambilan'   => $namaFoto,
                'tanggal_peminjaman' => now(),
                'status'             => 'dipinjam',
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

    /**
     * Unit yang masih bisa dipinjam per inventaris.
     *
     * Key = id inventaris. Inventaris tanpa data kondisi tidak masuk daftar,
     * karena jumlahnya tidak diketahui dan tidak boleh dibatasi.
     */
    private function hitungTersedia(): array
    {
        $adaKondisi = InventarisKondisi::pluck('id_inventaris')->unique();

        $unit = InventarisKondisi::whereIn('kondisi', ['Baik', 'Kurang Baik', 'Rusak'])
            ->selectRaw('id_inventaris, SUM(CAST(jumlah AS UNSIGNED)) as total')
            ->groupBy('id_inventaris')
            ->pluck('total', 'id_inventaris');

        $dipinjam = DB::table('peminjaman_detail')
            ->join(
                'peminjaman_inventaris',
                'peminjaman_inventaris.id',
                '=',
                'peminjaman_detail.id_peminjaman'
            )
            ->where('peminjaman_inventaris.status', 'dipinjam')
            ->selectRaw('peminjaman_detail.id_inventaris, SUM(peminjaman_detail.jumlah) as total')
            ->groupBy('peminjaman_detail.id_inventaris')
            ->pluck('total', 'id_inventaris');

        $result = [];

        foreach ($adaKondisi as $id) {
            $result[$id] = max(0, (int) ($unit[$id] ?? 0) - (int) ($dipinjam[$id] ?? 0));
        }

        return $result;
    }
}
