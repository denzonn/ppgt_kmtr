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

test('program baru selalu memakai tahun berjalan, walau dikirim tahun lain', function () {
    $this->travelTo('2021-06-15');
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $bidang = $p->bidangs()->create(['nama' => 'Umum']);
    $url = "/kepengurusan/{$p->id}/program-kerja";
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->postJson($url, ['bidang_id' => $bidang->id, 'nama' => 'Tanpa Tahun'])->assertOk();
    $this->actingAs($admin)->postJson($url, ['bidang_id' => $bidang->id, 'nama' => 'Tahun Lain', 'tahun' => 2099])->assertOk();

    expect(ProgramKerja::pluck('tahun')->unique()->all())->toBe([2021]);
});

test('periode di luar tahun berjalan memakai tahun terdekat', function () {
    $this->travelTo('2026-06-15');
    $admin = User::factory()->admin()->create();
    foreach ([[2020, 2022, 2022], [2030, 2032, 2030]] as [$mulai, $selesai, $harapan]) {
        $p = Periode::create(['tahun_mulai' => $mulai, 'tahun_selesai' => $selesai]);
        $bidang = $p->bidangs()->create(['nama' => 'Umum']);
        $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/program-kerja", ['bidang_id' => $bidang->id, 'nama' => 'X'])->assertOk();
        expect(ProgramKerja::where('bidang_id', $bidang->id)->value('tahun'))->toBe($harapan);
    }
});

test('mengubah program tidak mengubah tahunnya', function () {
    $this->travelTo('2022-06-15');
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $pk = programDi($p);
    $this->actingAs(User::factory()->admin()->create())
        ->putJson("/kepengurusan/{$p->id}/program-kerja/{$pk->id}", ['bidang_id' => $pk->bidang_id, 'nama' => 'Baru', 'tahun' => 2020])->assertOk();

    expect($pk->fresh()->tahun)->toBe(2021)->and($pk->fresh()->nama)->toBe('Baru');
});

test('nama program kembar ditolak hanya pada tahun yang sama', function () {
    $this->travelTo('2021-06-15');
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $pk = programDi($p);
    $admin = User::factory()->admin()->create();
    $url = "/kepengurusan/{$p->id}/program-kerja";

    $this->actingAs($admin)->postJson($url, ['bidang_id' => $pk->bidang_id, 'nama' => $pk->nama])
        ->assertUnprocessable()->assertJsonValidationErrors('nama');

    $this->travelTo('2022-06-15');
    $this->actingAs($admin)->postJson($url, ['bidang_id' => $pk->bidang_id, 'nama' => $pk->nama])->assertOk();
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
    Storage::disk('public')->assertExists(\App\Support\FileUpload::path($a));

    $this->actingAs($admin)->post($url, ['foto' => UploadedFile::fake()->image('b.jpg')], JSON_HDR)->assertOk();
    $b = $k->fresh()->foto;
    Storage::disk('public')->assertMissing(\App\Support\FileUpload::path($a));
    Storage::disk('public')->assertExists(\App\Support\FileUpload::path($b));

    $this->actingAs($admin)->postJson($url, ['hapus_foto' => 1])->assertOk();
    Storage::disk('public')->assertMissing(\App\Support\FileUpload::path($b));
    expect($k->fresh()->foto)->toBeNull();
});

test('menghapus kegiatan ikut menghapus fotonya', function () {
    Storage::fake('public');
    [$p, $admin, $pk] = periodeProgram();
    $k = kegiatanDi($pk);

    $this->actingAs($admin)->post("/kepengurusan/{$p->id}/kegiatan/{$k->id}/dokumentasi", ['foto' => UploadedFile::fake()->image('a.jpg')], JSON_HDR)->assertOk();
    $foto = $k->fresh()->foto;

    $this->actingAs($admin)->deleteJson("/kepengurusan/{$p->id}/kegiatan/{$k->id}")->assertOk();
    Storage::disk('public')->assertMissing(\App\Support\FileUpload::path($foto));
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

test('form program menampilkan tahun tanpa pilihan', function () {
    $this->travelTo('2026-06-15');
    $p = Periode::create(['tahun_mulai' => 2025, 'tahun_selesai' => 2027]);

    $html = $this->actingAs(User::factory()->admin()->create())->get("/kepengurusan/{$p->id}/program-kerja")->assertOk()->getContent();

    expect($html)->not->toContain('<select id="program_tahun"');
    expect($html)->toMatch('~id="program_tahun_teks"[^>]*>2026<~');
});

test('ringkasan program kerja menghitung semua hasil saringan', function () {
    $tahun = now()->year;
    $p = Periode::create(['tahun_mulai' => $tahun, 'tahun_selesai' => $tahun + 2]);
    $a = $p->bidangs()->create(['nama' => 'Bidang A']);
    $b = $p->bidangs()->create(['nama' => 'Bidang B']);
    $programA = ProgramKerja::create(['bidang_id' => $a->id, 'tahun' => $tahun, 'nama' => 'Program A']);
    $programB = ProgramKerja::create(['bidang_id' => $b->id, 'tahun' => $tahun, 'nama' => 'Program B']);
    Kegiatan::create(['program_kerja_id' => $programA->id, 'nama' => 'K1', 'deskripsi' => 'x', 'anggaran' => 1000, 'bulan' => [1]]);
    Kegiatan::create(['program_kerja_id' => $programA->id, 'nama' => 'K2', 'deskripsi' => 'x', 'anggaran' => 2000, 'bulan' => [2]]);
    Kegiatan::create(['program_kerja_id' => $programB->id, 'nama' => 'K3', 'deskripsi' => 'x', 'anggaran' => 4000, 'bulan' => [3]]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->getJson("/kepengurusan/{$p->id}/program-kerja/data")
        ->assertOk()
        ->assertJsonPath('total', 2)
        ->assertJsonPath('ringkasan', ['kegiatan' => 3, 'anggaran' => 7000]);

    $this->actingAs($admin)->getJson("/kepengurusan/{$p->id}/program-kerja/data?bidang_id={$a->id}")
        ->assertJsonPath('total', 1)
        ->assertJsonPath('ringkasan', ['kegiatan' => 2, 'anggaran' => 3000]);
});

test('daftar program kerja berkumpul per bidang', function () {
    $tahun = now()->year;
    $p = Periode::create(['tahun_mulai' => $tahun, 'tahun_selesai' => $tahun + 2]);
    $a = $p->bidangs()->create(['nama' => 'Bidang A']);
    $b = $p->bidangs()->create(['nama' => 'Bidang B']);
    foreach ([[$b, 'A dari B'], [$a, 'Z dari A'], [$b, 'B dari B'], [$a, 'Y dari A']] as [$bidang, $nama]) {
        ProgramKerja::create(['bidang_id' => $bidang->id, 'tahun' => $tahun, 'nama' => $nama]);
    }

    $this->actingAs(User::factory()->admin()->create())->getJson("/kepengurusan/{$p->id}/program-kerja/data")
        ->assertOk()
        ->assertJsonPath('data.*.nama', ['Y dari A', 'Z dari A', 'A dari B', 'B dari B']);
});
