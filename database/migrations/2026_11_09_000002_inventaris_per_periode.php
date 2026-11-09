<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Barang yang dihapus tetap tersimpan supaya riwayatnya bisa dilihat.
        Schema::table('inventaris', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::create('inventaris_riwayat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaris_id')->constrained('inventaris')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periodes')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi', 20); // ditambah | diubah | dihapus
            $table->json('kondisi_sebelum')->nullable();
            $table->json('kondisi_sesudah')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaris_riwayat');

        Schema::table('inventaris', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
