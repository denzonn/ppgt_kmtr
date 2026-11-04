<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_kerjas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_id')->constrained('bidangs')->restrictOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->string('nama');
            $table->timestamps();
            $table->unique(['bidang_id', 'tahun', 'nama']);
        });

        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_kerja_id')->constrained('program_kerjas')->restrictOnDelete();
            $table->string('nama');
            $table->text('deskripsi');
            $table->unsignedBigInteger('anggaran');
            $table->json('bulan');
            $table->string('foto')->nullable();
            $table->string('link', 2048)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatans');
        Schema::dropIfExists('program_kerjas');
    }
};
