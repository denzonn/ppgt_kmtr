<?php

use App\Models\DataAnggota;
use App\Models\Jemaat;
use App\Models\Pengurus;
use App\Models\Periode;
use App\Models\User;

function periodeLengkap(int $mulai = 2020, int $selesai = 2022): array
{
    $periode = Periode::create(['tahun_mulai' => $mulai, 'tahun_selesai' => $selesai]);
    $bidang = $periode->bidangs()->create(['nama' => 'Umum']);
    $ketua = $periode->jabatans()->create(['nama' => 'Ketua', 'bidang_id' => $bidang->id]);
    $ketuaBidang = $periode->jabatans()->create(['nama' => 'Anggota Bidang', 'bidang_id' => $bidang->id]);

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

test('satu anggota hanya satu jabatan dalam satu periode', function () {
    [$p, $ketua, $ketuaBidang, $bidang] = periodeLengkap();
    $admin = User::factory()->admin()->create();
    $a = anggota();

    $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/pengurus", [
        'jabatan_id' => $ketua->id, 'data_anggota_id' => $a->id,
    ])->assertOk();
    $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/pengurus", [
        'jabatan_id' => $ketuaBidang->id, 'data_anggota_id' => $a->id,
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
            'jabatan_id' => $ketuaBidang->id, 'data_anggota_id' => anggota($nama)->id,
        ])->assertOk();
    }
    expect($p->pengurus()->count())->toBe(2);
});

test('jabatan milik periode lain ditolak', function () {
    [$a] = periodeLengkap();
    [, $ketuaB] = periodeLengkap(2023, 2025);

    $this->actingAs(User::factory()->admin()->create())->postJson("/kepengurusan/{$a->id}/pengurus", [
        'jabatan_id' => $ketuaB->id, 'data_anggota_id' => anggota()->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('jabatan_id');
});

test('jabatan tanpa bidang belum bisa dipakai', function () {
    [$p] = periodeLengkap();
    $tanpaBidang = $p->jabatans()->create(['nama' => 'Lama']);

    $this->actingAs(User::factory()->admin()->create())->postJson("/kepengurusan/{$p->id}/pengurus", [
        'jabatan_id' => $tanpaBidang->id, 'data_anggota_id' => anggota()->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('jabatan_id');
});

test('bidang pengurus mengikuti jabatannya', function () {
    [$p, $ketua] = periodeLengkap();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/pengurus", [
        'jabatan_id' => $ketua->id, 'data_anggota_id' => anggota()->id,
    ])->assertOk();
    $this->actingAs($admin)->getJson("/kepengurusan/{$p->id}/pengurus/data")
        ->assertJsonPath('data.0.jabatan', 'Ketua')->assertJsonPath('data.0.bidang', 'Umum');
});

test('pengurus ditolak saat jumlah orang bidang sudah penuh', function () {
    [$p, $ketua, $anggotaBidang, $bidang] = periodeLengkap();
    $bidang->update(['jumlah_anggota' => 2]);
    $admin = User::factory()->admin()->create();
    $url = "/kepengurusan/{$p->id}/pengurus";

    $this->actingAs($admin)->postJson($url, ['jabatan_id' => $ketua->id, 'data_anggota_id' => anggota('Budi')->id])->assertOk();
    $this->actingAs($admin)->postJson($url, ['jabatan_id' => $anggotaBidang->id, 'data_anggota_id' => anggota('Ani')->id])->assertOk();
    $this->actingAs($admin)->postJson($url, ['jabatan_id' => $anggotaBidang->id, 'data_anggota_id' => anggota('Citra')->id])
        ->assertUnprocessable()->assertJsonValidationErrors('jabatan_id');

    // Mengubah pengurus di bidang yang sama tidak dihitung dua kali.
    $x = Pengurus::first();
    $this->actingAs($admin)->putJson("{$url}/{$x->id}", ['jabatan_id' => $anggotaBidang->id, 'data_anggota_id' => $x->data_anggota_id])->assertOk();
});

test('pindah ke bidang penuh ditolak', function () {
    [$p, $ketua] = periodeLengkap();
    $sosial = $p->bidangs()->create(['nama' => 'Sosial', 'jumlah_anggota' => 1]);
    $jabatanSosial = $p->jabatans()->create(['nama' => 'Ketua', 'bidang_id' => $sosial->id]);
    Pengurus::create(['periode_id' => $p->id, 'jabatan_id' => $jabatanSosial->id, 'data_anggota_id' => anggota('Ani')->id]);
    $x = Pengurus::create(['periode_id' => $p->id, 'jabatan_id' => $ketua->id, 'data_anggota_id' => anggota('Budi')->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->putJson("/kepengurusan/{$p->id}/pengurus/{$x->id}", ['jabatan_id' => $jabatanSosial->id, 'data_anggota_id' => $x->data_anggota_id])
        ->assertUnprocessable()->assertJsonValidationErrors('jabatan_id');
});

test('daftar pengurus bisa disaring per bidang', function () {
    [$p, $ketua] = periodeLengkap();
    $sosial = $p->bidangs()->create(['nama' => 'Sosial']);
    $jabatanSosial = $p->jabatans()->create(['nama' => 'Ketua', 'bidang_id' => $sosial->id]);
    Pengurus::create(['periode_id' => $p->id, 'jabatan_id' => $ketua->id, 'data_anggota_id' => anggota('Budi')->id]);
    Pengurus::create(['periode_id' => $p->id, 'jabatan_id' => $jabatanSosial->id, 'data_anggota_id' => anggota('Ani')->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->getJson("/kepengurusan/{$p->id}/pengurus/data?bidang_id={$sosial->id}")
        ->assertJsonCount(1, 'data')->assertJsonPath('data.0.nama', 'Ani');
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
        'jabatan_id' => $ketuaBidang->id, 'data_anggota_id' => $x->data_anggota_id,
    ])->assertOk();
    expect($x->fresh()->jabatan_id)->toBe($ketuaBidang->id);
    $this->actingAs($admin)->get("/kepengurusan/{$p->id}/pengurus")->assertOk()->assertSee('Anggota Bidang');
    $this->actingAs($admin)->getJson("/kepengurusan/{$p->id}/pengurus/data")->assertOk()->assertJsonPath('data.0.jabatan', 'Anggota Bidang');
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
    Pengurus::create(['periode_id' => $p->id, 'jabatan_id' => $ketuaBidang->id, 'data_anggota_id' => anggota()->id]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->deleteJson("/kepengurusan/{$p->id}/bidang/{$bidang->id}")->assertUnprocessable();
    $this->actingAs($admin)->deleteJson("/kepengurusan/{$p->id}/jabatan/{$ketuaBidang->id}")->assertUnprocessable();
});

test('data_anggota_id kosong memberi pesan Indonesian', function () {
    [$p, $ketua] = periodeLengkap();
    $this->actingAs(User::factory()->admin()->create())
        ->postJson("/kepengurusan/{$p->id}/pengurus", ['jabatan_id' => $ketua->id])
        ->assertUnprocessable()
        ->assertJsonPath('errors.data_anggota_id.0', 'Anggota wajib dipilih.');
});

test('id body non-integer memberi 422, bukan 500', function () {
    [$p] = periodeLengkap();
    $this->actingAs(User::factory()->admin()->create())
        ->postJson("/kepengurusan/{$p->id}/pengurus", ['jabatan_id' => [''], 'data_anggota_id' => 'x'])
        ->assertUnprocessable()->assertJsonValidationErrors(['jabatan_id', 'data_anggota_id']);
    $this->actingAs(User::factory()->admin()->create())
        ->postJson("/kepengurusan/{$p->id}/program-kerja", ['tahun' => 2020, 'bidang_id' => [''], 'nama' => 'X'])
        ->assertUnprocessable()->assertJsonValidationErrors('bidang_id');
});

test('daftar pengurus berurutan per bidang', function () {
    [$p, $ketua] = periodeLengkap();
    $sosial = $p->bidangs()->create(['nama' => 'Sosial']);
    $jabatanSosial = $p->jabatans()->create(['nama' => 'Ketua', 'bidang_id' => $sosial->id]);
    Pengurus::create(['periode_id' => $p->id, 'jabatan_id' => $jabatanSosial->id, 'data_anggota_id' => anggota('Ani')->id]);
    Pengurus::create(['periode_id' => $p->id, 'jabatan_id' => $ketua->id, 'data_anggota_id' => anggota('Budi')->id]);
    Pengurus::create(['periode_id' => $p->id, 'jabatan_id' => $jabatanSosial->id, 'data_anggota_id' => anggota('Citra')->id]);

    $data = $this->actingAs(User::factory()->admin()->create())->getJson("/kepengurusan/{$p->id}/pengurus/data")->json('data');

    expect(array_column($data, 'bidang'))->toBe(['Umum', 'Sosial', 'Sosial']);
});

function pengurusDenganFoto(): array
{
    Illuminate\Support\Facades\Storage::fake('public');
    [$p, $ketua] = periodeLengkap();
    $admin = User::factory()->admin()->create();

    test()->actingAs($admin)->post("/kepengurusan/{$p->id}/pengurus", [
        'jabatan_id' => $ketua->id,
        'data_anggota_id' => anggota()->id,
        'foto' => Illuminate\Http\UploadedFile::fake()->image('budi.jpg'),
    ], ['Accept' => 'application/json'])->assertOk();

    return [$p, $ketua, $admin, Pengurus::first()];
}

test('pengurus bisa disimpan dengan foto dan fotonya tampil di daftar', function () {
    [$p, , $admin, $pengurus] = pengurusDenganFoto();

    expect($pengurus->foto)->toStartWith(asset('storage/pengurus/'));
    Illuminate\Support\Facades\Storage::disk('public')->assertExists(\App\Support\FileUpload::path($pengurus->foto));

    $this->actingAs($admin)->getJson("/kepengurusan/{$p->id}/pengurus/data")
        ->assertJsonPath('data.0.foto', $pengurus->foto);
});

test('foto pengurus tidak wajib', function () {
    [$p, $ketua] = periodeLengkap();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->postJson("/kepengurusan/{$p->id}/pengurus", [
        'jabatan_id' => $ketua->id, 'data_anggota_id' => anggota()->id,
    ])->assertOk();

    expect(Pengurus::first()->foto)->toBeNull();
    $this->actingAs($admin)->getJson("/kepengurusan/{$p->id}/pengurus/data")->assertJsonPath('data.0.foto', null);
});

test('foto pengurus harus berupa gambar', function () {
    Illuminate\Support\Facades\Storage::fake('public');
    [$p, $ketua] = periodeLengkap();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post("/kepengurusan/{$p->id}/pengurus", [
        'jabatan_id' => $ketua->id,
        'data_anggota_id' => anggota()->id,
        'foto' => Illuminate\Http\UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf'),
    ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('foto');

    expect(Pengurus::count())->toBe(0);
});

test('mengganti foto pengurus menghapus file lamanya', function () {
    [$p, $ketua, $admin, $pengurus] = pengurusDenganFoto();
    $lama = $pengurus->foto;

    $this->actingAs($admin)->post("/kepengurusan/{$p->id}/pengurus/{$pengurus->id}", [
        '_method' => 'PUT',
        'jabatan_id' => $ketua->id,
        'data_anggota_id' => $pengurus->data_anggota_id,
        'foto' => Illuminate\Http\UploadedFile::fake()->image('baru.jpg'),
    ], ['Accept' => 'application/json'])->assertOk();

    $baru = $pengurus->fresh()->foto;
    expect($baru)->not->toBe($lama);
    Illuminate\Support\Facades\Storage::disk('public')->assertMissing(\App\Support\FileUpload::path($lama));
    Illuminate\Support\Facades\Storage::disk('public')->assertExists(\App\Support\FileUpload::path($baru));
});

test('mengubah pengurus tanpa memilih foto tetap mempertahankan foto lama', function () {
    [$p, $ketua, $admin, $pengurus] = pengurusDenganFoto();
    $lama = $pengurus->foto;

    $this->actingAs($admin)->putJson("/kepengurusan/{$p->id}/pengurus/{$pengurus->id}", [
        'jabatan_id' => $ketua->id, 'data_anggota_id' => $pengurus->data_anggota_id,
    ])->assertOk();

    expect($pengurus->fresh()->foto)->toBe($lama);
    Illuminate\Support\Facades\Storage::disk('public')->assertExists(\App\Support\FileUpload::path($lama));
});

test('foto pengurus bisa dihapus tanpa menghapus pengurusnya', function () {
    [$p, $ketua, $admin, $pengurus] = pengurusDenganFoto();
    $lama = $pengurus->foto;

    $this->actingAs($admin)->putJson("/kepengurusan/{$p->id}/pengurus/{$pengurus->id}", [
        'jabatan_id' => $ketua->id, 'data_anggota_id' => $pengurus->data_anggota_id, 'hapus_foto' => 1,
    ])->assertOk();

    expect($pengurus->fresh()->foto)->toBeNull();
    Illuminate\Support\Facades\Storage::disk('public')->assertMissing(\App\Support\FileUpload::path($lama));
});

test('menghapus pengurus ikut menghapus file fotonya', function () {
    [$p, , $admin, $pengurus] = pengurusDenganFoto();
    $foto = $pengurus->foto;

    $this->actingAs($admin)->deleteJson("/kepengurusan/{$p->id}/pengurus/{$pengurus->id}")->assertOk();

    Illuminate\Support\Facades\Storage::disk('public')->assertMissing(\App\Support\FileUpload::path($foto));
});
