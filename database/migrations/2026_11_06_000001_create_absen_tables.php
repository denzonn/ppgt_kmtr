<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absen', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('token')->unique();
            $table->json('jabatan');
            $table->timestamps();
        });

        // Jemaat dan jabatan disimpan sebagai teks supaya data absen lama tidak
        // berubah saat data jemaat atau pilihan jabatan diubah.
        Schema::create('absen_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('absen_id')->constrained('absen')->cascadeOnDelete();
            $table->string('nama');
            $table->string('asal_jemaat');
            $table->string('alamat', 500);
            $table->string('jabatan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absen_peserta');
        Schema::dropIfExists('absen');
    }
};
