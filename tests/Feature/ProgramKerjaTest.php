<?php

use App\Models\Kegiatan;
use App\Models\Periode;
use App\Models\ProgramKerja;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function programDi(Periode $p): ProgramKerja
{
    $bidang = $p->bidangs()->create(['nama' => 'Bidang ' . uniqid()]);

    return ProgramKerja::create(['bidang_id' => $bidang->id, 'tahun' => 2021, 'nama' => 'Perlawatan']);
}

function kegiatanDi(ProgramKerja $pk, array $extra = []): Kegiatan
{
    return $pk->kegiatans()->create($extra + [
        'nama' => 'Kunjungan', 'deskripsi' => 'Deskripsi', 'anggaran' => 1000000, 'bulan' => [3, 4],
    ]);
}

function payloadKegiatan(array $extra = []): array
{
    return $extra + ['nama' => 'Kunjungan', 'deskripsi' => 'Deskripsi', 'anggaran' => 1000000, 'bulan' => [3, 4]];
}

function periodeProgram(): array
{
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);

    return [$p, User::factory()->admin()->create(), programDi($p)];
}

const JSON_HDR = ['Accept' => 'application/json'];

test('tahun program harus dalam rentang periode', function () {
    [$p, $admin, $pk] = periodeProgram();
    $data = ['bidang_id' => $pk->bidang_id, 'nama' => 'Baru'];

    $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/program-kerja", $data + ['tahun' => 2021])->assertOk();
    $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/program-kerja", $data + ['tahun' => 2023])
        ->assertUnprocessable()->assertJsonValidationErrors('tahun');
});

test('bidang dari periode lain ditolak saat membuat program', function () {
    [$p, $admin] = periodeProgram();
    $lain = Periode::create(['tahun_mulai' => 2023, 'tahun_selesai' => 2025]);
    $bidangLain = $lain->bidangs()->create(['nama' => 'Lain']);

    $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/program-kerja", [
        'bidang_id' => $bidangLain->id, 'tahun' => 2021, 'nama' => 'X',
    ])->assertUnprocessable()->assertJsonValidationErrors('bidang_id');
});

test('program periode lain tidak bisa dibuka atau diisi', function () {
    $a = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $b = Periode::create(['tahun_mulai' => 2023, 'tahun_selesai' => 2025]);
    $user = User::factory()->untukPeriode($a)->create();
    $pkB = programDi($b);

    $this->actingAs($user)->putJson("/kepengurusan/{$a->id}/program-kerja/{$pkB->id}", [
        'bidang_id' => $pkB->bidang_id, 'tahun' => 2024, 'nama' => 'Ubah',
    ])->assertNotFound();
    $this->actingAs($user)->deleteJson("/kepengurusan/{$a->id}/program-kerja/{$pkB->id}")->assertNotFound();
    $this->actingAs($user)->postJson("/kepengurusan/{$a->id}/program-kerja/{$pkB->id}/kegiatan", payloadKegiatan())->assertNotFound();
});

test('kegiatan periode lain tidak bisa diubah', function () {
    $a = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $b = Periode::create(['tahun_mulai' => 2023, 'tahun_selesai' => 2025]);
    $user = User::factory()->untukPeriode($a)->create();
    $kB = kegiatanDi(programDi($b));

    $this->actingAs($user)->putJson("/kepengurusan/{$a->id}/kegiatan/{$kB->id}", payloadKegiatan())->assertNotFound();
    $this->actingAs($user)->deleteJson("/kepengurusan/{$a->id}/kegiatan/{$kB->id}")->assertNotFound();
    $this->actingAs($user)->postJson("/kepengurusan/{$a->id}/kegiatan/{$kB->id}/dokumentasi", ['link' => 'https://x.id'])->assertNotFound();
});

test('bulan dicentang lebih dari satu tersimpan', function () {
    [$p, $admin, $pk] = periodeProgram();

    $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/program-kerja/{$pk->id}/kegiatan", payloadKegiatan())->assertOk();
    expect($pk->kegiatans()->first()->bulan)->toBe([3, 4]);
});

test('kirim form dengan bulan string tersimpan sebagai integer', function () {
    [$p, $admin, $pk] = periodeProgram();

    $this->actingAs($admin)->post("/kepengurusan/{$p->id}/program-kerja/{$pk->id}/kegiatan", [
        'nama' => 'Kunjungan', 'deskripsi' => 'D', 'anggaran' => '1000000', 'bulan' => ['3', '4'],
    ], JSON_HDR)->assertOk();
    $k = $pk->kegiatans()->first();
    expect($k->bulan)->toBe([3, 4])->and($k->anggaran)->toBe(1000000);

    $this->actingAs($admin)->put("/kepengurusan/{$p->id}/kegiatan/{$k->id}", [
        'nama' => 'Kunjungan', 'deskripsi' => 'D', 'anggaran' => '5', 'bulan' => ['5', '6'],
    ], JSON_HDR)->assertOk();
    expect($k->fresh()->bulan)->toBe([5, 6]);

    $this->actingAs($admin)->post("/kepengurusan/{$p->id}/program-kerja/{$pk->id}/kegiatan", [
        'nama' => 'K2', 'deskripsi' => 'D', 'anggaran' => '1', 'bulan' => ['3', '3'],
    ], JSON_HDR)->assertUnprocessable();
});

test('bulan tidak valid ditolak', function () {
    [$p, $admin, $pk] = periodeProgram();

    foreach ([[], [13], [0], [3, 3]] as $bulan) {
        $errors = $this->actingAs($admin)
            ->postJson("/kepengurusan/{$p->id}/program-kerja/{$pk->id}/kegiatan", payloadKegiatan(['bulan' => $bulan]))
            ->assertUnprocessable()->json('errors');
        expect(collect(array_keys($errors))->contains(fn ($e) => str_starts_with($e, 'bulan')))->toBeTrue();
    }
    expect($pk->kegiatans()->count())->toBe(0);
});

test('anggaran dengan pemisah ribuan atau terlalu besar ditolak', function () {
    [$p, $admin, $pk] = periodeProgram();

    foreach (['1.000.000', 9999999999999, -1] as $anggaran) {
        $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/program-kerja/{$pk->id}/kegiatan", payloadKegiatan(['anggaran' => $anggaran]))
            ->assertUnprocessable()->assertJsonValidationErrors('anggaran');
    }
});

test('link javascript ditolak, link https diterima', function () {
    [$p, $admin, $pk] = periodeProgram();
    $k = kegiatanDi($pk);

    $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/kegiatan/{$k->id}/dokumentasi", ['link' => 'javascript:alert(1)'])
        ->assertUnprocessable()->assertJsonValidationErrors('link');
    $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/kegiatan/{$k->id}/dokumentasi", ['link' => 'https://drive.google.com/x'])->assertOk();
    expect($k->fresh()->link)->toBe('https://drive.google.com/x');
});

test('foto: mengganti menghapus file lama, hapus_foto menghapusnya', function () {
    Storage::fake('public');
    [$p, $admin, $pk] = periodeProgram();
    $k = kegiatanDi($pk);
    $url = "/kepengurusan/{$p->id}/kegiatan/{$k->id}/dokumentasi";

    $this->actingAs($admin)->post($url, ['foto' => UploadedFile::fake()->image('a.jpg')], JSON_HDR)->assertOk();
    $a = $k->fresh()->foto;
    Storage::disk('public')->assertExists($a);

    $this->actingAs($admin)->post($url, ['foto' => UploadedFile::fake()->image('b.jpg')], JSON_HDR)->assertOk();
    $b = $k->fresh()->foto;
    Storage::disk('public')->assertMissing($a);
    Storage::disk('public')->assertExists($b);

    $this->actingAs($admin)->postJson($url, ['hapus_foto' => 1])->assertOk();
    Storage::disk('public')->assertMissing($b);
    expect($k->fresh()->foto)->toBeNull();
});

test('menghapus kegiatan ikut menghapus fotonya', function () {
    Storage::fake('public');
    [$p, $admin, $pk] = periodeProgram();
    $k = kegiatanDi($pk);

    $this->actingAs($admin)->post("/kepengurusan/{$p->id}/kegiatan/{$k->id}/dokumentasi", ['foto' => UploadedFile::fake()->image('a.jpg')], JSON_HDR)->assertOk();
    $foto = $k->fresh()->foto;

    $this->actingAs($admin)->deleteJson("/kepengurusan/{$p->id}/kegiatan/{$k->id}")->assertOk();
    Storage::disk('public')->assertMissing($foto);
    expect(Kegiatan::count())->toBe(0);
});

test('foto bukan gambar atau lebih dari 2 MB ditolak', function () {
    Storage::fake('public');
    [$p, $admin, $pk] = periodeProgram();
    $k = kegiatanDi($pk);
    $url = "/kepengurusan/{$p->id}/kegiatan/{$k->id}/dokumentasi";

    foreach ([UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'), UploadedFile::fake()->image('b.jpg')->size(3000)] as $file) {
        $this->actingAs($admin)->post($url, ['foto' => $file], JSON_HDR)
            ->assertUnprocessable()->assertJsonValidationErrors('foto');
    }
});

test('program yang punya kegiatan tidak bisa dihapus', function () {
    [$p, $admin, $pk] = periodeProgram();
    kegiatanDi($pk);

    $this->actingAs($admin)->deleteJson("/kepengurusan/{$p->id}/program-kerja/{$pk->id}")
        ->assertUnprocessable()->assertJson(['status' => false]);
    expect(ProgramKerja::count())->toBe(1);
});

test('program tanpa kegiatan bisa dihapus', function () {
    [$p, $admin, $pk] = periodeProgram();

    $this->actingAs($admin)->deleteJson("/kepengurusan/{$p->id}/program-kerja/{$pk->id}")->assertOk();
    expect(ProgramKerja::count())->toBe(0);
});

test('bidang yang punya program tidak bisa dihapus', function () {
    [$p, $admin, $pk] = periodeProgram();

    $this->actingAs($admin)->deleteJson("/kepengurusan/{$p->id}/bidang/{$pk->bidang_id}")
        ->assertUnprocessable()->assertJson(['status' => false]);
});

test('rentang periode tidak bisa dipersempit melewati program', function () {
    [$p, $admin, $pk] = periodeProgram();
    $pk->update(['tahun' => 2022]);

    $this->actingAs($admin)->putJson("/periode/{$p->id}", ['tahun_mulai' => 2020, 'tahun_selesai' => 2021])->assertUnprocessable();
    $this->actingAs($admin)->putJson("/periode/{$p->id}", ['tahun_mulai' => 2020, 'tahun_selesai' => 2023])->assertOk();
});

test('daftar program bisa difilter tahun dan bidang', function () {
    [$p, $admin, $pk] = periodeProgram();
    kegiatanDi($pk);
    $lain = programDi($p);
    $lain->update(['tahun' => 2022]);

    $res = $this->actingAs($admin)->getJson("/kepengurusan/{$p->id}/program-kerja/data?tahun=2021")->assertOk();
    expect($res->json('total'))->toBe(1)->and($res->json('data.0.kegiatans'))->toHaveCount(1);
    expect($this->actingAs($admin)->getJson("/kepengurusan/{$p->id}/program-kerja/data?bidang_id={$lain->bidang_id}")->json('total'))->toBe(1);
    $this->actingAs($admin)->get("/kepengurusan/{$p->id}/program-kerja")->assertOk();
});
