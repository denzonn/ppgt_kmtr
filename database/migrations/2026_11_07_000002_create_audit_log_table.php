<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            // Tanpa foreign key: catatan tetap utuh, termasuk saat pengguna menghapus akunnya sendiri.
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_nama')->nullable();
            $table->string('aksi', 20)->index();
            $table->string('model', 50)->nullable()->index();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('label')->nullable();
            $table->json('perubahan')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
