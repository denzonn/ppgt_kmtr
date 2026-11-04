<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengurus extends Model
{
    protected $table = 'pengurus';

    protected $fillable = ['periode_id', 'jabatan_id', 'data_anggota_id', 'bidang_id'];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function anggota()
    {
        return $this->belongsTo(DataAnggota::class, 'data_anggota_id');
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }
}
