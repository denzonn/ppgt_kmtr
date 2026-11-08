<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absen extends Model
{
    protected $table = 'absen';

    protected $fillable = ['nama', 'token', 'jabatan'];

    protected $casts = ['jabatan' => 'array'];

    public function peserta()
    {
        return $this->hasMany(AbsenPeserta::class);
    }
}
