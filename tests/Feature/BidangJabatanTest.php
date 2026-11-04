<?php

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
    $this->actingAs($admin)->postJson("/kepengurusan/{$a->id}/bidang", ['nama' => 'Umum'])->assertOk();
    $this->actingAs($admin)->postJson("/kepengurusan/{$a->id}/bidang", ['nama' => 'Umum'])->assertUnprocessable();
    $this->actingAs($admin)->postJson("/kepengurusan/{$b->id}/bidang", ['nama' => 'Umum'])->assertOk();
});

test('periode yang punya bidang tidak bisa dihapus', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $p->bidangs()->create(['nama' => 'Umum']);
    $this->actingAs(User::factory()->admin()->create())->deleteJson("/periode/{$p->id}")->assertUnprocessable();
});

test('terkait_bidang tidak bisa diubah saat jabatan sudah punya pengurus', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $j = $p->jabatans()->create(['nama' => 'Ketua', 'terkait_bidang' => false]);
    $jemaat = Jemaat::create(['nama' => 'Jemaat Satu']);
    $a = DataAnggota::create(['jemaat_id' => $jemaat->id, 'nama' => 'Budi']);
    Pengurus::create(['periode_id' => $p->id, 'jabatan_id' => $j->id, 'data_anggota_id' => $a->id]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->putJson("/kepengurusan/{$p->id}/jabatan/{$j->id}", ['nama' => 'Ketua', 'terkait_bidang' => true])
        ->assertUnprocessable()->assertJsonValidationErrors('terkait_bidang');
    $this->actingAs($admin)->putJson("/kepengurusan/{$p->id}/jabatan/{$j->id}", ['nama' => 'Ketua Umum', 'terkait_bidang' => false])
        ->assertOk();
});
