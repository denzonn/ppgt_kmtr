<?php

namespace Database\Seeders;

use App\Http\Controllers\Admin\InventarisController;
use App\Models\Inventaris;
use App\Models\InventarisKondisi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Daftar Inventaris PPGT Klasis Makassar Timur Tahun 2025 (per 26 Oktober 2025).
 *
 * - harga: kolom "Nilai Buku Akhir" pada dokumen, nilai untuk seluruh jumlah barang.
 * - tanggal: dokumen yang hanya menulis tahun/bulan memakai hari pertama periode itu;
 *   perolehan bertahap memakai tanggal paling awal.
 * - Jenis (PAK/PDP/PPL) tidak ada pada dokumen, hasil pengelompokan saat input.
 *
 * Aman dijalankan ulang: baris dengan nama, jenis, dan tanggal yang sama dilewati.
 * php artisan db:seed --class=InventarisSeeder
 */
class InventarisSeeder extends Seeder
{
    // [no dokumen, nama, jenis, tanggal, harga, kondisi]
    public const DATA = [
        [1, 'PRINTER LASERJET P1102 (HP)', 'PAK', '2017-01-01', 200000, ['Kurang Baik' => 1]],
        [2, 'PRINTER (CANON)', 'PAK', '2022-01-01', 300000, ['Kurang Baik' => 1]],
        [3, 'PLAKAT/PIAGAM', 'PAK', '2017-01-01', 0, ['Baik' => 1]],
        [4, 'PLAKAT/PIAGAM', 'PAK', '2019-01-01', 0, ['Baik' => 8]],
        [5, 'PLAKAT/PIAGAM', 'PAK', '2020-01-01', 0, ['Baik' => 4]],
        [6, 'PLAKAT/PIAGAM', 'PAK', '2022-01-01', 0, ['Baik' => 6]],
        [7, 'PLAKAT/PIAGAM', 'PAK', '2023-12-01', 0, ['Baik' => 1]],
        [8, 'STEMPEL PENGURUS', 'PAK', '2017-01-01', 0, ['Baik' => 1]],
        [9, 'BENDERA PPGT', 'PAK', '2017-01-01', 0, ['Baik' => 2]],
        [10, 'BENDERA MERAH PUTIH', 'PAK', '2019-01-01', 10000, ['Baik' => 1]],
        [11, 'BENDERA MERAH PUTIH', 'PAK', '2017-01-01', 30000, ['Baik' => 1]],
        [12, 'PAPAN TULIS', 'PAK', '2017-01-01', 30000, ['Baik' => 1]],
        [13, 'FOTO PENGURUS', 'PAK', '2017-01-01', 0, ['Baik' => 8]],
        [14, 'FOTO PENGURUS', 'PAK', '2021-01-01', 0, ['Baik' => 1]],
        [15, 'ALBUM FOTO', 'PAK', '2019-01-01', 0, ['Baik' => 21]],
        [16, 'LAPTOP (ACER)', 'PAK', '2020-01-01', 500000, ['Baik' => 1]],
        [17, 'PAPAN POTENSI', 'PAK', '2020-01-01', 0, ['Baik' => 1]],
        [18, 'PAPAN NAMA SEKRET', 'PAK', '2020-01-01', 0, ['Baik' => 1]],
        [19, 'STAN BENDERA', 'PAK', '2022-01-01', 30000, ['Baik' => 2]],
        [20, 'STEMPEL PANITIA/TIMKER', 'PAK', '2022-01-01', 0, ['Baik' => 3]],
        [21, 'PALU SIDANG', 'PAK', '2022-01-01', 20000, ['Baik' => 1]],
        [22, 'BENDERA TIM TANGGAP', 'PAK', '2023-01-25', 0, ['Baik' => 1]],
        [23, 'HARDISK', 'PAK', '2022-01-01', 300000, ['Baik' => 1]],
        [24, 'PIRING (PLASTIK)', 'PDP', '2019-01-01', 100000, ['Baik' => 130]],
        [25, 'PIRING (KACA)', 'PDP', '2021-01-01', 25000, ['Baik' => 55]],
        [26, 'GELAS (PLASTIK)', 'PDP', '2019-01-01', 100000, ['Baik' => 145]],
        [27, 'GELAS (KACA)', 'PDP', '2021-01-01', 20000, ['Baik' => 35]],
        [28, 'PIRING KECIL (TATAKAN)', 'PDP', '2019-01-01', 8000, ['Baik' => 6]],
        [29, 'SENDOK NASI', 'PDP', '2019-01-01', 5000, ['Baik' => 1]],
        [30, 'BASKOM', 'PDP', '2019-01-01', 0, ['Rusak' => 2]],
        [31, 'NAMPAN/BAKI', 'PDP', '2019-01-01', 0, ['Baik' => 1]],
        [32, 'NAMPAN/BAKI', 'PDP', '2021-01-01', 20000, ['Baik' => 2]],
        [33, 'NAMPAN/BAKI', 'PDP', '2022-01-01', 20000, ['Baik' => 2]],
        [34, 'KOMPOR GAS', 'PDP', '2019-01-01', 50000, ['Baik' => 1]],
        [35, 'PANCI', 'PDP', '2019-01-01', 0, ['Rusak' => 1]],
        [36, 'RAK PIRING', 'PDP', '2019-01-01', 30000, ['Baik' => 2]],
        [37, 'PENAMPUNGAN AIR', 'PDP', '2019-01-01', 20000, ['Baik' => 1]],
        [38, 'SODET', 'PDP', '2022-01-01', 10000, ['Baik' => 3]],
        [39, 'MANGKOK', 'PDP', '2021-01-01', 10000, ['Baik' => 3]],
        [40, 'PARUTAN', 'PDP', '2022-01-01', 10000, ['Baik' => 3]],
        [41, 'NYIRU PLASTIK', 'PDP', '2022-01-01', 10000, ['Baik' => 1]],
        [42, 'SENDOK SAYUR', 'PDP', '2022-01-01', 5000, ['Baik' => 1]],
        [43, 'HEKTER TEMBAK', 'PAK', '2022-01-01', 10000, ['Baik' => 1]],
        [44, 'HEKTER TEMBAK', 'PAK', '2024-01-19', 50000, ['Rusak' => 3]],
        [45, 'BOX KONTAINER', 'PPL', '2022-12-13', 300000, ['Baik' => 3, 'Kurang Baik' => 1]],
        [46, 'BOX KONTAINER', 'PPL', '2024-03-01', 350000, ['Baik' => 4]],
        [47, "LAKKA'", 'PPL', '2023-01-01', 40000, ['Baik' => 13, 'Hilang' => 11]],
        [48, 'GALON', 'PDP', '2023-01-01', 20000, ['Baik' => 1]],
        [49, 'DISPENSER', 'PDP', '2024-01-01', 100000, ['Baik' => 1]],
        [50, 'LEM TEMBAK', 'PPL', '2024-05-12', 20000, ['Baik' => 1]],
        [51, 'PEMOTONG GABUS', 'PPL', '2024-05-12', 20000, ['Baik' => 1]],
        [52, 'JUMBO AIR', 'PDP', '2024-05-12', 150000, ['Baik' => 1]],
        [53, 'STAN GALON', 'PDP', '2024-09-13', 50000, ['Baik' => 1]],
        [54, 'KOTAK MAKANAN', 'PDP', '2024-01-01', 5000, ['Baik' => 1]],
        [55, 'LAMPU SOROT (TEBAL)', 'PPL', '2017-01-01', 30000, ['Kurang Baik' => 1]],
        [56, 'LAMPU SOROT (KOTAK)', 'PPL', '2022-01-01', 50000, ['Kurang Baik' => 2]],
        [57, 'LAMPU SOROT (KOTAK)', 'PPL', '2024-01-19', 80000, ['Kurang Baik' => 2]],
        [58, 'LAMPU SOROT (BULAT)', 'PPL', '2024-05-29', 30000, ['Baik' => 2]],
        [59, 'PROYEKTOR (ACER)', 'PPL', '2017-01-01', 500000, ['Kurang Baik' => 1]],
        [60, 'PROYEKTOR (CHEERLUX)', 'PPL', '2023-12-22', 3800000, ['Baik' => 1]],
        [61, 'TOA', 'PPL', '2017-01-01', 30000, ['Baik' => 1]],
        [62, 'SPEAKER', 'PPL', '2019-01-01', 300000, ['Baik' => 1]],
        [63, 'SPEAKER', 'PPL', '2023-01-01', 800000, ['Baik' => 1]],
        [64, 'KOTAK P3K', 'PPL', '2019-01-01', 50000, ['Baik' => 1]],
        [65, 'LEMARI', 'PAK', '2019-01-01', 250000, ['Baik' => 1, 'Kurang Baik' => 1, 'Rusak' => 1]],
        [66, 'LEMARI', 'PAK', '2024-01-25', 500000, ['Baik' => 2]],
        [67, 'MEJA (KANTOR)', 'PAK', '2019-01-01', 100000, ['Baik' => 1]],
        [68, 'MEJA (PENDEK)', 'PPL', '2019-01-01', 20000, ['Baik' => 1]],
        [69, 'TERPAL', 'PPL', '2019-01-01', 20000, ['Baik' => 1]],
        [70, 'TERPAL', 'PPL', '2024-05-12', 200000, ['Baik' => 3]],
        [71, 'CATUR', 'PPL', '2019-01-01', 30000, ['Baik' => 2]],
        [72, 'CAMERA (CANON)', 'PPL', '2019-01-01', 500000, ['Baik' => 1]],
        [73, 'TERMINAL LISTRIK', 'PPL', '2019-01-01', 50000, ['Baik' => 3]],
        [74, 'PENDINGIN RUANGAN (AC)', 'PPL', '2019-01-01', 1500000, ['Baik' => 2]],
        [75, 'BUKU BACAAN', 'PAK', '2019-01-01', 120000, ['Baik' => 42]],
        [76, 'BUKU BACAAN', 'PAK', '2023-01-01', 0, ['Rusak' => 40]],
        [77, 'BUKU BACAAN', 'PAK', '2024-01-01', 0, ['Rusak' => 19]],
        [78, 'GITAR', 'PPL', '2019-01-01', 300000, ['Baik' => 1]],
        [79, 'ROMPI (FUTSAL)', 'PPL', '2021-01-01', 0, ['Hilang' => 22]],
        [80, 'ROMPI (TIM TANGGAP)', 'PPL', '2021-01-01', 0, ['Baik' => 7]],
        [81, 'JAM DINDING', 'PPL', '2021-01-01', 10000, ['Baik' => 1]],
        [82, 'JAM DINDING', 'PPL', '2023-01-01', 10000, ['Baik' => 2]],
        [83, 'SARUNG BANTAL', 'PPL', '2021-01-01', 0, ['Hilang' => 8]],
        [84, 'KAIN UKIRAN TORAJA', 'PPL', '2022-01-01', 20000, ['Baik' => 2]],
        [85, 'SELIMUT', 'PPL', '2022-01-01', 30000, ['Baik' => 3]],
        [86, 'NET (TAKRAW)', 'PPL', '2023-08-01', 30000, ['Baik' => 3]],
        [87, 'NET (VOLLY)', 'PPL', '2023-08-01', 20000, ['Baik' => 2]],
        [88, 'BOLA (TENNIS)', 'PPL', '2023-08-01', 20000, ['Baik' => 7]],
        [89, 'BOLA (VOLLY)', 'PPL', '2023-08-01', 30000, ['Baik' => 3]],
        [90, 'SEPATU LARAS', 'PPL', '2022-12-01', 50000, ['Baik' => 1]],
        [91, 'TALI TAMBANG PLASTIK', 'PPL', '2022-12-01', 20000, ['Baik' => 1]],
        [92, 'HT', 'PPL', '2022-12-01', 250000, ['Baik' => 5]],
        [93, 'LIFE JAKET (DEWASA)', 'PPL', '2022-12-01', 50000, ['Baik' => 2]],
        [94, 'LIFE JAKET (ANAK)', 'PPL', '2022-12-01', 20000, ['Baik' => 1]],
        [95, 'BAN PENYELAMAT/RINGBUOY', 'PPL', '2022-12-01', 100000, ['Baik' => 1]],
        [96, 'KURSI KAYU', 'PPL', '2022-01-01', 10000, ['Baik' => 1]],
        [97, 'SOFA', 'PPL', '2022-01-01', 200000, ['Baik' => 2]],
        [98, 'PENGHAPUS PAPA TULIS', 'PAK', '2021-01-01', 10000, ['Baik' => 2]],
        [99, "PA'TONDRO", 'PPL', '2022-12-01', 200000, ['Baik' => 4]],
        [100, 'SAPU IJUK', 'PPL', '2021-01-01', 0, ['Baik' => 2]],
        [101, 'BAJU PENARI', 'PPL', '2024-05-29', 50000, ['Baik' => 7]],
        [102, 'MANSET', 'PPL', '2024-05-29', 30000, ['Baik' => 3]],
        [103, 'SELENDANG PUTIH', 'PPL', '2024-05-29', 30000, ['Baik' => 9]],
        [104, 'DAUN KAWAT', 'PPL', '2024-05-29', 50000, ['Baik' => 2]],
        [105, 'CCTV', 'PPL', '2024-05-11', 600000, ['Baik' => 2]],
        [106, 'BATOK CHARGER CCTV', 'PPL', '2024-05-29', 50000, ['Baik' => 2]],
        [107, 'MEMORY CCTV', 'PPL', '2024-05-29', 300000, ['Baik' => 1]],
        [108, 'FITTING LAMPU', 'PPL', '2024-05-12', 40000, ['Baik' => 4]],
        [109, 'SOLDER', 'PPL', '2024-05-12', 10000, ['Baik' => 1]],
        [110, 'LAYAR PROYEKTOR', 'PPL', '2023-12-22', 350000, ['Baik' => 1]],
        [111, 'TANDU', 'PPL', '2024-03-22', 350000, ['Baik' => 1]],
        [112, 'RANJANG LIPAT', 'PPL', '2024-03-23', 150000, ['Baik' => 1]],
        [113, 'RANSEL EMERGENCY KIT', 'PPL', '2024-03-26', 100000, ['Baik' => 1]],
        [114, 'PRINTER (EPSON L3210)', 'PAK', '2024-01-01', 2000000, ['Baik' => 1]],
        [115, 'MEJA PENDEK', 'PPL', '2024-10-10', 300000, ['Baik' => 1]],
        [116, 'KURSI PELASTIK', 'PPL', '2025-05-01', 2280000, ['Baik' => 24]],
        [117, 'MEJA PELASTIK', 'PPL', '2025-05-01', 130000, ['Baik' => 2]],
        [118, 'BANTAL', 'PPL', '2025-09-01', 200000, ['Baik' => 10]],
        [119, 'KARPET', 'PPL', '2025-09-01', 400000, ['Baik' => 2]],
        [120, 'POMPA AIR', 'PPL', '2025-09-01', 30000, ['Baik' => 2]],
        [121, 'BASKOM STENLIS', 'PDP', '2025-09-01', 20000, ['Baik' => 1]],
        [122, 'SPEAKER POLYTRON', 'PPL', '2025-09-01', 2700000, ['Baik' => 1]],
        [123, 'BOLAM', 'PPL', '2025-07-01', 530000, ['Baik' => 38, 'Kurang Baik' => 2]],
        [124, 'KABEL TEBAL 50M', 'PPL', '2025-07-01', 494000, ['Baik' => 1]],
        [125, 'BOLA TAKRAW', 'PPL', '2025-07-01', 200000, ['Baik' => 2]],
        [126, 'NET TAKRAW', 'PPL', '2025-07-01', 100000, ['Baik' => 1]],
        [127, 'NET TENIS MEJA', 'PPL', '2025-07-01', 100000, ['Baik' => 1]],
        [128, 'KABEL LAMPU', 'PPL', '2025-07-01', 700000, ['Baik' => 3, 'Kurang Baik' => 1]],
        [129, 'KARPET PUZZEL', 'PPL', '2025-05-01', 700000, ['Baik' => 1]],
        [131, 'MIC', 'PPL', '2025-08-18', 170000, ['Baik' => 1]],
        [132, 'STAND LCD', 'PPL', '2025-08-18', 100000, ['Baik' => 1]],
        [133, 'ROMPI TITAN', 'PPL', '2022-12-01', 40000, ['Baik' => 3]],
        [134, 'SEPATU BOOTH', 'PPL', '2024-12-01', 30000, ['Baik' => 1]],
        [135, 'SENTER', 'PPL', '2025-01-01', 480000, ['Baik' => 1]],
        [136, 'BAN PELASTIK', 'PPL', '2022-12-01', 10000, ['Baik' => 1]],
        [137, 'LINGGIS', 'PPL', '2025-01-01', 50000, ['Baik' => 1]],
        [138, 'SEKOPANG', 'PPL', '2025-01-01', 58000, ['Baik' => 1]],
        [139, 'CANGKUL', 'PPL', '2025-01-01', 45000, ['Baik' => 1]],
        [140, 'PENYANGGA TANGAN', 'PPL', '2025-03-01', 78000, ['Baik' => 1]],
        [141, 'PARANG', 'PPL', '2025-05-01', 15000, ['Baik' => 1]],
        [142, 'PERAHU', 'PPL', '2024-11-01', 150000, ['Baik' => 1]],
        [143, 'PERAHU KARET', 'PPL', '2024-12-01', 4800000, ['Baik' => 1]],
        [144, 'POMPA PERAHU', 'PPL', '2024-12-01', 60000, ['Baik' => 1]],
        [145, 'PALU', 'PPL', '2025-02-01', 25000, ['Baik' => 1]],
        [146, 'BAN KARET', 'PPL', '2024-12-01', 400000, ['Baik' => 3]],
        [147, 'LIFE JAKET', 'PPL', '2024-12-01', 50000, ['Baik' => 3]],
        [148, 'COOL BOX', 'PPL', '2023-05-01', 10000, ['Baik' => 1]],
        [149, 'KOTAK P3K', 'PPL', '2023-05-01', 30000, ['Baik' => 1]],
        [150, 'TAS P3K', 'PPL', '2023-06-01', 100000, ['Baik' => 1]],
        [151, 'GEMBOK', 'PPL', '2024-05-08', 25000, ['Baik' => 1]],
    ];

    public function run(): void
    {
        $generator = app(InventarisController::class);

        DB::transaction(function () use ($generator) {
            foreach (self::DATA as [, $nama, $jenis, $tanggal, $harga, $kondisi]) {
                $sudahAda = Inventaris::where('nama', $nama)
                    ->where('jenis', $jenis)
                    ->whereDate('tanggal_perolehan', $tanggal)
                    ->exists();

                if ($sudahAda) {
                    continue;
                }

                $inventaris = Inventaris::create([
                    'kode_inventaris' => $generator->generateKodeInventaris($jenis),
                    'jenis' => $jenis,
                    'nama' => $nama,
                    'tanggal_perolehan' => $tanggal,
                    'harga' => $harga,
                ]);

                foreach ($kondisi as $status => $jumlah) {
                    InventarisKondisi::create([
                        'id_inventaris' => $inventaris->id,
                        'kondisi' => $status,
                        'jumlah' => $jumlah,
                    ]);
                }
            }
        });
    }
}
