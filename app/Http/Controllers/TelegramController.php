<?php

namespace App\Http\Controllers;

use App\Support\BotPendampingan;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    /** Dipanggil Telegram setiap ada pesan masuk ke bot (mode webhook, untuk server online). */
    public function webhook(Request $request)
    {
        $rahasia = (string) config('services.telegram.rahasia_webhook');

        // Tanpa rahasia yang cocok, siapa pun bisa mengirim data palsu ke alamat ini.
        abort_unless($rahasia !== '' && hash_equals($rahasia, (string) $request->header('X-Telegram-Bot-Api-Secret-Token')), 403);

        BotPendampingan::proses($request->all());

        return response()->noContent();
    }
}
