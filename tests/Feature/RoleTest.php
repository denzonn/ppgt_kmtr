<?php

use App\Models\Role;
use App\Models\User;

test('admin membuat role dengan menu tercentang', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->postJson('/role', ['nama' => 'Bendahara', 'menus' => ['inventaris']])->assertOk();
    expect(Role::firstWhere('nama', 'Bendahara')->menus)->toBe(['inventaris']);
});

test('menu tak dikenal ditolak', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->postJson('/role', ['nama' => 'X', 'menus' => ['role']])->assertUnprocessable();
});

test('role admin tidak bisa diubah atau dihapus', function () {
    $admin = User::factory()->admin()->create();
    $id = $admin->role_id;
    $this->actingAs($admin)->putJson("/role/{$id}", ['nama' => 'Y', 'menus' => []])->assertUnprocessable();
    $this->actingAs($admin)->deleteJson("/role/{$id}")->assertUnprocessable();
});

test('role yang masih dipakai tidak bisa dihapus', function () {
    $admin = User::factory()->admin()->create();
    $dipakai = User::factory()->create()->role_id;
    $this->actingAs($admin)->deleteJson("/role/{$dipakai}")->assertUnprocessable();
});

test('role selain admin ditolak', function () {
    $this->actingAs(User::factory()->create())->get('/role')->assertForbidden();
});

test('admin dapat membuka halaman dan data role', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/role')->assertOk()->assertSee('Manajemen Role');
    $this->actingAs($admin)->getJson('/role/data')->assertOk()->assertJsonStructure(['data', 'total', 'last_page']);
});
