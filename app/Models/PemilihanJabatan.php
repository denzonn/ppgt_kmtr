<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemilihanJabatan extends Model
{
    protected $table = 'pemilihan_jabatan';

    protected $fillable = ['pemilihan_id', 'terpilih_id', 'nama'];

    public function pemilihan()
    {
        return $this->belongsTo(Pemilihan::class);
    }

    public function tahap()
    {
        return $this->hasMany(PemilihanPutaran::class, 'jabatan_id')->orderBy('id');
    }

    public function terpilih()
    {
        return $this->belongsTo(PemilihanPeserta::class, 'terpilih_id');
    }

    /** Tahap terakhir yang menentukan langkah berikutnya. Butuh relasi tahap sudah dimuat. */
    public function tahapTerakhir(): ?PemilihanPutaran
    {
        return $this->tahap->last();
    }

    /** Tahap baru hanya boleh dibuat saat belum ada tahap, atau tahap terakhir sudah ditutup tanpa penetapan. */
    public function bisaTambahTahap(): bool
    {
        return ! $this->terpilih_id && (! $this->tahapTerakhir() || $this->tahapTerakhir()->status === 'ditutup');
    }

    public function status(): string
    {
        return match (true) {
            (bool) $this->terpilih_id => 'Selesai',
            $this->tahap->contains('status', 'dibuka') => 'Pemungutan dibuka',
            $this->tahap->isEmpty() => 'Belum dimulai',
            default => 'Berlangsung',
        };
    }
}
