<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventarisController;
use App\Http\Controllers\Admin\PeminjamanController as AdminPeminjamanController;
use App\Http\Controllers\GenerateQRLinkController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.beranda');
});

Route::prefix('peminjaman')->group(
    function () {
        Route::get('/', [PeminjamanController::class, 'index'])->name('peminjaman');
        Route::post('/', [PeminjamanController::class, 'store'])->name('peminjaman.store');
    }
);

Route::middleware(['auth'])->group(
    function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('inventaris')->group(
            function () {
                Route::get('/', [InventarisController::class, 'index'])->name('inventaris');
                Route::get('/data', [InventarisController::class, 'getData'])->name('inventaris.getData');
                Route::post('/', [InventarisController::class, 'store'])->name('inventaris.store');
                Route::get('/{id}', [InventarisController::class, 'show'])->name('inventaris.show');
                Route::put('/{id}', [InventarisController::class, 'update'])->name('inventaris.update');
                Route::delete('/{id}', [InventarisController::class, 'destroy'])->name('inventaris.destroy');
            }
        );

        Route::prefix('peminjaman')->group(
            function () {
                Route::get('/', [AdminPeminjamanController::class, 'index'])->name('peminjaman-admin');
                Route::get('/data', [AdminPeminjamanController::class, 'getData'])->name('peminjaman-admin.getData');
                Route::post('/pengembalian', [AdminPeminjamanController::class, 'pengembalian'])->name('peminjaman-admin.pengembalian');
            }
        );

        Route::prefix('generator-qr-link')->name('generate_qr.')->group(function () {
            Route::get('/', [GenerateQRLinkController::class, 'index'])->name('index');
            Route::get('/data', [GenerateQRLinkController::class, 'getData'])->name('getData');
            Route::post('/', [GenerateQRLinkController::class, 'store'])->name('store');

            Route::get('/{id}/download', [GenerateQRLinkController::class, 'download'])
                ->name('download');

            Route::get('/{id}', [GenerateQRLinkController::class, 'show'])->name('show');
            Route::put('/{id}', [GenerateQRLinkController::class, 'update'])->name('update');
            Route::delete('/{id}', [GenerateQRLinkController::class, 'destroy'])->name('destroy');
        });
    }
);

require __DIR__ . '/auth.php';
