<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemilihan', function (Blueprint $table) {
            $table->id();
            $table->string('lingkup', 10); // klasis atau jemaat
            $table->foreignId('jemaat_id')->nullable()->constrained('jemaat')->cascadeOnDelete();
            $table->string('nama');
            $table->string('token', 40)->unique();
            $table->timestamps();
            $table->index(['lingkup', 'jemaat_id']);
        });

        Schema::create('pemilihan_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilihan_id')->constrained('pemilihan')->cascadeOnDelete();
            $table->foreignId('jemaat_id')->nullable()->constrained('jemaat')->nullOnDelete();
            $table->string('nama');
            $table->string('peran', 20);
            $table->string('kode', 10);
            $table->timestamps();
            $table->unique(['pemilihan_id', 'kode']);
        });

        // Satu jabatan berisi beberapa tahap, sampai ada satu nama yang ditetapkan.
        Schema::create('pemilihan_jabatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilihan_id')->constrained('pemilihan')->cascadeOnDelete();
            $table->foreignId('terpilih_id')->nullable()->constrained('pemilihan_peserta')->nullOnDelete();
            $table->string('nama', 100);
            $table->timestamps();
            $table->unique(['pemilihan_id', 'nama']);
        });

        Schema::create('pemilihan_putaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilihan_id')->constrained('pemilihan')->cascadeOnDelete();
            $table->foreignId('jabatan_id')->constrained('pemilihan_jabatan')->cascadeOnDelete();
            $table->string('tahap', 100);
            $table->string('status', 10)->default('draft');
            $table->timestamp('dibuka_pada')->nullable();
            $table->timestamp('ditutup_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('pemilihan_calon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('putaran_id')->constrained('pemilihan_putaran')->cascadeOnDelete();
            $table->foreignId('peserta_id')->constrained('pemilihan_peserta')->cascadeOnDelete();
            $table->unique(['putaran_id', 'peserta_id']);
        });

        // Suara dirahasiakan: dua tabel berikut sengaja tidak punya kolom yang bisa saling dihubungkan.
        // Surat suara tidak menyimpan pemilih, waktu, atau urutan (id acak). Tanda sudah memilih tidak menyimpan waktu.
        Schema::create('pemilihan_surat_suara', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('putaran_id')->constrained('pemilihan_putaran')->cascadeOnDelete();
            $table->foreignId('calon_id')->constrained('pemilihan_calon')->cascadeOnDelete();
        });

        Schema::create('pemilihan_sudah_memilih', function (Blueprint $table) {
            $table->foreignId('putaran_id')->constrained('pemilihan_putaran')->cascadeOnDelete();
            $table->foreignId('peserta_id')->constrained('pemilihan_peserta')->cascadeOnDelete();
            $table->primary(['putaran_id', 'peserta_id']);
        });

        $this->ubahMenuPengurusJemaat(fn (array $menus) => array_values(array_unique([...$menus, 'jemaat_pemilihan'])));
    }

    public function down(): void
    {
        $this->ubahMenuPengurusJemaat(fn (array $menus) => array_values(array_diff($menus, ['jemaat_pemilihan'])));

        Schema::dropIfExists('pemilihan_sudah_memilih');
        Schema::dropIfExists('pemilihan_surat_suara');
        Schema::dropIfExists('pemilihan_calon');
        Schema::dropIfExists('pemilihan_putaran');
        Schema::dropIfExists('pemilihan_jabatan');
        Schema::dropIfExists('pemilihan_peserta');
        Schema::dropIfExists('pemilihan');
    }

    private function ubahMenuPengurusJemaat(callable $ubah): void
    {
        $role = DB::table('roles')->where('nama', 'Pengurus Jemaat')->first();

        if ($role) {
            DB::table('roles')->where('id', $role->id)->update([
                'menus' => json_encode($ubah(json_decode($role->menus ?? '[]', true) ?: [])),
            ]);
        }
    }
};
