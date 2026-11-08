<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('linktree', function (Blueprint $table) {
            // Baris tanpa link adalah sub-judul.
            $table->string('link', 2048)->nullable()->change();
            $table->unsignedInteger('urutan')->default(0)->after('link');
        });

        // Link yang sudah ada tetap pada urutan lamanya.
        DB::table('linktree')->update(['urutan' => DB::raw('id')]);
    }

    public function down(): void
    {
        // Sub-judul tidak punya link, jadi tidak bisa ada di struktur lama.
        DB::table('linktree')->whereNull('link')->delete();

        Schema::table('linktree', function (Blueprint $table) {
            $table->dropColumn('urutan');
            $table->string('link', 2048)->nullable(false)->change();
        });
    }
};
