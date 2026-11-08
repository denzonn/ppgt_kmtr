<?php

use App\Models\Artikel;
use App\Models\Jemaat;

beforeEach(function () {
    $this->jemaatA = Jemaat::create(['nama' => 'Sudiang']);
    $this->jemaatB = Jemaat::create(['nama' => 'Tamalanrea']);
});

function artikelPublik(Jemaat $jemaat, array $ubah = []): Artikel
{
    return Artikel::create($ubah + [
        'jemaat_id' => $jemaat->id,
        'judul' => 'Ibadah Syukur Pemuda',
        'penulis' => 'Yohana',
        'isi' => '<p>Isi artikel</p>',
        'status' => 'disetujui',
        'diperiksa_pada' => now(),
    ]);
}

test('daftar artikel hanya menampilkan yang sudah disetujui', function () {
    artikelPublik($this->jemaatA, ['judul' => 'Sudah Disetujui']);
    artikelPublik($this->jemaatA, ['judul' => 'Masih Menunggu', 'status' => 'menunggu']);
    artikelPublik($this->jemaatA, ['judul' => 'Sudah Ditolak', 'status' => 'ditolak']);

    $this->get('/artikel')->assertOk()
        ->assertSee('Sudah Disetujui')
        ->assertDontSee('Masih Menunggu')
        ->assertDontSee('Sudah Ditolak');
});

test('daftar artikel tetap terbuka saat belum ada artikel', function () {
    $this->get('/artikel')->assertOk()->assertSee('Belum ada artikel');
});

test('artikel bisa dicari lewat judul atau penulis', function () {
    artikelPublik($this->jemaatA, ['judul' => 'Retret Pemuda', 'penulis' => 'Andi']);
    artikelPublik($this->jemaatA, ['judul' => 'Bakti Sosial', 'penulis' => 'Budi']);

    $this->get('/artikel?cari=retret')->assertSee('Retret Pemuda')->assertDontSee('Bakti Sosial');
    $this->get('/artikel?cari=budi')->assertSee('Bakti Sosial')->assertDontSee('Retret Pemuda');
    $this->get('/artikel?cari=tidakada')->assertOk()->assertSee('Artikel tidak ditemukan');
});

test('pencarian tidak memunculkan artikel yang belum disetujui', function () {
    artikelPublik($this->jemaatA, ['judul' => 'Rahasia Menunggu', 'penulis' => 'Andi', 'status' => 'menunggu']);

    $this->get('/artikel?cari=andi')->assertDontSee('Rahasia Menunggu');
});

test('artikel bisa disaring per jemaat', function () {
    artikelPublik($this->jemaatA, ['judul' => 'Artikel Sudiang']);
    artikelPublik($this->jemaatB, ['judul' => 'Artikel Tamalanrea']);

    $this->get("/artikel?jemaat={$this->jemaatB->id}")
        ->assertSee('Artikel Tamalanrea')
        ->assertDontSee('Artikel Sudiang');
});

test('parameter pencarian yang aneh tidak membuat halaman error', function () {
    $this->get('/artikel?cari[]=x&jemaat[]=y')->assertOk();
    $this->get('/artikel?jemaat=abc')->assertOk();
});

test('halaman berikutnya tetap membawa pencarian', function () {
    foreach (range(1, 10) as $nomor) {
        artikelPublik($this->jemaatA, ['judul' => "Retret {$nomor}"]);
    }

    $this->get('/artikel?cari=retret')->assertOk()
        ->assertSee('Halaman 1 dari 2')
        ->assertSee('cari=retret&amp;page=2', false);
});

test('pengunjung bisa membaca artikel yang sudah disetujui', function () {
    $artikel = artikelPublik($this->jemaatA, ['isi' => '<p>Kabar <strong>sukacita</strong></p>']);

    $this->get($artikel->urlBaca())->assertOk()
        ->assertSee('Ibadah Syukur Pemuda')
        ->assertSee('<strong>sukacita</strong>', false)
        ->assertSee('Jemaat Sudiang')
        ->assertSee('property="og:title" content="Ibadah Syukur Pemuda"', false);
});

test('artikel yang belum disetujui tidak bisa dibaca walau link-nya diketahui', function (string $status) {
    $artikel = artikelPublik($this->jemaatA, ['status' => $status]);

    $this->get("/artikel/{$artikel->id}/ibadah-syukur-pemuda")->assertNotFound();
})->with(['menunggu', 'ditolak']);

test('artikel yang diedit kembali tersembunyi sampai disetujui lagi', function () {
    $artikel = artikelPublik($this->jemaatA);
    $url = $artikel->urlBaca();
    $this->get($url)->assertOk();

    $artikel->update(['status' => 'menunggu']);

    $this->get($url)->assertNotFound();
});

test('link dengan judul lama diarahkan ke link terbaru', function () {
    $artikel = artikelPublik($this->jemaatA);

    $this->get("/artikel/{$artikel->id}/judul-lama")->assertRedirect($artikel->urlBaca())->assertStatus(301);
    $this->get("/artikel/{$artikel->id}")->assertRedirect($artikel->urlBaca());
});

test('judul tanpa huruf atau angka tetap bisa dibuka', function () {
    $artikel = artikelPublik($this->jemaatA, ['judul' => '???']);

    $this->get($artikel->urlBaca())->assertOk();
});

test('isi artikel berbahaya tidak ikut tampil', function () {
    $artikel = artikelPublik($this->jemaatA, ['isi' => '<p>Aman</p><script>alert(1)</script><img src="x" onerror="alert(2)">']);

    $this->get($artikel->urlBaca())->assertOk()
        ->assertSee('Aman')
        ->assertDontSee('alert(1)', false)
        ->assertDontSee('onerror', false);
});

test('halaman baca menampilkan artikel lain tanpa mengulang artikel yang dibuka', function () {
    $artikel = artikelPublik($this->jemaatA, ['judul' => 'Artikel Utama']);
    artikelPublik($this->jemaatB, ['judul' => 'Artikel Sebelah']);

    $respons = $this->get($artikel->urlBaca())->assertOk()->assertSee('Artikel Sebelah');

    expect($respons->viewData('lainnya')->pluck('id'))->not->toContain($artikel->id);
});

test('halaman artikel tidak menampilkan tombol login', function () {
    $this->get('/artikel')->assertDontSee(route('login'));
});
