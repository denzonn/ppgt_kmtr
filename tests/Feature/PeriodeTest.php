<?php

use App\Models\Bidang;
use App\Models\Periode;
use App\Models\User;

test('periode: pasangan tahun kembar dan tahun terbalik ditolak', function () {
    $admin = User::factory()->admin()->create();
    Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $this->actingAs($admin)->postJson('/periode', ['tahun_mulai' => 2020, 'tahun_selesai' => 2022])->assertUnprocessable();
    $this->actingAs($admin)->postJson('/periode', ['tahun_mulai' => 2025, 'tahun_selesai' => 2024])->assertUnprocessable();
    $this->actingAs($admin)->postJson('/periode', ['tahun_mulai' => 2022, 'tahun_selesai' => 2024, 'bidangs' => [Bidang::DEFAULT_NAMA[0]]])->assertOk();
});

test('periode yang masih punya pengguna tidak bisa dihapus', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    User::factory()->untukPeriode($p)->create();
    $this->actingAs(User::factory()->admin()->create())->deleteJson("/periode/{$p->id}")->assertUnprocessable();
});

test('periode: non-admin ditolak', function () {
    $this->actingAs(User::factory()->create())->getJson('/periode/data')->assertForbidden();
});

test('periode baru otomatis membuat bidang yang dicentang', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->postJson('/periode', [
        'tahun_mulai' => 2026, 'tahun_selesai' => 2028,
        'bidangs' => array_slice(Bidang::DEFAULT_NAMA, 0, 4),
    ])->assertOk();

    expect(Periode::first()->bidangs()->orderBy('id')->pluck('nama')->all())->toBe(array_slice(Bidang::DEFAULT_NAMA, 0, 4));
});

test('lima nama bidang sesuai ketentuan', function () {
    expect(Bidang::DEFAULT_NAMA)->toBe([
        'Bidang Umum',
        'Bidang Pengakaran Iman & Spiritualitas',
        'Bidang Kaderisasi & Organisasi',
        'Bidang Pengembang SDM',
        'Bidang Pelayanan Sosial & Kemasyarakatan',
    ]);
});

test('periode baru wajib minimal satu bidang dan tidak menerima nama lain', function () {
    $admin = User::factory()->admin()->create();
    $tahun = ['tahun_mulai' => 2026, 'tahun_selesai' => 2028];

    $this->actingAs($admin)->postJson('/periode', $tahun)->assertUnprocessable()->assertJsonValidationErrors('bidangs');
    $this->actingAs($admin)->postJson('/periode', $tahun + ['bidangs' => []])->assertUnprocessable()->assertJsonValidationErrors('bidangs');
    $this->actingAs($admin)->postJson('/periode', $tahun + ['bidangs' => ['Bidang Palsu']])->assertUnprocessable()->assertJsonValidationErrors('bidangs.0');
    $this->actingAs($admin)->postJson('/periode', $tahun + ['bidangs' => [Bidang::DEFAULT_NAMA[0], Bidang::DEFAULT_NAMA[0]]])->assertUnprocessable();
    expect(Periode::count())->toBe(0);
});

test('mengubah tahun periode tidak mengubah bidangnya', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $p->bidangs()->create(['nama' => 'Umum']);

    $this->actingAs(User::factory()->admin()->create())
        ->putJson("/periode/{$p->id}", ['tahun_mulai' => 2020, 'tahun_selesai' => 2023])->assertOk();
    expect($p->bidangs()->count())->toBe(1);
});

test('halaman periode menampilkan pilihan bidang', function () {
    $res = $this->actingAs(User::factory()->admin()->create())->get('/periode')->assertOk();
    foreach (Bidang::DEFAULT_NAMA as $nama) {
        $res->assertSee($nama);
    }
});
