<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class JemaatPengurus extends Model
{
    protected $table = 'jemaat_pengurus';

    protected $fillable = ['jemaat_bidang_id', 'data_anggota_id', 'jabatan', 'foto'];

    protected function nama(): Attribute
    {
        return Attribute::get(fn () => $this->anggota?->nama);
    }

    public function bidang()
    {
        return $this->belongsTo(JemaatBidang::class, 'jemaat_bidang_id');
    }

    public function anggota()
    {
        return $this->belongsTo(DataAnggota::class, 'data_anggota_id');
    }

    public function scopeMilikJemaat($query, int $jemaatId)
    {
        return $query->whereHas('bidang', fn ($q) => $q->where('jemaat_id', $jemaatId));
    }
}
