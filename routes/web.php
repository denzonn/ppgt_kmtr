<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventarisController;
use App\Http\Controllers\Admin\PeminjamanController as AdminPeminjamanController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\GenerateQRLinkController;
use App\Http\Controllers\PeminjamanController;
use App\Models\Periode;
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

        Route::view('/akses-kosong', 'pages.akses-kosong')->name('akses.kosong');

        // Stub, diganti di Task 7.
        Route::get('/kepengurusan/{periode}/program-kerja', fn (Periode $periode) => response(''))
            ->middleware(['periode', 'menu:program_kerja'])
            ->name('kepengurusan.program_kerja.index');

        Route::prefix('inventaris')->name('inventaris.')->middleware('menu:inventaris')->group(
            function () {
                Route::get('/', [InventarisController::class, 'index'])->name('index');
                Route::get('/data', [InventarisController::class, 'getData'])->name('getData');
                Route::post('/', [InventarisController::class, 'store'])->name('store');
                Route::get('/{id}', [InventarisController::class, 'show'])->name('show');
                Route::put('/{id}', [InventarisController::class, 'update'])->name('update');
                Route::delete('/{id}', [InventarisController::class, 'destroy'])->name('destroy');
            }
        );

        Route::prefix('data-anggota')->name('data_anggota.')->middleware('menu:data_anggota')->group(function () {
            Route::get('/', [AnggotaController::class, 'index'])->name('index');
            Route::get('/data', [AnggotaController::class, 'getData'])->name('getData');
            Route::post('/import', [AnggotaController::class, 'import'])->name('import');
            Route::post('/', [AnggotaController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [AnggotaController::class, 'edit'])->name('edit');
            Route::get('/{id}', [AnggotaController::class, 'show'])->name('show');
            Route::put('/{id}', [AnggotaController::class, 'update'])->name('update');
            Route::delete('/{id}', [AnggotaController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('data-peminjaman')->middleware('menu:peminjaman')->group(
            function () {
                Route::get('/', [AdminPeminjamanController::class, 'index'])->name('peminjaman-admin');
                Route::get('/data', [AdminPeminjamanController::class, 'getData'])->name('peminjaman-admin.getData');
                Route::post('/pengembalian', [AdminPeminjamanController::class, 'pengembalian'])->name('peminjaman-admin.pengembalian');
            }
        );

        Route::prefix('generator-qr-link')->name('generate_qr.')->middleware('menu:generator_qr')->group(function () {
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
