<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramKerja extends Model
{
    protected $fillable = ['bidang_id', 'tahun', 'nama'];

    public function scopeMilikPeriode($q, Periode $p)
    {
        return $q->whereHas('bidang', fn ($b) => $b->where('periode_id', $p->id));
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function kegiatans()
    {
        return $this->hasMany(Kegiatan::class);
    }
}
