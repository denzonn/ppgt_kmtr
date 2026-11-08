<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    const UPDATED_AT = null;

    /** Nama yang tampil untuk tiap jenis data. */
    const OBJEK = [
        'Absen' => 'Absen',
        'Artikel' => 'Artikel',
        'JemaatBidang' => 'Bidang Jemaat',
        'JemaatPengurus' => 'Pengurus Jemaat',
        'AbsenPeserta' => 'Peserta Absen',
        'Bidang' => 'Bidang',
        'DataAnggota' => 'Data Anggota',
        'Inventaris' => 'Inventaris',
        'InventarisKondisi' => 'Kondisi Inventaris',
        'Jabatan' => 'Jabatan',
        'Jemaat' => 'Jemaat',
        'Kegiatan' => 'Kegiatan',
        'Kelompok' => 'Kelompok Jemaat',
        'Linktree' => 'Linktree',
        'PeminjamanDetail' => 'Detail Peminjaman',
        'PeminjamanInventaris' => 'Peminjaman',
        'Pengurus' => 'Pengurus',
        'Pemilihan' => 'Pemilihan',
        'PemilihanCalon' => 'Calon Pemilihan',
        'PemilihanJabatan' => 'Jabatan Pemilihan',
        'PemilihanPeserta' => 'Peserta Pemilihan',
        'PemilihanPutaran' => 'Pemungutan Suara',
        'PendampinganKegiatan' => 'Rekap Pendampingan',
        'Periode' => 'Periode',
        'ProgramKerja' => 'Program Kerja',
        'QRCodeLink' => 'QR Code',
        'Role' => 'Role',
        'User' => 'Pengguna',
    ];

    protected $table = 'audit_log';

    protected $fillable = ['user_id', 'user_nama', 'aksi', 'model', 'model_id', 'label', 'perubahan', 'ip'];

    protected $casts = ['perubahan' => 'array'];
}
