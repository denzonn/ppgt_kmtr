<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $fillable = ['tahun_mulai', 'tahun_selesai'];

    protected function nama(): Attribute
    {
        return Attribute::get(fn () => "{$this->tahun_mulai}-{$this->tahun_selesai}");
    }

    public function isBerjalan(): bool
    {
        return $this->tahun_mulai <= now()->year && now()->year <= $this->tahun_selesai;
    }

    /** Tahun berjalan, atau tahun terdekat kalau periodenya sudah lewat/belum mulai. */
    public function tahunBerjalan(): int
    {
        return max($this->tahun_mulai, min(now()->year, $this->tahun_selesai));
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function bidangs()
    {
        return $this->hasMany(Bidang::class);
    }

    public function pengurus()
    {
        return $this->hasMany(Pengurus::class);
    }

    public function programKerjas()
    {
        return $this->hasManyThrough(ProgramKerja::class, Bidang::class);
    }

    public function jabatans()
    {
        return $this->hasMany(Jabatan::class);
    }
}
