<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Mengubah nilai file lama menjadi URL lengkap memakai APP_URL.
 * Pastikan APP_URL sudah benar sebelum menjalankan migrasi ini.
 */
return new class extends Migration
{
    /** [tabel, kolom, folder di public/images untuk data lama yang hanya berisi nama file] */
    private const KOLOM = [
        ['inventaris', 'foto', null],
        ['peminjaman_inventaris', 'foto_pengambilan', 'peminjaman'],
        ['peminjaman_inventaris', 'foto_pengembalian', 'pengembalian'],
        ['kegiatans', 'foto', null],
        ['pengurus', 'foto', null],
    ];

    public function up(): void
    {
        $base = rtrim(config('app.url'), '/');

        $this->ubahSemua(function (string $nilai, ?string $folder) use ($base) {
            if (preg_match('#^https?://#i', $nilai)) {
                return $nilai;
            }

            return $folder && ! str_contains($nilai, '/')
                ? "{$base}/images/{$folder}/" . rawurlencode($nilai)
                : "{$base}/storage/{$nilai}";
        });
    }

    public function down(): void
    {
        $base = rtrim(config('app.url'), '/');

        $this->ubahSemua(function (string $nilai, ?string $folder) use ($base) {
            if ($folder && str_starts_with($nilai, "{$base}/images/{$folder}/")) {
                return rawurldecode(Str::after($nilai, "{$base}/images/{$folder}/"));
            }

            return str_starts_with($nilai, "{$base}/storage/") ? Str::after($nilai, "{$base}/storage/") : $nilai;
        });
    }

    // Lewat DB::table supaya updated_at dan audit log tidak ikut berubah.
    private function ubahSemua(callable $ubah): void
    {
        foreach (self::KOLOM as [$tabel, $kolom, $folder]) {
            DB::table($tabel)->whereNotNull($kolom)->where($kolom, '!=', '')->get(['id', $kolom])
                ->each(function ($baris) use ($tabel, $kolom, $folder, $ubah) {
                    $baru = $ubah($baris->{$kolom}, $folder);

                    if ($baru !== $baris->{$kolom}) {
                        DB::table($tabel)->where('id', $baris->id)->update([$kolom => $baru]);
                    }
                });
        }
    }
};
