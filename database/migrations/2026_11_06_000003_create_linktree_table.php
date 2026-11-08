<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('linktree', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('link', 2048);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linktree');
    }
};
