<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\Admin\AbsenController as AdminAbsenController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LinktreeController as AdminLinktreeController;
use App\Http\Controllers\Admin\PeminjamanController as AdminPeminjamanController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\PeriodeController;
use App\Http\Controllers\Admin\PersetujuanArtikelController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TimTanggapController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\BilikSuaraController;
use App\Http\Controllers\GenerateQRLinkController;
use App\Http\Controllers\Jemaat\ArtikelController as JemaatArtikelController;
use App\Http\Controllers\Jemaat\BidangController as JemaatBidangController;
use App\Http\Controllers\Jemaat\KelompokController as JemaatKelompokController;
use App\Http\Controllers\Jemaat\PengurusController as JemaatPengurusController;
use App\Http\Controllers\Kepengurusan\BidangController;
use App\Http\Controllers\Kepengurusan\InventarisController;
use App\Http\Controllers\Kepengurusan\JabatanController;
use App\Http\Controllers\Kepengurusan\KegiatanController;
use App\Http\Controllers\Kepengurusan\PendampinganController;
use App\Http\Controllers\Kepengurusan\PengurusController;
use App\Http\Controllers\Kepengurusan\ProgramKerjaController;
use App\Http\Controllers\LinktreeController;
use App\Http\Controllers\PanduanController;
use App\Http\Controllers\PemilihanController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BerandaController::class, 'index']);

Route::get('/artikel', [ArtikelController::class, 'index'])->name('artikel.daftar');
Route::get('/artikel/{id}/{slug?}', [ArtikelController::class, 'show'])->whereNumber('id')->name('artikel.baca');

Route::prefix('peminjaman')->group(
    function () {
        Route::get('/', [PeminjamanController::class, 'index'])->name('peminjaman');
        Route::post('/', [PeminjamanController::class, 'store'])->name('peminjaman.store');
    }
);

Route::get('/links', [LinktreeController::class, 'index'])->name('linktree');

Route::get('/absen/{absen:token}', [AbsenController::class, 'index'])->name('absen.isi');
Route::post('/absen/{absen:token}', [AbsenController::class, 'store'])->middleware('throttle:60,1')->name('absen.kirim');

Route::prefix('bilik/{pemilihan:token}')->name('bilik.')->controller(BilikSuaraController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/periksa', 'periksa')->middleware('throttle:120,1')->name('periksa');
    Route::post('/pilih', 'pilih')->middleware('throttle:120,1')->name('pilih');
});

Route::post('/telegram/webhook', [TelegramController::class, 'webhook'])->middleware('throttle:120,1')->name('telegram.webhook');

Route::middleware(['auth'])->group(
    function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::view('/akses-kosong', 'pages.akses-kosong')->name('akses.kosong');

        Route::get('/panduan/{topik?}', [PanduanController::class, 'show'])->name('panduan');

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

        Route::prefix('kepengurusan/{periode}/pendampingan')->name('kepengurusan.pendampingan.')->middleware(['periode', 'menu:pendampingan'])->whereNumber('id')->controller(PendampinganController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/tempel', 'tempel')->name('tempel');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

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

        Route::get('/inventaris', [InventarisController::class, 'buka'])->middleware('menu:inventaris')->name('inventaris.index');

        Route::prefix('kepengurusan/{periode}/inventaris')->name('kepengurusan.inventaris.')->middleware(['periode', 'menu:inventaris'])->controller(InventarisController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'getData')->name('getData');
            Route::get('/riwayat', 'riwayat')->name('riwayat');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}', 'show')->name('show');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

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

        Route::prefix('jemaat-saya')->name('jemaat.')->group(function () {
            Route::get('/pengurus/anggota', [JemaatPengurusController::class, 'anggota'])->middleware('menu:jemaat_pengurus')->name('pengurus.anggota');
            Route::post('/artikel/gambar', [JemaatArtikelController::class, 'gambar'])->middleware('menu:artikel')->name('artikel.gambar');
            Route::get('/artikel/tulis', [JemaatArtikelController::class, 'create'])->middleware('menu:artikel')->name('artikel.create');
            Route::get('/artikel/{id}/edit', [JemaatArtikelController::class, 'edit'])->middleware('menu:artikel')->name('artikel.edit');

            foreach (['kelompok' => [JemaatKelompokController::class, 'jemaat_kelompok'], 'bidang' => [JemaatBidangController::class, 'jemaat_bidang'], 'pengurus' => [JemaatPengurusController::class, 'jemaat_pengurus'], 'artikel' => [JemaatArtikelController::class, 'artikel']] as $nama => [$controller, $menu]) {
                Route::prefix($nama)->name("{$nama}.")->middleware("menu:{$menu}")->group(function () use ($controller) {
                    Route::get('/', [$controller, 'index'])->name('index');
                    Route::get('/data', [$controller, 'getData'])->name('getData');
                    Route::post('/', [$controller, 'store'])->name('store');
                    Route::put('/{id}', [$controller, 'update'])->name('update');
                    Route::delete('/{id}', [$controller, 'destroy'])->name('destroy');
                });
            }
        });

        Route::prefix('persetujuan-artikel')->name('persetujuan_artikel.')->middleware('menu:persetujuan_artikel')->group(function () {
            Route::get('/', [PersetujuanArtikelController::class, 'index'])->name('index');
            Route::get('/data', [PersetujuanArtikelController::class, 'getData'])->name('getData');
            Route::post('/{artikel}/setujui', [PersetujuanArtikelController::class, 'setujui'])->name('setujui');
            Route::post('/{artikel}/tolak', [PersetujuanArtikelController::class, 'tolak'])->name('tolak');
        });

        Route::prefix('tim-tanggap')->name('tim_tanggap.')->middleware('menu:tim_tanggap')->group(function () {
            Route::get('/', [TimTanggapController::class, 'index'])->name('index');
            Route::get('/data', [TimTanggapController::class, 'getData'])->name('getData');
            Route::post('/{anggota}/pesan', [TimTanggapController::class, 'catatPesan'])->name('pesan');
        });

        // Admin mengelola Konferensi, akun jemaat mengelola pemilihan jemaatnya sendiri.
        foreach ([['pemilihan', 'pemilihan.', 'admin'], ['jemaat-saya/pemilihan', 'jemaat.pemilihan.', 'menu:jemaat_pemilihan']] as [$alamat, $nama, $izin]) {
            Route::prefix($alamat)->name($nama)->middleware($izin)->whereNumber(['pemilihan', 'peserta', 'putaran', 'jabatan'])->controller(PemilihanController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::get('/{pemilihan}', 'show')->name('show');
                Route::delete('/{pemilihan}', 'destroy')->name('destroy');
                Route::get('/{pemilihan}/kartu', 'kartu')->name('kartu');
                Route::get('/{pemilihan}/anggota', 'anggota')->name('anggota');
                Route::post('/{pemilihan}/peserta', 'pesertaStore')->name('peserta.store');
                Route::post('/{pemilihan}/peserta/demisioner', 'pesertaDemisioner')->name('peserta.demisioner');
                Route::post('/{pemilihan}/peserta/anggota', 'pesertaAnggota')->name('peserta.anggota');
                Route::put('/{pemilihan}/peserta/{peserta}', 'pesertaUpdate')->name('peserta.update');
                Route::post('/{pemilihan}/peserta/{peserta}/kode', 'pesertaKode')->name('peserta.kode');
                Route::delete('/{pemilihan}/peserta/{peserta}', 'pesertaDestroy')->name('peserta.destroy');
                Route::post('/{pemilihan}/jabatan', 'jabatanStore')->name('jabatan.store');
                Route::get('/{pemilihan}/jabatan/{jabatan}', 'jabatanShow')->name('jabatan.show');
                Route::delete('/{pemilihan}/jabatan/{jabatan}', 'jabatanDestroy')->name('jabatan.destroy');
                Route::post('/{pemilihan}/jabatan/{jabatan}/tahap', 'tahapStore')->name('jabatan.tahap');
                Route::post('/{pemilihan}/jabatan/{jabatan}/tetapkan', 'jabatanTetapkan')->name('jabatan.tetapkan');
                Route::post('/{pemilihan}/jabatan/{jabatan}/batal-tetapkan', 'jabatanBatalTetapkan')->name('jabatan.batal_tetapkan');
                Route::put('/{pemilihan}/putaran/{putaran}/calon', 'putaranCalon')->name('putaran.calon');
                Route::post('/{pemilihan}/putaran/{putaran}/buka', 'putaranBuka')->name('putaran.buka');
                Route::post('/{pemilihan}/putaran/{putaran}/tutup', 'putaranTutup')->name('putaran.tutup');
                Route::get('/{pemilihan}/putaran/{putaran}/status', 'putaranStatus')->name('putaran.status');
                Route::delete('/{pemilihan}/putaran/{putaran}', 'putaranDestroy')->name('putaran.destroy');
            });
        }

        Route::prefix('audit-log')->name('audit_log.')->middleware('admin')->group(function () {
            Route::get('/', [AuditLogController::class, 'index'])->name('index');
            Route::get('/data', [AuditLogController::class, 'getData'])->name('getData');
        });

        Route::prefix('data-linktree')->name('linktree.')->middleware('menu:linktree')->group(function () {
            Route::get('/', [AdminLinktreeController::class, 'index'])->name('index');
            Route::get('/data', [AdminLinktreeController::class, 'getData'])->name('getData');
            Route::post('/', [AdminLinktreeController::class, 'store'])->name('store');
            Route::put('/urutan', [AdminLinktreeController::class, 'urutan'])->name('urutan');
            Route::put('/{linktree}', [AdminLinktreeController::class, 'update'])->name('update');
            Route::delete('/{linktree}', [AdminLinktreeController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('data-absen')->name('absen.')->middleware('menu:absen')->group(function () {
            Route::get('/', [AdminAbsenController::class, 'index'])->name('index');
            Route::get('/data', [AdminAbsenController::class, 'getData'])->name('getData');
            Route::post('/', [AdminAbsenController::class, 'store'])->name('store');
            Route::get('/{absen}/qr', [AdminAbsenController::class, 'qr'])->name('qr');
            Route::get('/{absen}/peserta', [AdminAbsenController::class, 'peserta'])->name('peserta');
            Route::get('/{absen}', [AdminAbsenController::class, 'show'])->name('show');
            Route::put('/{absen}', [AdminAbsenController::class, 'update'])->name('update');
            Route::delete('/{absen}', [AdminAbsenController::class, 'destroy'])->name('destroy');
        });
    }
);

require __DIR__ . '/auth.php';
