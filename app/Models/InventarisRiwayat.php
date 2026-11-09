<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarisRiwayat extends Model
{
    protected $table = 'inventaris_riwayat';

    protected $fillable = ['inventaris_id', 'periode_id', 'user_id', 'aksi', 'kondisi_sebelum', 'kondisi_sesudah', 'keterangan'];

    protected $casts = ['kondisi_sebelum' => 'array', 'kondisi_sesudah' => 'array'];

    public function inventaris()
    {
        return $this->belongsTo(Inventaris::class)->withTrashed();
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
