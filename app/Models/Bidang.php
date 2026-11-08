<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    public const DEFAULT_NAMA = [
        'Bidang Umum',
        'Bidang Pengakaran Iman & Spiritualitas',
        'Bidang Kaderisasi & Organisasi',
        'Bidang Pengembang SDM',
        'Bidang Pelayanan Sosial & Kemasyarakatan',
    ];

    protected $fillable = ['periode_id', 'nama', 'jumlah_anggota'];

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function programKerjas()
    {
        return $this->hasMany(ProgramKerja::class);
    }

    public function jabatans()
    {
        return $this->hasMany(Jabatan::class);
    }

    public function pengurus()
    {
        return $this->hasManyThrough(Pengurus::class, Jabatan::class);
    }
}
