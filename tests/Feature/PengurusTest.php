<?php

use App\Models\DataAnggota;
use App\Models\Jemaat;
use App\Models\Pengurus;
use App\Models\Periode;
use App\Models\User;

function periodeLengkap(int $mulai = 2020, int $selesai = 2022): array
{
    $periode = Periode::create(['tahun_mulai' => $mulai, 'tahun_selesai' => $selesai]);
    $ketua = $periode->jabatans()->create(['nama' => 'Ketua', 'terkait_bidang' => false]);
    $ketuaBidang = $periode->jabatans()->create(['nama' => 'Ketua Bidang', 'terkait_bidang' => true]);
    $bidang = $periode->bidangs()->create(['nama' => 'Umum']);

    return [$periode, $ketua, $ketuaBidang, $bidang];
}

function anggota(string $nama = 'Budi'): DataAnggota
{
    $jemaat = Jemaat::firstOrCreate(['nama' => 'Jemaat Satu']);

    return DataAnggota::create([
        'jemaat_id' => $jemaat->id,
        'nama' => $nama,
        'no_anggota' => 'A-' . substr(md5($nama), 0, 8),
        'nik' => substr(str_pad((string) crc32($nama), 16, '1'), 0, 16),
        'alamat' => 'Jl. Rahasia',
        'telepon' => '08123456789',
    ]);
}

test('jabatan terkait bidang wajib punya bidang, yang tidak terkait harus kosong', function () {
    [$p, $ketua, $ketuaBidang, $bidang] = periodeLengkap();
    $admin = User::factory()->admin()->create();
    $a = anggota();

    $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/pengurus", [
        'jabatan_id' => $ketuaBidang->id, 'data_anggota_id' => $a->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('bidang_id');

    $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/pengurus", [
        'jabatan_id' => $ketua->id, 'data_anggota_id' => $a->id, 'bidang_id' => $bidang->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('bidang_id');
});

test('satu anggota hanya satu jabatan dalam satu periode', function () {
    [$p, $ketua, $ketuaBidang, $bidang] = periodeLengkap();
    $admin = User::factory()->admin()->create();
    $a = anggota();

    $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/pengurus", [
        'jabatan_id' => $ketua->id, 'data_anggota_id' => $a->id,
    ])->assertOk();
    $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/pengurus", [
        'jabatan_id' => $ketuaBidang->id, 'data_anggota_id' => $a->id, 'bidang_id' => $bidang->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('data_anggota_id');
});

test('anggota yang sama boleh menjabat di periode berbeda', function () {
    [$p1, $ketua1] = periodeLengkap();
    [$p2, $ketua2] = periodeLengkap(2023, 2025);
    $admin = User::factory()->admin()->create();
    $a = anggota();

    $this->actingAs($admin)->postJson("/kepengurusan/{$p1->id}/pengurus", [
        'jabatan_id' => $ketua1->id, 'data_anggota_id' => $a->id,
    ])->assertOk();
    $this->actingAs($admin)->postJson("/kepengurusan/{$p2->id}/pengurus", [
        'jabatan_id' => $ketua2->id, 'data_anggota_id' => $a->id,
    ])->assertOk();
});

test('satu jabatan boleh diisi beberapa orang', function () {
    [$p, , $ketuaBidang, $bidang] = periodeLengkap();
    $admin = User::factory()->admin()->create();

    foreach (['Budi', 'Ani'] as $nama) {
        $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/pengurus", [
            'jabatan_id' => $ketuaBidang->id, 'data_anggota_id' => anggota($nama)->id, 'bidang_id' => $bidang->id,
        ])->assertOk();
    }
    expect($p->pengurus()->count())->toBe(2);
});

test('jabatan atau bidang milik periode lain ditolak', function () {
    [$a, , $ketuaBidangA] = periodeLengkap();
    [$b, $ketuaB, , $bidangB] = periodeLengkap(2023, 2025);
    $admin = User::factory()->admin()->create();
    $ang = anggota();

    $this->actingAs($admin)->postJson("/kepengurusan/{$a->id}/pengurus", [
        'jabatan_id' => $ketuaB->id, 'data_anggota_id' => $ang->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('jabatan_id');

    $this->actingAs($admin)->postJson("/kepengurusan/{$a->id}/pengurus", [
        'jabatan_id' => $ketuaBidangA->id, 'data_anggota_id' => $ang->id, 'bidang_id' => $bidangB->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('bidang_id');
});

test('pengurus periode lain tidak bisa diakses lewat periode sendiri', function () {
    [$a] = periodeLengkap();
    [$b, $ketuaB] = periodeLengkap(2023, 2025);
    $milikB = Pengurus::create(['periode_id' => $b->id, 'jabatan_id' => $ketuaB->id, 'data_anggota_id' => anggota()->id]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->getJson("/kepengurusan/{$a->id}/pengurus/{$milikB->id}")->assertNotFound();
    $this->actingAs($admin)->deleteJson("/kepengurusan/{$a->id}/pengurus/{$milikB->id}")->assertNotFound();
});

test('pengurus bisa diubah dan dihapus', function () {
    [$p, $ketua, $ketuaBidang, $bidang] = periodeLengkap();
    $admin = User::factory()->admin()->create();
    $x = Pengurus::create(['periode_id' => $p->id, 'jabatan_id' => $ketua->id, 'data_anggota_id' => anggota()->id]);

    $this->actingAs($admin)->putJson("/kepengurusan/{$p->id}/pengurus/{$x->id}", [
        'jabatan_id' => $ketuaBidang->id, 'data_anggota_id' => $x->data_anggota_id, 'bidang_id' => $bidang->id,
    ])->assertOk();
    expect($x->fresh()->bidang_id)->toBe($bidang->id);
    $this->actingAs($admin)->get("/kepengurusan/{$p->id}/pengurus")->assertOk()->assertSee('Ketua Bidang');
    $this->actingAs($admin)->getJson("/kepengurusan/{$p->id}/pengurus/data")->assertOk()->assertJsonPath('data.0.jabatan', 'Ketua Bidang');
    $this->actingAs($admin)->deleteJson("/kepengurusan/{$p->id}/pengurus/{$x->id}")->assertOk();
    expect(Pengurus::count())->toBe(0);
});

test('pencarian anggota tanpa data pribadi', function () {
    [$p] = periodeLengkap();
    $admin = User::factory()->admin()->create();
    foreach (range(1, 25) as $i) {
        anggota("Anggota {$i}");
    }

    $res = $this->actingAs($admin)->getJson("/kepengurusan/{$p->id}/pengurus/anggota?search=Anggota")->assertOk();
    $rows = $res->json();
    expect($rows)->toHaveCount(20);
    expect(array_keys($rows[0]))->toEqualCanonicalizing(['id', 'nama', 'no_anggota', 'jemaat']);
    expect($rows[0]['jemaat'])->toBe('Jemaat Satu');
    expect($res->getContent())->not->toContain('08123456789')->not->toContain('Jl. Rahasia');
});

test('anggota yang menjadi pengurus tidak bisa dihapus', function () {
    [$p, $ketua] = periodeLengkap();
    $a = anggota();
    Pengurus::create(['periode_id' => $p->id, 'jabatan_id' => $ketua->id, 'data_anggota_id' => $a->id]);

    $this->actingAs(User::factory()->admin()->create())->deleteJson("/data-anggota/{$a->id}")
        ->assertUnprocessable()->assertJson(['success' => false]);
    expect(DataAnggota::find($a->id))->not->toBeNull();
});

test('anggota bukan pengurus tetap bisa dihapus', function () {
    $a = anggota();
    $this->actingAs(User::factory()->admin()->create())->deleteJson("/data-anggota/{$a->id}")->assertOk();
});

test('bidang dan jabatan yang punya pengurus tidak bisa dihapus', function () {
    [$p, , $ketuaBidang, $bidang] = periodeLengkap();
    Pengurus::create(['periode_id' => $p->id, 'jabatan_id' => $ketuaBidang->id, 'data_anggota_id' => anggota()->id, 'bidang_id' => $bidang->id]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->deleteJson("/kepengurusan/{$p->id}/bidang/{$bidang->id}")->assertUnprocessable();
    $this->actingAs($admin)->deleteJson("/kepengurusan/{$p->id}/jabatan/{$ketuaBidang->id}")->assertUnprocessable();
});
