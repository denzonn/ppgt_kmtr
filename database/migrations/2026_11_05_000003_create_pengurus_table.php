<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengurus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periodes')->restrictOnDelete();
            $table->foreignId('jabatan_id')->constrained('jabatans')->restrictOnDelete();
            $table->foreignId('data_anggota_id')->constrained('data_anggota')->restrictOnDelete();
            $table->foreignId('bidang_id')->nullable()->constrained('bidangs')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['periode_id', 'data_anggota_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengurus');
    }
};
