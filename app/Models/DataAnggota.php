<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataAnggota extends Model
{
    protected $table = 'data_anggota';

    protected $fillable = [
        'jemaat_id',
        'kelompok_id',
        'no_anggota',
        'nik',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'status_anggota',
        'alamat',
        'telepon',
        'email',
        'golongan_darah',
        'golongan_darah_rhesus',
        'pendidikan',
        'status_wilayah',
    ];

    public function kelompok()
    {
        return $this->belongsTo(Kelompok::class);
    }

    public function jemaat()
    {
        return $this->belongsTo(Jemaat::class, 'jemaat_id');
    }
}
