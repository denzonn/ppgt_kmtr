<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventarisController;
use App\Http\Controllers\Admin\PeminjamanController as AdminPeminjamanController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('peminjaman');
});

Route::prefix('peminjaman')->group(
    function () {
        Route::get('/', [PeminjamanController::class, 'index'])->name('peminjaman');
        Route::post('/', [PeminjamanController::class, 'store'])->name('peminjaman.store');
    }
);

Route::prefix('admin')->middleware(['auth'])->group(
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
    }
);

require __DIR__ . '/auth.php';
