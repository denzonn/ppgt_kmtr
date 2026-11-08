<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Pengurus extends Model
{
    protected $table = 'pengurus';

    protected $fillable = ['periode_id', 'jabatan_id', 'data_anggota_id', 'foto'];

    protected function nama(): Attribute
    {
        return Attribute::get(fn () => $this->anggota?->nama);
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function anggota()
    {
        return $this->belongsTo(DataAnggota::class, 'data_anggota_id');
    }
}
