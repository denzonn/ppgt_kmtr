<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemilihanCalon extends Model
{
    public $timestamps = false;

    protected $table = 'pemilihan_calon';

    protected $fillable = ['putaran_id', 'peserta_id'];

    public function putaran()
    {
        return $this->belongsTo(PemilihanPutaran::class, 'putaran_id');
    }

    public function peserta()
    {
        return $this->belongsTo(PemilihanPeserta::class, 'peserta_id');
    }
}
