<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Utusan dipilih dari data anggota. Nama tetap disimpan supaya catatan sidang utuh walau anggotanya dihapus.
    public function up(): void
    {
        Schema::table('pemilihan_peserta', function (Blueprint $table) {
            $table->foreignId('data_anggota_id')->nullable()->after('jemaat_id')->constrained('data_anggota')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pemilihan_peserta', function (Blueprint $table) {
            $table->dropConstrainedForeignId('data_anggota_id');
        });
    }
};
