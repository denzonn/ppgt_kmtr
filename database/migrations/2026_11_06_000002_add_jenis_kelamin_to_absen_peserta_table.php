<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absen_peserta', function (Blueprint $table) {
            // Nullable karena peserta yang sudah absen belum punya data ini.
            $table->string('jenis_kelamin', 20)->nullable()->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('absen_peserta', function (Blueprint $table) {
            $table->dropColumn('jenis_kelamin');
        });
    }
};
