<?php

use App\Models\Periode;
use App\Models\User;

test('periode: pasangan tahun kembar dan tahun terbalik ditolak', function () {
    $admin = User::factory()->admin()->create();
    Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $this->actingAs($admin)->postJson('/periode', ['tahun_mulai' => 2020, 'tahun_selesai' => 2022])->assertUnprocessable();
    $this->actingAs($admin)->postJson('/periode', ['tahun_mulai' => 2025, 'tahun_selesai' => 2024])->assertUnprocessable();
    $this->actingAs($admin)->postJson('/periode', ['tahun_mulai' => 2022, 'tahun_selesai' => 2024])->assertOk();
});

test('periode yang masih punya pengguna tidak bisa dihapus', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    User::factory()->untukPeriode($p)->create();
    $this->actingAs(User::factory()->admin()->create())->deleteJson("/periode/{$p->id}")->assertUnprocessable();
});

test('periode: non-admin ditolak', function () {
    $this->actingAs(User::factory()->create())->getJson('/periode/data')->assertForbidden();
});
