<?php

use App\Models\AuditLog;
use App\Models\DataAnggota;
use App\Models\Jemaat;
use App\Models\Pemilihan;
use App\Models\PemilihanPeserta;
use App\Models\PemilihanPutaran;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

function akunJemaatPemilihan(Jemaat $jemaat): User
{
    return User::factory()->create([
        'role_id' => Role::where('nama', 'Pengurus Jemaat')->value('id'),
        'jemaat_id' => $jemaat->id,
    ]);
}

function pesertaPemilihan(Pemilihan $pemilihan, string $nama, string $peran = 'utusan_penuh', ?int $jemaatId = null): PemilihanPeserta
{
    return $pemilihan->peserta()->create([
        'nama' => $nama,
        'peran' => $peran,
        'jemaat_id' => $jemaatId,
        'kode' => PemilihanPeserta::kodeBaru($pemilihan->id),
    ]);
}

function anggotaUntukPemilihan(string $nama, Jemaat $jemaat, string $status): DataAnggota
{
    return DataAnggota::create(['jemaat_id' => $jemaat->id, 'nama' => $nama, 'no_anggota' => 'X-'.uniqid(), 'jenis_kelamin' => 'Perempuan', 'status_anggota' => $status]);
}

function putaranDibuka(Pemilihan $pemilihan, array $calon, string $status = 'dibuka', string $jabatan = 'Ketua'): PemilihanPutaran
{
    $wadah = $pemilihan->jabatan()->firstOrCreate(['nama' => $jabatan]);
    $putaran = $wadah->tahap()->create(['pemilihan_id' => $pemilihan->id, 'tahap' => 'Bakal Calon', 'status' => $status]);
    $putaran->calon()->createMany(array_map(fn ($orang) => ['peserta_id' => $orang->id], $calon));

    return $putaran;
}

function pilihDiBilik($test, Pemilihan $pemilihan, PemilihanPeserta $pemilih, PemilihanPutaran $putaran, PemilihanPeserta $calon)
{
    return $test->post("/bilik/{$pemilihan->token}/pilih", [
        'kode' => $pemilih->kode,
        'calon_id' => $putaran->calon()->where('peserta_id', $calon->id)->value('id'),
    ]);
}

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->jemaatA = Jemaat::create(['nama' => 'Sudiang']);
    $this->jemaatB = Jemaat::create(['nama' => 'Tamalanrea']);
    $this->konferensi = Pemilihan::create(['lingkup' => 'klasis', 'nama' => 'Konferensi 2026', 'token' => str_repeat('a', 40)]);
    $this->penuhA = pesertaPemilihan($this->konferensi, 'Andi', 'utusan_penuh', $this->jemaatA->id);
    $this->penuhB = pesertaPemilihan($this->konferensi, 'Budi', 'utusan_penuh', $this->jemaatB->id);
    $this->cadangan = pesertaPemilihan($this->konferensi, 'Cici', 'utusan_cadangan', $this->jemaatA->id);
    $this->demisioner = pesertaPemilihan($this->konferensi, 'Dedi', 'demisioner');
});

// ---------- Akses ----------

test('pemilihan klasis hanya untuk admin', function () {
    $this->get('/pemilihan')->assertRedirect('/login');
    $this->actingAs(User::factory()->create())->get('/pemilihan')->assertForbidden();
    $this->actingAs($this->admin)->get('/pemilihan')->assertOk()->assertSee('Konferensi 2026');
    $this->actingAs($this->admin)->get("/pemilihan/{$this->konferensi->id}")->assertOk();
    $this->actingAs($this->admin)->get("/pemilihan/{$this->konferensi->id}?tab=peserta")->assertOk()->assertSee($this->penuhA->kode);
});

test('akun jemaat hanya melihat pemilihan jemaatnya sendiri', function () {
    $milikA = Pemilihan::create(['lingkup' => 'jemaat', 'jemaat_id' => $this->jemaatA->id, 'nama' => 'Rapat Sudiang', 'token' => str_repeat('b', 40)]);
    $milikB = Pemilihan::create(['lingkup' => 'jemaat', 'jemaat_id' => $this->jemaatB->id, 'nama' => 'Rapat Tamalanrea', 'token' => str_repeat('c', 40)]);
    $akun = akunJemaatPemilihan($this->jemaatA);

    $this->actingAs($akun)->get('/jemaat-saya/pemilihan')->assertOk()->assertSee('Rapat Sudiang')->assertDontSee('Rapat Tamalanrea')->assertDontSee('Konferensi 2026');
    $this->actingAs($akun)->get("/jemaat-saya/pemilihan/{$milikA->id}")->assertOk();
    $this->actingAs($akun)->get("/jemaat-saya/pemilihan/{$milikB->id}")->assertNotFound();
    $this->actingAs($akun)->get("/jemaat-saya/pemilihan/{$this->konferensi->id}")->assertNotFound();
    $this->actingAs($akun)->delete("/jemaat-saya/pemilihan/{$milikB->id}")->assertNotFound();
});

test('akun jemaat membuat pemilihan untuk jemaatnya', function () {
    $this->actingAs(akunJemaatPemilihan($this->jemaatA))->post('/jemaat-saya/pemilihan', ['nama' => 'Rapat Anggota', 'jemaat_id' => $this->jemaatB->id])->assertRedirect();

    expect(Pemilihan::where('nama', 'Rapat Anggota')->first())
        ->lingkup->toBe('jemaat')
        ->jemaat_id->toBe($this->jemaatA->id);
});

// ---------- Peserta ----------

test('admin menambah utusan per baris dan melewati nama yang sudah ada', function () {
    $this->actingAs($this->admin)->post("/pemilihan/{$this->konferensi->id}/peserta", [
        'peran' => 'utusan_penuh',
        'jemaat_id' => $this->jemaatA->id,
        'nama' => "Eka\n\n  Fani  \nandi",
    ])->assertRedirect();

    expect($this->konferensi->peserta()->where('jemaat_id', $this->jemaatA->id)->where('peran', 'utusan_penuh')->orderBy('nama')->pluck('nama')->all())->toBe(['Andi', 'Eka', 'Fani']);
});

test('utusan wajib punya jemaat', function () {
    $this->actingAs($this->admin)->post("/pemilihan/{$this->konferensi->id}/peserta", ['peran' => 'utusan_penuh', 'nama' => 'Eka'])->assertSessionHasErrors('jemaat_id');
});

test('peran klasis tidak bisa dipakai di pemilihan jemaat', function () {
    $rapat = Pemilihan::create(['lingkup' => 'jemaat', 'jemaat_id' => $this->jemaatA->id, 'nama' => 'Rapat', 'token' => str_repeat('b', 40)]);

    $this->actingAs(akunJemaatPemilihan($this->jemaatA))->post("/jemaat-saya/pemilihan/{$rapat->id}/peserta", ['peran' => 'utusan_penuh', 'nama' => 'Eka'])->assertSessionHasErrors('peran');
});

test('akun jemaat mengambil anggota aktif jemaatnya saja', function () {
    $rapat = Pemilihan::create(['lingkup' => 'jemaat', 'jemaat_id' => $this->jemaatA->id, 'nama' => 'Rapat', 'token' => str_repeat('b', 40)]);
    foreach ([['Aktif A', $this->jemaatA, 'Aktif'], ['Pasif A', $this->jemaatA, 'Tidak Aktif'], ['Aktif B', $this->jemaatB, 'Aktif']] as [$nama, $jemaat, $status]) {
        anggotaUntukPemilihan($nama, $jemaat, $status);
    }

    $this->actingAs(akunJemaatPemilihan($this->jemaatA))->post("/jemaat-saya/pemilihan/{$rapat->id}/peserta/anggota")->assertRedirect();

    expect($rapat->peserta()->pluck('nama')->all())->toBe(['Aktif A']);
});

test('kode bisa diganti dan kode lama tidak berlaku', function () {
    $lama = $this->penuhA->kode;
    $putaran = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB]);

    $this->actingAs($this->admin)->post("/pemilihan/{$this->konferensi->id}/peserta/{$this->penuhA->id}/kode")->assertRedirect();

    expect($this->penuhA->fresh()->kode)->not->toBe($lama);
    $this->post("/bilik/{$this->konferensi->token}/periksa", ['kode' => $lama])->assertSessionHas('ditolak');
});

test('peserta yang sudah memilih tidak bisa dihapus', function () {
    $putaran = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB]);
    pilihDiBilik($this, $this->konferensi, $this->penuhA, $putaran, $this->penuhB);

    $this->actingAs($this->admin)->delete("/pemilihan/{$this->konferensi->id}/peserta/{$this->penuhA->id}")->assertSessionHas('error');

    expect($this->penuhA->fresh())->not->toBeNull();
});

test('kartu kode diunduh sebagai PDF dan hanya untuk yang punya hak suara', function () {
    $isi = null;
    View::creator('pages.pemilihan.kartu-pdf', function ($view) use (&$isi) {
        $isi = $view->getData()['kelompok'];
    });

    $respons = $this->actingAs($this->admin)->get("/pemilihan/{$this->konferensi->id}/kartu")->assertOk()->assertHeader('content-type', 'application/pdf');

    expect($respons->headers->get('content-disposition'))->toContain('konferensi-2026-kartu-kode.pdf')
        ->and(str_starts_with($respons->getContent(), '%PDF'))->toBeTrue()
        ->and($isi->keys()->all())->toBe(['Sudiang', 'Tamalanrea'])
        ->and($isi->flatten()->pluck('nama')->all())->toBe(['Andi', 'Budi']);
});

test('kartu kode bisa diunduh per jemaat', function () {
    $isi = null;
    View::creator('pages.pemilihan.kartu-pdf', function ($view) use (&$isi) {
        $isi = $view->getData()['kelompok'];
    });

    $respons = $this->actingAs($this->admin)->get("/pemilihan/{$this->konferensi->id}/kartu?jemaat={$this->jemaatB->id}")->assertOk();

    expect($respons->headers->get('content-disposition'))->toContain('tamalanrea')
        ->and($isi->flatten()->pluck('nama')->all())->toBe(['Budi']);
});

test('kartu kode tidak dibuat kalau belum ada yang punya hak suara', function () {
    $kosong = Pemilihan::create(['lingkup' => 'klasis', 'nama' => 'Kosong', 'token' => str_repeat('k', 40)]);

    $this->actingAs($this->admin)->get("/pemilihan/{$kosong->id}/kartu")->assertRedirect()->assertSessionHas('error');
});

test('utusan dipilih dari data anggota jemaatnya', function () {
    $milikA = anggotaUntukPemilihan('Yakob', $this->jemaatA, 'Aktif');
    $milikB = anggotaUntukPemilihan('Rut', $this->jemaatB, 'Aktif');
    $url = "/pemilihan/{$this->konferensi->id}";

    $this->actingAs($this->admin)->getJson("{$url}/anggota?jemaat_id={$this->jemaatA->id}&cari=yak")
        ->assertOk()
        ->assertJsonPath('0.nama', 'Yakob')
        ->assertJsonPath('0.sudah', null)
        ->assertJsonCount(1);

    // Anggota jemaat lain yang diselipkan ke form diabaikan.
    $this->actingAs($this->admin)->post("{$url}/peserta", [
        'peran' => 'utusan_penuh', 'jemaat_id' => $this->jemaatA->id, 'anggota' => [$milikA->id, $milikB->id],
    ])->assertSessionHas('success');

    $baru = $this->konferensi->peserta()->where('data_anggota_id', $milikA->id)->first();
    expect($baru)->nama->toBe('Yakob')->jemaat_id->toBe($this->jemaatA->id)
        ->and($this->konferensi->peserta()->where('data_anggota_id', $milikB->id)->exists())->toBeFalse();

    // Anggota yang sudah terdaftar ditandai dan tidak dobel.
    $this->actingAs($this->admin)->getJson("{$url}/anggota?jemaat_id={$this->jemaatA->id}&cari=yak")->assertJsonPath('0.sudah', 'Utusan Penuh');
    $this->actingAs($this->admin)->post("{$url}/peserta", ['peran' => 'utusan_cadangan', 'jemaat_id' => $this->jemaatA->id, 'anggota' => [$milikA->id]]);
    expect($this->konferensi->peserta()->where('data_anggota_id', $milikA->id)->count())->toBe(1);
});

test('tambah peserta tanpa memilih anggota atau nama ditolak', function () {
    $this->actingAs($this->admin)->post("/pemilihan/{$this->konferensi->id}/peserta", ['peran' => 'utusan_penuh', 'jemaat_id' => $this->jemaatA->id])
        ->assertSessionHasErrors('anggota');
});

test('akun jemaat hanya bisa mencari anggota jemaatnya sendiri', function () {
    $rapat = Pemilihan::create(['lingkup' => 'jemaat', 'jemaat_id' => $this->jemaatA->id, 'nama' => 'Rapat', 'token' => str_repeat('b', 40)]);
    anggotaUntukPemilihan('Milik A', $this->jemaatA, 'Aktif');
    anggotaUntukPemilihan('Milik B', $this->jemaatB, 'Aktif');

    $this->actingAs(akunJemaatPemilihan($this->jemaatA))->getJson("/jemaat-saya/pemilihan/{$rapat->id}/anggota?jemaat_id={$this->jemaatB->id}")
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.nama', 'Milik A');
});

// ---------- Memilih ----------

test('bilik suara menolak link yang salah', function () {
    $this->get('/bilik/token-salah')->assertNotFound();
});

test('bilik menampilkan menunggu saat belum ada pemungutan yang dibuka', function () {
    $this->get("/bilik/{$this->konferensi->token}")->assertOk()->assertSee('Belum ada pemungutan suara');
});

test('utusan penuh bisa memilih dan kodenya hangus untuk pemungutan itu', function () {
    $putaran = putaranDibuka($this->konferensi, [$this->penuhA, $this->demisioner]);

    $this->post("/bilik/{$this->konferensi->token}/periksa", ['kode' => strtolower($this->penuhA->kode)])
        ->assertOk()
        ->assertSee('Dedi')
        ->assertHeader('Cache-Control', 'no-store, private');

    pilihDiBilik($this, $this->konferensi, $this->penuhA, $putaran, $this->demisioner)->assertSessionHas('terkirim');
    pilihDiBilik($this, $this->konferensi, $this->penuhA, $putaran, $this->demisioner)->assertSessionHas('ditolak', 'Kode ini sudah digunakan pada pemungutan suara ini.');
    $this->post("/bilik/{$this->konferensi->token}/periksa", ['kode' => $this->penuhA->kode])->assertSessionHas('ditolak');

    expect(DB::table('pemilihan_surat_suara')->count())->toBe(1);
});

test('kode bisa dipakai lagi pada pemungutan berikutnya', function () {
    $pertama = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB], 'ditutup');
    DB::table('pemilihan_sudah_memilih')->insert(['putaran_id' => $pertama->id, 'peserta_id' => $this->penuhA->id]);
    $kedua = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB], 'dibuka', 'Sekretaris');

    pilihDiBilik($this, $this->konferensi, $this->penuhA, $kedua, $this->penuhB)->assertSessionHas('terkirim');
});

test('utusan cadangan dan pengurus demisioner tidak punya hak suara', function ($peran) {
    $putaran = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB]);
    $orang = $this->{$peran};

    $this->post("/bilik/{$this->konferensi->token}/periksa", ['kode' => $orang->kode])->assertSessionHas('ditolak', 'Kode ini tidak memiliki hak suara.');
    pilihDiBilik($this, $this->konferensi, $orang, $putaran, $this->penuhA)->assertSessionHas('ditolak');

    expect(DB::table('pemilihan_surat_suara')->count())->toBe(0);
})->with(['cadangan', 'demisioner']);

test('cadangan yang naik menjadi utusan penuh langsung punya hak suara', function () {
    $putaran = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB]);

    $this->actingAs($this->admin)->put("/pemilihan/{$this->konferensi->id}/peserta/{$this->cadangan->id}", ['nama' => 'Cici', 'peran' => 'utusan_penuh'])->assertRedirect();

    pilihDiBilik($this, $this->konferensi, $this->cadangan, $putaran, $this->penuhA)->assertSessionHas('terkirim');
});

test('kode dari kegiatan lain ditolak', function () {
    $lain = Pemilihan::create(['lingkup' => 'klasis', 'nama' => 'Lain', 'token' => str_repeat('z', 40)]);
    $orang = pesertaPemilihan($lain, 'Gina');
    putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB]);

    $this->post("/bilik/{$this->konferensi->token}/periksa", ['kode' => $orang->kode])->assertSessionHas('ditolak', 'Kode tidak ditemukan. Periksa kembali kartu Anda.');
});

test('pilihan di luar daftar calon ditolak', function () {
    $putaran = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB]);
    $lain = putaranDibuka($this->konferensi, [$this->demisioner, $this->penuhA], 'draft', 'Sekretaris');

    $this->post("/bilik/{$this->konferensi->token}/pilih", [
        'kode' => $this->penuhA->kode,
        'calon_id' => $lain->calon()->value('id'),
    ])->assertSessionHas('ditolak');

    expect(DB::table('pemilihan_sudah_memilih')->count())->toBe(0);
});

test('suara tidak diterima setelah pemungutan ditutup', function () {
    $putaran = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB]);
    $this->actingAs($this->admin)->post("/pemilihan/{$this->konferensi->id}/putaran/{$putaran->id}/tutup");

    pilihDiBilik($this, $this->konferensi, $this->penuhA, $putaran, $this->penuhB)->assertSessionHas('ditolak');

    expect(DB::table('pemilihan_surat_suara')->count())->toBe(0);
});

// ---------- Kerahasiaan ----------

test('surat suara tidak bisa dihubungkan ke pemilihnya', function () {
    expect(Schema::getColumnListing('pemilihan_surat_suara'))->toEqualCanonicalizing(['id', 'putaran_id', 'calon_id'])
        ->and(Schema::getColumnListing('pemilihan_sudah_memilih'))->toEqualCanonicalizing(['putaran_id', 'peserta_id']);
});

test('pilihan dan kode tidak tercatat di audit log', function () {
    $putaran = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB]);
    AuditLog::query()->delete();

    pilihDiBilik($this, $this->konferensi, $this->penuhA, $putaran, $this->penuhB)->assertSessionHas('terkirim');

    expect(AuditLog::count())->toBe(0);

    $baru = pesertaPemilihan($this->konferensi, 'Hani');
    $log = AuditLog::where('model', 'PemilihanPeserta')->where('model_id', $baru->id)->first();

    expect(json_encode($log->perubahan))->not->toContain($baru->kode);
});

test('hasil tidak tampil selama pemungutan masih dibuka', function () {
    $putaran = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB]);
    pilihDiBilik($this, $this->konferensi, $this->penuhA, $putaran, $this->penuhB);

    expect($putaran->fresh()->hasil())->toBeEmpty();

    $this->actingAs($this->admin)->get("/pemilihan/{$this->konferensi->id}/jabatan/{$putaran->jabatan_id}")->assertOk()
        ->assertSee('Hasil dirahasiakan sampai')
        ->assertDontSee('Suara terbanyak mutlak');

    $this->actingAs($this->admin)->getJson("/pemilihan/{$this->konferensi->id}/putaran/{$putaran->id}/status")
        ->assertJsonPath('sudah', 1)
        ->assertJsonPath('belum', ['Budi']);
});

// ---------- Alur jabatan dan tahap ----------

test('jabatan dibuat dulu dan namanya tidak boleh dobel', function () {
    $this->actingAs($this->admin)->post("/pemilihan/{$this->konferensi->id}/jabatan", ['nama' => 'Ketua'])
        ->assertRedirect("/pemilihan/{$this->konferensi->id}/jabatan/".$this->konferensi->jabatan()->value('id'));

    $this->actingAs($this->admin)->post("/pemilihan/{$this->konferensi->id}/jabatan", ['nama' => 'Ketua'])->assertSessionHasErrors('nama');
    $this->actingAs($this->admin)->get("/pemilihan/{$this->konferensi->id}")->assertOk()->assertSee('Ketua')->assertSee('Belum dimulai');
});

test('tahap pertama hanya boleh dari utusan penuh dan pengurus demisioner', function () {
    $jabatan = $this->konferensi->jabatan()->create(['nama' => 'Ketua']);
    $url = "/pemilihan/{$this->konferensi->id}/jabatan/{$jabatan->id}";

    $this->actingAs($this->admin)->get($url)->assertOk()->assertSee('Mulai tahap pertama')->assertSee('Dedi')->assertDontSee('Cici');
    $this->actingAs($this->admin)->post("{$url}/tahap", ['tahap' => 'Penjaringan', 'calon' => [$this->penuhA->id, $this->cadangan->id]])->assertSessionHasErrors('calon.1');
    $this->actingAs($this->admin)->post("{$url}/tahap", ['tahap' => 'Penjaringan', 'calon' => [$this->penuhA->id, $this->demisioner->id]])->assertSessionHasNoErrors();

    expect($jabatan->tahap()->first()->calon()->count())->toBe(2);
});

test('tahap baru tidak bisa dibuat sebelum tahap terakhir ditutup', function () {
    $putaran = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB], 'draft');

    $this->actingAs($this->admin)->post("/pemilihan/{$this->konferensi->id}/jabatan/{$putaran->jabatan_id}/tahap", [
        'tahap' => 'Calon', 'calon' => [$this->penuhA->id, $this->penuhB->id],
    ])->assertSessionHas('error');

    expect(PemilihanPutaran::count())->toBe(1);
});

test('hanya satu pemungutan yang boleh dibuka', function () {
    putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB]);
    $kedua = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB], 'draft', 'Sekretaris');

    $this->actingAs($this->admin)->post("/pemilihan/{$this->konferensi->id}/putaran/{$kedua->id}/buka")->assertSessionHas('error', 'Pemungutan suara Ketua masih dibuka. Tutup dulu.');

    expect($kedua->fresh()->status)->toBe('draft');
});

test('alur lengkap dari penjaringan sampai terpilih lalu jabatan berikutnya', function () {
    $this->actingAs($this->admin);
    $this->post("/pemilihan/{$this->konferensi->id}/jabatan", ['nama' => 'Ketua']);
    $jabatan = $this->konferensi->jabatan()->first();
    $url = "/pemilihan/{$this->konferensi->id}/jabatan/{$jabatan->id}";
    $putaranUrl = "/pemilihan/{$this->konferensi->id}/putaran";

    // Tahap 1: penjaringan bakal calon.
    $this->post("{$url}/tahap", ['tahap' => 'Penjaringan Bakal Calon', 'calon' => [$this->penuhA->id, $this->penuhB->id, $this->demisioner->id]]);
    $bakal = $jabatan->tahap()->first();
    $this->post("{$putaranUrl}/{$bakal->id}/buka")->assertSessionHas('success');
    pilihDiBilik($this, $this->konferensi, $this->penuhA, $bakal, $this->demisioner);
    pilihDiBilik($this, $this->konferensi, $this->penuhB, $bakal, $this->penuhA);

    // Belum boleh ditetapkan selama masih dibuka.
    $this->post("{$url}/tetapkan", ['peserta_id' => $this->penuhA->id])->assertSessionHas('error');

    $this->post("{$putaranUrl}/{$bakal->id}/tutup");
    expect($bakal->fresh()->hasil()->pluck('suara')->all())->toBe([1, 1, 0]);

    // Halaman jabatan menawarkan dua pilihan, dan semua calon sebelumnya sudah tercentang.
    $html = $this->get($url)->assertOk()->assertSee('Suara tertinggi sama')->assertSee('Lanjut ke tahap berikutnya')->getContent();
    expect(substr_count($html, 'name="calon[]"'))->toBe(3)
        ->and(preg_match_all('/name="calon\[\]" value="\d+" checked/', $html))->toBe(3);

    // Tahap 2 hanya dari calon tahap 1, dan yang tidak sesuai sudah dihapus.
    $this->post("{$url}/tahap", ['tahap' => 'Pemilihan Calon', 'calon' => [$this->penuhA->id, $this->cadangan->id]])->assertSessionHasErrors('calon.1');
    $this->post("{$url}/tahap", ['tahap' => 'Pemilihan Calon', 'calon' => [$this->penuhA->id, $this->demisioner->id]])->assertSessionHasNoErrors();
    $calon = $jabatan->tahap()->reorder('id', 'desc')->first();
    expect($calon->calon()->pluck('peserta_id')->all())->toEqualCanonicalizing([$this->penuhA->id, $this->demisioner->id]);

    $this->post("{$putaranUrl}/{$calon->id}/buka");
    pilihDiBilik($this, $this->konferensi, $this->penuhA, $calon, $this->demisioner);
    pilihDiBilik($this, $this->konferensi, $this->penuhB, $calon, $this->demisioner);
    $this->post("{$putaranUrl}/{$calon->id}/tutup");

    // Yang ditetapkan harus calon tahap terakhir.
    $this->post("{$url}/tetapkan", ['peserta_id' => $this->penuhB->id])->assertSessionHasErrors('peserta_id');
    $this->post("{$url}/tetapkan", ['peserta_id' => $this->demisioner->id])->assertSessionHas('success');
    expect($jabatan->fresh()->terpilih_id)->toBe($this->demisioner->id);

    $this->get($url)->assertSee('Ketua terpilih')->assertDontSee('Lanjut ke tahap berikutnya');
    $this->post("{$url}/tahap", ['tahap' => 'Ulang', 'calon' => [$this->penuhA->id, $this->demisioner->id]])->assertSessionHas('error', 'Jabatan ini sudah ditetapkan.');

    // Jabatan berikutnya.
    $this->post("/pemilihan/{$this->konferensi->id}/jabatan", ['nama' => 'Sekretaris'])->assertSessionHasNoErrors();
    $this->get("/pemilihan/{$this->konferensi->id}")->assertSee('Hasil yang sudah ditetapkan')->assertSee('Selesai');
});

test('penetapan bisa dibatalkan', function () {
    $putaran = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB], 'ditutup');
    $putaran->jabatan->update(['terpilih_id' => $this->penuhA->id]);

    $this->actingAs($this->admin)->post("/pemilihan/{$this->konferensi->id}/jabatan/{$putaran->jabatan_id}/batal-tetapkan")->assertSessionHas('success');

    expect($putaran->jabatan->fresh()->terpilih_id)->toBeNull();
});

test('jabatan yang sudah punya pemungutan berjalan tidak bisa dihapus', function () {
    $putaran = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB], 'ditutup');

    $this->actingAs($this->admin)->delete("/pemilihan/{$this->konferensi->id}/jabatan/{$putaran->jabatan_id}")->assertSessionHas('error');

    expect($putaran->jabatan->fresh())->not->toBeNull();
});

test('batas suara terbanyak mutlak adalah setengah ditambah satu', function () {
    expect(PemilihanPutaran::batasMutlak(27))->toBe(14)
        ->and(PemilihanPutaran::batasMutlak(26))->toBe(14)
        ->and(PemilihanPutaran::batasMutlak(1))->toBe(1);
});

test('pemungutan yang sudah berjalan tidak bisa dihapus satu per satu', function () {
    $putaran = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB], 'ditutup');

    $this->actingAs($this->admin)->delete("/pemilihan/{$this->konferensi->id}/putaran/{$putaran->id}")->assertSessionHas('error');

    expect($putaran->fresh())->not->toBeNull();
});

test('kegiatan dengan pemungutan yang dibuka tidak bisa dihapus', function () {
    putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB]);

    $this->actingAs($this->admin)->delete("/pemilihan/{$this->konferensi->id}")->assertSessionHas('error');

    expect($this->konferensi->fresh())->not->toBeNull();
});

test('kegiatan selesai bisa dihapus beserta suaranya', function () {
    $putaran = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB]);
    pilihDiBilik($this, $this->konferensi, $this->penuhA, $putaran, $this->penuhB);
    $putaran->update(['status' => 'ditutup']);

    $this->actingAs($this->admin)->delete("/pemilihan/{$this->konferensi->id}")->assertRedirect('/pemilihan');

    expect(DB::table('pemilihan_surat_suara')->count())->toBe(0)
        ->and(DB::table('pemilihan_sudah_memilih')->count())->toBe(0)
        ->and(PemilihanPeserta::count())->toBe(0);
});

test('calon tahap yang belum dibuka hanya bisa diubah dari tahap tepat sebelumnya', function () {
    $pertama = putaranDibuka($this->konferensi, [$this->penuhA, $this->penuhB, $this->demisioner], 'ditutup');
    $kedua = $pertama->jabatan->tahap()->create(['pemilihan_id' => $this->konferensi->id, 'tahap' => 'Calon', 'status' => 'ditutup']);
    $kedua->calon()->createMany([['peserta_id' => $this->penuhA->id], ['peserta_id' => $this->penuhB->id]]);
    $ketiga = $pertama->jabatan->tahap()->create(['pemilihan_id' => $this->konferensi->id, 'tahap' => 'Akhir']);
    $ketiga->calon()->createMany([['peserta_id' => $this->penuhA->id], ['peserta_id' => $this->penuhB->id]]);
    $url = "/pemilihan/{$this->konferensi->id}/putaran/{$ketiga->id}/calon";

    // Dedi ikut tahap pertama, tetapi tidak lolos ke tahap kedua.
    $this->actingAs($this->admin)->put($url, ['calon' => [$this->penuhA->id, $this->demisioner->id]])->assertSessionHasErrors('calon.1');
    $this->actingAs($this->admin)->put($url, ['calon' => [$this->penuhB->id, $this->penuhA->id]])->assertSessionHasNoErrors();
});

test('tombol tambah utusan aman untuk nama jemaat yang memuat tanda petik', function () {
    $jemaat = Jemaat::create(['nama' => "Baji' Ma'rumpa"]);

    $html = $this->actingAs($this->admin)->get("/pemilihan/{$this->konferensi->id}?tab=peserta")->assertOk()->getContent();

    expect($html)->toContain('data-tambah-peserta="{&quot;jemaat_id&quot;:'.$jemaat->id.',&quot;jemaat&quot;:&quot;Baji&#039; Ma&#039;rumpa&quot;')
        ->and($html)->not->toContain("onclick='bukaTambahPeserta");
});

test('utusan penuh tiap jemaat maksimal tiga dan kelebihannya tidak tersimpan sama sekali', function () {
    $url = "/pemilihan/{$this->konferensi->id}/peserta";
    $a = anggotaUntukPemilihan('Satu', $this->jemaatA, 'Aktif');
    $b = anggotaUntukPemilihan('Dua', $this->jemaatA, 'Aktif');
    $c = anggotaUntukPemilihan('Tiga', $this->jemaatA, 'Aktif');

    // Andi sudah utusan penuh. Tiga nama baru membuat jumlahnya empat, jadi semuanya batal.
    $this->actingAs($this->admin)->post($url, ['peran' => 'utusan_penuh', 'jemaat_id' => $this->jemaatA->id, 'anggota' => [$a->id, $b->id, $c->id]])
        ->assertSessionHasErrors('anggota');
    expect($this->konferensi->peserta()->where('jemaat_id', $this->jemaatA->id)->where('peran', 'utusan_penuh')->count())->toBe(1);

    $this->actingAs($this->admin)->post($url, ['peran' => 'utusan_penuh', 'jemaat_id' => $this->jemaatA->id, 'anggota' => [$a->id, $b->id]])
        ->assertSessionHasNoErrors();
    $this->actingAs($this->admin)->post($url, ['peran' => 'utusan_penuh', 'jemaat_id' => $this->jemaatA->id, 'nama' => 'Ketik Manual'])
        ->assertSessionHasErrors('anggota');

    // Cadangan tidak dibatasi.
    $this->actingAs($this->admin)->post($url, ['peran' => 'utusan_cadangan', 'jemaat_id' => $this->jemaatA->id, 'anggota' => [$c->id]])
        ->assertSessionHasNoErrors();
    expect($this->konferensi->peserta()->where('jemaat_id', $this->jemaatA->id)->where('peran', 'utusan_penuh')->count())->toBe(3);
});

test('cadangan tidak bisa dinaikkan kalau utusan penuh jemaatnya sudah tiga', function () {
    pesertaPemilihan($this->konferensi, 'Eka', 'utusan_penuh', $this->jemaatA->id);
    pesertaPemilihan($this->konferensi, 'Fani', 'utusan_penuh', $this->jemaatA->id);

    $this->actingAs($this->admin)->put("/pemilihan/{$this->konferensi->id}/peserta/{$this->cadangan->id}", ['nama' => 'Cici', 'peran' => 'utusan_penuh'])
        ->assertSessionHasErrors('anggota');

    expect($this->cadangan->fresh()->peran)->toBe('utusan_cadangan');
});

test('pengurus demisioner hanya diambil dari data pengurus', function () {
    $this->actingAs($this->admin)->get("/pemilihan/{$this->konferensi->id}?tab=peserta")->assertOk()
        ->assertSee('Ambil pengurus')
        ->assertDontSee('&quot;peran&quot;:&quot;demisioner&quot;', false);
});
