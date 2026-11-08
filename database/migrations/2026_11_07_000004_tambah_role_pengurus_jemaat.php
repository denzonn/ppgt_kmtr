<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('jemaat_id')->nullable()->after('periode_id')->constrained('jemaat')->nullOnDelete();
        });

        $klasis = DB::table('roles')->where('nama', 'Pengguna Periode')->first();

        if ($klasis) {
            $menus = json_decode($klasis->menus ?? '[]', true) ?: [];

            DB::table('roles')->where('id', $klasis->id)->update([
                'nama' => 'Pengurus Klasis',
                'menus' => json_encode(array_values(array_unique([...$menus, 'persetujuan_artikel']))),
                'updated_at' => now(),
            ]);
        }

        DB::table('roles')->insert([
            'nama' => 'Pengurus Jemaat',
            'is_admin' => false,
            'menus' => json_encode(['data_anggota', 'jemaat_bidang', 'jemaat_pengurus', 'artikel']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $jemaat = DB::table('roles')->where('nama', 'Pengurus Jemaat')->value('id');
        DB::table('users')->where('role_id', $jemaat)->update(['role_id' => DB::table('roles')->where('nama', 'Pengurus Klasis')->value('id')]);
        DB::table('roles')->where('id', $jemaat)->delete();

        $klasis = DB::table('roles')->where('nama', 'Pengurus Klasis')->first();

        if ($klasis) {
            $menus = array_values(array_diff(json_decode($klasis->menus ?? '[]', true) ?: [], ['persetujuan_artikel']));
            DB::table('roles')->where('id', $klasis->id)->update(['nama' => 'Pengguna Periode', 'menus' => json_encode($menus)]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jemaat_id');
        });
    }
};
