<?php

use App\Models\QRCodeLink;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create();
});

test('QR disimpan sebagai path relatif, bukan alamat lengkap', function () {
    $this->actingAs($this->admin)->postJson('/generator-qr-link', ['nama' => 'Warta', 'link' => 'https://ppgt.id/warta'])->assertOk();

    $item = QRCodeLink::first();
    expect($item->qr_code)->toBe("qr-codes/{$item->id}.svg");
    Storage::disk('public')->assertExists($item->qr_code);
});

test('alamat gambar mengikuti alamat web yang sedang dibuka', function () {
    $this->actingAs($this->admin)->postJson('/generator-qr-link', ['nama' => 'Warta', 'link' => 'https://ppgt.id/warta']);

    $url = $this->actingAs($this->admin)->getJson('http://ppgt.test/generator-qr-link/data')->json('data.0.qr_code');
    expect($url)->toStartWith('http://ppgt.test/storage/qr-codes/');
});

test('data lama berisi alamat lengkap tetap tampil di alamat yang benar', function () {
    Storage::disk('public')->put('qr-codes/9.svg', '<svg/>');
    $item = QRCodeLink::create(['nama' => 'Lama', 'link' => 'https://a.id', 'qr_code' => 'http://127.0.0.1:8000/storage/qr-codes/9.svg']);

    $url = $this->actingAs($this->admin)->getJson('http://ppgt.test/generator-qr-link/data')->json('data.0.qr_code');
    expect($url)->toStartWith('http://ppgt.test/storage/qr-codes/9.svg');

    $this->actingAs($this->admin)->deleteJson("/generator-qr-link/{$item->id}")->assertOk();
    Storage::disk('public')->assertMissing('qr-codes/9.svg');
});

test('alamat gambar berubah setelah link diubah supaya tidak memakai gambar lama dari cache', function () {
    $this->travelTo('2026-11-05 10:00:00');
    $this->actingAs($this->admin)->postJson('/generator-qr-link', ['nama' => 'Warta', 'link' => 'https://ppgt.id/lama']);
    $item = QRCodeLink::first();
    $sebelum = $this->actingAs($this->admin)->getJson("/generator-qr-link/{$item->id}/download")->json('url');

    $this->travelTo('2026-11-05 10:05:00');
    $this->actingAs($this->admin)->putJson("/generator-qr-link/{$item->id}", ['nama' => 'Warta', 'link' => 'https://ppgt.id/baru'])->assertOk();
    $sesudah = $this->actingAs($this->admin)->getJson("/generator-qr-link/{$item->id}/download")->json('url');

    expect($sesudah)->not->toBe($sebelum);
});

test('link hanya boleh http atau https', function (string $link) {
    $this->actingAs($this->admin)->postJson('/generator-qr-link', ['nama' => 'X', 'link' => $link])
        ->assertUnprocessable()->assertJsonValidationErrors('link');
    expect(QRCodeLink::count())->toBe(0);
})->with(['javascript:alert(1)', 'ftp://ppgt.id/a', 'bukan link']);

test('link terlalu panjang ditolak dengan pesan, bukan error 500', function () {
    $panjang = 'https://ppgt.id/' . str_repeat('a', 1300);

    $this->actingAs($this->admin)->postJson('/generator-qr-link', ['nama' => 'X', 'link' => $panjang])
        ->assertUnprocessable()->assertJsonValidationErrors('link');
});

test('link sepanjang batas maksimal tetap berhasil dibuat', function () {
    $this->actingAs($this->admin)->postJson('/generator-qr-link', ['nama' => 'X', 'link' => 'https://ppgt.id/' . str_repeat('a', 1184)])->assertOk();
});

test('unduh membuat ulang file yang hilang', function () {
    $this->actingAs($this->admin)->postJson('/generator-qr-link', ['nama' => 'Warta', 'link' => 'https://ppgt.id/warta']);
    $item = QRCodeLink::first();
    Storage::disk('public')->delete($item->qr_code);

    $this->actingAs($this->admin)->getJson("/generator-qr-link/{$item->id}/download")->assertOk()->assertJsonPath('nama', 'Warta');
    Storage::disk('public')->assertExists($item->fresh()->qr_code);
});

test('hapus ikut menghapus file', function () {
    $this->actingAs($this->admin)->postJson('/generator-qr-link', ['nama' => 'Warta', 'link' => 'https://ppgt.id/warta']);
    $item = QRCodeLink::first();

    $this->actingAs($this->admin)->deleteJson("/generator-qr-link/{$item->id}")->assertOk();
    Storage::disk('public')->assertMissing("qr-codes/{$item->id}.svg");
});

test('role tanpa menu QR ditolak', function () {
    $this->actingAs(User::factory()->create())->getJson('/generator-qr-link/data')->assertForbidden();
});
