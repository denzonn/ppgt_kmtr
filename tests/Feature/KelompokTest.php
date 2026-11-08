<?php

use App\Imports\AnggotaImport;
use App\Models\DataAnggota;
use App\Models\Jemaat;
use App\Models\Kelompok;
use App\Models\Role;
use App\Models\User;
use Maatwebsite\Excel\Row;

function pengurusKelompok(Jemaat $jemaat): User
{
    return User::factory()->create(['role_id' => Role::firstWhere('nama', 'Pengurus Jemaat')->id, 'jemaat_id' => $jemaat->id]);
}

function isianKelompok(Jemaat $jemaat, array $ubah = []): array
{
    return $ubah + ['jemaat_id' => $jemaat->id, 'nama' => 'Budi', 'jenis_kelamin' => 'Laki-laki', 'status_anggota' => 'Aktif', 'status_wilayah' => 'dalam_wilayah'];
}

function barisImport(array $isi): Row
{
    return new class($isi) extends Row
    {
        public function __construct(private array $isi) {}

        public function toArray($nullValue = null, $calculateFormulas = true, $formatData = true, ?string $endColumn = null): array
        {
            return $this->isi;
        }

        public function getIndex(): int
        {
            return 2;
        }
    };
}

test('pengurus jemaat mengelola kelompok jemaatnya sendiri', function () {
    $jemaat = Jemaat::create(['nama' => 'Jemaat A']);
    $akun = pengurusKelompok($jemaat);

    $this->actingAs($akun)->get(route('jemaat.kelompok.index'))->assertOk()->assertSee('Tambah Kelompok');
    $this->actingAs($akun)->postJson(route('jemaat.kelompok.store'), ['nama' => 'Hermon'])->assertOk();
    $this->actingAs($akun)->postJson(route('jemaat.kelompok.store'), ['nama' => 'Hermon'])->assertUnprocessable();

    $kelompok = Kelompok::firstWhere('nama', 'Hermon');
    expect($kelompok->jemaat_id)->toBe($jemaat->id);

    $this->actingAs($akun)->putJson(route('jemaat.kelompok.update', $kelompok->id), ['nama' => 'Daud'])->assertOk();
    expect($kelompok->fresh()->nama)->toBe('Daud');
});

test('nama kelompok boleh sama antarjemaat tapi data jemaat lain tidak bisa disentuh', function () {
    $lain = Kelompok::create(['jemaat_id' => Jemaat::create(['nama' => 'Jemaat B'])->id, 'nama' => 'Hermon']);
    $akun = pengurusKelompok(Jemaat::create(['nama' => 'Jemaat A']));

    $this->actingAs($akun)->postJson(route('jemaat.kelompok.store'), ['nama' => 'Hermon'])->assertOk();
    $this->actingAs($akun)->putJson(route('jemaat.kelompok.update', $lain->id), ['nama' => 'X'])->assertNotFound();
    $this->actingAs($akun)->deleteJson(route('jemaat.kelompok.destroy', $lain->id))->assertNotFound();
});

test('kelompok yang masih punya anggota tidak bisa dihapus', function () {
    $jemaat = Jemaat::create(['nama' => 'Jemaat A']);
    $kelompok = Kelompok::create(['jemaat_id' => $jemaat->id, 'nama' => 'Hermon']);
    $anggota = DataAnggota::create(['jemaat_id' => $jemaat->id, 'kelompok_id' => $kelompok->id, 'nama' => 'Budi']);
    $akun = pengurusKelompok($jemaat);

    $this->actingAs($akun)->deleteJson(route('jemaat.kelompok.destroy', $kelompok->id))->assertUnprocessable();

    $anggota->update(['kelompok_id' => null]);
    $this->actingAs($akun)->deleteJson(route('jemaat.kelompok.destroy', $kelompok->id))->assertOk();
});

test('kelompok wajib dipilih kalau jemaat sudah punya kelompok', function () {
    $jemaat = Jemaat::create(['nama' => 'Jemaat A']);
    $akun = pengurusKelompok($jemaat);

    // Belum punya kelompok: anggota tetap bisa didata.
    $this->actingAs($akun)->postJson(route('data_anggota.store'), isianKelompok($jemaat))->assertOk();

    $kelompok = Kelompok::create(['jemaat_id' => $jemaat->id, 'nama' => 'Hermon']);

    $this->actingAs($akun)->postJson(route('data_anggota.store'), isianKelompok($jemaat))->assertJsonValidationErrors('kelompok_id');
    $this->actingAs($akun)->postJson(route('data_anggota.store'), isianKelompok($jemaat, ['kelompok_id' => $kelompok->id]))->assertOk();

    expect(DataAnggota::whereNotNull('kelompok_id')->count())->toBe(1);
});

test('kelompok harus milik jemaat anggota', function () {
    $jemaat = Jemaat::create(['nama' => 'Jemaat A']);
    Kelompok::create(['jemaat_id' => $jemaat->id, 'nama' => 'Hermon']);
    $lain = Kelompok::create(['jemaat_id' => Jemaat::create(['nama' => 'Jemaat B'])->id, 'nama' => 'Daud']);

    $this->actingAs(pengurusKelompok($jemaat))->postJson(route('data_anggota.store'), isianKelompok($jemaat, ['kelompok_id' => $lain->id]))
        ->assertJsonValidationErrors('kelompok_id');
});

test('kelompok lama lepas saat anggota pindah ke jemaat tanpa kelompok', function () {
    $asal = Jemaat::create(['nama' => 'Jemaat A']);
    $tujuan = Jemaat::create(['nama' => 'Jemaat B']);
    $kelompok = Kelompok::create(['jemaat_id' => $asal->id, 'nama' => 'Hermon']);
    $anggota = DataAnggota::create(['jemaat_id' => $asal->id, 'kelompok_id' => $kelompok->id, 'nama' => 'Budi']);

    $this->actingAs(User::factory()->admin()->create())->putJson(route('data_anggota.update', $anggota->id), isianKelompok($tujuan))->assertOk();

    expect($anggota->fresh()->kelompok_id)->toBeNull();
});

test('daftar anggota bisa difilter per kelompok dan yang belum punya kelompok', function () {
    $jemaat = Jemaat::create(['nama' => 'Jemaat A']);
    $kelompok = Kelompok::create(['jemaat_id' => $jemaat->id, 'nama' => 'Hermon']);
    DataAnggota::create(['jemaat_id' => $jemaat->id, 'kelompok_id' => $kelompok->id, 'nama' => 'Ada Kelompok']);
    DataAnggota::create(['jemaat_id' => $jemaat->id, 'nama' => 'Tanpa Kelompok']);
    $akun = pengurusKelompok($jemaat);

    $this->actingAs($akun)->getJson(route('data_anggota.getData', ['kelompok' => $kelompok->id]))
        ->assertJsonPath('data.total', 1)
        ->assertJsonPath('data.data.0.kelompok.nama', 'Hermon')
        ->assertJsonPath('filters.kelompok.0.nama', 'Hermon');

    $this->actingAs($akun)->getJson(route('data_anggota.getData', ['kelompok' => 'kosong']))
        ->assertJsonPath('data.total', 1)
        ->assertJsonPath('data.data.0.nama', 'Tanpa Kelompok');
});

test('import mencocokkan kelompok dan mencatat nama yang belum terdaftar', function () {
    $jemaat = Jemaat::create(['nama' => 'Jemaat A']);
    $kelompok = Kelompok::create(['jemaat_id' => $jemaat->id, 'nama' => 'Hermon']);
    $import = new AnggotaImport();
    $isi = fn (string $no, string $kel) => barisImport(['no_anggota' => $no, 'nama' => "Nama {$no}", 'jenis_kelamin' => 'L', 'jemaat' => 'jemaat a', 'kelompok' => $kel]);

    $import->onRow($isi('A-1', ' hermon '));
    $import->onRow($isi('A-2', 'Tidak Ada'));

    expect(DataAnggota::firstWhere('no_anggota', 'A-1')->kelompok_id)->toBe($kelompok->id)
        ->and(DataAnggota::firstWhere('no_anggota', 'A-2')->kelompok_id)->toBeNull()
        ->and($import->berhasil)->toBe(2)
        ->and($import->catatan)->toHaveCount(1);
});

test('data kelompok memuat ringkasan, jumlah per jenis kelamin, dan contoh anggota', function () {
    $jemaat = Jemaat::create(['nama' => 'Jemaat A']);
    $kelompok = Kelompok::create(['jemaat_id' => $jemaat->id, 'nama' => 'Hermon']);

    foreach (['Laki-laki', 'L', 'Perempuan', 'P', 'Perempuan'] as $i => $jk) {
        DataAnggota::create(['jemaat_id' => $jemaat->id, 'kelompok_id' => $kelompok->id, 'nama' => "Anggota {$i}", 'jenis_kelamin' => $jk]);
    }

    DataAnggota::create(['jemaat_id' => $jemaat->id, 'nama' => 'Tanpa Kelompok']);
    DataAnggota::create(['jemaat_id' => Jemaat::create(['nama' => 'Jemaat B'])->id, 'nama' => 'Jemaat Lain']);

    $this->actingAs(pengurusKelompok($jemaat))->getJson(route('jemaat.kelompok.getData'))
        ->assertJsonPath('ringkasan.total_anggota', 6)
        ->assertJsonPath('ringkasan.belum_berkelompok', 1)
        ->assertJsonPath('data.0.anggota_count', 5)
        ->assertJsonPath('data.0.laki', 2)
        ->assertJsonPath('data.0.perempuan', 3)
        ->assertJsonCount(4, 'data.0.contoh');
});

test('kelompok tampil sebagai submenu data anggota, bukan kepengurusan', function () {
    $akun = pengurusKelompok(Jemaat::create(['nama' => 'Jemaat A']));

    $this->actingAs($akun)->get(route('jemaat.kelompok.index'))
        ->assertSee('data-grup="anggota" aria-expanded="true"', false)
        ->assertSee('data-grup="jemaat" aria-expanded="false"', false);
});

test('admin tanpa jemaat melihat data anggota sebagai menu tunggal', function () {
    $this->actingAs(User::factory()->admin()->create())->get('/dashboard')
        ->assertDontSee('data-grup="anggota"', false)
        ->assertSee(route('data_anggota.index'), false);
});

test('menu kelompok diberikan ke role pengurus jemaat dan muncul di sidebar', function () {
    $akun = pengurusKelompok(Jemaat::create(['nama' => 'Jemaat A']));

    expect($akun->bisaMenu('jemaat_kelompok'))->toBeTrue();
    $this->actingAs($akun)->get(route('jemaat.kelompok.index'))->assertSee(route('jemaat.kelompok.index'), false);
});
