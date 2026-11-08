<?php

use App\Models\PendampinganKegiatan;
use App\Models\Periode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;

const REKAP_CONTOH = "🟦 November 2026 🌈:\n\nTANGGAL 01 November (MINGGU)\n* Rapat SC RA Satria Kasih\nHadir : Denson, Bill, Rocky\n\nTANGGAL 02 NOVEMBER (SENIN)\n* Rapat SC RA Tamalanrea\nHadir : Rocky, Cici";

beforeEach(function () {
    $this->periode = Periode::create(['tahun_mulai' => 2025, 'tahun_selesai' => 2027]);
    $this->pengurus = User::factory()->untukPeriode($this->periode)->create();
    $this->url = "/kepengurusan/{$this->periode->id}/pendampingan";
    config(['services.telegram.token' => 'TOKEN', 'services.telegram.rahasia_webhook' => 'rahasia', 'services.telegram.pengirim' => '111, 222']);
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
});

function pesanTelegram(string $teks, string $dari = '111', string $jenis = 'message'): array
{
    return ['update_id' => 1, $jenis => ['chat' => ['id' => 999], 'from' => ['id' => (int) $dari], 'text' => $teks]];
}

function kirimWebhook($test, array $update, string $rahasia = 'rahasia')
{
    return $test->postJson('/telegram/webhook', $update, ['X-Telegram-Bot-Api-Secret-Token' => $rahasia]);
}

test('role Pengurus Klasis mendapat menu rekap pendampingan', function () {
    expect(Role::where('nama', 'Pengurus Klasis')->value('menus'))->toContain('pendampingan');

    $this->actingAs($this->pengurus)->get($this->url)->assertOk()->assertSee('Rekap Pendampingan');
});

test('akun tanpa menu pendampingan tidak bisa membuka rekap', function () {
    $role = Role::create(['nama' => 'Lain', 'is_admin' => false, 'menus' => ['program_kerja']]);
    $akun = User::factory()->untukPeriode($this->periode)->create(['role_id' => $role->id]);

    $this->actingAs($akun)->get($this->url)->assertForbidden();
});

test('rekap dari Telegram tersimpan dan pengirim mendapat balasan', function () {
    kirimWebhook($this, pesanTelegram(REKAP_CONTOH))->assertNoContent();

    expect(PendampinganKegiatan::orderBy('tanggal')->get()->map(fn ($k) => [$k->tanggal->toDateString(), $k->nama, $k->hadir])->all())->toBe([
        ['2026-11-01', 'Rapat SC RA Satria Kasih', ['Denson', 'Bill', 'Rocky']],
        ['2026-11-02', 'Rapat SC RA Tamalanrea', ['Rocky', 'Cici']],
    ]);

    Http::assertSent(fn ($r) => str_contains($r->url(), 'sendMessage') && $r['chat_id'] === 999 && str_contains($r['text'], '2 kegiatan (2 baru'));
});

test('rekap yang dikirim ulang memperbarui, bukan menggandakan', function () {
    kirimWebhook($this, pesanTelegram(REKAP_CONTOH));
    kirimWebhook($this, pesanTelegram(str_replace('Hadir : Rocky, Cici', 'Hadir : Rocky, Cici, Nia', REKAP_CONTOH), '222', 'edited_message'));

    expect(PendampinganKegiatan::count())->toBe(2)
        ->and(PendampinganKegiatan::where('nama', 'Rapat SC RA Tamalanrea')->first()->hadir)->toBe(['Rocky', 'Cici', 'Nia']);

    Http::assertSent(fn ($r) => str_contains((string) $r['text'], '1 diperbarui, 1 tidak berubah'));
});

test('pengirim yang tidak terdaftar ditolak tetapi diberi tahu ID-nya', function () {
    kirimWebhook($this, pesanTelegram(REKAP_CONTOH, '333'))->assertNoContent();

    expect(PendampinganKegiatan::count())->toBe(0);
    Http::assertSent(fn ($r) => str_contains($r['text'], 'belum terdaftar') && str_contains($r['text'], '333'));
});

test('perintah id membalas ID Telegram pengirim', function () {
    kirimWebhook($this, pesanTelegram('/id', '333'));

    Http::assertSent(fn ($r) => str_contains($r['text'], 'ID Telegram Anda: 333'));
});

test('webhook tanpa rahasia yang benar ditolak', function () {
    kirimWebhook($this, pesanTelegram(REKAP_CONTOH), 'salah')->assertForbidden();

    config(['services.telegram.rahasia_webhook' => '']);
    kirimWebhook($this, pesanTelegram(REKAP_CONTOH), '')->assertForbidden();

    expect(PendampinganKegiatan::count())->toBe(0);
});

test('teks tanpa kegiatan dibalas petunjuk format', function () {
    kirimWebhook($this, pesanTelegram('halo semua'));

    expect(PendampinganKegiatan::count())->toBe(0);
    Http::assertSent(fn ($r) => str_contains($r['text'], 'Tidak ada kegiatan yang terbaca'));
});

test('rekap bisa ditempel langsung di halaman', function () {
    $this->actingAs($this->pengurus)->post("{$this->url}/tempel", ['teks' => REKAP_CONTOH."\nbaris aneh"])
        ->assertSessionHas('success')
        ->assertSessionHas('abaikan', ['baris aneh']);

    $this->actingAs($this->pengurus)->get($this->url)->assertOk()
        ->assertSee('Rapat SC RA Satria Kasih')
        ->assertSee('Denson')
        ->assertSee('November 2026');

    expect(PendampinganKegiatan::where('sumber', 'manual')->count())->toBe(2);
});

test('halaman hanya menampilkan rekap dalam tahun periode dan bisa disaring per bulan', function () {
    PendampinganKegiatan::create(['tanggal' => '2026-11-01', 'nama' => 'Kegiatan November', 'hadir' => ['Rocky', 'Cici'], 'sumber' => 'manual']);
    PendampinganKegiatan::create(['tanggal' => '2026-10-05', 'nama' => 'Kegiatan Oktober', 'hadir' => ['rocky'], 'sumber' => 'manual']);
    PendampinganKegiatan::create(['tanggal' => '2024-01-01', 'nama' => 'Kegiatan Periode Lalu', 'hadir' => ['Andi'], 'sumber' => 'manual']);

    $respons = $this->actingAs($this->pengurus)->get($this->url)->assertOk()
        ->assertSee('Kegiatan November')->assertSee('Kegiatan Oktober')->assertDontSee('Kegiatan Periode Lalu');
    expect($respons->viewData('peringkat')->first())->toBe(['nama' => 'Rocky', 'jumlah' => 2]);

    $this->actingAs($this->pengurus)->get("{$this->url}?bulan=2026-10")->assertOk()
        ->assertSee('Kegiatan Oktober')->assertDontSee('Kegiatan November');
});

test('kegiatan bisa diubah dan dihapus', function () {
    $kegiatan = PendampinganKegiatan::create(['tanggal' => '2026-11-01', 'nama' => 'Rapat', 'hadir' => ['Rocky'], 'sumber' => 'manual']);

    $this->actingAs($this->pengurus)->put("{$this->url}/{$kegiatan->id}", ['tanggal' => '2026-11-02', 'nama' => 'Rapat Panitia', 'hadir' => 'Rocky, Cici dan Nia', 'sambutan' => 'Cici'])
        ->assertSessionHas('success');
    expect($kegiatan->fresh())->nama->toBe('Rapat Panitia')->hadir->toBe(['Rocky', 'Cici', 'Nia'])->sambutan->toBe(['Cici'])
        ->and($kegiatan->fresh()->tanggal->toDateString())->toBe('2026-11-02');

    $this->actingAs($this->pengurus)->put("{$this->url}/{$kegiatan->id}", ['tanggal' => 'bukan tanggal', 'nama' => 'X'])->assertSessionHasErrors('tanggal');

    $this->actingAs($this->pengurus)->delete("{$this->url}/{$kegiatan->id}")->assertSessionHas('success');
    expect(PendampinganKegiatan::count())->toBe(0);
});
