<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendampingan_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nama');
            // Nama yang hadir disimpan persis seperti ditulis pengurus, tidak dihubungkan ke data anggota.
            $table->json('hadir');
            $table->string('sumber', 20);
            $table->timestamps();
            $table->unique(['tanggal', 'nama']);
        });

        $this->ubahMenuPengurusKlasis(fn (array $menus) => array_values(array_unique([...$menus, 'pendampingan'])));
    }

    public function down(): void
    {
        $this->ubahMenuPengurusKlasis(fn (array $menus) => array_values(array_diff($menus, ['pendampingan'])));

        Schema::dropIfExists('pendampingan_kegiatan');
    }

    private function ubahMenuPengurusKlasis(callable $ubah): void
    {
        $role = DB::table('roles')->where('nama', 'Pengurus Klasis')->first();

        if ($role) {
            DB::table('roles')->where('id', $role->id)->update([
                'menus' => json_encode($ubah(json_decode($role->menus ?? '[]', true) ?: [])),
            ]);
        }
    }
};
