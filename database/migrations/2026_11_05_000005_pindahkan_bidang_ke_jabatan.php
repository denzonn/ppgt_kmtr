<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bidangs', function (Blueprint $table) {
            $table->unsignedSmallInteger('jumlah_anggota')->nullable()->after('nama');
        });

        Schema::table('jabatans', function (Blueprint $table) {
            // Jabatan lama tetap tanpa bidang sampai dipilih di halaman Jabatan.
            $table->foreignId('bidang_id')->nullable()->after('periode_id')->constrained('bidangs')->restrictOnDelete();
            $table->index('periode_id');
            $table->dropUnique(['periode_id', 'nama']);
            $table->unique(['bidang_id', 'nama']);
            $table->dropColumn('terkait_bidang');
        });

        // Bidang pengurus kini selalu mengikuti jabatannya.
        DB::table('pengurus')->whereNotNull('bidang_id')->orderBy('id')->each(function ($p) {
            DB::table('jabatans')->where('id', $p->jabatan_id)->whereNull('bidang_id')->update(['bidang_id' => $p->bidang_id]);
        });

        Schema::table('pengurus', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bidang_id');
        });
    }

    public function down(): void
    {
        Schema::table('pengurus', function (Blueprint $table) {
            $table->foreignId('bidang_id')->nullable()->after('data_anggota_id')->constrained('bidangs')->restrictOnDelete();
        });

        DB::table('pengurus')->orderBy('id')->each(function ($p) {
            DB::table('pengurus')->where('id', $p->id)->update([
                'bidang_id' => DB::table('jabatans')->where('id', $p->jabatan_id)->value('bidang_id'),
            ]);
        });

        Schema::table('jabatans', function (Blueprint $table) {
            $table->boolean('terkait_bidang')->default(false);
        });

        DB::table('jabatans')->whereNotNull('bidang_id')->update(['terkait_bidang' => true]);

        Schema::table('jabatans', function (Blueprint $table) {
            $table->dropForeign(['bidang_id']);
            $table->dropUnique(['bidang_id', 'nama']);
            $table->dropColumn('bidang_id');
            $table->unique(['periode_id', 'nama']);
            $table->dropIndex(['periode_id']);
        });

        Schema::table('bidangs', function (Blueprint $table) {
            $table->dropColumn('jumlah_anggota');
        });
    }
};
