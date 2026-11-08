<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->insert([
            'nama' => 'Tim Tanggap',
            'is_admin' => false,
            'menus' => json_encode(['tim_tanggap']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Gagal kalau role masih dipakai akun, supaya akun tidak diam-diam berpindah hak akses.
        DB::table('roles')->where('nama', 'Tim Tanggap')->delete();
    }
};
