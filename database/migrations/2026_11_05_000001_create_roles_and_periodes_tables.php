<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->boolean('is_admin')->default(false);
            $table->json('menus');
            $table->timestamps();
        });

        Schema::create('periodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun_mulai');
            $table->unsignedSmallInteger('tahun_selesai');
            $table->timestamps();
            $table->unique(['tahun_mulai', 'tahun_selesai']);
        });

        $now = now();
        $adminId = DB::table('roles')->insertGetId([
            'nama' => 'Admin', 'is_admin' => true, 'menus' => json_encode([]),
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $penggunaId = DB::table('roles')->insertGetId([
            'nama' => 'Pengguna Periode', 'is_admin' => false,
            'menus' => json_encode(['program_kerja', 'bidang', 'jabatan', 'pengurus']),
            'created_at' => $now, 'updated_at' => $now,
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('roles')->restrictOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('periodes')->restrictOnDelete();
        });

        DB::table('users')->where('roles', 'admin')->update(['role_id' => $adminId]);
        DB::table('users')->where('roles', '!=', 'admin')->update(['role_id' => $penggunaId]);

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable(false)->change();
            $table->dropColumn('roles');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('roles', ['admin', 'user'])->default('user')->after('email_verified_at');
        });

        $adminIds = DB::table('roles')->where('is_admin', true)->pluck('id');
        DB::table('users')->whereIn('role_id', $adminIds)->update(['roles' => 'admin']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['periode_id']);
            $table->dropColumn(['role_id', 'periode_id']);
        });

        Schema::dropIfExists('periodes');
        Schema::dropIfExists('roles');
    }
};
