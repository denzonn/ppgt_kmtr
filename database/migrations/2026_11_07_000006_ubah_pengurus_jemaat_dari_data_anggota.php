<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jemaat_bidang', function (Blueprint $table) {
            $table->unsignedSmallInteger('jumlah_anggota')->nullable()->after('nama');
        });

        // Nama kini mengikuti data anggota, jadi kolom ketik manual dilepas.
        Schema::table('jemaat_pengurus', function (Blueprint $table) {
            $table->dropColumn('nama');
            $table->foreignId('data_anggota_id')->after('jemaat_bidang_id')->unique()->constrained('data_anggota')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('jemaat_pengurus', function (Blueprint $table) {
            $table->dropConstrainedForeignId('data_anggota_id');
            $table->string('nama')->default('')->after('jemaat_bidang_id');
        });

        Schema::table('jemaat_bidang', function (Blueprint $table) {
            $table->dropColumn('jumlah_anggota');
        });
    }
};
