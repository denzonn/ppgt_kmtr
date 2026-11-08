<?php

use App\Models\Absen;
use App\Models\AuditLog;
use App\Models\Inventaris;
use App\Models\InventarisKondisi;
use App\Models\Jemaat;
use App\Models\Linktree;
use App\Models\DataAnggota;
use App\Models\Role;
use App\Models\User;

function logTerakhir(): AuditLog
{
    return AuditLog::latest('id')->first();
}

function isianInventaris(array $kondisi): array
{
    return ['jenis' => 'PPL', 'nama' => 'Kursi', 'tanggal_perolehan' => '2026-01-01', 'harga' => 1000, 'kondisi' => $kondisi];
}

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['name' => 'Admin Utama']);
    AuditLog::query()->delete();
});

test('data baru dicatat beserta siapa yang membuatnya', function () {
    $this->actingAs($this->admin)
        ->postJson('/data-linktree', ['nama' => 'Warta', 'link' => 'https://ppgt.id/warta'])
        ->assertOk();

    $log = AuditLog::where('model', 'Linktree')->first();
    expect($log->aksi)->toBe('dibuat');
    expect($log->user_id)->toBe($this->admin->id);
    expect($log->user_nama)->toBe('Admin Utama');
    expect($log->label)->toBe('Warta');
    expect($log->model_id)->toBe(Linktree::first()->id);
    expect($log->perubahan['link']['baru'])->toBe('https://ppgt.id/warta');
});

test('perubahan hanya mencatat kolom yang berubah, nilai lama dan baru', function () {
    $item = Linktree::create(['nama' => 'Lama', 'link' => 'https://ppgt.id/a']);
    AuditLog::query()->delete();

    $this->actingAs($this->admin)
        ->putJson("/data-linktree/{$item->id}", ['nama' => 'Baru', 'link' => 'https://ppgt.id/a'])
        ->assertOk();

    $log = logTerakhir();
    expect($log->aksi)->toBe('diubah');
    expect($log->perubahan)->toEqual(['nama' => ['lama' => 'Lama', 'baru' => 'Baru']]);
});

test('penghapusan menyimpan isi data yang dihapus', function () {
    $item = Linktree::create(['nama' => 'Warta', 'link' => 'https://ppgt.id/a']);

    $this->actingAs($this->admin)->deleteJson("/data-linktree/{$item->id}")->assertOk();

    $log = logTerakhir();
    expect($log->aksi)->toBe('dihapus');
    expect($log->label)->toBe('Warta');
    expect($log->perubahan['link']['lama'])->toBe('https://ppgt.id/a');
});

test('penyimpanan tanpa perubahan tidak menambah catatan', function () {
    $item = Linktree::create(['nama' => 'Warta', 'link' => 'https://ppgt.id/a']);
    AuditLog::query()->delete();

    $this->actingAs($this->admin)
        ->putJson("/data-linktree/{$item->id}", ['nama' => 'Warta', 'link' => 'https://ppgt.id/a'])
        ->assertOk();

    expect(AuditLog::count())->toBe(0);
});

test('isian publik tanpa login tetap dicatat', function () {
    Jemaat::create(['nama' => 'Jemaat A']);
    $absen = Absen::create(['nama' => 'Ibadah', 'token' => 'tokenaudit', 'jabatan' => ['Anggota']]);
    AuditLog::query()->delete();

    $this->post('/absen/tokenaudit', [
        'nama' => 'Budi', 'jenis_kelamin' => 'Laki-laki', 'asal_jemaat' => 'Undangan', 'alamat' => 'Jl. A', 'jabatan' => 'Anggota',
    ]);

    $log = logTerakhir();
    expect($log->model)->toBe('AbsenPeserta');
    expect($log->user_id)->toBeNull();
    expect($log->label)->toBe('Budi');
    expect($absen->peserta()->count())->toBe(1);
});

test('password tidak pernah tersimpan di catatan', function () {
    $this->actingAs($this->admin);

    $user = User::factory()->create(['password' => 'rahasia12345', 'password_view' => 'rahasia12345']);
    $user->update(['password' => 'rahasiabaru99', 'password_view' => 'rahasiabaru99']);

    $isi = AuditLog::where('model', 'User')->pluck('perubahan')->toJson();

    expect($isi)->not->toContain('rahasia12345')->not->toContain('rahasiabaru99')->not->toContain('$2y$');
    expect(logTerakhir()->perubahan['password'])->toEqual(['lama' => '(disembunyikan)', 'baru' => '(disembunyikan)']);
});

test('NIK anggota disembunyikan di catatan', function () {
    $this->actingAs($this->admin);
    $jemaat = Jemaat::create(['nama' => 'Jemaat A']);

    DataAnggota::create(['jemaat_id' => $jemaat->id, 'nama' => 'Budi', 'no_anggota' => 'A-1', 'nik' => '7371000011112222']);

    expect(AuditLog::where('model', 'DataAnggota')->first()->perubahan['nik']['baru'])->toBe('(disembunyikan)');
});

test('login, login gagal, dan logout dicatat', function () {
    $user = User::factory()->create(['email' => 'budi@ppgt.id']);
    AuditLog::query()->delete();

    $this->post('/login', ['email' => 'budi@ppgt.id', 'password' => 'salah-sekali']);
    $gagal = logTerakhir();
    expect($gagal->aksi)->toBe('login gagal');
    expect($gagal->label)->toBe('budi@ppgt.id');
    expect(json_encode($gagal->getAttributes()))->not->toContain('salah-sekali');

    $this->post('/login', ['email' => 'budi@ppgt.id', 'password' => 'password']);
    expect(logTerakhir()->aksi)->toBe('login');
    expect(logTerakhir()->user_id)->toBe($user->id);

    AuditLog::query()->delete();
    $this->post('/logout');

    // Token ingat-saya yang berganti saat logout bukan aktivitas tersendiri.
    expect(AuditLog::pluck('aksi')->all())->toBe(['logout']);
});

test('perubahan jumlah kondisi inventaris dicatat hanya yang berubah', function () {
    $this->actingAs($this->admin)
        ->postJson('/inventaris', isianInventaris(['Baik' => 5, 'Rusak' => 1, 'Hilang' => 0]))
        ->assertOk();
    $item = Inventaris::first();
    AuditLog::query()->delete();

    $this->actingAs($this->admin)
        ->putJson("/inventaris/{$item->id}", isianInventaris(['Baik' => 5, 'Rusak' => 3, 'Hilang' => 2]))
        ->assertOk();

    expect(InventarisKondisi::where('id_inventaris', $item->id)->pluck('jumlah', 'kondisi')->map(fn ($j) => (int) $j)->all())
        ->toEqualCanonicalizing(['Baik' => 5, 'Rusak' => 3, 'Hilang' => 2]);

    $log = AuditLog::where('model', 'InventarisKondisi')->orderBy('id')->get();
    expect($log->pluck('aksi')->all())->toBe(['diubah', 'dibuat']);
    expect($log[0]->label)->toBe('Rusak');
    expect((string) $log[0]->perubahan['jumlah']['baru'])->toBe('3');
});

test('kondisi yang dijadikan nol atau tidak dikirim terhapus dan dicatat', function () {
    $this->actingAs($this->admin)
        ->postJson('/inventaris', isianInventaris(['Baik' => 5, 'Rusak' => 1, 'Hilang' => 2]))
        ->assertOk();
    $item = Inventaris::first();
    AuditLog::query()->delete();

    $this->actingAs($this->admin)
        ->putJson("/inventaris/{$item->id}", isianInventaris(['Baik' => 5, 'Rusak' => 0]))
        ->assertOk();

    expect(InventarisKondisi::where('id_inventaris', $item->id)->pluck('kondisi')->all())->toBe(['Baik']);
    expect(AuditLog::where('model', 'InventarisKondisi')->pluck('aksi')->all())->toBe(['dihapus', 'dihapus']);
});

test('menghapus inventaris mencatat kondisinya juga', function () {
    $this->actingAs($this->admin)->postJson('/inventaris', isianInventaris(['Baik' => 5]))->assertOk();
    $item = Inventaris::first();
    AuditLog::query()->delete();

    $this->actingAs($this->admin)->deleteJson("/inventaris/{$item->id}")->assertOk();

    expect(AuditLog::where('aksi', 'dihapus')->pluck('model')->all())->toContain('InventarisKondisi', 'Inventaris');
});

test('mengatur urutan linktree mencatat item yang berpindah', function () {
    $a = Linktree::create(['nama' => 'A', 'link' => 'https://ppgt.id/a', 'urutan' => 1]);
    $b = Linktree::create(['nama' => 'B', 'link' => 'https://ppgt.id/b', 'urutan' => 2]);
    $c = Linktree::create(['nama' => 'C', 'link' => 'https://ppgt.id/c', 'urutan' => 3]);
    AuditLog::query()->delete();

    $this->actingAs($this->admin)
        ->putJson('/data-linktree/urutan', ['ids' => [$b->id, $a->id, $c->id]])
        ->assertOk();

    expect($a->fresh()->urutan)->toBe(2);
    expect(AuditLog::orderBy('id')->pluck('label')->all())->toBe(['B', 'A']);
});

test('kegagalan mencatat tidak menggagalkan penyimpanan data', function () {
    AuditLog::creating(fn () => throw new RuntimeException('gagal'));

    $this->actingAs($this->admin)
        ->postJson('/data-linktree', ['nama' => 'Warta', 'link' => 'https://ppgt.id/warta'])
        ->assertOk();

    expect(Linktree::count())->toBe(1);
});

test('catatan tetap ada dengan nama pelaku setelah penggunanya dihapus', function () {
    $this->actingAs($this->admin);
    $user = User::factory()->create(['name' => 'Akan Dihapus']);
    $this->actingAs($user);
    Linktree::create(['nama' => 'Warta', 'link' => 'https://ppgt.id/a']);
    $log = logTerakhir();

    $user->delete();

    expect($log->fresh()->user_nama)->toBe('Akan Dihapus');
    expect(logTerakhir()->aksi)->toBe('dihapus');
    expect(logTerakhir()->user_nama)->toBe('Akan Dihapus');
});

test('hanya admin yang bisa membuka audit log', function () {
    $this->get('/audit-log')->assertRedirect('/login');

    $role = Role::create(['nama' => 'Umum', 'is_admin' => false, 'menus' => ['inventaris', 'linktree']]);
    $user = User::factory()->create(['role_id' => $role->id]);
    $this->actingAs($user)->get('/audit-log')->assertForbidden();
    $this->actingAs($user)->getJson('/audit-log/data')->assertForbidden();

    $this->actingAs($this->admin)->get('/audit-log')->assertOk()->assertSee('Audit Log');
});

test('catatan tidak bisa diubah atau dihapus lewat web', function () {
    $this->actingAs($this->admin);
    Linktree::create(['nama' => 'Warta', 'link' => 'https://ppgt.id/a']);
    $log = logTerakhir();

    $this->deleteJson("/audit-log/{$log->id}")->assertNotFound();
    $this->putJson("/audit-log/{$log->id}", [])->assertNotFound();
    expect($log->fresh())->not->toBeNull();
});

test('daftar audit log bisa dicari dan difilter, terbaru di atas', function () {
    $this->actingAs($this->admin);
    Linktree::create(['nama' => 'Warta', 'link' => 'https://ppgt.id/a']);
    $hapus = Linktree::create(['nama' => 'Instagram', 'link' => 'https://ppgt.id/b']);
    $hapus->delete();

    $this->getJson('/audit-log/data')->assertOk()
        ->assertJsonPath('data.0.aksi', 'dihapus')
        ->assertJsonPath('data.0.objek', 'Linktree')
        ->assertJsonPath('total', 3);

    $this->getJson('/audit-log/data?search=Warta')->assertJsonPath('total', 1);
    $this->getJson('/audit-log/data?search=Admin%20Utama')->assertJsonPath('total', 3);
    $this->getJson('/audit-log/data?aksi=dihapus')->assertJsonPath('total', 1);
});

test('waktu ditampilkan dalam WITA', function () {
    $this->travelTo('2026-11-07 01:00:00');
    $this->actingAs($this->admin);
    Linktree::create(['nama' => 'Warta', 'link' => 'https://ppgt.id/a']);

    $this->getJson('/audit-log/data')->assertJsonPath('data.0.waktu', '07/11/2026 09:00 WITA');
});

test('membaca audit log tidak menambah catatan baru', function () {
    $this->actingAs($this->admin);
    $this->getJson('/audit-log/data')->assertOk();

    expect(AuditLog::count())->toBe(0);
});
