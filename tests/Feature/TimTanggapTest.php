<?php

use App\Models\AuditLog;
use App\Models\DataAnggota;
use App\Models\Jemaat;
use App\Models\Role;
use App\Models\User;
use App\Support\NomorWa;

function akunTimTanggap(): User
{
    return User::factory()->create(['role_id' => Role::firstWhere('nama', 'Tim Tanggap')->id]);
}

function anggotaDarah(Jemaat $jemaat, string $nama, ?string $golongan = 'A', ?string $rhesus = 'Positif', ?string $telepon = '081234567890', array $tambahan = []): DataAnggota
{
    return DataAnggota::create($tambahan + [
        'jemaat_id' => $jemaat->id,
        'nama' => $nama,
        'no_anggota' => 'D-' . uniqid(),
        'jenis_kelamin' => 'Laki-laki',
        'status_anggota' => 'Aktif',
        'golongan_darah' => $golongan,
        'golongan_darah_rhesus' => $rhesus,
        'telepon' => $telepon,
    ]);
}

beforeEach(function () {
    $this->jemaatA = Jemaat::create(['nama' => 'Jemaat Maranatha']);
    $this->jemaatB = Jemaat::create(['nama' => 'Jemaat Sion']);
    $this->tim = akunTimTanggap();
});

// ---------- Role dan akses ----------

test('role Tim Tanggap tersedia hanya dengan menu golongan darah', function () {
    expect(Role::firstWhere('nama', 'Tim Tanggap')->menus)->toBe(['tim_tanggap']);
});

test('akun Tim Tanggap tidak butuh periode atau jemaat', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->postJson('/pengguna', [
        'name' => 'Tim', 'email' => 'tim@ppgt.id', 'password' => 'rahasia1', 'role_id' => Role::firstWhere('nama', 'Tim Tanggap')->id,
    ])->assertOk();
});

test('Tim Tanggap langsung masuk ke halaman golongan darah dan menunya tampil', function () {
    expect($this->tim->homeUrl())->toBe(route('tim_tanggap.index'));

    $this->actingAs($this->tim)->get('/tim-tanggap')->assertOk()
        ->assertSee(route('tim_tanggap.index'), false)
        ->assertDontSee(route('data_anggota.index'), false);
});

test('halaman tertutup untuk tamu dan role lain', function () {
    $this->get('/tim-tanggap')->assertRedirect('/login');

    $lain = User::factory()->create(['role_id' => Role::firstWhere('nama', 'Pengurus Jemaat')->id, 'jemaat_id' => $this->jemaatA->id]);
    $this->actingAs($lain)->get('/tim-tanggap')->assertForbidden();
    $this->actingAs($lain)->getJson('/tim-tanggap/data')->assertForbidden();
});

test('Tim Tanggap tidak bisa membuka data anggota lengkap', function () {
    $this->actingAs($this->tim)->get('/data-anggota')->assertForbidden();
    $this->actingAs($this->tim)->getJson('/data-anggota/data')->assertForbidden();
});

test('admin bisa membuka halaman golongan darah', function () {
    $this->actingAs(User::factory()->admin()->create())->get('/tim-tanggap')->assertOk();
});

// ---------- Data ----------

test('data mencakup anggota seluruh jemaat', function () {
    anggotaDarah($this->jemaatA, 'Ani');
    anggotaDarah($this->jemaatB, 'Budi');

    $res = $this->actingAs($this->tim)->getJson('/tim-tanggap/data')->assertOk();

    expect(collect($res->json('data'))->pluck('jemaat')->all())->toBe(['Jemaat Maranatha', 'Jemaat Sion']);
    expect($res->json('data.0.golongan'))->toBe('A');
    expect($res->json('data.0.rhesus'))->toBe('Positif');
});

test('data pribadi yang tidak perlu tidak ikut dikirim', function () {
    anggotaDarah($this->jemaatA, 'Ani', tambahan: ['nik' => '7371000011112222', 'alamat' => 'Jl. Rahasia 9', 'email' => 'ani@rahasia.id', 'tanggal_lahir' => '2000-01-01']);

    $isi = $this->actingAs($this->tim)->getJson('/tim-tanggap/data')->getContent();

    expect($isi)->not->toContain('7371000011112222')->not->toContain('Jl. Rahasia')->not->toContain('ani@rahasia.id')->not->toContain('2000-01-01');
});

test('filter golongan, rhesus, jemaat, dan pencarian', function (array $filter, array $hasil) {
    anggotaDarah($this->jemaatA, 'Ani', 'A', 'Positif');
    anggotaDarah($this->jemaatA, 'Budi', 'O', 'Negatif');
    anggotaDarah($this->jemaatB, 'Cici', 'O', 'Positif');
    anggotaDarah($this->jemaatB, 'Dedi', '', '');
    anggotaDarah($this->jemaatB, 'Eka', null, null);

    if (isset($filter['jemaat'])) {
        $filter['jemaat'] = Jemaat::firstWhere('nama', $filter['jemaat'])->id;
    }

    $res = $this->actingAs($this->tim)->getJson('/tim-tanggap/data?' . http_build_query($filter));

    expect(collect($res->json('data'))->pluck('nama')->all())->toBe($hasil);
})->with([
    'golongan' => [['golongan' => 'O'], ['Budi', 'Cici']],
    'rhesus' => [['golongan' => 'O', 'rhesus' => 'Negatif'], ['Budi']],
    'jemaat' => [['jemaat' => 'Jemaat Sion'], ['Cici', 'Dedi', 'Eka']],
    'cari' => [['search' => 'Ani'], ['Ani']],
    'belum diisi' => [['golongan' => 'kosong'], ['Dedi', 'Eka']],
]);

test('filter hanya yang punya nomor WA', function () {
    anggotaDarah($this->jemaatA, 'Ada Nomor');
    anggotaDarah($this->jemaatA, 'Kosong', telepon: '');
    anggotaDarah($this->jemaatA, 'Null', telepon: null);

    $res = $this->actingAs($this->tim)->getJson('/tim-tanggap/data?punya_wa=1');

    expect(collect($res->json('data'))->pluck('nama')->all())->toBe(['Ada Nomor']);
});

test('statistik menghitung tiap golongan dan yang bisa dihubungi', function () {
    anggotaDarah($this->jemaatA, 'A1', 'A');
    anggotaDarah($this->jemaatA, 'A2', 'A', telepon: null);
    anggotaDarah($this->jemaatA, 'O1', 'O', telepon: '');
    anggotaDarah($this->jemaatB, 'X1', '', '');

    $res = $this->actingAs($this->tim)->getJson('/tim-tanggap/data')->assertOk();

    expect($res->json('statistik.A'))->toEqual(['total' => 2, 'punya_wa' => 1]);
    expect($res->json('statistik.O'))->toEqual(['total' => 1, 'punya_wa' => 0]);
    expect($res->json('statistik.B'))->toEqual(['total' => 0, 'punya_wa' => 0]);
    expect($res->json('statistik.kosong'))->toEqual(['total' => 1, 'punya_wa' => 1]);
});

// ---------- Nomor WhatsApp ----------

test('nomor telepon diubah ke format WhatsApp', function (?string $telepon, ?string $hasil) {
    expect(NomorWa::dari($telepon))->toBe($hasil);
})->with([
    'awalan 0' => ['081234567890', '6281234567890'],
    'awalan 62' => ['6281234567890', '6281234567890'],
    'awalan +62' => ['+62 812-3456-7890', '6281234567890'],
    'awalan 8' => ['81234567890', '6281234567890'],
    'ada spasi dan tanda' => ['0812 3456.7890', '6281234567890'],
    'kosong' => ['', null],
    'null' => [null, null],
    'terlalu pendek' => ['0812', null],
    'terlalu panjang' => ['08123456789012345678', null],
    'bukan angka' => ['tidak ada', null],
    'telepon rumah' => ['0411123456', null],
]);

test('data menyertakan nomor WA yang siap dipakai', function () {
    anggotaDarah($this->jemaatA, 'Ani', telepon: '0812-3456-7890');
    anggotaDarah($this->jemaatA, 'Budi', telepon: '123');

    $this->actingAs($this->tim)->getJson('/tim-tanggap/data')
        ->assertJsonPath('data.0.wa', '6281234567890')
        ->assertJsonPath('data.1.wa', null);
});

// ---------- Pencatatan pesan ----------

test('membuka WhatsApp untuk seorang anggota tercatat di audit log', function () {
    $anggota = anggotaDarah($this->jemaatA, 'Ani');
    AuditLog::query()->delete();

    $this->actingAs($this->tim)->postJson("/tim-tanggap/{$anggota->id}/pesan")->assertOk();

    $log = AuditLog::latest('id')->first();
    expect($log->aksi)->toBe('kirim pesan wa');
    expect($log->label)->toBe('Ani');
    expect($log->user_id)->toBe($this->tim->id);
});

test('pencatatan pesan tertutup untuk role lain dan anggota yang tidak ada', function () {
    $anggota = anggotaDarah($this->jemaatA, 'Ani');
    $lain = User::factory()->create(['role_id' => Role::firstWhere('nama', 'Pengurus Jemaat')->id, 'jemaat_id' => $this->jemaatA->id]);

    $this->actingAs($lain)->postJson("/tim-tanggap/{$anggota->id}/pesan")->assertForbidden();
    $this->actingAs($this->tim)->postJson('/tim-tanggap/999999/pesan')->assertNotFound();
});

test('aksi kirim pesan bisa difilter di audit log', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/audit-log')->assertSee('value="kirim pesan wa"', false);
});
