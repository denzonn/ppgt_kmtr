<?php

use App\Models\Periode;
use App\Models\Role;
use App\Models\User;

function penggunaDenganMenu(array $menus, ?Periode $periode = null): User
{
    $role = Role::create(['nama' => 'Role '.uniqid(), 'is_admin' => false, 'menus' => $menus]);

    return User::factory()->create(['role_id' => $role->id, 'periode_id' => $periode?->id]);
}

test('admin melihat menu yang dikelompokkan', function () {
    $this->actingAs(User::factory()->admin()->create())->get('/dashboard')
        ->assertOk()
        ->assertSee('Inventaris &amp; Peminjaman', false)
        ->assertSee('data-grup="layanan"', false)
        ->assertSee('data-grup="administrasi"', false)
        ->assertSee(route('absen.index'), false)
        ->assertSee(route('linktree.index'), false)
        ->assertSee(route('generate_qr.index'), false)
        ->assertSee(route('role.index'), false)
        ->assertSee(route('periode.index'), false)
        ->assertSee(route('pengguna.index'), false)
        ->assertSee(route('audit_log.index'), false);
});

test('grup yang berisi halaman aktif terbuka dan grup lain tertutup', function () {
    $this->actingAs(User::factory()->admin()->create())->get('/audit-log')
        ->assertSee('data-grup="administrasi" aria-expanded="true"', false)
        ->assertSee('data-grup="layanan" aria-expanded="false"', false)
        ->assertSee('data-grup="inventaris" aria-expanded="false"', false);
});

test('halaman peminjaman membuka grup inventaris', function () {
    $this->actingAs(User::factory()->admin()->create())->get(route('peminjaman-admin'))
        ->assertSee('data-grup="inventaris" aria-expanded="true"', false);
});

test('pengguna hanya melihat menu yang diizinkan dan grup kosong disembunyikan', function () {
    $this->actingAs(penggunaDenganMenu(['absen']))->get('/data-absen')
        ->assertOk()
        ->assertSee(route('absen.index'), false)
        ->assertSee('data-grup="layanan" aria-expanded="true"', false)
        ->assertDontSee(route('linktree.index'), false)
        ->assertDontSee(route('generate_qr.index'), false)
        ->assertDontSee('data-grup="administrasi"', false)
        ->assertDontSee('data-grup="inventaris"', false)
        ->assertDontSee('data-grup="kepengurusan"', false)
        ->assertDontSee(route('audit_log.index'), false);
});

test('menu kepengurusan tampil dalam satu grup sesuai izin dan periode', function () {
    $periode = Periode::create(['tahun_mulai' => 2024, 'tahun_selesai' => 2026]);

    $this->actingAs(penggunaDenganMenu(['bidang', 'pengurus'], $periode))
        ->get(route('kepengurusan.bidang.index', $periode))
        ->assertOk()
        ->assertSee('data-grup="kepengurusan" aria-expanded="true"', false)
        ->assertSee(route('kepengurusan.bidang.index', $periode), false)
        ->assertSee(route('kepengurusan.pengurus.index', $periode), false)
        ->assertDontSee(route('kepengurusan.jabatan.index', $periode), false);
});

test('tanpa periode grup kepengurusan tidak tampil', function () {
    $this->actingAs(penggunaDenganMenu(['bidang', 'absen']))->get('/data-absen')
        ->assertOk()
        ->assertDontSee('data-grup="kepengurusan"', false);
});
