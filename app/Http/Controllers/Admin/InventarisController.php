<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Models\InventarisKondisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

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
            'kode_inventaris' => 'unique:inventaris,kode_inventaris',
        ]);

        if ($request->hasFile('foto')) {
            $image = $request->file('foto');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/inventaris'), $imageName);
        } else {
            return response()->json(['status' => false, 'message' => 'Foto is required.'], 400);
        }

        $data = [
            'kode_inventaris' => $request->kode_inventaris,
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
            'tanggal_perolehan' => 'required',
            'harga' => 'required|numeric',
            'kode_inventaris' => 'required|unique:inventaris,kode_inventaris,' . $id,
        ]);

        $data = [
            'kode_inventaris' => $request->kode_inventaris,
            'nama' => $request->nama,
            'harga' => $request->harga,
            'tanggal_perolehan' => $request->tanggal_perolehan,
        ];

        if ($request->hasFile('foto')) {

            if ($inventaris->foto && file_exists(public_path('images/inventaris/' . $inventaris->foto))) {
                unlink(public_path('images/inventaris/' . $inventaris->foto));
            }

            $namaFoto = time() . '.' . $request->foto->extension();

            $request->foto->move(public_path('images/inventaris'), $namaFoto);

            $data['foto'] = $namaFoto;
        }

        $inventaris->update($data);

        InventarisKondisi::where('id_inventaris', $id)->delete();

        foreach ($request->kondisi as $kondisi => $jumlah) {

            if ($jumlah > 0) {

                InventarisKondisi::create([
                    'id_inventaris' => $id,
                    'kondisi' => $kondisi,
                    'jumlah' => $jumlah
                ]);
            }
        }

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy($id)
    {
        $inventaris = Inventaris::with('kondisi')->findOrFail($id);

        // hapus foto
        if ($inventaris->foto) {

            $path = public_path('images/inventaris/' . $inventaris->foto);

            if (File::exists($path)) {
                File::delete($path);
            }
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
