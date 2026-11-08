<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class AuditLogger
{
    /** Nilainya tidak pernah disimpan. Kolom $hidden di model ikut diperlakukan sama. */
    private const DISEMBUNYIKAN = ['password', 'password_view', 'nik'];

    /** Bukan aktivitas pengguna, jadi perubahannya saja tidak perlu dicatat. */
    private const DIABAIKAN = ['created_at', 'updated_at', 'remember_token'];

    private const KOLOM_LABEL = ['nama', 'name', 'judul', 'nama_peminjam', 'kondisi', 'kode_inventaris'];

    public static function dibuat(Model $model): void
    {
        self::catatModel('dibuat', $model, self::susun($model, $model->getAttributes(), 'baru'));
    }

    public static function dihapus(Model $model): void
    {
        self::catatModel('dihapus', $model, self::susun($model, $model->getAttributes(), 'lama'));
    }

    public static function diubah(Model $model): void
    {
        $perubahan = [];

        foreach (array_diff_key($model->getChanges(), array_flip(self::DIABAIKAN)) as $kolom => $baru) {
            $perubahan[$kolom] = [
                'lama' => self::nilai($model, $kolom, $model->getRawOriginal($kolom)),
                'baru' => self::nilai($model, $kolom, $baru),
            ];
        }

        if ($perubahan !== []) {
            self::catatModel('diubah', $model, $perubahan);
        }
    }

    public static function akses(string $aksi, ?User $user, ?string $label = null): void
    {
        self::tulis($aksi, $user, ['model' => null, 'label' => $label ?? $user?->email]);
    }

    private static function catatModel(string $aksi, Model $model, array $perubahan): void
    {
        if ($model instanceof AuditLog) {
            return;
        }

        self::tulis($aksi, Auth::user(), [
            'model' => class_basename($model),
            'model_id' => $model->getKey(),
            'label' => self::label($model),
            'perubahan' => $perubahan,
        ]);
    }

    // Kegagalan mencatat tidak boleh menggagalkan pekerjaan pengguna, tetapi tetap masuk log aplikasi.
    private static function tulis(string $aksi, ?User $user, array $data): void
    {
        try {
            $data['label'] = isset($data['label']) ? Str::limit((string) $data['label'], 250) : null;

            AuditLog::create($data + [
                'user_id' => $user?->id,
                'user_nama' => $user?->name,
                'aksi' => $aksi,
                'ip' => request()->ip(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private static function susun(Model $model, array $atribut, string $sisi): array
    {
        $hasil = [];

        foreach (array_diff_key($atribut, array_flip(self::DIABAIKAN)) as $kolom => $nilai) {
            if ($nilai !== null) {
                $hasil[$kolom] = [$sisi => self::nilai($model, $kolom, $nilai)];
            }
        }

        return $hasil;
    }

    private static function nilai(Model $model, string $kolom, mixed $nilai): mixed
    {
        if (in_array($kolom, self::DISEMBUNYIKAN, true) || in_array($kolom, $model->getHidden(), true)) {
            return '(disembunyikan)';
        }

        return $nilai instanceof DateTimeInterface ? $nilai->format('Y-m-d H:i:s') : $nilai;
    }

    private static function label(Model $model): ?string
    {
        foreach (self::KOLOM_LABEL as $kolom) {
            $nilai = $model->getAttribute($kolom);

            if (is_scalar($nilai) && $nilai !== '') {
                return (string) $nilai;
            }
        }

        return null;
    }
}
