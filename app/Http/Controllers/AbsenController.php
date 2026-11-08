<?php

namespace App\Http\Controllers;

use App\Models\Absen;
use App\Models\Jemaat;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class AbsenController extends Controller
{
    public function index(Absen $absen)
    {
        return view('pages.absen', [
            'absen' => $absen,
            'jemaat' => $this->pilihanJemaat(),
        ]);
    }

    public function store(Request $request, Absen $absen)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'asal_jemaat' => ['required', Rule::in($this->pilihanJemaat())],
            'alamat' => ['required', 'string', 'max:500'],
            'jabatan' => ['required', Rule::in($absen->jabatan)],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 255 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin yang dipilih tidak tersedia.',
            'asal_jemaat.required' => 'Asal jemaat wajib dipilih.',
            'asal_jemaat.in' => 'Asal jemaat yang dipilih tidak tersedia.',
            'alamat.required' => 'Alamat wajib diisi.',
            'alamat.max' => 'Alamat maksimal 500 karakter.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'jabatan.in' => 'Jabatan yang dipilih tidak tersedia.',
        ]);

        $absen->peserta()->create($validated);

        return redirect()->route('absen.isi', [$absen->token, 'terkirim' => 1]);
    }

    private function pilihanJemaat(): Collection
    {
        return Jemaat::orderBy('nama')->pluck('nama')->push('Undangan')->unique()->values();
    }
}
