<?php

namespace App\Models;

use App\Support\KontenArtikel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Artikel extends Model
{
    protected $fillable = ['jemaat_id', 'judul', 'penulis', 'foto', 'isi', 'status', 'alasan_tolak', 'diperiksa_oleh', 'diperiksa_pada'];

    protected $casts = ['diperiksa_pada' => 'datetime'];

    /** Disaring di sini supaya isi selalu aman, dari jalur mana pun disimpan. */
    protected function isi(): Attribute
    {
        return Attribute::set(fn ($nilai) => KontenArtikel::bersihkan($nilai));
    }

    /** Hanya artikel yang sudah disetujui yang boleh dilihat pengunjung. */
    public function scopeTayang(Builder $query): Builder
    {
        return $query->where('status', 'disetujui');
    }

    public function scopeTerbaru(Builder $query): Builder
    {
        return $query->latest('diperiksa_pada')->latest('id');
    }

    public function urlBaca(): string
    {
        return route('artikel.baca', ['id' => $this->id, 'slug' => Str::slug($this->judul) ?: null]);
    }

    public function tanggalTayang(): string
    {
        return ($this->diperiksa_pada ?? $this->created_at)->timezone('Asia/Makassar')->translatedFormat('d F Y');
    }

    public function jemaat()
    {
        return $this->belongsTo(Jemaat::class);
    }
}
