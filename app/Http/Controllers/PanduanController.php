<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PanduanController extends Controller
{
    public function show(Request $request, ?string $topik = null)
    {
        $daftar = collect(config('panduan'))->filter(fn (array $t) => $this->bolehBaca($request->user(), $t['izin']));

        if ($topik === null) {
            return redirect()->route('panduan', $daftar->keys()->first());
        }

        abort_unless($daftar->has($topik), 404);

        $kunci = $daftar->keys();
        $posisi = $kunci->search($topik);

        return view('pages.panduan', [
            'daftar' => $daftar,
            'topik' => $topik,
            'isi' => $daftar[$topik],
            'sebelumnya' => $posisi > 0 ? $kunci[$posisi - 1] : null,
            'berikutnya' => $kunci[$posisi + 1] ?? null,
        ]);
    }

    /** Administrator melihat semua topik supaya bisa membantu pengguna role lain. */
    private function bolehBaca(User $user, ?string $izin): bool
    {
        return $izin === null || $user->isAdmin() || ($izin !== 'admin' && $user->bisaMenu($izin));
    }
}
