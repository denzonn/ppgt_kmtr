<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelompok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jemaat_id')->constrained('jemaat')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->timestamps();
            $table->unique(['jemaat_id', 'nama']);
        });

        // Anggota lama belum punya kelompok, jadi kolomnya boleh kosong.
        Schema::table('data_anggota', function (Blueprint $table) {
            $table->foreignId('kelompok_id')->nullable()->after('jemaat_id')->constrained('kelompok')->nullOnDelete();
        });

        $this->ubahMenuRole(fn (array $menus) => in_array('jemaat_bidang', $menus, true)
            ? array_values(array_unique([...$menus, 'jemaat_kelompok']))
            : $menus);
    }

    public function down(): void
    {
        $this->ubahMenuRole(fn (array $menus) => array_values(array_diff($menus, ['jemaat_kelompok'])));

        Schema::table('data_anggota', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kelompok_id');
        });

        Schema::dropIfExists('kelompok');
    }

    private function ubahMenuRole(callable $ubah): void
    {
        foreach (DB::table('roles')->get(['id', 'menus']) as $role) {
            DB::table('roles')->where('id', $role->id)->update([
                'menus' => json_encode($ubah(json_decode($role->menus ?? '[]', true) ?: [])),
            ]);
        }
    }
};
