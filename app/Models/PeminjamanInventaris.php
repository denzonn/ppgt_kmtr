<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeminjamanInventaris extends Model
{
    protected $table = 'peminjaman_inventaris';

    protected $fillable = [
        'foto_pengambilan',
        'nama_peminjam',
        'foto_pengembalian',
        'tanggal_peminjaman',
        'tanggal_pengembalian',
        'status',
    ];

    protected $casts = [
        'tanggal_peminjaman'   => 'datetime',
        'tanggal_pengembalian' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(PeminjamanDetail::class, 'id_peminjaman');
    }

    public function getTotalBarangAttribute()
    {
        return $this->details->sum('jumlah');
    }
}
