<?php

use App\Models\Inventaris;
use App\Models\PeminjamanInventaris;
use App\Models\Periode;
use App\Models\User;
use App\Support\FileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create();
    $this->inv = '/kepengurusan/'.Periode::create(['tahun_mulai' => 2025, 'tahun_selesai' => 2027])->id.'/inventaris';
});

function isianInventarisFoto(array $tambahan = []): array
{
    return $tambahan + ['jenis' => 'PPL', 'nama' => 'Kursi', 'tanggal_perolehan' => '2026-01-01', 'harga' => 1000, 'kondisi' => ['Baik' => 5]];
}

function migrasiUrlFile()
{
    return require database_path('migrations/2026_11_07_000003_ubah_file_ke_url_lengkap.php');
}

test('file disimpan di storage dan database berisi URL lengkap', function () {
    $url = FileUpload::simpan(UploadedFile::fake()->image('a.jpg'), 'inventaris');

    expect($url)->toStartWith(asset('storage/inventaris/'));
    Storage::disk('public')->assertExists(FileUpload::path($url));
});

test('file selain gambar seperti PDF juga disimpan sebagai URL lengkap', function () {
    $url = FileUpload::simpan(UploadedFile::fake()->create('surat.pdf', 10, 'application/pdf'), 'dokumen');

    expect($url)->toStartWith(asset('storage/dokumen/'))->toEndWith('.pdf');
    Storage::disk('public')->assertExists(FileUpload::path($url));
});

test('path dikenali dari URL lengkap, alamat lain, dan data lama', function (string $nilai, ?string $path) {
    expect(FileUpload::path($nilai))->toBe($path);
})->with([
    'url' => ['http://127.0.0.1:8000/storage/pengurus/a.jpg', 'pengurus/a.jpg'],
    'alamat lain' => ['https://ppgt.id/storage/pengurus/a.jpg', 'pengurus/a.jpg'],
    'subfolder' => ['https://ppgt.id/app/storage/pengurus/a.jpg', 'pengurus/a.jpg'],
    'relatif lama' => ['pengurus/a.jpg', 'pengurus/a.jpg'],
    'di luar storage' => ['http://127.0.0.1:8000/images/peminjaman/a.jpg', null],
]);

test('menghapus file lewat URL lengkap', function () {
    $url = FileUpload::simpan(UploadedFile::fake()->image('a.jpg'), 'inventaris');
    $path = FileUpload::path($url);

    FileUpload::hapus($url);

    Storage::disk('public')->assertMissing($path);
});

test('menghapus nilai kosong atau file di luar storage tidak error', function () {
    FileUpload::hapus(null);
    FileUpload::hapus('http://127.0.0.1:8000/images/peminjaman/a.jpg');

    expect(true)->toBeTrue();
});

test('foto inventaris tersimpan sebagai URL dan file lama terhapus saat diganti', function () {
    $this->actingAs($this->admin)->post($this->inv, isianInventarisFoto(['foto' => UploadedFile::fake()->image('a.jpg')]), ['Accept' => 'application/json'])->assertOk();
    $item = Inventaris::first();
    $lama = $item->foto;
    expect($lama)->toStartWith(asset('storage/inventaris/'));

    $this->actingAs($this->admin)->post("{$this->inv}/{$item->id}", isianInventarisFoto(['_method' => 'PUT', 'foto' => UploadedFile::fake()->image('b.jpg')]), ['Accept' => 'application/json'])->assertOk();

    expect($item->fresh()->foto)->toStartWith(asset('storage/inventaris/'))->not->toBe($lama);
    Storage::disk('public')->assertMissing(FileUpload::path($lama));
    Storage::disk('public')->assertExists(FileUpload::path($item->fresh()->foto));
});

test('foto peminjaman dan pengembalian tersimpan sebagai URL', function () {
    $this->actingAs($this->admin)->postJson($this->inv, isianInventarisFoto())->assertOk();
    $item = Inventaris::first();

    $this->post('/peminjaman', [
        'nama_peminjam' => 'Budi',
        'foto_pengambilan' => UploadedFile::fake()->image('ambil.jpg'),
        'inventaris' => [['id' => $item->id, 'jumlah' => 1]],
    ], ['Accept' => 'application/json'])->assertOk();

    $pinjam = PeminjamanInventaris::first();
    expect($pinjam->foto_pengambilan)->toStartWith(asset('storage/peminjaman/'));

    $this->actingAs($this->admin)->post('/data-peminjaman/pengembalian', [
        'id' => $pinjam->id,
        'foto_pengembalian' => UploadedFile::fake()->image('kembali.jpg'),
    ], ['Accept' => 'application/json'])->assertOk();

    expect($pinjam->fresh()->foto_pengembalian)->toStartWith(asset('storage/pengembalian/'));
});

test('migrasi mengubah data lama menjadi URL lengkap dan aman dijalankan ulang', function () {
    config(['app.url' => 'http://127.0.0.1:8000/']);

    $relatif = Inventaris::create(['kode_inventaris' => 'A', 'jenis' => 'PPL', 'nama' => 'A', 'tanggal_perolehan' => '2026-01-01', 'harga' => 1, 'foto' => 'inventaris/a.png']);
    $kosong = Inventaris::create(['kode_inventaris' => 'B', 'jenis' => 'PPL', 'nama' => 'B', 'tanggal_perolehan' => '2026-01-01', 'harga' => 1]);
    $sudah = Inventaris::create(['kode_inventaris' => 'C', 'jenis' => 'PPL', 'nama' => 'C', 'tanggal_perolehan' => '2026-01-01', 'harga' => 1, 'foto' => 'https://ppgt.id/storage/inventaris/c.png']);
    $pinjam = PeminjamanInventaris::create(['nama_peminjam' => 'X', 'foto_pengambilan' => 'foto lama.png', 'foto_pengembalian' => 'pengembalian/k.png', 'tanggal_peminjaman' => now(), 'status' => 'dipinjam']);

    migrasiUrlFile()->up();
    migrasiUrlFile()->up();

    expect($relatif->fresh()->foto)->toBe('http://127.0.0.1:8000/storage/inventaris/a.png');
    expect($kosong->fresh()->foto)->toBeNull();
    expect($sudah->fresh()->foto)->toBe('https://ppgt.id/storage/inventaris/c.png');
    expect($pinjam->fresh()->foto_pengambilan)->toBe('http://127.0.0.1:8000/images/peminjaman/foto%20lama.png');
    expect($pinjam->fresh()->foto_pengembalian)->toBe('http://127.0.0.1:8000/storage/pengembalian/k.png');
});

test('konversi data tidak menambah catatan audit', function () {
    Inventaris::create(['kode_inventaris' => 'A', 'jenis' => 'PPL', 'nama' => 'A', 'tanggal_perolehan' => '2026-01-01', 'harga' => 1, 'foto' => 'inventaris/a.png']);
    $sebelum = DB::table('audit_log')->count();

    migrasiUrlFile()->up();

    expect(DB::table('audit_log')->count())->toBe($sebelum);
});

test('rollback migrasi mengembalikan bentuk lama', function () {
    config(['app.url' => 'http://127.0.0.1:8000']);

    $item = Inventaris::create(['kode_inventaris' => 'A', 'jenis' => 'PPL', 'nama' => 'A', 'tanggal_perolehan' => '2026-01-01', 'harga' => 1, 'foto' => 'inventaris/a.png']);
    $pinjam = PeminjamanInventaris::create(['nama_peminjam' => 'X', 'foto_pengambilan' => 'foto lama.png', 'tanggal_peminjaman' => now(), 'status' => 'dipinjam']);

    migrasiUrlFile()->up();
    migrasiUrlFile()->down();

    expect($item->fresh()->foto)->toBe('inventaris/a.png');
    expect($pinjam->fresh()->foto_pengambilan)->toBe('foto lama.png');
});
