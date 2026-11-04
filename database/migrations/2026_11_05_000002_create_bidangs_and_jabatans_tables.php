<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bidangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periodes')->restrictOnDelete();
            $table->string('nama');
            $table->timestamps();
            $table->unique(['periode_id', 'nama']);
        });

        Schema::create('jabatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periodes')->restrictOnDelete();
            $table->string('nama');
            $table->boolean('terkait_bidang')->default(false);
            $table->timestamps();
            $table->unique(['periode_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jabatans');
        Schema::dropIfExists('bidangs');
    }
};
