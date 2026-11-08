<?php

use App\Models\Linktree;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('pengunjung tanpa login melihat semua link yang mengarah ke tujuannya', function () {
    Linktree::create(['nama' => 'Instagram PPGT', 'link' => 'https://instagram.com/ppgt']);
    Linktree::create(['nama' => 'Warta Jemaat', 'link' => 'https://ppgt.id/warta']);

    $this->get('/links')
        ->assertOk()
        ->assertSeeInOrder(['Instagram PPGT', 'Warta Jemaat'])
        ->assertSee('href="https://instagram.com/ppgt"', false)
        ->assertSee('href="https://ppgt.id/warta"', false);
});

test('halaman link tetap terbuka saat belum ada link', function () {
    $this->get('/links')->assertOk()->assertSee('Belum ada link');
});

test('beranda menautkan ke halaman link', function () {
    $this->get('/')->assertOk()->assertSee(route('linktree'));
});

test('admin menambah, mengubah, dan menghapus link', function () {
    $this->actingAs($this->admin)
        ->postJson('/data-linktree', ['nama' => 'Warta', 'link' => 'https://ppgt.id/warta'])
        ->assertOk();
    $item = Linktree::first();
    expect($item->nama)->toBe('Warta');

    $this->actingAs($this->admin)
        ->putJson("/data-linktree/{$item->id}", ['nama' => 'Warta Baru', 'link' => 'https://ppgt.id/baru'])
        ->assertOk();
    expect($item->fresh()->link)->toBe('https://ppgt.id/baru');

    $this->actingAs($this->admin)->deleteJson("/data-linktree/{$item->id}")->assertOk();
    expect(Linktree::count())->toBe(0);
});

test('admin melihat daftar link di halaman data', function () {
    Linktree::create(['nama' => 'Warta', 'link' => 'https://ppgt.id/warta']);

    $this->actingAs($this->admin)->getJson('/data-linktree/data')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.nama', 'Warta');
});

test('nama dan link wajib diisi', function (string $kolom) {
    $isian = ['nama' => 'Warta', 'link' => 'https://ppgt.id/warta'];
    $isian[$kolom] = '';

    $this->actingAs($this->admin)->postJson('/data-linktree', $isian)
        ->assertUnprocessable()->assertJsonValidationErrors($kolom);
})->with(['nama', 'link']);

test('link hanya boleh http atau https', function (string $link) {
    $this->actingAs($this->admin)->postJson('/data-linktree', ['nama' => 'X', 'link' => $link])
        ->assertUnprocessable()->assertJsonValidationErrors('link');
    expect(Linktree::count())->toBe(0);
})->with(['javascript:alert(1)', 'ftp://ppgt.id/a', 'bukan link']);

test('pengelolaan link butuh login dan izin menu', function () {
    $this->get('/data-linktree')->assertRedirect('/login');

    $role = Role::create(['nama' => 'Umum', 'is_admin' => false, 'menus' => ['inventaris']]);
    $user = User::factory()->create(['role_id' => $role->id]);
    $this->actingAs($user)->get('/data-linktree')->assertForbidden();

    $role->update(['menus' => ['linktree']]);
    $this->actingAs($user->fresh())->get('/data-linktree')->assertOk()->assertSee('Tambah Link');
});

test('sub-judul tampil di antara link dan tidak bisa diklik', function () {
    Linktree::create(['nama' => 'Juknis', 'link' => 'https://ppgt.id/juknis', 'urutan' => 1]);
    Linktree::create(['nama' => 'Pedoman', 'link' => null, 'urutan' => 2]);
    Linktree::create(['nama' => 'Pembinaan', 'link' => 'https://ppgt.id/bina', 'urutan' => 3]);

    $this->get('/links')
        ->assertOk()
        ->assertSeeInOrder(['Juknis', 'Pedoman', 'Pembinaan'])
        ->assertSee('<h2 class="lt-judul"', false)
        ->assertDontSee('href=""', false);
});

test('admin menambah sub-judul tanpa link', function () {
    $this->actingAs($this->admin)
        ->postJson('/data-linktree', ['jenis' => 'judul', 'nama' => 'Pedoman'])
        ->assertOk();

    expect(Linktree::first()->link)->toBeNull();
});

test('isian link diabaikan saat menyimpan sub-judul', function () {
    $this->actingAs($this->admin)
        ->postJson('/data-linktree', ['jenis' => 'judul', 'nama' => 'Pedoman', 'link' => 'javascript:alert(1)'])
        ->assertOk();

    expect(Linktree::first()->link)->toBeNull();
});

test('mengubah sub-judul tidak meminta link', function () {
    $judul = Linktree::create(['nama' => 'Pedoman', 'link' => null]);

    $this->actingAs($this->admin)
        ->putJson("/data-linktree/{$judul->id}", ['nama' => 'Bahan Khotbah'])
        ->assertOk();

    expect($judul->fresh()->nama)->toBe('Bahan Khotbah');
    expect($judul->fresh()->link)->toBeNull();
});

test('mengubah link tetap wajib berisi link', function () {
    $item = Linktree::create(['nama' => 'Warta', 'link' => 'https://ppgt.id/warta']);

    $this->actingAs($this->admin)
        ->putJson("/data-linktree/{$item->id}", ['nama' => 'Warta', 'link' => ''])
        ->assertUnprocessable()->assertJsonValidationErrors('link');
});

test('item baru ditambahkan di urutan paling bawah', function () {
    Linktree::create(['nama' => 'Lama', 'link' => 'https://ppgt.id/lama', 'urutan' => 5]);

    $this->actingAs($this->admin)
        ->postJson('/data-linktree', ['nama' => 'Baru', 'link' => 'https://ppgt.id/baru'])
        ->assertOk();

    expect(Linktree::where('nama', 'Baru')->value('urutan'))->toBe(6);
});

test('admin mengatur urutan dan halaman publik mengikutinya', function () {
    $a = Linktree::create(['nama' => 'Pertama', 'link' => 'https://ppgt.id/a', 'urutan' => 1]);
    $b = Linktree::create(['nama' => 'Kedua', 'link' => 'https://ppgt.id/b', 'urutan' => 2]);
    $c = Linktree::create(['nama' => 'Ketiga', 'link' => null, 'urutan' => 3]);

    $this->actingAs($this->admin)
        ->putJson('/data-linktree/urutan', ['ids' => [$c->id, $a->id, $b->id]])
        ->assertOk();

    $this->get('/links')->assertSeeInOrder(['Ketiga', 'Pertama', 'Kedua']);
    $this->actingAs($this->admin)->getJson('/data-linktree/data')
        ->assertJsonPath('data.0.nama', 'Ketiga')->assertJsonPath('data.2.nama', 'Kedua');
});

test('urutan dengan id yang tidak ada atau kembar ditolak', function (array $ids) {
    $a = Linktree::create(['nama' => 'A', 'link' => 'https://ppgt.id/a', 'urutan' => 7]);

    $this->actingAs($this->admin)
        ->putJson('/data-linktree/urutan', ['ids' => array_map(fn ($id) => $id === 'a' ? $a->id : $id, $ids)])
        ->assertUnprocessable();

    expect($a->fresh()->urutan)->toBe(7);
})->with([
    'tidak ada' => [['a', 99999]],
    'kembar' => [['a', 'a']],
]);

test('mengatur urutan butuh login', function () {
    $this->putJson('/data-linktree/urutan', ['ids' => [1]])->assertUnauthorized();
});
