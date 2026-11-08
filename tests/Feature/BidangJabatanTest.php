<?php

use App\Models\Bidang;
use App\Models\DataAnggota;
use App\Models\Jemaat;
use App\Models\Pengurus;
use App\Models\Periode;
use App\Models\User;

test('bidang periode lain tidak bisa dibuka lewat periode sendiri', function () {
    $a = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $b = Periode::create(['tahun_mulai' => 2023, 'tahun_selesai' => 2025]);
    $milikB = $b->bidangs()->create(['nama' => 'Spiritualitas']);
    $user = User::factory()->untukPeriode($a)->create();
    $this->actingAs($user)->getJson("/kepengurusan/{$a->id}/bidang/{$milikB->id}")->assertNotFound();
    $this->actingAs($user)->deleteJson("/kepengurusan/{$a->id}/bidang/{$milikB->id}")->assertNotFound();
});

test('nama bidang boleh sama antarperiode tapi unik dalam periode', function () {
    $a = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $b = Periode::create(['tahun_mulai' => 2023, 'tahun_selesai' => 2025]);
    $admin = User::factory()->admin()->create();
    $data = ['nama' => 'Umum', 'jumlah_anggota' => 4];
    $this->actingAs($admin)->postJson("/kepengurusan/{$a->id}/bidang", $data)->assertOk();
    $this->actingAs($admin)->postJson("/kepengurusan/{$a->id}/bidang", $data)->assertUnprocessable();
    $this->actingAs($admin)->postJson("/kepengurusan/{$b->id}/bidang", $data)->assertOk();
});

test('jumlah anggota bidang wajib angka minimal 1', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $admin = User::factory()->admin()->create();
    foreach ([null, 0, 'empat', 1000] as $nilai) {
        $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/bidang", ['nama' => 'Umum', 'jumlah_anggota' => $nilai])
            ->assertUnprocessable()->assertJsonValidationErrors('jumlah_anggota');
    }
    $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/bidang", ['nama' => 'Umum', 'jumlah_anggota' => '4'])->assertOk();
    expect(Bidang::first()->jumlah_anggota)->toBe(4);
});

test('jumlah anggota tidak bisa lebih kecil dari pengurus yang sudah ada', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $b = $p->bidangs()->create(['nama' => 'Umum', 'jumlah_anggota' => 4]);
    $j = $p->jabatans()->create(['nama' => 'Anggota', 'bidang_id' => $b->id]);
    $jemaat = Jemaat::create(['nama' => 'Jemaat Satu']);
    foreach (['Budi', 'Ani'] as $nama) {
        $a = DataAnggota::create(['jemaat_id' => $jemaat->id, 'nama' => $nama]);
        Pengurus::create(['periode_id' => $p->id, 'jabatan_id' => $j->id, 'data_anggota_id' => $a->id]);
    }
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->putJson("/kepengurusan/{$p->id}/bidang/{$b->id}", ['nama' => 'Umum', 'jumlah_anggota' => 1])
        ->assertUnprocessable()->assertJsonValidationErrors('jumlah_anggota');
    $this->actingAs($admin)->putJson("/kepengurusan/{$p->id}/bidang/{$b->id}", ['nama' => 'Umum', 'jumlah_anggota' => 2])->assertOk();
    $this->actingAs($admin)->getJson("/kepengurusan/{$p->id}/bidang/data")
        ->assertJsonPath('data.0.pengurus_count', 2)->assertJsonPath('data.0.jumlah_anggota', 2);
});

test('periode yang punya bidang tidak bisa dihapus', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $p->bidangs()->create(['nama' => 'Umum']);
    $this->actingAs(User::factory()->admin()->create())->deleteJson("/periode/{$p->id}")->assertUnprocessable();
});

test('jabatan wajib punya bidang dari periode yang sama', function () {
    $a = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $b = Periode::create(['tahun_mulai' => 2023, 'tahun_selesai' => 2025]);
    $bidangA = $a->bidangs()->create(['nama' => 'Umum']);
    $bidangB = $b->bidangs()->create(['nama' => 'Umum']);
    $admin = User::factory()->admin()->create();
    $url = "/kepengurusan/{$a->id}/jabatan";

    $this->actingAs($admin)->postJson($url, ['nama' => 'Ketua'])
        ->assertUnprocessable()->assertJsonValidationErrors('bidang_id');
    $this->actingAs($admin)->postJson($url, ['nama' => 'Ketua', 'bidang_id' => $bidangB->id])
        ->assertUnprocessable()->assertJsonValidationErrors('bidang_id');
    $this->actingAs($admin)->postJson($url, ['nama' => 'Ketua', 'bidang_id' => $bidangA->id])->assertOk();
    $this->actingAs($admin)->getJson("{$url}/data")->assertJsonPath('data.0.bidang.nama', 'Umum');
});

test('nama jabatan boleh sama antarbidang tapi unik dalam satu bidang', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $umum = $p->bidangs()->create(['nama' => 'Umum']);
    $sosial = $p->bidangs()->create(['nama' => 'Sosial']);
    $admin = User::factory()->admin()->create();
    $url = "/kepengurusan/{$p->id}/jabatan";

    $this->actingAs($admin)->postJson($url, ['nama' => 'Anggota', 'bidang_id' => $umum->id])->assertOk();
    $this->actingAs($admin)->postJson($url, ['nama' => 'Anggota', 'bidang_id' => $sosial->id])->assertOk();
    $this->actingAs($admin)->postJson($url, ['nama' => 'Anggota', 'bidang_id' => $umum->id])
        ->assertUnprocessable()->assertJsonValidationErrors('nama');
});

test('bidang jabatan tidak bisa dipindah saat sudah punya pengurus, tapi nama bisa diubah', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $umum = $p->bidangs()->create(['nama' => 'Umum']);
    $sosial = $p->bidangs()->create(['nama' => 'Sosial']);
    $j = $p->jabatans()->create(['nama' => 'Ketua', 'bidang_id' => $umum->id]);
    $a = DataAnggota::create(['jemaat_id' => Jemaat::create(['nama' => 'Jemaat Satu'])->id, 'nama' => 'Budi']);
    Pengurus::create(['periode_id' => $p->id, 'jabatan_id' => $j->id, 'data_anggota_id' => $a->id]);
    $admin = User::factory()->admin()->create();
    $url = "/kepengurusan/{$p->id}/jabatan/{$j->id}";

    $this->actingAs($admin)->putJson($url, ['nama' => 'Ketua', 'bidang_id' => $sosial->id])
        ->assertUnprocessable()->assertJsonValidationErrors('bidang_id');
    // Form mengirim id sebagai string.
    $this->actingAs($admin)->putJson($url, ['nama' => 'Ketua Umum', 'bidang_id' => (string) $umum->id])->assertOk();
});

test('jabatan lama tanpa bidang bisa diberi bidang', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $umum = $p->bidangs()->create(['nama' => 'Umum']);
    $j = $p->jabatans()->create(['nama' => 'Ketua']);

    $this->actingAs(User::factory()->admin()->create())
        ->putJson("/kepengurusan/{$p->id}/jabatan/{$j->id}", ['nama' => 'Ketua', 'bidang_id' => $umum->id])->assertOk();
    expect($j->fresh()->bidang_id)->toBe($umum->id);
});

test('bidang yang masih punya jabatan tidak bisa dihapus', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $b = $p->bidangs()->create(['nama' => 'Umum']);
    $p->jabatans()->create(['nama' => 'Ketua', 'bidang_id' => $b->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->deleteJson("/kepengurusan/{$p->id}/bidang/{$b->id}")->assertUnprocessable();
});
