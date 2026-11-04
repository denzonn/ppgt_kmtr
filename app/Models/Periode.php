<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $fillable = ['tahun_mulai', 'tahun_selesai'];

    protected function nama(): Attribute
    {
        return Attribute::get(fn () => "{$this->tahun_mulai}-{$this->tahun_selesai}");
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function bidangs()
    {
        return $this->hasMany(Bidang::class);
    }

    public function pengurus()
    {
        return $this->hasMany(Pengurus::class);
    }

    public function jabatans()
    {
        return $this->hasMany(Jabatan::class);
    }
}
