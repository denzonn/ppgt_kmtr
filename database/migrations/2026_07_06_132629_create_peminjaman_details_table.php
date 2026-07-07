<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('peminjaman_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_peminjaman')->constrained('peminjaman_inventaris')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('id_inventaris')->constrained('inventaris')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('jumlah');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_detail');
    }
};
