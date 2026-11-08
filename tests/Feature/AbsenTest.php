<?php

use App\Models\Absen;
use App\Models\Jemaat;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    Jemaat::create(['nama' => 'Jemaat Maranatha']);
});

function buatAbsen(): Absen
{
    return Absen::create(['nama' => 'Ibadah Pemuda', 'token' => 'tokenabsen123456', 'jabatan' => ['Ketua', 'Anggota']]);
}

function isian(array $ubah = []): array
{
    return $ubah + ['nama' => 'Budi', 'jenis_kelamin' => 'Laki-laki', 'asal_jemaat' => 'Jemaat Maranatha', 'alamat' => 'Jl. Merdeka 1', 'jabatan' => 'Anggota'];
}

test('admin membuat absen dan pilihan jabatan dipisah koma', function () {
    $this->actingAs($this->admin)
        ->postJson('/data-absen', ['nama' => 'Ibadah Pemuda', 'jabatan' => ' Ketua, Anggota ,,Ketua'])
        ->assertOk();

    $absen = Absen::first();
    expect($absen->jabatan)->toBe(['Ketua', 'Anggota']);
    expect($absen->token)->not->toBeEmpty();
});

test('jabatan yang hanya berisi koma ditolak', function () {
    $this->actingAs($this->admin)
        ->postJson('/data-absen', ['nama' => 'Ibadah', 'jabatan' => ' , ,'])
        ->assertUnprocessable()->assertJsonValidationErrors('jabatan');
});

test('mengubah absen tidak mengubah alamat QR yang sudah dibagikan', function () {
    $absen = buatAbsen();

    $this->actingAs($this->admin)
        ->putJson("/data-absen/{$absen->id}", ['nama' => 'Ibadah Baru', 'jabatan' => 'Ketua'])
        ->assertOk();

    $absen->refresh();
    expect($absen->nama)->toBe('Ibadah Baru');
    expect($absen->jabatan)->toBe(['Ketua']);
    expect($absen->token)->toBe('tokenabsen123456');
});

test('admin membuka halaman absen dari menu samping', function () {
    $this->actingAs($this->admin)->get('/data-absen')
        ->assertOk()->assertSee('Buat Absen')->assertSee(route('absen.index'));
});

test('QR tampil sebagai gambar SVG', function () {
    $absen = buatAbsen();

    $this->actingAs($this->admin)->get("/data-absen/{$absen->id}/qr")
        ->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
});

test('halaman admin tidak bisa dibuka tanpa login atau tanpa izin menu', function () {
    $absen = buatAbsen();
    $this->get('/data-absen')->assertRedirect('/login');
    $this->get("/data-absen/{$absen->id}/peserta")->assertRedirect('/login');

    $role = Role::create(['nama' => 'Umum', 'is_admin' => false, 'menus' => ['inventaris']]);
    $user = User::factory()->create(['role_id' => $role->id]);
    $this->actingAs($user)->get('/data-absen')->assertForbidden();
});

test('peserta tanpa login melihat form dengan pilihan jemaat, undangan, dan jabatan', function () {
    $absen = buatAbsen();

    $this->get("/absen/{$absen->token}")
        ->assertOk()
        ->assertSee('Ibadah Pemuda')
        ->assertSee('Jemaat Maranatha')
        ->assertSee('Undangan')
        ->assertSee('Ketua')
        ->assertSee('Laki-laki')
        ->assertSee('Perempuan');
});

test('token yang salah menghasilkan 404', function () {
    $this->get('/absen/tokensalah')->assertNotFound();
});

test('peserta mengirim absen dan tersimpan pada kegiatannya', function () {
    $absen = buatAbsen();

    $this->post("/absen/{$absen->token}", isian(['asal_jemaat' => 'Undangan']))
        ->assertRedirect("/absen/{$absen->token}?terkirim=1");

    $peserta = $absen->peserta()->first();
    expect($peserta->nama)->toBe('Budi');
    expect($peserta->jenis_kelamin)->toBe('Laki-laki');
    expect($peserta->asal_jemaat)->toBe('Undangan');
    expect($peserta->alamat)->toBe('Jl. Merdeka 1');
    expect($peserta->jabatan)->toBe('Anggota');
});

test('setelah terkirim peserta melihat ucapan terima kasih', function () {
    $absen = buatAbsen();

    $this->followingRedirects()->post("/absen/{$absen->token}", isian())
        ->assertOk()->assertSee('Terima kasih');
});

test('peserta bisa mengirim absen lebih dari sekali', function () {
    $absen = buatAbsen();

    $this->post("/absen/{$absen->token}", isian());
    $this->post("/absen/{$absen->token}", isian(['nama' => 'Ani']))->assertSessionHasNoErrors();

    expect($absen->peserta()->count())->toBe(2);
});

test('setelah terkirim tersedia tautan untuk mengisi absen lagi', function () {
    $absen = buatAbsen();

    $this->followingRedirects()->post("/absen/{$absen->token}", isian())
        ->assertSee('Isi Absen Lagi')
        ->assertSee('href="'.route('absen.isi', $absen->token).'"', false);
});

test('membuka kembali tautan absen menampilkan form yang kosong', function () {
    $absen = buatAbsen();
    $this->post("/absen/{$absen->token}", isian());

    $this->get("/absen/{$absen->token}")
        ->assertOk()->assertSee('name="alamat"', false)->assertDontSee('Terima kasih');
});

test('semua isian wajib diisi', function (string $kolom) {
    $absen = buatAbsen();

    $this->post("/absen/{$absen->token}", isian([$kolom => '']))->assertSessionHasErrors($kolom);
    expect($absen->peserta()->count())->toBe(0);
})->with(['nama', 'jenis_kelamin', 'asal_jemaat', 'alamat', 'jabatan']);

test('jenis kelamin, jemaat, dan jabatan di luar pilihan ditolak', function (string $kolom) {
    $absen = buatAbsen();

    $this->post("/absen/{$absen->token}", isian([$kolom => 'Pilihan Palsu']))->assertSessionHasErrors($kolom);
    expect($absen->peserta()->count())->toBe(0);
})->with(['jenis_kelamin', 'asal_jemaat', 'jabatan']);

test('admin melihat daftar peserta satu kegiatan saja', function () {
    $absen = buatAbsen();
    $absen->peserta()->create(isian());
    $lain = Absen::create(['nama' => 'Lain', 'token' => 'tokenlain', 'jabatan' => ['Anggota']]);
    $lain->peserta()->create(isian(['nama' => 'Orang Lain']));

    $this->actingAs($this->admin)->getJson("/data-absen/{$absen->id}/peserta")
        ->assertOk()->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.nama', 'Budi')
        ->assertJsonPath('data.0.jenis_kelamin', 'Laki-laki');
});

test('daftar absen menyertakan jumlah peserta', function () {
    $absen = buatAbsen();
    $absen->peserta()->create(isian());

    $this->actingAs($this->admin)->getJson('/data-absen/data')
        ->assertOk()->assertJsonPath('data.0.peserta_count', 1);
});

test('menghapus absen ikut menghapus pesertanya', function () {
    $absen = buatAbsen();
    $absen->peserta()->create(isian());

    $this->actingAs($this->admin)->deleteJson("/data-absen/{$absen->id}")->assertOk();

    $this->assertDatabaseCount('absen', 0);
    $this->assertDatabaseCount('absen_peserta', 0);
});
