<?php

use App\Models\Periode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('pengguna: role kepengurusan wajib punya periode', function () {
    $role = Role::firstWhere('nama', 'Pengguna Periode');
    $this->actingAs(User::factory()->admin()->create())->postJson('/pengguna', [
        'name' => 'A', 'email' => 'a@x.id', 'password' => 'rahasia1', 'role_id' => $role->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('periode_id');
});

test('pengguna: dibuat dengan password_view dan password ter-hash', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $role = Role::firstWhere('nama', 'Pengguna Periode');
    $this->actingAs(User::factory()->admin()->create())->postJson('/pengguna', [
        'name' => 'A', 'email' => 'a@x.id', 'password' => 'rahasia1', 'role_id' => $role->id, 'periode_id' => $p->id,
    ])->assertOk();
    $u = User::firstWhere('email', 'a@x.id');
    expect($u->password_view)->toBe('rahasia1')
        ->and(Hash::check('rahasia1', $u->password))->toBeTrue();
});

test('pengguna: role admin tidak bisa diberikan', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->postJson('/pengguna', [
        'name' => 'A', 'email' => 'a@x.id', 'password' => 'rahasia1', 'role_id' => $admin->role_id,
    ])->assertUnprocessable();
});

test('pengguna: akun admin tidak bisa dibuka lewat master pengguna', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->getJson("/pengguna/{$admin->id}")->assertNotFound();
    $this->actingAs($admin)->deleteJson("/pengguna/{$admin->id}")->assertNotFound();
});

test('pengguna: non-admin ditolak dan tidak bisa membaca password_view', function () {
    $this->actingAs(User::factory()->create())->getJson('/pengguna/data')->assertForbidden();
});

test('pengguna: ubah tanpa password dan periode kosong untuk role non-kepengurusan', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $umum = Role::create(['nama' => 'Umum', 'menus' => ['dashboard']]);
    $u = User::factory()->untukPeriode($p)->create(['password_view' => 'lama']);
    $this->actingAs(User::factory()->admin()->create())->putJson("/pengguna/{$u->id}", [
        'name' => 'B', 'email' => $u->email, 'password' => '', 'role_id' => $umum->id, 'periode_id' => $p->id,
    ])->assertOk();
    $u->refresh();
    expect($u->name)->toBe('B')->and($u->password_view)->toBe('lama')->and($u->periode_id)->toBeNull();
});

test('halaman periode dan pengguna tampil untuk admin', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/periode')->assertOk()->assertSee('Master Periode');
    $this->actingAs($admin)->get('/pengguna')->assertOk()->assertSee('Master Pengguna');
});

test('password_view tersembunyi default tapi muncul di show pengguna', function () {
    $u = User::factory()->create(['password_view' => 'rahasia1']);
    expect($u->toArray())->not->toHaveKey('password_view');
    $this->actingAs(User::factory()->admin()->create())->getJson("/pengguna/{$u->id}")
        ->assertOk()->assertJsonPath('password_view', 'rahasia1');
});
