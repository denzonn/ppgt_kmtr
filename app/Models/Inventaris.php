<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventaris extends Model
{
    protected $table = 'inventaris';

    protected $fillable = [
        'kode_inventaris',
        'foto',
        'nama',
        'tanggal_perolehan',
        'harga',
    ];

    public function peminjaman()
    {
        return $this->hasMany(PeminjamanInventaris::class, 'id_inventaris');
    }

    public function kondisi()
    {
        return $this->hasMany(InventarisKondisi::class, 'id_inventaris');
    }
}
