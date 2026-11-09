<?php

use App\Support\RekapPendampingan;

test('membaca contoh rekap dari pengurus', function () {
    $teks = <<<'TEKS'
[🤝 PJ Pendampingan/Kegiatan PK : 🤝]

🟦 November 2026 🌈:

TANGGAL 01 November (MINGGU)
* Rapat SC RA Satria Kasih
Hadir : Denson, Bill, Rocky

TANGGAL 02 NOVEMBER (SENIN)
* Review Wall PPGT Biringkanaya
Hadir : Kak Mikel, Yuan, Tryfena, Fila

* Rapat SC RA Tamalanrea
Hadir : Rocky, Cici

TANGGAL 03 NOVEMBER (SELASA)
* Bazar Tempat Pan. Natal PPGT KMTr
Hadir : Kak Rahel, Kak Sheva, Tryfena, Yuan, Filo, Nia, Dita, Yeris, Fila, Rocky, Kak Yerika, Igun, Boy, Kak Mikel, Denson, Silvy, Cici

* Rapat Final Check RA Tamalanrea
Hadir : Kak Mikel, Nia, Rocky, Kak Rahel
TEKS;

    $hasil = RekapPendampingan::baca($teks, 2020);

    expect($hasil['abaikan'])->toBe([])
        ->and($hasil['kegiatan'])->toHaveCount(5)
        ->and($hasil['kegiatan'][0])->toBe(['tanggal' => '2026-11-01', 'nama' => 'Rapat SC RA Satria Kasih', 'hadir' => ['Denson', 'Bill', 'Rocky'], 'sambutan' => []])
        ->and($hasil['kegiatan'][2])->toBe(['tanggal' => '2026-11-02', 'nama' => 'Rapat SC RA Tamalanrea', 'hadir' => ['Rocky', 'Cici'], 'sambutan' => []])
        ->and($hasil['kegiatan'][3]['nama'])->toBe('Bazar Tempat Pan. Natal PPGT KMTr')
        ->and($hasil['kegiatan'][3]['hadir'])->toHaveCount(17)
        ->and($hasil['kegiatan'][3]['hadir'][0])->toBe('Kak Rahel');
});

test('variasi penulisan tetap terbaca', function () {
    $teks = <<<'TEKS'
Tgl 5 Des 2026
- Ibadah Pemuda
hadir: Andi dan Budi; Citra & Dewi,
Eka, Fani
1. Rapat Panitia
Hadir :
TEKS;

    $hasil = RekapPendampingan::baca($teks);

    expect($hasil['kegiatan'])->toBe([
        ['tanggal' => '2026-12-05', 'nama' => 'Ibadah Pemuda', 'hadir' => ['Andi', 'Budi', 'Citra', 'Dewi', 'Eka', 'Fani'], 'sambutan' => []],
        ['tanggal' => '2026-12-05', 'nama' => 'Rapat Panitia', 'hadir' => [], 'sambutan' => []],
    ]);
});

test('tahun mengikuti judul bulan dan nama dobel dihitung sekali', function () {
    $hasil = RekapPendampingan::baca("Januari 2027\nTANGGAL 3 JANUARI\n* Retret\nHadir : Rocky, rocky, Cici", 2026);

    expect($hasil['kegiatan'][0]['tanggal'])->toBe('2027-01-03')
        ->and($hasil['kegiatan'][0]['hadir'])->toBe(['Rocky', 'Cici']);
});

test('baris yang tidak dikenali dilaporkan, bukan ditebak', function () {
    $hasil = RekapPendampingan::baca("* Kegiatan tanpa tanggal\nTANGGAL 31 FEBRUARI\n* Tanggal mustahil\nHadir : Andi\ncatatan bebas", 2026);

    expect($hasil['kegiatan'])->toBe([])
        ->and($hasil['abaikan'])->toBe(['* Kegiatan tanpa tanggal', 'TANGGAL 31 FEBRUARI', '* Tanggal mustahil', 'Hadir : Andi', 'catatan bebas']);
});

test('baris setelah daftar hadir tanpa koma dianggap catatan, bukan nama', function () {
    $hasil = RekapPendampingan::baca("TANGGAL 1 NOVEMBER 2026\n* Rapat\nHadir : Rocky, Cici\nterima kasih semua", 2026);

    expect($hasil['kegiatan'][0]['hadir'])->toBe(['Rocky', 'Cici'])
        ->and($hasil['abaikan'])->toBe(['terima kasih semua']);
});

test('baris sambutan dicatat terpisah dari daftar hadir', function () {
    $teks = <<<'TEKS'
🟦 Oktober 2026 🌈:

TANGGAL 28 OKTOBER (RABU)
* Pekan Pemuda Jebro
Hadir : Grace, Igun, Fila, Filo
Sambutan : Fila

* Bazar Tempat Pan. PAB PPGT Filadelfia
Hadir : Kak Sheva, Denson, Igun

TANGGAL 30 OKTOBER (JUMAT)
* Pelayanan Ke Jemaat Sudiang
Hadir : Fila (PF), Igun
TEKS;

    $hasil = RekapPendampingan::baca($teks, 2020);

    expect($hasil['abaikan'])->toBe([])
        ->and($hasil['kegiatan'][0])->toBe(['tanggal' => '2026-10-28', 'nama' => 'Pekan Pemuda Jebro', 'hadir' => ['Grace', 'Igun', 'Fila', 'Filo'], 'sambutan' => ['Fila']])
        ->and($hasil['kegiatan'][1]['sambutan'])->toBe([])
        ->and($hasil['kegiatan'][2]['hadir'])->toBe(['Fila (PF)', 'Igun']);
});

test('tanggal tanpa kata tanggal dan format tebal whatsapp tetap terbaca', function () {
    $teks = <<<'TEKS'
*Absensi kegiatan :*

> 26 Oktober 2026 :
• Rapat Panitia Natal PPGT KMTr
*Hadir :* Kak mikel, denson, silvy

> 27 Oktober 2026 :
• Rapat Evaluasi Tahunan Tim Kreatif
*Hadir :* Dita, Grace, Cici

• Rapat Evaluasi Tim SnC 
*Hadir :* Kak rahel, Yuan

Senin, 2 November 2026
• 17 Agustus Kenangan
Hadir : Rocky

3 November (Selasa)
* Ibadah
Hadir : Cici
TEKS;

    $hasil = RekapPendampingan::baca($teks, 2020);

    expect($hasil['abaikan'])->toBe([])
        ->and(array_map(fn ($k) => [$k['tanggal'], $k['nama'], $k['hadir']], $hasil['kegiatan']))->toBe([
            ['2026-10-26', 'Rapat Panitia Natal PPGT KMTr', ['Kak mikel', 'denson', 'silvy']],
            ['2026-10-27', 'Rapat Evaluasi Tahunan Tim Kreatif', ['Dita', 'Grace', 'Cici']],
            ['2026-10-27', 'Rapat Evaluasi Tim SnC', ['Kak rahel', 'Yuan']],
            ['2026-11-02', '17 Agustus Kenangan', ['Rocky']],
            ['2026-11-03', 'Ibadah', ['Cici']],
        ]);
});
