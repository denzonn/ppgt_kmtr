<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsenPeserta extends Model
{
    protected $table = 'absen_peserta';

    protected $fillable = ['nama', 'jenis_kelamin', 'asal_jemaat', 'alamat', 'jabatan'];
}
