<?php

use App\Models\Inventaris;
use App\Models\InventarisKondisi;

function inventarisDengan(string $nama, string $kode, int $baik): Inventaris
{
    $item = Inventaris::create([
        'kode_inventaris' => $kode,
        'jenis' => 'PPL',
        'nama' => $nama,
        'tanggal_perolehan' => '2026-01-01',
        'harga' => 1000,
    ]);

    if ($baik > 0) {
        InventarisKondisi::create(['id_inventaris' => $item->id, 'kondisi' => 'Baik', 'jumlah' => $baik]);
    }

    return $item;
}

test('pilihan inventaris hanya menampilkan nama', function () {
    inventarisDengan('BANTAL', '061/PPL/1856', 10);

    $html = $this->get('/peminjaman')->assertOk()->getContent();

    preg_match('#<select id="inventaris_select".*?</select>#s', $html, $select);
    preg_match_all('#<option[^>]*>(.*?)</option>#s', $select[0], $teks);

    expect(array_map('trim', $teks[1]))->toBe(['', 'BANTAL']);
});

test('dropdown tidak lagi menampilkan kode dan sisa unit', function () {
    inventarisDengan('BANTAL', '061/PPL/1856', 10);

    $this->get('/peminjaman')
        ->assertDontSee("data('kode');", false)
        ->assertDontSee('Sisa \' +', false);
});

test('batas jumlah tetap tersedia untuk dipakai saat inventaris dipilih', function () {
    inventarisDengan('BANTAL', '061/PPL/1856', 10);

    $this->get('/peminjaman')->assertSee('data-tersedia="10"', false);
});
