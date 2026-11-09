<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    const MENU_UMUM = [
        'dashboard' => 'Dashboard',
        'peminjaman' => 'Peminjaman',
        'data_anggota' => 'Data Anggota',
        'generator_qr' => 'Generator QR',
        'persetujuan_artikel' => 'Persetujuan Artikel',
        'tim_tanggap' => 'Golongan Darah (Tim Tanggap)',
        'absen' => 'Absen',
        'linktree' => 'Linktree',
    ];

    const MENU_KEPENGURUSAN = [
        'program_kerja' => 'Program Kerja',
        'bidang' => 'Bidang',
        'jabatan' => 'Jabatan',
        'pengurus' => 'Pengurus',
        'pendampingan' => 'Rekap Pendampingan',
        'inventaris' => 'Inventaris',
    ];

    /** Menu yang hanya berfungsi untuk akun yang terikat ke satu jemaat. */
    const MENU_JEMAAT = [
        'jemaat_kelompok' => 'Kelompok Jemaat',
        'jemaat_bidang' => 'Bidang Jemaat',
        'jemaat_pengurus' => 'Pengurus Jemaat',
        'artikel' => 'Artikel',
        'jemaat_pemilihan' => 'Pemilihan Jemaat',
    ];

    protected $fillable = ['nama', 'is_admin', 'menus'];

    protected function casts(): array
    {
        return ['is_admin' => 'boolean', 'menus' => 'array'];
    }

    public function butuhPeriode(): bool
    {
        return array_intersect([...array_keys(self::MENU_KEPENGURUSAN), 'persetujuan_artikel'], $this->menus ?? []) !== [];
    }

    public function butuhJemaat(): bool
    {
        return array_intersect(array_keys(self::MENU_JEMAAT), $this->menus ?? []) !== [];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
