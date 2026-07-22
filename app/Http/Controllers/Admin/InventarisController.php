<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Models\InventarisKondisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class InventarisController extends Controller
{
    public function index()
    {
        return view('pages.admin.inventaris');
    }

    public function getData(Request $request)
    {
        $inventaris = Inventaris::query();

        if ($request->has('search') && !empty($request->search)) {
            $inventaris->where('nama', 'like', '%' . $request->search . '%');
        }

        $data = $inventaris->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_perolehan' => 'required|date',
            'harga' => 'required|numeric',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $imageName = $request->file('foto')
                ->store('inventaris', 'public');
        } else {

            return response()->json([
                'status' => false,
                'message' => 'Foto is required.'
            ], 400);
        }

        $lastInventaris = Inventaris::orderBy('kode_inventaris', 'desc')->first();

        if ($lastInventaris) {
            $lastNumber = (int) substr($lastInventaris->kode_inventaris, 3); // Ambil angka setelah INV
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $kodeInventaris = 'INV-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $data = [
            'kode_inventaris' => $kodeInventaris,
            'nama' => $request->nama,
            'tanggal_perolehan' => $request->tanggal_perolehan,
            'harga' => $request->harga,
            'foto' => $imageName,
        ];

        $inventaris = Inventaris::create($data);

        foreach ($request->kondisi as $kondisi => $jumlah) {

            if ($jumlah > 0) {

                InventarisKondisi::create([
                    'id_inventaris' => $inventaris->id,
                    'kondisi'       => $kondisi,
                    'jumlah'        => $jumlah,
                ]);
            }
        }

        return response()->json(['status' => true]);
    }

    public function show($id)
    {
        $inventaris = Inventaris::with('kondisi')->findOrFail($id);

        return response()->json($inventaris);
    }

    public function update(Request $request, $id)
    {
        $inventaris = Inventaris::findOrFail($id);

        $request->validate([
            'nama' => 'required',
            'tanggal_perolehan' => 'required|date',
            'harga' => 'required|numeric',
        ]);

        $data = [
            'nama' => $request->nama,
            'harga' => $request->harga,
            'tanggal_perolehan' => $request->tanggal_perolehan,
        ];

        if ($request->hasFile('foto')) {

            if ($inventaris->foto) {
                Storage::disk('public')->delete($inventaris->foto);
            }

            $data['foto'] = $request->file('foto')
                ->store('inventaris', 'public');
        }

        $inventaris->update($data);

        InventarisKondisi::where('id_inventaris', $id)->delete();

        foreach ($request->kondisi as $kondisi => $jumlah) {

            if ($jumlah > 0) {

                InventarisKondisi::create([
                    'id_inventaris' => $id,
                    'kondisi' => $kondisi,
                    'jumlah' => $jumlah,
                ]);
            }
        }

        return response()->json([
            'status' => true,
        ]);
    }

    public function destroy($id)
    {
        $inventaris = Inventaris::with('kondisi')->findOrFail($id);

        if ($inventaris->foto) {
            Storage::disk('public')->delete($inventaris->foto);
        }

        // hapus kondisi
        $inventaris->kondisi()->delete();

        // hapus inventaris
        $inventaris->delete();

        return response()->json([
            'status' => true,
            'message' => 'Inventaris berhasil dihapus.'
        ]);
    }
}
