<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Models\InventarisKondisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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

    private function generateKodeInventaris(string $jenis): string
    {
        $lastInventaris = Inventaris::where('jenis', $jenis)
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastInventaris) {
            $parts = explode('/', $lastInventaris->kode_inventaris);

            if (isset($parts[0]) && ctype_digit($parts[0])) {
                $nextNumber = (int) $parts[0] + 1;
            }
        }

        do {
            $randomNumber = random_int(1000, 9999);

            $kode = str_pad($nextNumber, 3, '0', STR_PAD_LEFT)
                . '/' . $jenis
                . '/' . $randomNumber;
        } while (
            Inventaris::where('kode_inventaris', $kode)->exists()
        );

        return $kode;
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis' => [
                'required',
                Rule::in(['PAK', 'PDP', 'PPL']),
            ],
            'nama' => 'required|string|max:255',
            'tanggal_perolehan' => 'required|date',
            'harga' => 'required|numeric|min:0',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'kondisi' => 'required|array',
            'kondisi.*' => 'required|integer|min:0',
        ]);

        if (!$request->hasFile('foto')) {
            return response()->json([
                'status' => false,
                'message' => 'Foto inventaris wajib diunggah.',
            ], 422);
        }

        $imageName = $request->file('foto')
            ->store('inventaris', 'public');

        $inventaris = Inventaris::create([
            'kode_inventaris' => $this->generateKodeInventaris(
                $request->jenis
            ),
            'jenis' => $request->jenis,
            'nama' => $request->nama,
            'tanggal_perolehan' => $request->tanggal_perolehan,
            'harga' => $request->harga,
            'foto' => $imageName,
        ]);

        foreach ($request->kondisi as $kondisi => $jumlah) {
            if ($jumlah > 0) {
                InventarisKondisi::create([
                    'id_inventaris' => $inventaris->id,
                    'kondisi' => $kondisi,
                    'jumlah' => $jumlah,
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Inventaris berhasil ditambahkan.',
        ]);
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
            'jenis' => [
                'required',
                Rule::in(['PAK', 'PDP', 'PPL']),
            ],
            'nama' => 'required|string|max:255',
            'tanggal_perolehan' => 'required|date',
            'harga' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'kondisi' => 'required|array',
            'kondisi.*' => 'required|integer|min:0',
        ]);

        $data = [
            'jenis' => $request->jenis,
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
            'message' => 'Inventaris berhasil diperbarui.',
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
