<?php

use App\Models\Inventaris;
use App\Models\InventarisRiwayat;
use App\Models\Periode;
use App\Models\User;

function isianBarang(array $kondisi, array $tambahan = []): array
{
    return $tambahan + ['jenis' => 'PPL', 'nama' => 'Kursi', 'tanggal_perolehan' => '2026-01-01', 'harga' => 1000, 'kondisi' => $kondisi];
}

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->lama = Periode::create(['tahun_mulai' => 2023, 'tahun_selesai' => 2025]);
    $this->url = "/kepengurusan/{$this->lama->id}/inventaris";
});

test('tambah, ubah kondisi, dan hapus tercatat di riwayat periode beserta laporannya', function () {
    $this->actingAs($this->admin)->postJson($this->url, isianBarang(['Baik' => 10]))->assertOk();
    $item = Inventaris::first();

    // Edit tanpa perubahan kondisi tidak menambah riwayat.
    $this->actingAs($this->admin)->putJson("{$this->url}/{$item->id}", isianBarang(['Baik' => 10], ['nama' => 'Kursi Lipat']))->assertOk();
    $this->actingAs($this->admin)->putJson("{$this->url}/{$item->id}", isianBarang(['Baik' => 7, 'Rusak' => 2, 'Hilang' => 1], ['keterangan' => 'Retreat']))->assertOk();

    $this->actingAs($this->admin)->deleteJson("{$this->url}/{$item->id}")->assertUnprocessable()->assertJsonValidationErrors('keterangan');
    $this->actingAs($this->admin)->deleteJson("{$this->url}/{$item->id}", ['keterangan' => 'Dibuang'])->assertOk();

    expect(InventarisRiwayat::orderBy('id')->pluck('aksi')->all())->toBe(['ditambah', 'diubah', 'dihapus']);
    expect(InventarisRiwayat::firstWhere('aksi', 'diubah'))
        ->kondisi_sebelum->toBe(['Baik' => 10])
        ->keterangan->toBe('Retreat');

    $res = $this->actingAs($this->admin)->getJson("{$this->url}/data")->assertOk();
    expect($res->json('total'))->toBe(0);
    expect($res->json('laporan'))->toEqual(['ditambah' => 1, 'dihapus' => 1, 'perubahan' => ['Baik' => -3, 'Rusak' => 2, 'Hilang' => 1]]);

    // Barang terhapus tetap ada dan riwayatnya bisa dibuka.
    expect($this->actingAs($this->admin)->getJson("{$this->url}/data?status=dihapus")->json('data.0.id'))->toBe($item->id);
    expect($this->actingAs($this->admin)->getJson("{$this->url}/riwayat?inventaris={$item->id}")->json('*.aksi'))
        ->toBe(['dihapus', 'diubah', 'ditambah']);
});

test('periode lama hanya bisa melihat setelah ada periode baru, barangnya lanjut ke periode baru', function () {
    $this->actingAs($this->admin)->postJson($this->url, isianBarang(['Baik' => 5]))->assertOk();
    $item = Inventaris::first();

    $baru = Periode::create(['tahun_mulai' => 2025, 'tahun_selesai' => 2027]);
    $urlBaru = "/kepengurusan/{$baru->id}/inventaris";

    $this->actingAs($this->admin)->get($this->url)->assertOk()->assertSee('hanya bisa dilihat')->assertDontSee('id="btnTambahInventaris"', false);
    $this->actingAs($this->admin)->postJson($this->url, isianBarang(['Baik' => 1]))->assertForbidden();
    $this->actingAs($this->admin)->putJson("{$this->url}/{$item->id}", isianBarang(['Baik' => 1]))->assertForbidden();
    $this->actingAs($this->admin)->deleteJson("{$this->url}/{$item->id}", ['keterangan' => 'x'])->assertForbidden();

    expect($this->actingAs($this->admin)->getJson("{$urlBaru}/data")->json('data.0.id'))->toBe($item->id);
    $this->actingAs($this->admin)->putJson("{$urlBaru}/{$item->id}", isianBarang(['Baik' => 4, 'Hilang' => 1]))->assertOk();

    // Perubahan masuk ke laporan periode baru, laporan periode lama tetap utuh.
    expect($this->actingAs($this->admin)->getJson("{$urlBaru}/data")->json('laporan.perubahan'))->toEqual(['Baik' => -1, 'Hilang' => 1]);
    expect($this->actingAs($this->admin)->getJson("{$this->url}/data")->json('laporan'))->toEqual(['ditambah' => 1, 'dihapus' => 0, 'perubahan' => []]);
});

test('barang terhapus tidak bisa dipinjam dan kodenya tidak dipakai ulang', function () {
    $this->actingAs($this->admin)->postJson($this->url, isianBarang(['Baik' => 5]))->assertOk();
    $item = Inventaris::first();
    $this->actingAs($this->admin)->deleteJson("{$this->url}/{$item->id}", ['keterangan' => 'Dibuang'])->assertOk();

    $this->get('/peminjaman')->assertOk()->assertDontSee($item->kode_inventaris);
    $this->postJson('/peminjaman', ['nama_peminjam' => 'Budi', 'inventaris' => [['id' => $item->id, 'jumlah' => 1]]])
        ->assertJsonValidationErrors('inventaris.0.id');

    $this->actingAs($this->admin)->postJson($this->url, isianBarang(['Baik' => 1]))->assertOk();
    expect(Inventaris::latest('id')->first()->kode_inventaris)->toStartWith('002/PPL/');
});

test('kondisi di luar daftar ditolak', function () {
    $this->actingAs($this->admin)->postJson($this->url, isianBarang(['Bagus' => 5]))->assertJsonValidationErrors('kondisi');
});
