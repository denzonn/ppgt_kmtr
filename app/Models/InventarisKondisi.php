<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarisKondisi extends Model
{
    protected $table = 'inventaris_kondisi';

    protected $fillable = [
        'id_inventaris',
        'kondisi',
        'jumlah',
    ];

    public function inventaris()
    {
        return $this->belongsTo(Inventaris::class, 'id_inventaris');
    }
}
