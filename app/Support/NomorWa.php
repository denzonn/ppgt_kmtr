<?php

namespace App\Support;

class NomorWa
{
    /** Nomor untuk tautan wa.me, misalnya 6281234567890. Null kalau bukan nomor HP Indonesia yang valid. */
    public static function dari(?string $telepon): ?string
    {
        $angka = preg_replace('/\D/', '', (string) $telepon);

        if (str_starts_with($angka, '0')) {
            $angka = '62' . substr($angka, 1);
        } elseif (str_starts_with($angka, '8')) {
            $angka = '62' . $angka;
        }

        return preg_match('/^628\d{7,11}$/', $angka) ? $angka : null;
    }
}
