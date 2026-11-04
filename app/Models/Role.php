<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    const MENU_UMUM = [
        'dashboard' => 'Dashboard',
        'inventaris' => 'Inventaris',
        'peminjaman' => 'Peminjaman',
        'data_anggota' => 'Data Anggota',
        'generator_qr' => 'Generator QR',
    ];

    const MENU_KEPENGURUSAN = [
        'program_kerja' => 'Program Kerja',
        'bidang' => 'Bidang',
        'jabatan' => 'Jabatan',
        'pengurus' => 'Pengurus',
    ];

    protected $fillable = ['nama', 'is_admin', 'menus'];

    protected function casts(): array
    {
        return ['is_admin' => 'boolean', 'menus' => 'array'];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
