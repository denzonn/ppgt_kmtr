<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $fillable = ['periode_id', 'nama', 'terkait_bidang'];

    protected $casts = ['terkait_bidang' => 'boolean'];

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }
}
