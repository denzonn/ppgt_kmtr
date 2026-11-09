<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventaris extends Model
{
    use SoftDeletes;

    protected $table = 'inventaris';

    protected $fillable = [
        'kode_inventaris',
        'jenis',
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

    public function riwayat()
    {
        return $this->hasMany(InventarisRiwayat::class);
    }

    /** Jumlah per kondisi, misalnya ['Baik' => 5, 'Rusak' => 1]. */
    public function petaKondisi(): array
    {
        return $this->kondisi()->pluck('jumlah', 'kondisi')->map(fn ($j) => (int) $j)->filter()->all();
    }
}
