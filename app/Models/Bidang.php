<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    protected $fillable = ['periode_id', 'nama'];

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }
}
