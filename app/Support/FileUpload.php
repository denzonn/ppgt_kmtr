<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUpload
{
    /** Menyimpan file ke storage publik dan mengembalikan URL lengkap untuk disimpan di database. */
    public static function simpan(UploadedFile $file, string $folder): string
    {
        return asset('storage/' . $file->store($folder, 'public'));
    }

    public static function hapus(?string $nilai): void
    {
        $path = self::path($nilai);

        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    /** Path di storage publik dari nilai database. Null kalau file tidak berada di storage. */
    public static function path(?string $nilai): ?string
    {
        if (! $nilai || ! preg_match('#^https?://#i', $nilai)) {
            return $nilai ?: null;
        }

        $path = rawurldecode((string) parse_url($nilai, PHP_URL_PATH));

        return Str::contains($path, '/storage/') ? Str::after($path, '/storage/') : null;
    }
}
