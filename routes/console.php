<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Untuk komputer lokal yang tidak bisa menerima webhook: ambil pesan bot secara berkala.
Artisan::command('pendampingan:telegram {--sekali : Ambil sekali saja lalu berhenti}', function () {
    if (! App\Support\BotPendampingan::aktif()) {
        $this->error('TELEGRAM_BOT_TOKEN belum diisi di .env.');

        return 1;
    }

    $this->info('Menunggu pesan rekap dari bot Telegram. Tekan Ctrl+C untuk berhenti.');

    do {
        $offset = (int) Illuminate\Support\Facades\Cache::get('telegram_offset', 0);
        $jawaban = Illuminate\Support\Facades\Http::timeout(40)->get(App\Support\BotPendampingan::alamat('getUpdates'), [
            'offset' => $offset,
            'timeout' => $this->option('sekali') ? 0 : 30,
            'allowed_updates' => json_encode(['message', 'edited_message']),
        ]);

        if (! $jawaban->ok()) {
            $this->error('Telegram menolak: '.$jawaban->json('description', 'tanpa keterangan').'. Kalau webhook terpasang, hapus dulu dengan deleteWebhook.');

            return 1;
        }

        foreach ($jawaban->json('result', []) as $update) {
            App\Support\BotPendampingan::proses($update);
            Illuminate\Support\Facades\Cache::forever('telegram_offset', $update['update_id'] + 1);
            $this->line('Pesan diproses: '.($update['update_id']));
        }
    } while (! $this->option('sekali'));

    return 0;
})->purpose('Ambil rekap pendampingan dari bot Telegram');
