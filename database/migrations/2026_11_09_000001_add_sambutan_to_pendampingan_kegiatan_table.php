<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendampingan_kegiatan', function (Blueprint $table) {
            // Nama yang membawakan sambutan, ditulis seperti di rekap (baris "Sambutan : ...").
            $table->json('sambutan')->nullable()->after('hadir');
        });
    }

    public function down(): void
    {
        Schema::table('pendampingan_kegiatan', function (Blueprint $table) {
            $table->dropColumn('sambutan');
        });
    }
};
