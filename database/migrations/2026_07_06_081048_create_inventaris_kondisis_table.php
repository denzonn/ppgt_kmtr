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
        Schema::create('inventaris_kondisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_inventaris')->constrained('inventaris')->onUpdate('cascade')->onDelete('cascade');
            $table->enum('kondisi', ['Baik', 'Kurang Baik', 'Rusak', 'Hilang']);
            $table->string('jumlah');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaris_kondisi');
    }
};
