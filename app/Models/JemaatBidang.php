<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JemaatBidang extends Model
{
    protected $table = 'jemaat_bidang';

    protected $fillable = ['jemaat_id', 'nama', 'jumlah_anggota'];

    public function pengurus()
    {
        return $this->hasMany(JemaatPengurus::class);
    }
}
