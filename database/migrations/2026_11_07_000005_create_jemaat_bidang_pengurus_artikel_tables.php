<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jemaat_bidang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jemaat_id')->constrained('jemaat')->cascadeOnDelete();
            $table->string('nama');
            $table->timestamps();
            $table->unique(['jemaat_id', 'nama']);
        });

        Schema::create('jemaat_pengurus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jemaat_bidang_id')->constrained('jemaat_bidang')->cascadeOnDelete();
            $table->string('nama');
            $table->string('jabatan');
            $table->string('foto')->nullable();
            $table->timestamps();
        });

        Schema::create('artikels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jemaat_id')->constrained('jemaat')->cascadeOnDelete();
            $table->string('judul');
            $table->string('penulis');
            $table->string('foto')->nullable();
            $table->longText('isi');
            $table->string('status', 20)->default('menunggu')->index();
            $table->text('alasan_tolak')->nullable();
            // Nama disalin supaya riwayat tetap utuh walau akunnya dihapus.
            $table->string('diperiksa_oleh')->nullable();
            $table->timestamp('diperiksa_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikels');
        Schema::dropIfExists('jemaat_pengurus');
        Schema::dropIfExists('jemaat_bidang');
    }
};
