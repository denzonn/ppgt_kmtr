<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeminjamanDetail extends Model
{
    protected $table = 'peminjaman_detail';

    protected $fillable = [
        'id_peminjaman',
        'id_inventaris',
        'jumlah',
    ];

    public function peminjaman()
    {
        return $this->belongsTo(PeminjamanInventaris::class, 'id_peminjaman');
    }

    public function inventaris()
    {
        return $this->belongsTo(Inventaris::class, 'id_inventaris');
    }
}
