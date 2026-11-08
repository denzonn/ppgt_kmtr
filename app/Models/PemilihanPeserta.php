<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemilihanPeserta extends Model
{
    public const PERAN = [
        'utusan_penuh' => 'Utusan Penuh',
        'utusan_cadangan' => 'Utusan Cadangan',
        'demisioner' => 'Pengurus Demisioner',
        'pemilih' => 'Pemilih',
    ];

    public const PERAN_MEMILIH = ['utusan_penuh', 'pemilih'];

    /** Tiap jemaat mengutus 3 utusan penuh ke Konferensi (ART Pasal 9 ayat 10). */
    public const MAKS_UTUSAN_PENUH = 3;

    public const PERAN_CALON = ['utusan_penuh', 'demisioner', 'pemilih'];

    // Tanpa huruf dan angka yang mudah tertukar, seperti O dan 0.
    private const HURUF_KODE = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    protected $table = 'pemilihan_peserta';

    protected $fillable = ['pemilihan_id', 'jemaat_id', 'data_anggota_id', 'nama', 'peran', 'kode'];

    // Juga membuat kode tidak ikut tercatat di audit log.
    protected $hidden = ['kode'];

    public function pemilihan()
    {
        return $this->belongsTo(Pemilihan::class);
    }

    public function jemaat()
    {
        return $this->belongsTo(Jemaat::class);
    }

    public function anggota()
    {
        return $this->belongsTo(DataAnggota::class, 'data_anggota_id');
    }

    public function bisaMemilih(): bool
    {
        return in_array($this->peran, self::PERAN_MEMILIH, true);
    }

    public function bisaDicalonkan(): bool
    {
        return in_array($this->peran, self::PERAN_CALON, true);
    }

    public static function kodeBaru(int $pemilihanId): string
    {
        do {
            $kode = '';
            for ($i = 0; $i < 6; $i++) {
                $kode .= self::HURUF_KODE[random_int(0, strlen(self::HURUF_KODE) - 1)];
            }
        } while (self::where('pemilihan_id', $pemilihanId)->where('kode', $kode)->exists());

        return $kode;
    }
}
