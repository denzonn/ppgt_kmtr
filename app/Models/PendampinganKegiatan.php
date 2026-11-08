<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendampinganKegiatan extends Model
{
    protected $table = 'pendampingan_kegiatan';

    protected $fillable = ['tanggal', 'nama', 'hadir', 'sambutan', 'sumber'];

    protected $casts = ['tanggal' => 'date', 'hadir' => 'array', 'sambutan' => 'array'];

    public function scopeMilikPeriode($query, Periode $periode)
    {
        return $query->whereYear('tanggal', '>=', $periode->tahun_mulai)->whereYear('tanggal', '<=', $periode->tahun_selesai);
    }
}
