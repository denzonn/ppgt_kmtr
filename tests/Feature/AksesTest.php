<?php

use App\Models\Periode;
use App\Models\Role;
use App\Models\User;

function roleDengan(array $menus): Role
{
    return Role::create(['nama' => 'Role '.uniqid(), 'is_admin' => false, 'menus' => $menus]);
}

test('role admin bisa membuka semua menu', function () {
    $admin = User::factory()->admin()->create();
    foreach (['/dashboard', '/inventaris', '/data-anggota', '/data-peminjaman', '/generator-qr-link'] as $url) {
        $this->actingAs($admin)->get($url)->assertOk();
    }
});

test('role tanpa centang ditolak dan diizinkan setelah dicentang', function () {
    $role = roleDengan([]);
    $user = User::factory()->create(['role_id' => $role->id]);
    $this->actingAs($user)->get('/inventaris')->assertForbidden();
    $role->update(['menus' => ['inventaris']]);
    $this->actingAs($user->fresh())->get('/inventaris')->assertOk();
});

test('pengguna periode dari dashboard diarahkan ke program kerja periodenya', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $user = User::factory()->untukPeriode($p)->create();
    $this->actingAs($user)->get('/dashboard')
        ->assertRedirect(url("/kepengurusan/{$p->id}/program-kerja"));
});

test('pengguna periode ditolak dari periode lain', function () {
    $a = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $b = Periode::create(['tahun_mulai' => 2023, 'tahun_selesai' => 2025]);
    $user = User::factory()->untukPeriode($a)->create();
    $this->actingAs($user)->get("/kepengurusan/{$b->id}/program-kerja")->assertForbidden();
    $this->actingAs($user)->get("/kepengurusan/{$a->id}/program-kerja")->assertOk();
});

test('pengguna tanpa menu yang bisa dibuka diarahkan ke halaman pesan', function () {
    $user = User::factory()->create(); // Pengguna Periode tanpa periode_id
    $this->actingAs($user)->get('/dashboard')->assertRedirect(route('akses.kosong'));
    $this->actingAs($user)->get(route('akses.kosong'))->assertOk()->assertSee('Hubungi admin');
});

test('login mengabaikan intended ke halaman terlarang', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $user = User::factory()->untukPeriode($p)->create();
    $this->get('/inventaris')->assertRedirect('/login');
    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(url("/kepengurusan/{$p->id}/program-kerja"));
});

test('peminjaman publik tetap terbuka tanpa login', function () {
    $this->get('/peminjaman')->assertOk();
});

test('sidebar hanya menampilkan menu yang dimiliki', function () {
    $user = User::factory()->create(['role_id' => roleDengan(['inventaris'])->id]);
    $this->actingAs($user)->get('/inventaris')->assertOk()
        ->assertSee(route('inventaris.index'), false)
        ->assertDontSee(route('data_anggota.index'), false)
        ->assertDontSee(route('peminjaman-admin'), false)
        ->assertDontSee(route('generate_qr.index'), false);
});

test('gate per menu di dalam kepengurusan', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $role = roleDengan(['bidang']);
    $user = User::factory()->untukPeriode($p)->create(['role_id' => $role->id]);
    $this->actingAs($user)->get("/kepengurusan/{$p->id}/pengurus")->assertForbidden();
    $this->actingAs($user)->get("/kepengurusan/{$p->id}/bidang")->assertOk();
    $role->update(['menus' => ['bidang', 'pengurus']]);
    $this->actingAs($user->fresh())->get("/kepengurusan/{$p->id}/pengurus")->assertOk();
});

test('admin tanpa periode bisa membuka program kerja periode mana pun', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $this->actingAs(User::factory()->admin()->create())
        ->get("/kepengurusan/{$p->id}/program-kerja")->assertOk();
});

test('sidebar menampilkan nama role pengguna', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $this->actingAs(User::factory()->untukPeriode($p)->create())
        ->get("/kepengurusan/{$p->id}/program-kerja")->assertOk()
        ->assertSee('Pengguna Periode')->assertDontSee('Administrator');
});
