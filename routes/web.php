<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventarisController;
use App\Http\Controllers\Admin\PeminjamanController as AdminPeminjamanController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\PeriodeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\GenerateQRLinkController;
use App\Http\Controllers\Kepengurusan\BidangController;
use App\Http\Controllers\Kepengurusan\JabatanController;
use App\Http\Controllers\Kepengurusan\KegiatanController;
use App\Http\Controllers\Kepengurusan\PengurusController;
use App\Http\Controllers\Kepengurusan\ProgramKerjaController;
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

        Route::view('/akses-kosong', 'pages.akses-kosong')->name('akses.kosong');

        Route::prefix('kepengurusan/{periode}')->name('kepengurusan.')->middleware(['periode', 'menu:program_kerja'])->group(
            function () {
                Route::get('/program-kerja', [ProgramKerjaController::class, 'index'])->name('program_kerja.index');
                Route::get('/program-kerja/data', [ProgramKerjaController::class, 'getData'])->name('program_kerja.getData');
                Route::post('/program-kerja', [ProgramKerjaController::class, 'store'])->name('program_kerja.store');
                Route::get('/program-kerja/{id}', [ProgramKerjaController::class, 'show'])->name('program_kerja.show');
                Route::put('/program-kerja/{id}', [ProgramKerjaController::class, 'update'])->name('program_kerja.update');
                Route::delete('/program-kerja/{id}', [ProgramKerjaController::class, 'destroy'])->name('program_kerja.destroy');
                Route::post('/program-kerja/{id}/kegiatan', [KegiatanController::class, 'store'])->name('kegiatan.store');
                Route::put('/kegiatan/{id}', [KegiatanController::class, 'update'])->name('kegiatan.update');
                Route::delete('/kegiatan/{id}', [KegiatanController::class, 'destroy'])->name('kegiatan.destroy');
                Route::post('/kegiatan/{id}/dokumentasi', [KegiatanController::class, 'dokumentasi'])->name('kegiatan.dokumentasi');
            }
        );

        foreach (['bidang' => BidangController::class, 'jabatan' => JabatanController::class] as $nama => $controller) {
            Route::prefix("kepengurusan/{periode}/{$nama}")->name("kepengurusan.{$nama}.")->middleware(['periode', "menu:{$nama}"])->group(
                function () use ($controller) {
                    Route::get('/', [$controller, 'index'])->name('index');
                    Route::get('/data', [$controller, 'getData'])->name('getData');
                    Route::post('/', [$controller, 'store'])->name('store');
                    Route::get('/{id}', [$controller, 'show'])->name('show');
                    Route::put('/{id}', [$controller, 'update'])->name('update');
                    Route::delete('/{id}', [$controller, 'destroy'])->name('destroy');
                }
            );
        }

        Route::prefix('kepengurusan/{periode}/pengurus')->name('kepengurusan.pengurus.')->middleware(['periode', 'menu:pengurus'])->group(
            function () {
                Route::get('/', [PengurusController::class, 'index'])->name('index');
                Route::get('/data', [PengurusController::class, 'getData'])->name('getData');
                Route::get('/anggota', [PengurusController::class, 'anggota'])->name('anggota');
                Route::post('/', [PengurusController::class, 'store'])->name('store');
                Route::get('/{id}', [PengurusController::class, 'show'])->name('show');
                Route::put('/{id}', [PengurusController::class, 'update'])->name('update');
                Route::delete('/{id}', [PengurusController::class, 'destroy'])->name('destroy');
            }
        );

        Route::prefix('role')->name('role.')->middleware('admin')->group(
            function () {
                Route::get('/', [RoleController::class, 'index'])->name('index');
                Route::get('/data', [RoleController::class, 'getData'])->name('getData');
                Route::post('/', [RoleController::class, 'store'])->name('store');
                Route::get('/{id}', [RoleController::class, 'show'])->name('show');
                Route::put('/{id}', [RoleController::class, 'update'])->name('update');
                Route::delete('/{id}', [RoleController::class, 'destroy'])->name('destroy');
            }
        );

        Route::prefix('periode')->name('periode.')->middleware('admin')->group(
            function () {
                Route::get('/', [PeriodeController::class, 'index'])->name('index');
                Route::get('/data', [PeriodeController::class, 'getData'])->name('getData');
                Route::post('/', [PeriodeController::class, 'store'])->name('store');
                Route::get('/{id}', [PeriodeController::class, 'show'])->name('show');
                Route::put('/{id}', [PeriodeController::class, 'update'])->name('update');
                Route::delete('/{id}', [PeriodeController::class, 'destroy'])->name('destroy');
            }
        );

        Route::prefix('pengguna')->name('pengguna.')->middleware('admin')->group(
            function () {
                Route::get('/', [PenggunaController::class, 'index'])->name('index');
                Route::get('/data', [PenggunaController::class, 'getData'])->name('getData');
                Route::post('/', [PenggunaController::class, 'store'])->name('store');
                Route::get('/{id}', [PenggunaController::class, 'show'])->name('show');
                Route::put('/{id}', [PenggunaController::class, 'update'])->name('update');
                Route::delete('/{id}', [PenggunaController::class, 'destroy'])->name('destroy');
            }
        );

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
