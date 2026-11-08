<?php

use App\Models\Bidang;
use App\Models\Jemaat;
use App\Models\Kegiatan;
use App\Models\Periode;
use App\Models\ProgramKerja;

test('beranda tetap terbuka saat data masih kosong', function () {
    $this->get('/')->assertOk()->assertSee('Belum ada kegiatan');
});

test('beranda menampilkan isi resmi dari AD/ART', function () {
    $this->get('/')->assertOk()
        ->assertSee('Bersekutu, bersaksi,')
        ->assertSee('dan melayani.')
        ->assertSee('memuliakan Tuhan, memberitakan kebaikan-Nya')
        ->assertSee('11 Desember 1962')
        ->assertSee('Bintang delapan')
        ->assertSee('15–35 tahun');
});

test('beranda tidak menampilkan tombol login', function () {
    $this->get('/')->assertOk()->assertDontSee(route('login'));
});

test('beranda memakai data periode berjalan dan menyembunyikan anggaran', function () {
    $tahun = now()->year;
    $lama = Periode::create(['tahun_mulai' => $tahun - 3, 'tahun_selesai' => $tahun]);
    $kini = Periode::create(['tahun_mulai' => $tahun, 'tahun_selesai' => $tahun + 2]);
    Jemaat::create(['nama' => 'Jemaat Contoh']);

    Bidang::create(['periode_id' => $lama->id, 'nama' => 'Bidang Periode Lama', 'jumlah_anggota' => 1]);
    $bidang = Bidang::create(['periode_id' => $kini->id, 'nama' => 'Bidang Periode Kini', 'jumlah_anggota' => 1]);
    $program = ProgramKerja::create(['bidang_id' => $bidang->id, 'tahun' => $tahun, 'nama' => 'Program Tahun Ini']);
    $programLain = ProgramKerja::create(['bidang_id' => $bidang->id, 'tahun' => $tahun + 1, 'nama' => 'Program Tahun Lain']);

    Kegiatan::create(['program_kerja_id' => $program->id, 'nama' => 'Kegiatan Tahun Ini', 'deskripsi' => 'Isi', 'anggaran' => 7654321, 'bulan' => [1, 3]]);
    Kegiatan::create(['program_kerja_id' => $programLain->id, 'nama' => 'Kegiatan Tahun Lain', 'deskripsi' => 'Isi', 'anggaran' => 1, 'bulan' => [1]]);

    $this->get('/')->assertOk()
        ->assertSee('Jemaat Contoh')
        ->assertSee('Bidang Periode Kini')
        ->assertDontSee('Bidang Periode Lama')
        ->assertSee('Program Tahun Ini')
        ->assertDontSee('Program Tahun Lain')
        ->assertSee('Kegiatan Tahun Ini')
        ->assertSee('Januari & Maret')
        ->assertDontSee('Kegiatan Tahun Lain')
        ->assertDontSee('7654321')
        ->assertDontSee('7.654.321');
});

test('kegiatan tanpa rincian khusus tetap menampilkan deskripsinya', function () {
    $tahun = now()->year;
    $periode = Periode::create(['tahun_mulai' => $tahun, 'tahun_selesai' => $tahun + 2]);
    $bidang = Bidang::create(['periode_id' => $periode->id, 'nama' => 'Bidang Baru', 'jumlah_anggota' => 1]);
    $program = ProgramKerja::create(['bidang_id' => $bidang->id, 'tahun' => $tahun, 'nama' => 'Program Baru']);
    Kegiatan::create(['program_kerja_id' => $program->id, 'nama' => 'Kegiatan Baru', 'deskripsi' => 'Deskripsi dari admin', 'anggaran' => 1, 'bulan' => [2]]);

    $this->get('/')->assertOk()->assertSee('Kegiatan Baru')->assertSee('Deskripsi dari admin')->assertSee('Program Prioritas');
});

test('rincian kegiatan menggantikan deskripsi dari admin saat namanya terdaftar', function () {
    $tahun = now()->year;
    $periode = Periode::create(['tahun_mulai' => $tahun, 'tahun_selesai' => $tahun + 2]);
    $bidang = Bidang::create(['periode_id' => $periode->id, 'nama' => 'Umum', 'jumlah_anggota' => 1]);
    $program = ProgramKerja::create(['bidang_id' => $bidang->id, 'tahun' => $tahun, 'nama' => 'Bahan Khotbah']);
    Kegiatan::create(['program_kerja_id' => $program->id, 'nama' => 'Workshop Penulisan Bahan Khotbah PPGT KMTr', 'deskripsi' => 'Teks admin', 'anggaran' => 1, 'bulan' => [1]]);

    $this->get('/')->assertOk()->assertSee('Pdt. Lukas Dayung, M.Th')->assertDontSee('Teks admin');
});
