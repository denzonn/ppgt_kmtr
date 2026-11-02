<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_anggota', function (Blueprint $table) {
            $table->id();

            $table->foreignId('jemaat_id')
                ->constrained('jemaat')
                ->cascadeOnDelete();

            $table->string('no_anggota', 30)->nullable()->index();
            $table->string('nik', 16)->unique()->nullable()->index();
            $table->string('nama');
            $table->string('jenis_kelamin', 20)->nullable();

            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();

            $table->string('status_anggota', 50)->nullable();

            $table->text('alamat')->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('email')->nullable();

            $table->string('golongan_darah', 20)->nullable();
            $table->string('golongan_darah_rhesus', 30)->nullable();

            $table->string('pendidikan')->nullable()->index();
            $table->string('jemaat')->nullable()->index();

            $table->enum('status_wilayah', ['dalam_wilayah', 'luar_wilayah'])->default('dalam_wilayah')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_anggota');
    }
};
