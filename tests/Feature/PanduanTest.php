<?php

use App\Models\Jemaat;
use App\Models\Role;
use App\Models\User;

function akunRole(string $role, array $ubah = []): User
{
    return User::factory()->create(['role_id' => Role::firstWhere('nama', $role)->id] + $ubah);
}

test('panduan wajib login', function () {
    $this->get('/panduan')->assertRedirect('/login');
});

test('setiap akun melihat menu panduan dan diarahkan ke topik pertama', function () {
    $akun = akunRole('Tim Tanggap');

    $this->actingAs($akun)->get('/panduan')->assertRedirect(route('panduan', 'login-navigasi'));
    $this->actingAs($akun)->get(route('panduan', 'login-navigasi'))->assertOk()->assertSee(url('/panduan'), false);
});

test('topik mengikuti hak akses akun', function () {
    $akun = akunRole('Pengurus Jemaat', ['jemaat_id' => Jemaat::create(['nama' => 'Jemaat A'])->id]);

    $this->actingAs($akun)->get(route('panduan', 'kelompok'))->assertOk()
        ->assertSee(route('panduan', 'artikel'), false)
        ->assertDontSee(route('panduan', 'persetujuan-artikel'), false)
        ->assertDontSee(route('panduan', 'manajemen-role'), false);

    $this->actingAs($akun)->get(route('panduan', 'manajemen-role'))->assertNotFound();
    $this->actingAs($akun)->get(route('panduan', 'tidak-ada'))->assertNotFound();
});

test('administrator membuka semua topik', function () {
    $admin = User::factory()->admin()->create();

    foreach (array_keys(config('panduan')) as $topik) {
        $this->actingAs($admin)->get(route('panduan', $topik))->assertOk();
    }
});

test('setiap gambar panduan tersedia dan punya teks alternatif', function () {
    foreach (config('panduan') as $kunci => $topik) {
        foreach ($topik['bagian'] as $bagian) {
            if (isset($bagian['gambar'])) {
                expect(public_path("images/panduan/{$bagian['gambar']}.webp"))->toBeFile()
                    ->and($bagian['alt'] ?? '')->not->toBe('', "{$kunci}: alt kosong");
            }
        }
    }
});
