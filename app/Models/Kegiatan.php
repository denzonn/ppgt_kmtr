<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    protected $fillable = ['program_kerja_id', 'nama', 'deskripsi', 'anggaran', 'bulan', 'foto', 'link'];

    protected $casts = ['bulan' => 'array', 'anggaran' => 'integer'];

    public function scopeMilikPeriode($q, Periode $p)
    {
        return $q->whereHas('programKerja', fn ($pk) => $pk->milikPeriode($p));
    }

    public function programKerja()
    {
        return $this->belongsTo(ProgramKerja::class);
    }
}
