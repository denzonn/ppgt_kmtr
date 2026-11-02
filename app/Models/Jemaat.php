<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jemaat extends Model
{
    protected $table = 'jemaat';

    protected $fillable = [
        'nama',
    ];

    public function anggota()
    {
        return $this->hasMany(DataAnggota::class, 'jemaat_id');
    }
}
