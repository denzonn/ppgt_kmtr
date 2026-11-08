<?php

use App\Models\Artikel;
use App\Models\AuditLog;
use App\Models\DataAnggota;
use App\Models\Jemaat;
use App\Models\JemaatBidang;
use App\Models\JemaatPengurus;
use App\Models\Periode;
use App\Models\Role;
use App\Models\User;
use App\Support\FileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

const JSON_JEMAAT = ['Accept' => 'application/json'];

function roleJemaat(): Role
{
    return Role::firstWhere('nama', 'Pengurus Jemaat');
}

function akunJemaat(Jemaat $jemaat): User
{
    return User::factory()->create(['role_id' => roleJemaat()->id, 'jemaat_id' => $jemaat->id]);
}

function akunKlasis(int $mulai, int $selesai): User
{
    $periode = Periode::firstOrCreate(['tahun_mulai' => $mulai, 'tahun_selesai' => $selesai]);

    return User::factory()->create(['role_id' => Role::firstWhere('nama', 'Pengurus Klasis')->id, 'periode_id' => $periode->id]);
}

function klasisBerjalan(): User
{
    return akunKlasis(now()->year - 1, now()->year + 1);
}

function isianAnggota(array $ubah = []): array
{
    return $ubah + ['nama' => 'Budi', 'jenis_kelamin' => 'Laki-laki', 'status_anggota' => 'Aktif', 'status_wilayah' => 'dalam_wilayah'];
}

function anggotaDi(Jemaat $jemaat, string $nama): DataAnggota
{
    return DataAnggota::create(['jemaat_id' => $jemaat->id, 'nama' => $nama, 'no_anggota' => 'X-' . uniqid(), 'jenis_kelamin' => 'Perempuan']);
}

function artikelDi(Jemaat $jemaat, array $ubah = []): Artikel
{
    return Artikel::create($ubah + ['jemaat_id' => $jemaat->id, 'judul' => 'Ibadah Syukur', 'penulis' => 'Yohana', 'isi' => 'Isi artikel', 'status' => 'menunggu']);
}

function bidangDi(Jemaat $jemaat, string $nama = 'Umum', ?int $jumlah = 5): JemaatBidang
{
    return JemaatBidang::create(['jemaat_id' => $jemaat->id, 'nama' => $nama, 'jumlah_anggota' => $jumlah]);
}

function isianArtikel(array $ubah = []): array
{
    return $ubah + ['judul' => 'Judul', 'penulis' => 'Yohana', 'isi' => '<p>Isi</p>', 'foto' => UploadedFile::fake()->image('sampul.jpg')];
}

function versi(Artikel $artikel): string
{
    return $artikel->fresh()->updated_at->toISOString();
}

beforeEach(function () {
    Storage::fake('public');
    $this->jemaatA = Jemaat::create(['nama' => 'Jemaat Maranatha']);
    $this->jemaatB = Jemaat::create(['nama' => 'Jemaat Sion']);
    $this->akunA = akunJemaat($this->jemaatA);
    $this->admin = User::factory()->admin()->create();
});

// ---------- Role ----------

test('role Pengguna Periode berganti nama menjadi Pengurus Klasis dan bisa menyetujui artikel', function () {
    expect(Role::where('nama', 'Pengguna Periode')->exists())->toBeFalse();
    expect(Role::firstWhere('nama', 'Pengurus Klasis')->menus)->toContain('program_kerja', 'persetujuan_artikel');
});

test('role Pengurus Jemaat tersedia dengan menu jemaat', function () {
    expect(roleJemaat()->menus)->toEqualCanonicalizing(['data_anggota', 'jemaat_kelompok', 'jemaat_bidang', 'jemaat_pengurus', 'artikel', 'jemaat_pemilihan']);
});

test('menu jemaat bisa dipilih saat mengatur role', function () {
    $this->actingAs($this->admin)->postJson('/role', ['nama' => 'Coba', 'menus' => ['artikel', 'persetujuan_artikel']])->assertOk();

    expect(Role::firstWhere('nama', 'Coba')->menus)->toBe(['artikel', 'persetujuan_artikel']);
    $this->actingAs($this->admin)->get('/role')->assertSee('Jemaat')->assertSee('value="jemaat_bidang"', false);
});

// ---------- Pengguna ----------

test('akun Pengurus Jemaat wajib memilih jemaat dan tidak butuh periode', function () {
    $isian = ['name' => 'Ani', 'email' => 'ani@ppgt.id', 'password' => 'rahasia1', 'role_id' => roleJemaat()->id];

    $this->actingAs($this->admin)->postJson('/pengguna', $isian)
        ->assertUnprocessable()->assertJsonValidationErrors('jemaat_id');

    $this->actingAs($this->admin)->postJson('/pengguna', $isian + ['jemaat_id' => $this->jemaatB->id])->assertOk();

    expect(User::firstWhere('email', 'ani@ppgt.id')->jemaat_id)->toBe($this->jemaatB->id);
});

test('jemaat yang tidak ada ditolak', function () {
    $this->actingAs($this->admin)->postJson('/pengguna', [
        'name' => 'Ani', 'email' => 'ani@ppgt.id', 'password' => 'rahasia1', 'role_id' => roleJemaat()->id, 'jemaat_id' => 99999,
    ])->assertUnprocessable()->assertJsonValidationErrors('jemaat_id');
});

test('pindah ke role lain mengosongkan jemaat akun', function () {
    $periode = Periode::create(['tahun_mulai' => 2030, 'tahun_selesai' => 2032]);

    $this->actingAs($this->admin)->putJson("/pengguna/{$this->akunA->id}", [
        'name' => 'Ani', 'email' => 'ani@ppgt.id', 'role_id' => Role::firstWhere('nama', 'Pengurus Klasis')->id, 'periode_id' => $periode->id, 'jemaat_id' => $this->jemaatA->id,
    ])->assertOk();

    expect($this->akunA->fresh()->jemaat_id)->toBeNull();
});

test('form pengguna menyediakan pilihan jemaat', function () {
    $this->actingAs($this->admin)->get('/pengguna')->assertOk()->assertSee('id="pengguna_jemaat"', false)->assertSee('Jemaat Sion');
});

// ---------- Akses & sidebar ----------

test('Pengurus Jemaat masuk ke menu miliknya setelah login', function () {
    expect($this->akunA->homeUrl())->toBe(route('data_anggota.index'));
});

test('sidebar Pengurus Jemaat menampilkan menu jemaat tanpa menu klasis', function () {
    $this->actingAs($this->akunA)->get('/jemaat-saya/artikel')->assertOk()
        ->assertDontSee('Jemaat Saya')
        ->assertSee(route('jemaat.bidang.index'), false)
        ->assertSee(route('jemaat.pengurus.index'), false)
        ->assertSee(route('jemaat.artikel.index'), false)
        ->assertSee(route('data_anggota.index'), false)
        ->assertDontSee(route('persetujuan_artikel.index'), false);
});

test('bidang dan pengurus satu grup Kepengurusan, artikel berdiri sendiri', function () {
    $this->actingAs($this->akunA)->get('/jemaat-saya/bidang')->assertOk()
        ->assertSee('data-grup="jemaat" aria-expanded="true"', false)
        ->assertSee('Kepengurusan');
    $this->actingAs($this->akunA)->get('/jemaat-saya/artikel')
        ->assertSee('data-grup="jemaat" aria-expanded="false"', false);
});

test('admin tidak punya jemaat sehingga menu jemaat tertutup', function () {
    $this->actingAs($this->admin)->get('/jemaat-saya/bidang')->assertForbidden();
    $this->actingAs($this->admin)->get('/dashboard')->assertDontSee('data-grup="jemaat"', false);
});

test('halaman jemaat tertutup untuk tamu dan role lain', function () {
    $this->get('/jemaat-saya/artikel')->assertRedirect('/login');
    $this->actingAs(klasisBerjalan())->get('/jemaat-saya/artikel')->assertForbidden();
});

// ---------- Anggota ----------

test('Pengurus Jemaat hanya melihat anggota dan statistik jemaatnya', function () {
    anggotaDi($this->jemaatA, 'Punya A');
    anggotaDi($this->jemaatB, 'Punya B');

    $res = $this->actingAs($this->akunA)->getJson('/data-anggota/data')->assertOk();

    expect(collect($res->json('data.data'))->pluck('nama')->all())->toBe(['Punya A']);
    expect($res->json('statistics.total_anggota'))->toBe(1);
    expect(collect($res->json('filters.jemaat'))->pluck('nama')->all())->toBe(['Jemaat Maranatha']);
});

test('kartu statistik Pengurus Jemaat menampilkan anggota aktif, bukan total jemaat', function () {
    anggotaDi($this->jemaatA, 'Aktif')->update(['status_anggota' => 'Aktif']);
    anggotaDi($this->jemaatA, 'Tidak')->update(['status_anggota' => 'Tidak Aktif']);
    anggotaDi($this->jemaatB, 'Lain')->update(['status_anggota' => 'Aktif']);

    $this->actingAs($this->akunA)->getJson('/data-anggota/data')->assertJsonPath('statistics.total_aktif', 1);
    $this->actingAs($this->akunA)->get('/data-anggota')->assertSee('Anggota Aktif')->assertDontSee('Total Jemaat');
    $this->actingAs($this->admin)->get('/data-anggota')->assertSee('Total Jemaat')->assertDontSee('Anggota Aktif');
});

test('anggota yang menjadi pengurus jemaat tidak bisa dihapus', function () {
    $a = anggotaDi($this->jemaatA, 'Ani');
    bidangDi($this->jemaatA)->pengurus()->create(['data_anggota_id' => $a->id, 'jabatan' => 'Ketua']);

    $this->actingAs($this->akunA)->deleteJson("/data-anggota/{$a->id}")->assertUnprocessable();

    expect($a->fresh())->not->toBeNull();
});

test('filter jemaat lain tidak membuka data jemaat itu', function () {
    anggotaDi($this->jemaatB, 'Punya B');

    $res = $this->actingAs($this->akunA)->getJson("/data-anggota/data?jemaat={$this->jemaatB->id}");

    expect($res->json('data.data'))->toBe([]);
});

test('anggota jemaat lain tidak bisa dibuka, diubah, atau dihapus', function () {
    $lain = anggotaDi($this->jemaatB, 'Punya B');

    $this->actingAs($this->akunA)->getJson("/data-anggota/{$lain->id}")->assertNotFound();
    $this->actingAs($this->akunA)->getJson("/data-anggota/{$lain->id}/edit")->assertNotFound();
    $this->actingAs($this->akunA)->putJson("/data-anggota/{$lain->id}", isianAnggota())->assertNotFound();
    $this->actingAs($this->akunA)->deleteJson("/data-anggota/{$lain->id}")->assertNotFound();

    expect($lain->fresh()->nama)->toBe('Punya B');
});

test('anggota baru selalu masuk ke jemaat akun walau mengirim jemaat lain', function () {
    $this->actingAs($this->akunA)->postJson('/data-anggota', isianAnggota(['jemaat_id' => $this->jemaatB->id]))->assertOk();
    $this->actingAs($this->akunA)->postJson('/data-anggota', isianAnggota(['nama' => 'Tanpa Kolom']))->assertOk();

    expect(DataAnggota::where('jemaat_id', $this->jemaatA->id)->count())->toBe(2);
    expect(DataAnggota::where('jemaat_id', $this->jemaatB->id)->count())->toBe(0);
});

test('anggota tidak bisa dipindahkan ke jemaat lain', function () {
    $milik = anggotaDi($this->jemaatA, 'Punya A');

    $this->actingAs($this->akunA)->putJson("/data-anggota/{$milik->id}", isianAnggota(['jemaat_id' => $this->jemaatB->id]))->assertOk();

    expect($milik->fresh()->jemaat_id)->toBe($this->jemaatA->id);
});

test('import Excel ditutup untuk Pengurus Jemaat', function () {
    $this->actingAs($this->akunA)->post('/data-anggota/import', ['file' => UploadedFile::fake()->create('a.xlsx', 1)], JSON_JEMAAT)->assertForbidden();
    $this->actingAs($this->akunA)->get('/data-anggota')->assertOk()->assertDontSee('id="btnImportAnggota"', false);
});

test('admin tetap melihat semua anggota dan bisa memilih jemaat', function () {
    anggotaDi($this->jemaatA, 'Punya A');
    anggotaDi($this->jemaatB, 'Punya B');

    $this->actingAs($this->admin)->getJson('/data-anggota/data')->assertJsonPath('statistics.total_anggota', 2);
    $this->actingAs($this->admin)->postJson('/data-anggota', isianAnggota(['jemaat_id' => $this->jemaatB->id]))->assertOk();
    $this->actingAs($this->admin)->postJson('/data-anggota', isianAnggota())->assertUnprocessable()->assertJsonValidationErrors('jemaat_id');
});

// ---------- Bidang jemaat ----------

test('bidang tersimpan pada jemaat akun dan awalnya kosong', function () {
    $this->actingAs($this->akunA)->getJson('/jemaat-saya/bidang/data')->assertOk()->assertJsonCount(0, 'data');

    $this->actingAs($this->akunA)->postJson('/jemaat-saya/bidang', ['nama' => 'Bidang Umum', 'jumlah_anggota' => 4, 'jemaat_id' => $this->jemaatB->id])->assertOk();

    $bidang = JemaatBidang::first();
    expect($bidang->jemaat_id)->toBe($this->jemaatA->id);
    expect($bidang->jumlah_anggota)->toBe(4);
});

test('jumlah orang bidang wajib diisi dan berupa angka positif', function (mixed $jumlah) {
    $this->actingAs($this->akunA)->postJson('/jemaat-saya/bidang', ['nama' => 'Umum', 'jumlah_anggota' => $jumlah])
        ->assertUnprocessable()->assertJsonValidationErrors('jumlah_anggota');
})->with(['', 0, 'abc', 1000]);

test('jumlah orang tidak boleh lebih kecil dari pengurus yang sudah ada', function () {
    $bidang = bidangDi($this->jemaatA, 'Umum', 5);
    $bidang->pengurus()->create(['data_anggota_id' => anggotaDi($this->jemaatA, 'A')->id, 'jabatan' => 'X']);
    $bidang->pengurus()->create(['data_anggota_id' => anggotaDi($this->jemaatA, 'B')->id, 'jabatan' => 'Y']);

    $this->actingAs($this->akunA)->putJson("/jemaat-saya/bidang/{$bidang->id}", ['nama' => 'Umum', 'jumlah_anggota' => 1])
        ->assertUnprocessable()->assertJsonValidationErrors('jumlah_anggota');
    $this->actingAs($this->akunA)->putJson("/jemaat-saya/bidang/{$bidang->id}", ['nama' => 'Umum', 'jumlah_anggota' => 2])->assertOk();
});

test('nama bidang unik per jemaat, jemaat lain boleh sama', function () {
    bidangDi($this->jemaatB, 'Bidang Umum');

    $this->actingAs($this->akunA)->postJson('/jemaat-saya/bidang', ['nama' => 'Bidang Umum', 'jumlah_anggota' => 3])->assertOk();
    $this->actingAs($this->akunA)->postJson('/jemaat-saya/bidang', ['nama' => 'Bidang Umum', 'jumlah_anggota' => 3])
        ->assertUnprocessable()->assertJsonValidationErrors('nama');
});

test('bidang jemaat lain tidak terlihat dan tidak bisa diubah atau dihapus', function () {
    $lain = bidangDi($this->jemaatB, 'Punya B');

    $this->actingAs($this->akunA)->getJson('/jemaat-saya/bidang/data')->assertJsonCount(0, 'data');
    $this->actingAs($this->akunA)->putJson("/jemaat-saya/bidang/{$lain->id}", ['nama' => 'Curang', 'jumlah_anggota' => 2])->assertNotFound();
    $this->actingAs($this->akunA)->deleteJson("/jemaat-saya/bidang/{$lain->id}")->assertNotFound();

    expect($lain->fresh()->nama)->toBe('Punya B');
});

test('bidang bisa diubah, dan yang masih punya pengurus tidak bisa dihapus', function () {
    $bidang = bidangDi($this->jemaatA, 'Lama');
    $this->actingAs($this->akunA)->putJson("/jemaat-saya/bidang/{$bidang->id}", ['nama' => 'Baru', 'jumlah_anggota' => 7])->assertOk();
    expect($bidang->fresh()->nama)->toBe('Baru');
    expect($bidang->fresh()->jumlah_anggota)->toBe(7);

    $pengurus = $bidang->pengurus()->create(['data_anggota_id' => anggotaDi($this->jemaatA, 'Ani')->id, 'jabatan' => 'Ketua']);
    $this->actingAs($this->akunA)->deleteJson("/jemaat-saya/bidang/{$bidang->id}")->assertUnprocessable();

    $pengurus->delete();
    $this->actingAs($this->akunA)->deleteJson("/jemaat-saya/bidang/{$bidang->id}")->assertOk();
    expect(JemaatBidang::count())->toBe(0);
});

// ---------- Pengurus jemaat ----------

test('pengurus diambil dari data anggota dengan jabatan manual dan foto', function () {
    $bidang = bidangDi($this->jemaatA);
    $anggota = anggotaDi($this->jemaatA, 'Ani Toding');

    $this->actingAs($this->akunA)->post('/jemaat-saya/pengurus', [
        'jemaat_bidang_id' => $bidang->id, 'data_anggota_id' => $anggota->id, 'jabatan' => 'Koordinator Musik',
        'foto' => UploadedFile::fake()->image('ani.jpg'),
    ], JSON_JEMAAT)->assertOk();

    $pengurus = JemaatPengurus::first();
    expect($pengurus->jabatan)->toBe('Koordinator Musik');
    expect($pengurus->foto)->toStartWith(asset('storage/jemaat-pengurus/'));
    Storage::disk('public')->assertExists(FileUpload::path($pengurus->foto));

    $this->actingAs($this->akunA)->getJson('/jemaat-saya/pengurus/data')
        ->assertJsonPath('data.0.nama', 'Ani Toding')
        ->assertJsonPath('data.0.data_anggota_id', $anggota->id)
        ->assertJsonPath('data.0.bidang', 'Umum')
        ->assertJsonPath('data.0.foto', $pengurus->foto)
        ->assertJsonPath('bidang.0.jumlah_anggota', 5)
        ->assertJsonPath('bidang.0.terisi', 1);
});

test('nama pengurus mengikuti perubahan di data anggota', function () {
    $anggota = anggotaDi($this->jemaatA, 'Nama Lama');
    bidangDi($this->jemaatA)->pengurus()->create(['data_anggota_id' => $anggota->id, 'jabatan' => 'Ketua']);

    $anggota->update(['nama' => 'Nama Baru']);

    $this->actingAs($this->akunA)->getJson('/jemaat-saya/pengurus/data')->assertJsonPath('data.0.nama', 'Nama Baru');
});

test('pencarian anggota hanya menampilkan anggota jemaat sendiri', function () {
    anggotaDi($this->jemaatA, 'Budi Maranatha');
    anggotaDi($this->jemaatB, 'Budi Sion');

    $this->actingAs($this->akunA)->getJson('/jemaat-saya/pengurus/anggota?search=Budi')
        ->assertOk()->assertJsonCount(1)->assertJsonPath('0.nama', 'Budi Maranatha');
});

test('bidang, anggota, dan jabatan pengurus wajib diisi', function (string $kolom) {
    $isian = ['jemaat_bidang_id' => bidangDi($this->jemaatA)->id, 'data_anggota_id' => anggotaDi($this->jemaatA, 'Ani')->id, 'jabatan' => 'Ketua'];
    $isian[$kolom] = '';

    $this->actingAs($this->akunA)->postJson('/jemaat-saya/pengurus', $isian)
        ->assertUnprocessable()->assertJsonValidationErrors($kolom);
})->with(['jemaat_bidang_id', 'data_anggota_id', 'jabatan']);

test('nama yang diketik tanpa data anggota ditolak', function () {
    $this->actingAs($this->akunA)->postJson('/jemaat-saya/pengurus', ['jemaat_bidang_id' => bidangDi($this->jemaatA)->id, 'nama' => 'Ketik Manual', 'jabatan' => 'Ketua'])
        ->assertUnprocessable()->assertJsonValidationErrors('data_anggota_id');
});

test('anggota jemaat lain tidak bisa dijadikan pengurus', function () {
    $this->actingAs($this->akunA)->postJson('/jemaat-saya/pengurus', [
        'jemaat_bidang_id' => bidangDi($this->jemaatA)->id, 'data_anggota_id' => anggotaDi($this->jemaatB, 'Orang B')->id, 'jabatan' => 'Ketua',
    ])->assertUnprocessable()->assertJsonValidationErrors('data_anggota_id');
});

test('satu anggota hanya bisa menjadi satu pengurus', function () {
    $bidang = bidangDi($this->jemaatA);
    $anggota = anggotaDi($this->jemaatA, 'Ani');
    $bidang->pengurus()->create(['data_anggota_id' => $anggota->id, 'jabatan' => 'Ketua']);

    $this->actingAs($this->akunA)->postJson('/jemaat-saya/pengurus', ['jemaat_bidang_id' => $bidang->id, 'data_anggota_id' => $anggota->id, 'jabatan' => 'Wakil'])
        ->assertUnprocessable()->assertJsonValidationErrors('data_anggota_id');
});

test('bidang yang sudah penuh menolak pengurus baru', function () {
    $bidang = bidangDi($this->jemaatA, 'Umum', 1);
    $ada = $bidang->pengurus()->create(['data_anggota_id' => anggotaDi($this->jemaatA, 'Ani')->id, 'jabatan' => 'Ketua']);
    $isian = ['jemaat_bidang_id' => $bidang->id, 'data_anggota_id' => anggotaDi($this->jemaatA, 'Budi')->id, 'jabatan' => 'Wakil'];

    $this->actingAs($this->akunA)->postJson('/jemaat-saya/pengurus', $isian)
        ->assertUnprocessable()->assertJsonValidationErrors('jemaat_bidang_id');

    // Mengubah pengurus di bidang yang sama tidak terhitung menambah orang.
    $this->actingAs($this->akunA)->putJson("/jemaat-saya/pengurus/{$ada->id}", ['jemaat_bidang_id' => $bidang->id, 'data_anggota_id' => $ada->data_anggota_id, 'jabatan' => 'Ketua Baru'])->assertOk();
});

test('pengurus tidak bisa dipindah ke bidang yang penuh', function () {
    $penuh = bidangDi($this->jemaatA, 'Penuh', 1);
    $penuh->pengurus()->create(['data_anggota_id' => anggotaDi($this->jemaatA, 'Ani')->id, 'jabatan' => 'Ketua']);
    $pindah = bidangDi($this->jemaatA, 'Asal', 3)->pengurus()->create(['data_anggota_id' => anggotaDi($this->jemaatA, 'Budi')->id, 'jabatan' => 'Ketua']);

    $this->actingAs($this->akunA)->putJson("/jemaat-saya/pengurus/{$pindah->id}", ['jemaat_bidang_id' => $penuh->id, 'data_anggota_id' => $pindah->data_anggota_id, 'jabatan' => 'Ketua'])
        ->assertUnprocessable()->assertJsonValidationErrors('jemaat_bidang_id');
});

test('foto pengurus jemaat tidak wajib tetapi harus gambar', function () {
    $isian = ['jemaat_bidang_id' => bidangDi($this->jemaatA)->id, 'data_anggota_id' => anggotaDi($this->jemaatA, 'Ani')->id, 'jabatan' => 'Ketua'];

    $this->actingAs($this->akunA)->post('/jemaat-saya/pengurus', $isian + ['foto' => UploadedFile::fake()->create('a.pdf', 5, 'application/pdf')], JSON_JEMAAT)
        ->assertUnprocessable()->assertJsonValidationErrors('foto');

    $this->actingAs($this->akunA)->postJson('/jemaat-saya/pengurus', $isian)->assertOk();
    expect(JemaatPengurus::first()->foto)->toBeNull();
});

test('pengurus tidak bisa dimasukkan ke bidang jemaat lain', function () {
    $this->actingAs($this->akunA)->postJson('/jemaat-saya/pengurus', ['jemaat_bidang_id' => bidangDi($this->jemaatB)->id, 'data_anggota_id' => anggotaDi($this->jemaatA, 'Ani')->id, 'jabatan' => 'Ketua'])
        ->assertUnprocessable()->assertJsonValidationErrors('jemaat_bidang_id');
});

test('pengurus jemaat lain tidak terlihat dan tidak bisa diubah atau dihapus', function () {
    $lain = bidangDi($this->jemaatB)->pengurus()->create(['data_anggota_id' => anggotaDi($this->jemaatB, 'Orang B')->id, 'jabatan' => 'Ketua']);
    $bidang = bidangDi($this->jemaatA);

    $this->actingAs($this->akunA)->getJson('/jemaat-saya/pengurus/data')->assertJsonCount(0, 'data');
    $this->actingAs($this->akunA)->putJson("/jemaat-saya/pengurus/{$lain->id}", ['jemaat_bidang_id' => $bidang->id, 'data_anggota_id' => anggotaDi($this->jemaatA, 'Ani')->id, 'jabatan' => 'X'])->assertNotFound();
    $this->actingAs($this->akunA)->deleteJson("/jemaat-saya/pengurus/{$lain->id}")->assertNotFound();

    expect($lain->fresh()->jabatan)->toBe('Ketua');
});

test('foto pengurus jemaat bisa diganti, dihapus, dan terhapus bersama pengurusnya', function () {
    $bidang = bidangDi($this->jemaatA);
    $isi = ['jemaat_bidang_id' => $bidang->id, 'data_anggota_id' => anggotaDi($this->jemaatA, 'Ani')->id, 'jabatan' => 'Ketua'];
    $this->actingAs($this->akunA)->post('/jemaat-saya/pengurus', $isi + ['foto' => UploadedFile::fake()->image('a.jpg')], JSON_JEMAAT)->assertOk();
    $p = JemaatPengurus::first();
    $a = $p->foto;

    $this->actingAs($this->akunA)->post("/jemaat-saya/pengurus/{$p->id}", $isi + ['_method' => 'PUT', 'foto' => UploadedFile::fake()->image('b.jpg')], JSON_JEMAAT)->assertOk();
    $b = $p->fresh()->foto;
    Storage::disk('public')->assertMissing(FileUpload::path($a));
    Storage::disk('public')->assertExists(FileUpload::path($b));

    $this->actingAs($this->akunA)->putJson("/jemaat-saya/pengurus/{$p->id}", $isi)->assertOk();
    expect($p->fresh()->foto)->toBe($b);

    $this->actingAs($this->akunA)->putJson("/jemaat-saya/pengurus/{$p->id}", $isi + ['hapus_foto' => 1])->assertOk();
    expect($p->fresh()->foto)->toBeNull();
    Storage::disk('public')->assertMissing(FileUpload::path($b));

    $this->actingAs($this->akunA)->post("/jemaat-saya/pengurus/{$p->id}", $isi + ['_method' => 'PUT', 'foto' => UploadedFile::fake()->image('c.jpg')], JSON_JEMAAT)->assertOk();
    $c = $p->fresh()->foto;
    $this->actingAs($this->akunA)->deleteJson("/jemaat-saya/pengurus/{$p->id}")->assertOk();
    Storage::disk('public')->assertMissing(FileUpload::path($c));
});

// ---------- Artikel ----------

test('artikel baru berstatus menunggu dengan penulis diketik manual pada jemaat akun', function () {
    $this->actingAs($this->akunA)->post('/jemaat-saya/artikel', [
        'judul' => 'Ibadah Syukur', 'penulis' => 'Yohana Toding', 'isi' => '<p>Isi artikel</p>', 'jemaat_id' => $this->jemaatB->id, 'status' => 'disetujui',
        'foto' => UploadedFile::fake()->image('sampul.jpg'),
    ], JSON_JEMAAT)->assertOk();

    $artikel = Artikel::first();
    expect($artikel->status)->toBe('menunggu');
    expect($artikel->penulis)->toBe('Yohana Toding');
    expect($artikel->jemaat_id)->toBe($this->jemaatA->id);
    expect($artikel->foto)->toStartWith(asset('storage/artikel/'));
});

test('judul, penulis, isi, dan foto sampul wajib saat membuat artikel', function (string $kolom) {
    $isian = isianArtikel();
    unset($isian[$kolom]);

    $this->actingAs($this->akunA)->post('/jemaat-saya/artikel', $isian, JSON_JEMAAT)
        ->assertUnprocessable()->assertJsonValidationErrors($kolom);
})->with(['judul', 'penulis', 'isi', 'foto']);

test('saat edit sampul lama boleh tetap dipakai, tetapi tidak bisa dihapus', function () {
    $artikel = artikelDi($this->jemaatA, ['foto' => 'http://localhost/storage/artikel/lama.jpg']);

    $this->actingAs($this->akunA)->putJson("/jemaat-saya/artikel/{$artikel->id}", ['judul' => 'A', 'penulis' => 'B', 'isi' => '<p>C</p>', 'hapus_foto' => 1])->assertOk();

    expect($artikel->fresh()->foto)->toBe('http://localhost/storage/artikel/lama.jpg');
});

test('artikel lama tanpa sampul harus diberi sampul saat diedit', function () {
    $artikel = artikelDi($this->jemaatA);

    $this->actingAs($this->akunA)->putJson("/jemaat-saya/artikel/{$artikel->id}", ['judul' => 'A', 'penulis' => 'B', 'isi' => '<p>C</p>'])
        ->assertUnprocessable()->assertJsonValidationErrors('foto');
});

test('format dari CKEditor tersimpan utuh', function (string $html) {
    $this->actingAs($this->akunA)->post('/jemaat-saya/artikel', isianArtikel(['isi' => $html]), JSON_JEMAAT)->assertOk();

    expect(str_replace(' />', '>', html_entity_decode(Artikel::first()->isi)))->toBe(html_entity_decode($html));
})->with([
    'paragraf' => '<p>Dalam Hal Ini</p>',
    'judul' => '<h2>Satu</h2><h3>Dua</h3><h4>Tiga</h4>',
    'gaya teks' => '<p><strong>a</strong> <i>b</i> <u>c</u> <s>d</s></p>',
    'daftar' => '<ul><li>a<ol><li>b</li></ol></li></ul>',
    'kutipan' => '<blockquote><p>a</p></blockquote>',
    'perataan' => '<p class="ta-center">a</p>',
    'gambar' => '<figure class="image image-style-side"><img src="http://localhost/storage/artikel/isi/a.jpg" alt="foto"><figcaption>Keterangan</figcaption></figure>',
    'tabel' => '<figure class="table"><table><thead><tr><th>a</th></tr></thead><tbody><tr><td colspan="2">b</td></tr></tbody></table></figure>',
    'garis' => '<hr>',
]);

test('tautan tersimpan dan selalu diberi rel aman', function () {
    $this->actingAs($this->akunA)->post('/jemaat-saya/artikel', isianArtikel(['isi' => '<p><a href="https://ppgt.id/warta" target="_blank">warta</a></p>']), JSON_JEMAAT)->assertOk();

    expect(html_entity_decode(Artikel::first()->isi))
        ->toContain('href="https://ppgt.id/warta"')->toContain('target="_blank"')->toContain('rel="noopener noreferrer"');
});

test('kode berbahaya dibuang dari isi artikel', function (string $html, string $dilarang) {
    $this->actingAs($this->akunA)->post('/jemaat-saya/artikel', isianArtikel(['isi' => "<p>aman</p>{$html}"]), JSON_JEMAAT)->assertOk();

    expect(strtolower(html_entity_decode(Artikel::first()->isi)))->not->toContain($dilarang)->toContain('aman');
})->with([
    'script' => ['<script>alert(1)</script>', 'alert(1)'],
    'onerror' => ['<img src="http://a.id/x.png" onerror="alert(1)">', 'onerror'],
    'javascript link' => ['<a href="javascript:alert(1)">x</a>', 'javascript:'],
    'iframe' => ['<iframe src="https://evil.id"></iframe>', 'iframe'],
    'style' => ['<p style="position:fixed;inset:0">x</p>', 'position:fixed'],
    'style tag' => ['<style>body{display:none}</style>', 'display:none'],
    'class asing' => ['<p class="fixed inset-0">x</p>', 'inset-0'],
    'gambar base64' => ['<img src="data:image/png;base64,AAAA">', 'data:image'],
    'svg' => ['<svg onload="alert(1)"></svg>', 'svg'],
]);

test('penyaringan juga berlaku saat isi diubah dan lewat model langsung', function () {
    $artikel = artikelDi($this->jemaatA, ['foto' => 'http://localhost/storage/artikel/a.jpg', 'isi' => '<p>a</p><script>alert(1)</script>']);
    expect($artikel->isi)->not->toContain('script');

    $this->actingAs($this->akunA)->putJson("/jemaat-saya/artikel/{$artikel->id}", ['judul' => 'A', 'penulis' => 'B', 'isi' => '<p>b</p><script>alert(2)</script>'])->assertOk();
    expect($artikel->fresh()->isi)->not->toContain('script');
});

test('isi panjang tidak terpotong', function () {
    $html = '<p>' . str_repeat('kata ', 30000) . 'AKHIR</p>';

    $this->actingAs($this->akunA)->post('/jemaat-saya/artikel', isianArtikel(['isi' => $html]), JSON_JEMAAT)->assertOk();

    expect(Artikel::first()->isi)->toContain('AKHIR');
});

test('isi yang kosong setelah disaring ditolak', function (string $html) {
    $this->actingAs($this->akunA)->post('/jemaat-saya/artikel', isianArtikel(['isi' => $html]), JSON_JEMAAT)
        ->assertUnprocessable()->assertJsonValidationErrors('isi');
})->with(['<p>&nbsp;</p>', '<p></p><p> </p>', '<script>alert(1)</script>']);

test('isi berupa gambar saja tetap diterima', function () {
    $this->actingAs($this->akunA)->post('/jemaat-saya/artikel', isianArtikel(['isi' => '<figure class="image"><img src="http://localhost/storage/artikel/isi/a.jpg" alt=""></figure>']), JSON_JEMAAT)->assertOk();
});

// ---------- Gambar di dalam artikel ----------

test('gambar di editor terunggah dan mengembalikan URL lengkap', function () {
    $res = $this->actingAs($this->akunA)->post('/jemaat-saya/artikel/gambar', ['upload' => UploadedFile::fake()->image('a.jpg')], JSON_JEMAAT)->assertOk();

    expect($res->json('url'))->toStartWith(asset('storage/artikel/isi/'));
    Storage::disk('public')->assertExists(FileUpload::path($res->json('url')));
});

test('unggahan gambar yang ditolak mengembalikan pesan untuk editor', function () {
    $this->actingAs($this->akunA)->post('/jemaat-saya/artikel/gambar', ['upload' => UploadedFile::fake()->create('a.pdf', 5, 'application/pdf')], JSON_JEMAAT)
        ->assertUnprocessable()->assertJsonPath('error.message', 'File harus berupa gambar JPG, PNG, atau GIF.');
});

test('unggah gambar tertutup untuk tamu dan role lain', function () {
    $this->post('/jemaat-saya/artikel/gambar', ['upload' => UploadedFile::fake()->image('a.jpg')])->assertRedirect('/login');
    $this->actingAs(klasisBerjalan())->post('/jemaat-saya/artikel/gambar', ['upload' => UploadedFile::fake()->image('a.jpg')], JSON_JEMAAT)->assertForbidden();
});

test('gambar yang dibuang dari isi ikut terhapus dari storage', function () {
    $url = $this->actingAs($this->akunA)->post('/jemaat-saya/artikel/gambar', ['upload' => UploadedFile::fake()->image('a.jpg')], JSON_JEMAAT)->json('url');
    $tetap = $this->actingAs($this->akunA)->post('/jemaat-saya/artikel/gambar', ['upload' => UploadedFile::fake()->image('b.jpg')], JSON_JEMAAT)->json('url');
    $artikel = artikelDi($this->jemaatA, ['foto' => 'http://localhost/storage/artikel/s.jpg', 'isi' => "<figure class=\"image\"><img src=\"{$url}\"></figure><figure class=\"image\"><img src=\"{$tetap}\"></figure>"]);

    $this->actingAs($this->akunA)->putJson("/jemaat-saya/artikel/{$artikel->id}", ['judul' => 'A', 'penulis' => 'B', 'isi' => "<figure class=\"image\"><img src=\"{$tetap}\"></figure>"])->assertOk();

    Storage::disk('public')->assertMissing(FileUpload::path($url));
    Storage::disk('public')->assertExists(FileUpload::path($tetap));

    $this->actingAs($this->akunA)->deleteJson("/jemaat-saya/artikel/{$artikel->id}")->assertOk();
    Storage::disk('public')->assertMissing(FileUpload::path($tetap));
});

test('gambar yang juga dipakai artikel lain tidak terhapus', function () {
    $url = $this->actingAs($this->akunA)->post('/jemaat-saya/artikel/gambar', ['upload' => UploadedFile::fake()->image('a.jpg')], JSON_JEMAAT)->json('url');
    $isi = "<figure class=\"image\"><img src=\"{$url}\"></figure>";
    artikelDi($this->jemaatB, ['isi' => $isi]);
    $artikel = artikelDi($this->jemaatA, ['isi' => $isi]);

    $this->actingAs($this->akunA)->deleteJson("/jemaat-saya/artikel/{$artikel->id}")->assertOk();

    Storage::disk('public')->assertExists(FileUpload::path($url));
});

test('menulis artikel memakai halaman sendiri, bukan modal', function () {
    $this->actingAs($this->akunA)->get('/jemaat-saya/artikel')->assertOk()
        ->assertSee(route('jemaat.artikel.create'), false)
        ->assertDontSee('id="modalArtikel"', false)
        ->assertDontSee('artikel-editor', false);

    $this->actingAs($this->akunA)->get('/jemaat-saya/artikel/tulis')->assertOk()
        ->assertSee('artikel-editor', false)
        ->assertSee('id="formArtikel"', false)
        ->assertSee('id="artikel_isi"', false)
        ->assertSee('Kirim untuk Disetujui');
});

test('halaman edit memuat isi artikel miliknya', function () {
    $artikel = artikelDi($this->jemaatA, ['judul' => 'Judul "Khusus"', 'isi' => '<h2>Isi Lama</h2>', 'foto' => 'http://localhost/storage/artikel/s.jpg']);

    $this->actingAs($this->akunA)->get("/jemaat-saya/artikel/{$artikel->id}/edit")->assertOk()
        ->assertSee('value="Judul &quot;Khusus&quot;"', false)
        ->assertSee('&lt;h2&gt;Isi Lama&lt;/h2&gt;', false)
        ->assertSee('http://localhost/storage/artikel/s.jpg', false);
});

test('halaman edit menampilkan alasan penolakan', function () {
    $artikel = artikelDi($this->jemaatA, ['status' => 'ditolak', 'alasan_tolak' => 'Mohon lengkapi tanggal']);

    $this->actingAs($this->akunA)->get("/jemaat-saya/artikel/{$artikel->id}/edit")->assertSee('Mohon lengkapi tanggal');
});

test('sampul wajib dipilih di form baru dan di artikel lama tanpa sampul', function () {
    $this->actingAs($this->akunA)->get('/jemaat-saya/artikel/tulis')->assertSee('data-sampul-wajib="1"', false);

    $tanpa = artikelDi($this->jemaatA);
    $this->actingAs($this->akunA)->get("/jemaat-saya/artikel/{$tanpa->id}/edit")->assertSee('data-sampul-wajib="1"', false);

    $ada = artikelDi($this->jemaatA, ['foto' => 'http://localhost/storage/artikel/s.jpg']);
    $this->actingAs($this->akunA)->get("/jemaat-saya/artikel/{$ada->id}/edit")->assertSee('data-sampul-wajib="0"', false);
});

test('halaman edit artikel jemaat lain tertutup', function () {
    $lain = artikelDi($this->jemaatB);

    $this->actingAs($this->akunA)->get("/jemaat-saya/artikel/{$lain->id}/edit")->assertNotFound();
    $this->actingAs(klasisBerjalan())->get("/jemaat-saya/artikel/{$lain->id}/edit")->assertForbidden();
    $this->actingAs(klasisBerjalan())->get('/jemaat-saya/artikel/tulis')->assertForbidden();
});

test('daftar artikel tidak mengirim isi lengkap', function () {
    artikelDi($this->jemaatA, ['isi' => '<p>Isi yang panjang</p>']);

    expect($this->actingAs($this->akunA)->getJson('/jemaat-saya/artikel/data')->json('data.0'))->not->toHaveKey('isi');
});

test('halaman persetujuan memuat gaya isi yang sama dengan editor', function () {
    $this->actingAs($this->admin)->get('/persetujuan-artikel')->assertOk()->assertSee('konten-artikel', false)->assertSee('id="bacaIsi" class="ck-content', false);
});

test('artikel jemaat lain tidak terlihat dan tidak bisa diubah atau dihapus', function () {
    $lain = artikelDi($this->jemaatB, ['judul' => 'Punya B']);
    artikelDi($this->jemaatA, ['judul' => 'Punya A']);

    $this->actingAs($this->akunA)->getJson('/jemaat-saya/artikel/data')
        ->assertJsonCount(1, 'data')->assertJsonPath('data.0.judul', 'Punya A');
    $this->actingAs($this->akunA)->putJson("/jemaat-saya/artikel/{$lain->id}", ['judul' => 'Curang', 'penulis' => 'X', 'isi' => 'X'])->assertNotFound();
    $this->actingAs($this->akunA)->deleteJson("/jemaat-saya/artikel/{$lain->id}")->assertNotFound();

    expect($lain->fresh()->judul)->toBe('Punya B');
});

test('mengubah artikel yang disetujui mengembalikannya ke menunggu', function () {
    $artikel = artikelDi($this->jemaatA, ['foto' => 'http://localhost/storage/artikel/s.jpg', 'status' => 'disetujui', 'diperiksa_oleh' => 'Klasis', 'diperiksa_pada' => now()]);

    $this->actingAs($this->akunA)->putJson("/jemaat-saya/artikel/{$artikel->id}", ['judul' => 'Baru', 'penulis' => 'Yohana', 'isi' => 'Isi baru'])->assertOk();

    $artikel->refresh();
    expect($artikel->status)->toBe('menunggu');
    expect($artikel->diperiksa_oleh)->toBeNull();
    expect($artikel->diperiksa_pada)->toBeNull();
});

test('artikel yang ditolak bisa diperbaiki dan dikirim ulang', function () {
    $artikel = artikelDi($this->jemaatA, ['foto' => 'http://localhost/storage/artikel/s.jpg', 'status' => 'ditolak', 'alasan_tolak' => 'Kurang lengkap', 'diperiksa_oleh' => 'Klasis', 'diperiksa_pada' => now()]);

    $this->actingAs($this->akunA)->getJson('/jemaat-saya/artikel/data')->assertJsonPath('data.0.alasan_tolak', 'Kurang lengkap');

    $this->actingAs($this->akunA)->putJson("/jemaat-saya/artikel/{$artikel->id}", ['judul' => 'Perbaikan', 'penulis' => 'Yohana', 'isi' => 'Lengkap'])->assertOk();

    expect($artikel->fresh()->status)->toBe('menunggu');
    expect($artikel->fresh()->alasan_tolak)->toBeNull();
});

test('menghapus artikel ikut menghapus sampulnya, dan mengganti sampul menghapus yang lama', function () {
    $this->actingAs($this->akunA)->post('/jemaat-saya/artikel', ['judul' => 'A', 'penulis' => 'B', 'isi' => '<p>C</p>', 'foto' => UploadedFile::fake()->image('a.jpg')], JSON_JEMAAT)->assertOk();
    $artikel = Artikel::first();
    $a = $artikel->foto;

    $this->actingAs($this->akunA)->post("/jemaat-saya/artikel/{$artikel->id}", ['_method' => 'PUT', 'judul' => 'A', 'penulis' => 'B', 'isi' => '<p>C</p>', 'foto' => UploadedFile::fake()->image('b.jpg')], JSON_JEMAAT)->assertOk();
    $b = $artikel->fresh()->foto;
    Storage::disk('public')->assertMissing(FileUpload::path($a));

    $this->actingAs($this->akunA)->deleteJson("/jemaat-saya/artikel/{$artikel->id}")->assertOk();
    Storage::disk('public')->assertMissing(FileUpload::path($b));
    expect(Artikel::count())->toBe(0);
});

// ---------- Persetujuan ----------

test('Pengurus Klasis periode berjalan melihat artikel semua jemaat', function () {
    artikelDi($this->jemaatA, ['judul' => 'Dari A']);
    artikelDi($this->jemaatB, ['judul' => 'Dari B', 'status' => 'disetujui']);
    $klasis = klasisBerjalan();

    $this->actingAs($klasis)->get('/persetujuan-artikel')->assertOk();
    $this->actingAs($klasis)->getJson('/persetujuan-artikel/data')->assertJsonPath('total', 2);
    $this->actingAs($klasis)->getJson('/persetujuan-artikel/data?status=menunggu')
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.judul', 'Dari A')
        ->assertJsonPath('data.0.jemaat', 'Jemaat Maranatha');
});

test('Pengurus Klasis menyetujui artikel dan namanya tercatat', function () {
    $artikel = artikelDi($this->jemaatA);
    $klasis = klasisBerjalan();

    $this->actingAs($klasis)->postJson("/persetujuan-artikel/{$artikel->id}/setujui", ['versi' => versi($artikel)])->assertOk();

    $artikel->refresh();
    expect($artikel->status)->toBe('disetujui');
    expect($artikel->diperiksa_oleh)->toBe($klasis->name);
    expect($artikel->diperiksa_pada)->not->toBeNull();
});

test('penolakan wajib menyertakan alasan', function () {
    $artikel = artikelDi($this->jemaatA);
    $klasis = klasisBerjalan();

    $this->actingAs($klasis)->postJson("/persetujuan-artikel/{$artikel->id}/tolak", ['versi' => versi($artikel), 'alasan' => ''])
        ->assertUnprocessable()->assertJsonValidationErrors('alasan');
    expect($artikel->fresh()->status)->toBe('menunggu');

    $this->actingAs($klasis)->postJson("/persetujuan-artikel/{$artikel->id}/tolak", ['versi' => versi($artikel), 'alasan' => 'Perlu sumber'])->assertOk();
    expect($artikel->fresh()->status)->toBe('ditolak');
    expect($artikel->fresh()->alasan_tolak)->toBe('Perlu sumber');
});

test('artikel yang sudah diperiksa tidak bisa diperiksa lagi', function (string $status) {
    $artikel = artikelDi($this->jemaatA, ['status' => $status]);

    $this->actingAs(klasisBerjalan())->postJson("/persetujuan-artikel/{$artikel->id}/setujui", ['versi' => versi($artikel)])->assertUnprocessable();

    expect($artikel->fresh()->status)->toBe($status);
})->with(['disetujui', 'ditolak']);

test('artikel yang baru diubah penulis tidak bisa disetujui dengan isi lama', function () {
    $artikel = artikelDi($this->jemaatA);
    $this->travelTo(now()->subMinute());
    $artikel->update(['isi' => 'Versi lama']);
    $dibaca = versi($artikel);
    $this->travelBack();

    $artikel->update(['isi' => 'Versi baru yang belum dibaca']);

    $this->actingAs(klasisBerjalan())->postJson("/persetujuan-artikel/{$artikel->id}/setujui", ['versi' => $dibaca])->assertStatus(409);

    expect($artikel->fresh()->status)->toBe('menunggu');
});

test('Pengurus Klasis periode lama tidak bisa menyetujui', function () {
    $artikel = artikelDi($this->jemaatA);
    $lama = akunKlasis(now()->year - 6, now()->year - 4);

    $this->actingAs($lama)->get('/persetujuan-artikel')->assertForbidden();
    $this->actingAs($lama)->postJson("/persetujuan-artikel/{$artikel->id}/setujui", ['versi' => versi($artikel)])->assertForbidden();

    expect($artikel->fresh()->status)->toBe('menunggu');
});

test('Pengurus Jemaat tidak bisa menyetujui artikelnya sendiri', function () {
    $artikel = artikelDi($this->jemaatA);

    $this->actingAs($this->akunA)->postJson("/persetujuan-artikel/{$artikel->id}/setujui", ['versi' => versi($artikel)])->assertForbidden();

    expect($artikel->fresh()->status)->toBe('menunggu');
});

test('admin bisa menyetujui dan sidebar klasis menampilkan menunya', function () {
    $artikel = artikelDi($this->jemaatA);

    $this->actingAs($this->admin)->postJson("/persetujuan-artikel/{$artikel->id}/setujui", ['versi' => versi($artikel)])->assertOk();
    expect($artikel->fresh()->status)->toBe('disetujui');

    $this->actingAs(klasisBerjalan())->get('/persetujuan-artikel')->assertSee(route('persetujuan_artikel.index'), false);
});

test('Pengurus Klasis periode lama tidak melihat menu persetujuan', function () {
    $lama = akunKlasis(now()->year - 6, now()->year - 4);

    expect($lama->bisaMenu('persetujuan_artikel'))->toBeFalse();
    expect($lama->bisaMenu('program_kerja'))->toBeTrue();
});

test('aktivitas jemaat dan persetujuan masuk audit log dengan nama yang jelas', function () {
    $artikel = artikelDi($this->jemaatA, ['judul' => 'Ibadah Syukur']);
    $klasis = klasisBerjalan();
    AuditLog::query()->delete();

    $this->actingAs($klasis)->postJson("/persetujuan-artikel/{$artikel->id}/setujui", ['versi' => versi($artikel)])->assertOk();

    $log = AuditLog::latest('id')->first();
    expect($log->label)->toBe('Ibadah Syukur');
    expect($log->user_nama)->toBe($klasis->name);
    expect($log->perubahan['status']['baru'])->toBe('disetujui');
    expect(AuditLog::OBJEK[$log->model])->toBe('Artikel');
});

test('beranda hanya menampilkan artikel yang sudah disetujui', function () {
    artikelDi($this->jemaatA, ['judul' => 'Sudah Disetujui', 'status' => 'disetujui']);
    artikelDi($this->jemaatA, ['judul' => 'Masih Menunggu', 'status' => 'menunggu']);
    artikelDi($this->jemaatA, ['judul' => 'Sudah Ditolak', 'status' => 'ditolak']);

    $this->get('/')->assertOk()
        ->assertSee('Sudah Disetujui')
        ->assertDontSee('Masih Menunggu')
        ->assertDontSee('Sudah Ditolak');
});

// ---------- Daftar artikel ----------

test('daftar artikel menampilkan cuplikan isi tanpa tag HTML', function () {
    artikelDi($this->jemaatA, ['isi' => '<h2>Judul</h2><p>Kabar <strong>baik</strong> &amp; sukacita</p><p>Paragraf dua</p>']);

    $this->actingAs($this->akunA)->getJson('/jemaat-saya/artikel/data')
        ->assertJsonPath('data.0.ringkasan', 'Judul Kabar baik & sukacita Paragraf dua');
});

test('cuplikan artikel panjang dipotong', function () {
    artikelDi($this->jemaatA, ['isi' => '<p>' . str_repeat('kata ', 200) . '</p>']);

    $ringkasan = $this->actingAs($this->akunA)->getJson('/jemaat-saya/artikel/data')->json('data.0.ringkasan');

    expect(mb_strlen($ringkasan))->toBeLessThanOrEqual(143);
    expect($ringkasan)->toEndWith('...');
});

test('daftar artikel menampilkan tanggal dalam WITA', function () {
    $this->travelTo('2026-11-07 20:00:00');
    artikelDi($this->jemaatA);

    $this->actingAs($this->akunA)->getJson('/jemaat-saya/artikel/data')->assertJsonPath('data.0.tanggal', '08 Nov 2026');
});

test('ringkasan jumlah per status hanya menghitung jemaat sendiri dan tidak ikut terfilter', function () {
    artikelDi($this->jemaatA, ['status' => 'menunggu']);
    artikelDi($this->jemaatA, ['status' => 'menunggu']);
    artikelDi($this->jemaatA, ['status' => 'disetujui']);
    artikelDi($this->jemaatB, ['status' => 'ditolak']);

    $res = $this->actingAs($this->akunA)->getJson('/jemaat-saya/artikel/data?status=disetujui')->assertOk();

    expect($res->json('statistik'))->toEqual(['semua' => 3, 'menunggu' => 2, 'disetujui' => 1, 'ditolak' => 0]);
    expect($res->json('data'))->toHaveCount(1);
    expect($res->json('data.0.status'))->toBe('disetujui');
    expect($res->json('total'))->toBe(1);
});

test('filter status tidak membuka artikel jemaat lain', function () {
    artikelDi($this->jemaatB, ['status' => 'ditolak']);

    $this->actingAs($this->akunA)->getJson('/jemaat-saya/artikel/data?status=ditolak')->assertJsonCount(0, 'data');
});

test('cuplikan tidak menyambung kata dari daftar bersarang', function () {
    artikelDi($this->jemaatA, ['isi' => '<ul><li>poin<ul><li>anak</li></ul></li></ul>']);

    $this->actingAs($this->akunA)->getJson('/jemaat-saya/artikel/data')->assertJsonPath('data.0.ringkasan', 'poin anak');
});
