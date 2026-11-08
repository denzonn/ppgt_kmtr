<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $fillable = ['periode_id', 'bidang_id', 'nama'];

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function pengurus()
    {
        return $this->hasMany(Pengurus::class);
    }
}
