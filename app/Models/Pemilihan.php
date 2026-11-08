<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemilihan extends Model
{
    protected $table = 'pemilihan';

    protected $fillable = ['lingkup', 'jemaat_id', 'nama', 'token'];

    protected $hidden = ['token'];

    public function jemaat()
    {
        return $this->belongsTo(Jemaat::class);
    }

    public function peserta()
    {
        return $this->hasMany(PemilihanPeserta::class);
    }

    public function putaran()
    {
        return $this->hasMany(PemilihanPutaran::class);
    }

    public function jabatan()
    {
        return $this->hasMany(PemilihanJabatan::class)->orderBy('id');
    }

    public function putaranDibuka(): ?PemilihanPutaran
    {
        return $this->putaran()->with('jabatan')->where('status', 'dibuka')->first();
    }

    public function jumlahHakSuara(): int
    {
        return $this->peserta()->whereIn('peran', PemilihanPeserta::PERAN_MEMILIH)->count();
    }

    /** Peran yang boleh dipakai di tiap lingkup pemilihan. */
    public function pilihanPeran(): array
    {
        return $this->lingkup === 'klasis'
            ? array_intersect_key(PemilihanPeserta::PERAN, array_flip(['utusan_penuh', 'utusan_cadangan', 'demisioner']))
            : array_intersect_key(PemilihanPeserta::PERAN, array_flip(['pemilih']));
    }
}
