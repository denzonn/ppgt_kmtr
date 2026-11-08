<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Menerima pesan rekap dari bot Telegram, lalu membalas ringkasan hasilnya.
 * Hanya pengirim yang ID-nya terdaftar di TELEGRAM_PENGIRIM yang bisa menyimpan rekap.
 */
class BotPendampingan
{
    public static function aktif(): bool
    {
        return filled(config('services.telegram.token'));
    }

    public static function proses(array $update): void
    {
        // Pesan yang diedit di Telegram ikut diproses, jadi rekap bisa diperbaiki tanpa dikirim ulang.
        $pesan = $update['message'] ?? $update['edited_message'] ?? null;
        $teks = trim((string) ($pesan['text'] ?? ''));
        $chat = $pesan['chat']['id'] ?? null;
        $pengirim = (string) ($pesan['from']['id'] ?? '');

        if (! $chat || $teks === '') {
            return;
        }

        if (Str::startsWith($teks, ['/start', '/id'])) {
            self::kirim($chat, "ID Telegram Anda: {$pengirim}\n\nBerikan ID ini kepada admin supaya rekap Anda bisa disimpan.");

            return;
        }

        if (! in_array($pengirim, self::pengirimTerdaftar(), true)) {
            self::kirim($chat, "Maaf, Anda belum terdaftar untuk mengirim rekap.\nID Telegram Anda: {$pengirim}");

            return;
        }

        $hasil = RekapPendampingan::baca($teks);

        if ($hasil['kegiatan'] === []) {
            self::kirim($chat, "Tidak ada kegiatan yang terbaca.\n\nPastikan ada baris TANGGAL, nama kegiatan diawali *, lalu baris Hadir :");

            return;
        }

        $simpan = RekapPendampingan::simpan($hasil['kegiatan'], 'telegram');
        $balasan = '✅ Rekap tersimpan: '.count($hasil['kegiatan']).' kegiatan'
            ." ({$simpan['baru']} baru, {$simpan['diperbarui']} diperbarui, {$simpan['sama']} tidak berubah).";

        if ($hasil['abaikan'] !== []) {
            $balasan .= "\n\n⚠️ Baris yang tidak terbaca:\n".collect($hasil['abaikan'])->take(5)->map(fn ($b) => '• '.Str::limit($b, 80))->join("\n");
        }

        self::kirim($chat, $balasan);
    }

    /** @return list<string> */
    public static function pengirimTerdaftar(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) config('services.telegram.pengirim')))));
    }

    public static function kirim(int|string $chat, string $teks): void
    {
        try {
            Http::timeout(10)->post(self::alamat('sendMessage'), ['chat_id' => $chat, 'text' => $teks]);
        } catch (Throwable $e) {
            // Rekap tetap tersimpan walau balasan gagal terkirim.
            report($e);
        }
    }

    public static function alamat(string $metode): string
    {
        return 'https://api.telegram.org/bot'.config('services.telegram.token').'/'.$metode;
    }
}
