<?php

namespace Database\Seeders;

use App\Models\DataAnggota;
use App\Models\Jemaat;
use Illuminate\Database\Seeder;

class DataAnggotaSeeder extends Seeder
{
    public function run(): void
    {
        $jemaatList = Jemaat::all();

        if ($jemaatList->isEmpty()) {
            $this->command->error('Data jemaat belum tersedia. Jalankan JemaatSeeder terlebih dahulu.');
            return;
        }

        $namaAnggota = [
            'Andi Setiawan',
            'Budi Santoso',
            'Citra Lestari',
            'Dewi Anggraini',
            'Eka Pratama',
            'Fajar Nugraha',
            'Grace Natalia',
            'Hendra Wijaya',
            'Indah Permata',
            'Johan Saputra',
            'Kevin Alexander',
            'Lidya Maria',
            'Martha Sari',
            'Nathaniel Putra',
            'Olivia Natalia',
            'Petrus Baan',
            'Rina Puspita',
            'Samuel Rante',
            'Tiara Oktavia',
            'Yohanis Tandirerung',
            'Agus Salu',
            'Bernadeta Liku',
            'Daniel Palino',
            'Esther Rerung',
            'Fransiskus Lamba',
            'Gabriella Toding',
            'Herlin Manda',
            'Irene Aprianti',
            'Markus Pali',
            'Nathanael Sesa',
        ];

        $tempatLahir = [
            'Makassar',
            'Rantepao',
            'Palopo',
            'Toraja',
            'Parepare',
            'Enrekang',
            'Makassar',
            'Tana Toraja',
        ];

        $pendidikan = [
            'SMA/SMK',
            'Diploma',
            'S1',
            'S2',
            'SMP',
        ];

        $golonganDarah = ['A', 'B', 'AB', 'O'];

        $statusAnggota = ['Aktif', 'Aktif', 'Aktif', 'Tidak Aktif'];

        foreach ($namaAnggota as $index => $nama) {

            $jenisKelamin = $index % 2 === 0
                ? 'Laki-laki'
                : 'Perempuan';

            $nomor = str_pad($index + 1, 4, '0', STR_PAD_LEFT);

            DataAnggota::updateOrCreate(
                [
                    'nik' => '737100000000' . $nomor,
                ],
                [
                    'jemaat_id' => $jemaatList[$index % $jemaatList->count()]->id,
                    'no_anggota' => 'PPGT-' . $nomor,
                    'nama' => $nama,
                    'jenis_kelamin' => $jenisKelamin,
                    'tempat_lahir' => $tempatLahir[$index % count($tempatLahir)],
                    'tanggal_lahir' => now()
                        ->subYears(18 + ($index % 15))
                        ->subDays($index * 12)
                        ->format('Y-m-d'),
                    'status_anggota' => $statusAnggota[$index % count($statusAnggota)],
                    'alamat' => 'Jl. Contoh No. ' . ($index + 1) . ', Makassar',
                    'telepon' => '0812345678' . str_pad($index, 2, '0', STR_PAD_LEFT),
                    'email' => 'anggota' . ($index + 1) . '@example.com',
                    'golongan_darah' => $golonganDarah[$index % count($golonganDarah)],
                    'golongan_darah_rhesus' => $index % 2 === 0 ? 'Positif' : 'Negatif',
                    'pendidikan' => $pendidikan[$index % count($pendidikan)],
                ]
            );
        }

        $this->command->info('Berhasil membuat 30 data anggota dummy.');
    }
}
