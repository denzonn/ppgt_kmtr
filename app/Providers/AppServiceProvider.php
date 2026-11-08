<?php

namespace App\Providers;

use App\Support\AuditLogger;
use Carbon\Carbon;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');
        // URL::forceScheme('https');

        Event::listen('eloquent.created: *', fn ($event, $data) => AuditLogger::dibuat($data[0]));
        Event::listen('eloquent.updated: *', fn ($event, $data) => AuditLogger::diubah($data[0]));
        Event::listen('eloquent.deleted: *', fn ($event, $data) => AuditLogger::dihapus($data[0]));

        Event::listen(Login::class, fn (Login $e) => AuditLogger::akses('login', $e->user));
        Event::listen(Logout::class, fn (Logout $e) => AuditLogger::akses('logout', $e->user));
        Event::listen(Failed::class, fn (Failed $e) => AuditLogger::akses('login gagal', null, $e->credentials['email'] ?? null));
    }
}
