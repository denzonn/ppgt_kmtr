<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelompok extends Model
{
    protected $table = 'kelompok';

    protected $fillable = ['jemaat_id', 'nama'];

    public function anggota()
    {
        return $this->hasMany(DataAnggota::class);
    }
}
