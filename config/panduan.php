<?php

/*
 * Isi menu Panduan. Kunci luar menjadi alamat halaman (/panduan/{kunci}).
 * izin: null = semua akun, 'admin' = administrator, selain itu kunci menu di Role.
 * gambar: nama berkas di public/images/panduan. Teks mendukung **tebal**.
 */

return [

    // ---------- Mulai ----------
    'login-navigasi' => [
        'judul' => 'Login dan Navigasi',
        'grup' => 'Mulai',
        'izin' => null,
        'pengantar' => 'Panduan ini menjelaskan cara masuk ke aplikasi dan mengenali bagian-bagian layar.',
        'bagian' => [
            [
                'judul' => 'Masuk ke aplikasi',
                'gambar' => 'login',
                'alt' => 'Halaman login dengan isian email dan password',
                'langkah' => [
                    'Buka alamat aplikasi di peramban.',
                    'Ketik **Email** dan **Password** yang diberikan Administrator.',
                    'Klik **Login**.',
                ],
                'teks' => 'Centang **Ingat saya** hanya di komputer pribadi, bukan di komputer bersama.',
                'catatan' => 'Akun tidak dapat didaftarkan sendiri. Jika belum punya akun atau lupa password, hubungi Administrator.',
            ],
            [
                'judul' => 'Mengenali layar',
                'gambar' => 'navigasi',
                'alt' => 'Halaman aplikasi dengan menu di sebelah kiri dan isi di sebelah kanan',
                'langkah' => [
                    '**Menu di sebelah kiri** berisi semua fitur yang bisa Anda buka. Menu yang tampil mengikuti hak akses akun Anda.',
                    'Klik menu yang punya tanda panah untuk membuka submenunya.',
                    '**Bagian kanan** menampilkan isi halaman yang sedang dibuka.',
                    'Klik **Logout** di kiri bawah setelah selesai, terutama di komputer bersama.',
                ],
                'teks' => 'Di layar ponsel, menu tersembunyi. Ketuk ikon menu di bagian atas untuk membukanya.',
            ],
        ],
    ],

    // ---------- Pengurus Jemaat ----------
    'data-anggota' => [
        'judul' => 'Data Anggota',
        'grup' => 'Jemaat',
        'izin' => 'data_anggota',
        'pengantar' => 'Data Anggota menyimpan identitas seluruh anggota. Pengurus Jemaat hanya melihat dan mengubah anggota jemaatnya sendiri.',
        'bagian' => [
            [
                'judul' => 'Melihat dan mencari anggota',
                'gambar' => 'anggota-daftar',
                'alt' => 'Daftar anggota dengan kolom pencarian dan filter',
                'langkah' => [
                    'Klik menu **Data Anggota**. Pengurus Jemaat memilih **Daftar Anggota** pada submenunya.',
                    'Ketik nama atau nomor anggota di kolom pencarian.',
                    'Gunakan filter wilayah, jemaat, dan kelompok untuk mempersempit daftar.',
                ],
                'teks' => 'Tulisan **Belum ada kelompok** berarti anggota itu perlu dilengkapi. Pilih filter **Belum ada kelompok** untuk melihat semuanya sekaligus.',
            ],
            [
                'judul' => 'Menambah anggota',
                'gambar' => 'anggota-form',
                'alt' => 'Form tambah anggota',
                'langkah' => [
                    'Klik **Tambah Anggota**.',
                    'Isi semua kolom bertanda bintang merah, termasuk **Kelompok**.',
                    'Lengkapi data lain, seperti golongan darah dan nomor telepon, jika tersedia.',
                    'Klik **Simpan Anggota**.',
                ],
                'teks' => 'Nomor anggota dibuat otomatis. NIK boleh dikosongkan, tetapi jika diisi harus 16 digit.',
                'catatan' => 'Jika pilihan Kelompok kosong, buat dahulu kelompoknya di menu **Data Anggota > Kelompok**.',
            ],
            [
                'judul' => 'Melihat detail, mengubah, dan menghapus',
                'gambar' => 'anggota-detail',
                'alt' => 'Jendela detail anggota',
                'langkah' => [
                    'Klik ikon **mata** pada baris anggota untuk melihat detailnya.',
                    'Klik ikon **pensil** untuk mengubah data, lalu klik **Simpan Perubahan**.',
                    'Klik ikon **tempat sampah** untuk menghapus anggota.',
                ],
                'catatan' => 'Anggota yang tercatat sebagai pengurus tidak bisa dihapus. Lepaskan dahulu dari daftar pengurus.',
            ],
            [
                'judul' => 'Import dari Excel (khusus Administrator)',
                'gambar' => 'anggota-import',
                'alt' => 'Jendela import data anggota dari Excel',
                'langkah' => [
                    'Klik **Import Excel**.',
                    'Siapkan file XLSX, XLS, atau CSV berukuran maksimal 10 MB. Kolom wajibnya **no_anggota**, **nama**, **jenis_kelamin**, dan **jemaat**.',
                    'Isi kolom **kelompok** jika sudah ada. Kolom lain yang dibaca: tempat_lahir, tanggal_lahir, golongan_darah, golongan_darah_rhesus, alamat, telepon, dan email.',
                    'Pilih file, lalu klik **Mulai Import**.',
                    'Baca hasilnya: jumlah yang berhasil, dilewati, gagal, dan yang masuk tanpa kelompok.',
                ],
                'catatan' => 'Nomor anggota yang sudah terdaftar akan dilewati, jadi import tidak mengubah data anggota lama.',
            ],
        ],
    ],

    'kelompok' => [
        'judul' => 'Kelompok',
        'grup' => 'Jemaat',
        'izin' => 'jemaat_kelompok',
        'pengantar' => 'Setiap jemaat memiliki beberapa kelompok, misalnya Kelompok Hermon dan Kelompok Daud. Buat daftar kelompok ini sebelum melengkapi data anggota.',
        'bagian' => [
            [
                'judul' => 'Menambah kelompok',
                'gambar' => 'kelompok-form',
                'alt' => 'Form tambah kelompok dengan isian nama kelompok',
                'langkah' => [
                    'Buka **Data Anggota > Kelompok**.',
                    'Klik **Tambah Kelompok**.',
                    'Ketik **Nama Kelompok**, lalu klik **Simpan**.',
                    'Ulangi untuk setiap kelompok di jemaat Anda.',
                ],
                'catatan' => 'Setelah jemaat punya kelompok, setiap anggota yang ditambah atau diubah wajib memilih kelompok.',
            ],
            [
                'judul' => 'Mengubah dan menghapus kelompok',
                'gambar' => 'kelompok-daftar',
                'alt' => 'Ringkasan kelompok dan kartu setiap kelompok beserta jumlah anggotanya',
                'langkah' => [
                    'Lihat ringkasan di bagian atas untuk mengetahui berapa anggota yang sudah punya kelompok. Klik **belum punya kelompok** untuk langsung membuka daftar anggota yang perlu dilengkapi.',
                    'Klik **Lihat anggota** pada kartu untuk membuka anggota kelompok itu.',
                    'Klik ikon **pensil** di pojok kartu untuk mengganti nama kelompok.',
                    'Klik ikon **tempat sampah** untuk menghapusnya.',
                ],
                'teks' => 'Kelompok yang masih punya anggota tidak bisa dihapus, jadi pindahkan dahulu anggotanya lewat menu Daftar Anggota.',
            ],
        ],
    ],

    'bidang-jemaat' => [
        'judul' => 'Bidang Jemaat',
        'grup' => 'Jemaat',
        'izin' => 'jemaat_bidang',
        'pengantar' => 'Bidang adalah pembagian tugas pengurus di jemaat Anda. Buat bidang sebelum mengisi pengurus.',
        'bagian' => [
            [
                'judul' => 'Menambah bidang',
                'gambar' => 'jemaat-bidang-form',
                'alt' => 'Form tambah bidang dengan isian nama dan jumlah orang',
                'langkah' => [
                    'Buka **Kepengurusan > Bidang**.',
                    'Klik **Tambah Bidang**.',
                    'Isi **Nama Bidang** dan **Jumlah Orang**, yaitu banyaknya pengurus di bidang itu.',
                    'Klik **Simpan**.',
                ],
            ],
            [
                'judul' => 'Mengubah dan menghapus bidang',
                'gambar' => 'jemaat-bidang-daftar',
                'alt' => 'Daftar bidang jemaat',
                'langkah' => [
                    'Lihat lingkaran di setiap baris untuk mengetahui jumlah pengurus yang sudah terisi.',
                    'Klik **Atur pengurus** untuk mengisi pengurusnya.',
                    'Klik ikon **pensil** untuk mengubah nama atau jumlah orang.',
                    'Klik ikon **tempat sampah** untuk menghapus bidang.',
                ],
                'catatan' => 'Bidang yang masih punya pengurus tidak bisa dihapus, dan jumlah orangnya tidak bisa lebih kecil dari pengurus yang sudah terisi.',
            ],
        ],
    ],

    'pengurus-jemaat' => [
        'judul' => 'Pengurus Jemaat',
        'grup' => 'Jemaat',
        'izin' => 'jemaat_pengurus',
        'pengantar' => 'Pengurus dipilih dari Data Anggota jemaat Anda, jadi pastikan anggotanya sudah terdaftar dan bidangnya sudah dibuat.',
        'bagian' => [
            [
                'judul' => 'Melihat susunan pengurus',
                'gambar' => 'jemaat-pengurus-daftar',
                'alt' => 'Daftar pengurus yang dikelompokkan per bidang',
                'langkah' => [
                    'Buka **Kepengurusan > Pengurus**.',
                    'Pengurus tampil per bidang, lengkap dengan jumlah yang sudah terisi.',
                ],
            ],
            [
                'judul' => 'Menambah pengurus',
                'gambar' => 'jemaat-pengurus-form',
                'alt' => 'Form tambah pengurus',
                'langkah' => [
                    'Klik **Tambah Pengurus**.',
                    'Ketik nama atau nomor anggota di kolom **Anggota**, lalu pilih orangnya dari hasil pencarian.',
                    'Pilih **Bidang** dan isi **Jabatan**, misalnya Ketua.',
                    'Tambahkan foto jika ada (JPG, PNG, atau GIF, maksimal 2 MB).',
                    'Klik **Simpan**.',
                ],
                'teks' => 'Untuk mengubah atau menghapus, gunakan ikon **pensil** dan **tempat sampah** pada kartu pengurus.',
            ],
        ],
    ],

    'artikel' => [
        'judul' => 'Artikel',
        'grup' => 'Jemaat',
        'izin' => 'artikel',
        'pengantar' => 'Bagikan kabar kegiatan jemaat lewat artikel. Setiap artikel diperiksa Pengurus Klasis sebelum disetujui.',
        'bagian' => [
            [
                'judul' => 'Menulis artikel',
                'gambar' => 'artikel-tulis',
                'alt' => 'Halaman tulis artikel',
                'langkah' => [
                    'Klik menu **Artikel**, lalu **Tulis Artikel**.',
                    'Klik area **Tambahkan foto sampul** dan pilih gambar (JPG, PNG, atau GIF, maksimal 2 MB).',
                    'Isi judul, nama penulis, dan isi artikel. Tombol di atas kolom isi dipakai untuk memberi format dan menyisipkan gambar.',
                    'Klik **Kirim untuk Disetujui**.',
                ],
            ],
            [
                'judul' => 'Memantau status artikel',
                'gambar' => 'artikel-daftar',
                'alt' => 'Daftar artikel dengan tab status',
                'langkah' => [
                    'Klik kotak **Menunggu**, **Disetujui**, atau **Ditolak** untuk menyaring artikel.',
                    'Jika ditolak, baca alasannya pada kartu artikel.',
                    'Klik ikon **pensil**, perbaiki, lalu kirim kembali.',
                ],
                'catatan' => 'Artikel yang diubah akan kembali berstatus Menunggu dan diperiksa ulang, termasuk artikel yang sudah disetujui.',
            ],
        ],
    ],

    // ---------- Pengurus Klasis ----------
    'persetujuan-artikel' => [
        'judul' => 'Persetujuan Artikel',
        'grup' => 'Klasis',
        'izin' => 'persetujuan_artikel',
        'pengantar' => 'Pengurus Klasis memeriksa artikel kiriman jemaat. Menu ini hanya tersedia untuk pengurus pada periode yang sedang berjalan.',
        'bagian' => [
            [
                'judul' => 'Memilih artikel',
                'gambar' => 'persetujuan-daftar',
                'alt' => 'Daftar artikel dengan tab Menunggu, Disetujui, Ditolak, dan Semua',
                'langkah' => [
                    'Klik menu **Persetujuan Artikel**. Tab **Menunggu** langsung terbuka.',
                    'Klik **Baca** pada artikel yang akan diperiksa.',
                ],
            ],
            [
                'judul' => 'Menyetujui artikel',
                'gambar' => 'persetujuan-baca',
                'alt' => 'Tampilan baca artikel dengan tombol Tolak dan Setujui di bagian bawah',
                'langkah' => [
                    'Baca artikel sampai selesai.',
                    'Klik **Setujui**, lalu pastikan sekali lagi pada jendela konfirmasi.',
                ],
                'catatan' => 'Jika muncul pesan bahwa artikel baru saja diubah penulis, buka dan baca ulang artikelnya sebelum memutuskan.',
            ],
            [
                'judul' => 'Menolak artikel',
                'gambar' => 'persetujuan-tolak',
                'alt' => 'Jendela isian alasan penolakan',
                'langkah' => [
                    'Klik **Tolak**.',
                    'Tulis alasan yang jelas agar penulis tahu bagian yang harus diperbaiki.',
                    'Klik **Tolak** pada jendela tersebut.',
                ],
                'teks' => 'Alasan akan tampil di halaman Artikel milik jemaat. Setelah diperbaiki, artikel masuk lagi ke tab Menunggu.',
            ],
        ],
    ],

    'bidang-klasis' => [
        'judul' => 'Bidang Klasis',
        'grup' => 'Klasis',
        'izin' => 'bidang',
        'pengantar' => 'Bidang menyusun kepengurusan klasis pada satu periode. Urutan pengisiannya: Bidang, Jabatan, lalu Pengurus.',
        'bagian' => [
            [
                'judul' => 'Melihat bidang',
                'gambar' => 'klasis-bidang',
                'alt' => 'Daftar bidang dengan jumlah jabatan, program kerja, dan orang',
                'langkah' => [
                    'Buka **Kepengurusan > Bidang**.',
                    'Lihat jumlah jabatan, program kerja, dan orang yang sudah terisi di setiap bidang.',
                ],
            ],
            [
                'judul' => 'Menambah bidang',
                'gambar' => 'klasis-bidang-form',
                'alt' => 'Form tambah bidang',
                'langkah' => [
                    'Klik **Tambah Bidang**.',
                    'Isi **Nama Bidang** dan **Jumlah Orang dalam Bidang**.',
                    'Klik **Simpan**.',
                ],
                'catatan' => 'Bidang yang masih memiliki jabatan atau program kerja tidak bisa dihapus.',
            ],
        ],
    ],

    'jabatan-klasis' => [
        'judul' => 'Jabatan Klasis',
        'grup' => 'Klasis',
        'izin' => 'jabatan',
        'pengantar' => 'Jabatan adalah posisi di dalam setiap bidang, misalnya Ketua Bidang.',
        'bagian' => [
            [
                'judul' => 'Melihat jabatan',
                'gambar' => 'klasis-jabatan',
                'alt' => 'Daftar jabatan per bidang',
                'langkah' => [
                    'Buka **Kepengurusan > Jabatan**.',
                    'Klik **Tambah Jabatan** pada baris bidang untuk langsung mengisi jabatan di bidang itu.',
                ],
            ],
            [
                'judul' => 'Menambah jabatan',
                'gambar' => 'klasis-jabatan-form',
                'alt' => 'Form tambah jabatan',
                'langkah' => [
                    'Pilih **Bidang**.',
                    'Isi **Nama Jabatan**.',
                    'Klik **Simpan**.',
                ],
                'catatan' => 'Jabatan yang sudah dipakai pengurus tidak bisa dihapus atau dipindahkan ke bidang lain.',
            ],
        ],
    ],

    'pengurus-klasis' => [
        'judul' => 'Pengurus Klasis',
        'grup' => 'Klasis',
        'izin' => 'pengurus',
        'pengantar' => 'Pengurus dipilih dari Data Anggota dan ditempatkan pada jabatan yang sudah dibuat.',
        'bagian' => [
            [
                'judul' => 'Melihat pengurus',
                'gambar' => 'klasis-pengurus',
                'alt' => 'Daftar pengurus per bidang',
                'langkah' => [
                    'Buka **Kepengurusan > Pengurus**.',
                    'Pengurus tampil per bidang, bersama sisa orang yang masih bisa diisi.',
                ],
            ],
            [
                'judul' => 'Menambah pengurus',
                'gambar' => 'klasis-pengurus-form',
                'alt' => 'Form tambah pengurus',
                'langkah' => [
                    'Klik **Tambah Pengurus**.',
                    'Cari nama atau nomor anggota, lalu pilih orangnya.',
                    'Pilih **Jabatan**.',
                    'Tambahkan foto jika ada (maksimal 2 MB), lalu klik **Simpan**.',
                ],
                'catatan' => 'Satu anggota hanya bisa menjadi satu pengurus dalam satu periode.',
            ],
        ],
    ],

    'program-kerja' => [
        'judul' => 'Program Kerja',
        'grup' => 'Klasis',
        'izin' => 'program_kerja',
        'pengantar' => 'Catat program setiap bidang, kegiatan di dalamnya, sampai dokumentasinya.',
        'bagian' => [
            [
                'judul' => 'Melihat program kerja',
                'gambar' => 'klasis-program',
                'alt' => 'Daftar program kerja dan kegiatannya',
                'langkah' => [
                    'Buka **Kepengurusan > Program Kerja**.',
                    'Gunakan filter tahun dan bidang di kanan atas untuk menyaring daftar.',
                ],
            ],
            [
                'judul' => 'Menambah program',
                'gambar' => 'klasis-program-form',
                'alt' => 'Form tambah program',
                'langkah' => [
                    'Klik **Tambah Program**.',
                    'Pilih **Bidang** dan isi **Nama Program**.',
                    'Klik **Simpan**.',
                ],
            ],
            [
                'judul' => 'Menambah kegiatan dan dokumentasi',
                'gambar' => 'klasis-kegiatan-form',
                'alt' => 'Form tambah kegiatan',
                'langkah' => [
                    'Klik **Kegiatan** pada baris program.',
                    'Isi nama kegiatan, deskripsi, dan anggaran, lalu pilih bulan pelaksanaan. Bulan boleh lebih dari satu.',
                    'Klik **Simpan**.',
                    'Setelah kegiatan berjalan, klik **Tambah** di kolom Dokumentasi, lalu unggah foto (JPG atau PNG, maksimal 2 MB) atau isi link.',
                ],
                'catatan' => 'Program yang masih memiliki kegiatan tidak bisa dihapus.',
            ],
        ],
    ],

    // ---------- Layanan ----------
    'inventaris' => [
        'judul' => 'Inventaris',
        'grup' => 'Layanan',
        'izin' => 'inventaris',
        'pengantar' => 'Inventaris mencatat barang organisasi, nilainya, dan kondisinya.',
        'bagian' => [
            [
                'judul' => 'Melihat inventaris',
                'gambar' => 'inventaris-daftar',
                'alt' => 'Daftar inventaris dengan ringkasan nilai dan kondisi',
                'langkah' => [
                    'Klik **Inventaris & Peminjaman > Inventaris**.',
                    'Cari barang berdasarkan kode, nama, atau jenis.',
                ],
            ],
            [
                'judul' => 'Menambah barang',
                'gambar' => 'inventaris-form',
                'alt' => 'Form tambah inventaris',
                'langkah' => [
                    'Klik **Tambah Inventaris**.',
                    'Tambahkan foto jika ada, lalu pilih jenis dan isi nama, harga, serta tanggal perolehan.',
                    'Gulir ke bawah dan isi jumlah barang berdasarkan kondisinya.',
                    'Simpan data.',
                ],
                'teks' => 'Kode inventaris dibuat otomatis berdasarkan jenis yang dipilih.',
            ],
        ],
    ],

    'peminjaman' => [
        'judul' => 'Peminjaman',
        'grup' => 'Layanan',
        'izin' => 'peminjaman',
        'pengantar' => 'Peminjam mengisi form tanpa perlu akun. Pengurus memantau dan menyelesaikan pengembaliannya.',
        'bagian' => [
            [
                'judul' => 'Form untuk peminjam',
                'gambar' => 'publik-peminjaman',
                'alt' => 'Form peminjaman inventaris untuk umum',
                'langkah' => [
                    'Bagikan alamat **/peminjaman** kepada peminjam.',
                    'Peminjam mengisi nama, memilih barang, dan menekan tombol **+** untuk setiap barang.',
                    'Peminjam mengunggah foto pengambilan sebagai bukti, lalu mengirim form.',
                ],
                'teks' => 'Barang yang sedang dipinjam semuanya tidak dapat dipilih.',
            ],
            [
                'judul' => 'Memantau peminjaman',
                'gambar' => 'peminjaman-daftar',
                'alt' => 'Riwayat peminjaman',
                'langkah' => [
                    'Klik **Inventaris & Peminjaman > Peminjaman**.',
                    'Saring berdasarkan status **Dipinjam** atau **Dikembalikan**.',
                ],
            ],
            [
                'judul' => 'Mencatat pengembalian',
                'gambar' => 'peminjaman-detail',
                'alt' => 'Jendela detail peminjaman',
                'langkah' => [
                    'Klik ikon **mata** pada peminjaman yang berstatus Dipinjam.',
                    'Gulir ke bagian **Konfirmasi Pengembalian**, lalu ambil atau pilih foto pengembalian.',
                    'Klik **Selesaikan Pengembalian**.',
                ],
            ],
        ],
    ],

    'absen' => [
        'judul' => 'Absen',
        'grup' => 'Layanan',
        'izin' => 'absen',
        'pengantar' => 'Buat daftar hadir kegiatan yang diisi peserta lewat QR Code, tanpa perlu akun.',
        'bagian' => [
            [
                'judul' => 'Membuat absen',
                'gambar' => 'absen-form',
                'alt' => 'Form buat absen',
                'langkah' => [
                    'Klik **Layanan > Absen**, lalu **Buat Absen**.',
                    'Isi **Nama Kegiatan**.',
                    'Isi **Pilihan Jabatan** dan pisahkan dengan koma, misalnya Peserta, Panitia, Pengurus.',
                    'Klik **Simpan**.',
                ],
            ],
            [
                'judul' => 'Membagikan dan melihat hasil',
                'gambar' => 'absen-daftar',
                'alt' => 'Kartu absen berisi QR Code',
                'langkah' => [
                    'Klik **Unduh QR** untuk mencetak atau membagikan QR Code.',
                    'Klik **sudah absen** untuk melihat daftar peserta yang sudah mengisi.',
                ],
            ],
            [
                'judul' => 'Yang dilihat peserta',
                'gambar' => 'publik-absen',
                'alt' => 'Form absen untuk peserta',
                'langkah' => [
                    'Peserta memindai QR Code.',
                    'Peserta mengisi nama, jenis kelamin, asal jemaat, alamat, dan jabatan.',
                    'Peserta menekan **Kirim**.',
                ],
            ],
        ],
    ],

    'linktree' => [
        'judul' => 'Linktree',
        'grup' => 'Layanan',
        'izin' => 'linktree',
        'pengantar' => 'Satu halaman yang mengumpulkan semua link penting dan bisa dibuka siapa saja.',
        'bagian' => [
            [
                'judul' => 'Mengatur link',
                'gambar' => 'linktree-daftar',
                'alt' => 'Daftar link yang bisa diatur urutannya',
                'langkah' => [
                    'Klik **Layanan > Linktree**.',
                    'Klik **Tambah Link**, isi nama dan alamatnya, lalu simpan.',
                    'Klik **Tambah Sub-judul** untuk membuat judul pemisah antarlink.',
                    'Gunakan tombol **panah** untuk mengatur urutan.',
                ],
            ],
            [
                'judul' => 'Hasil yang dilihat pengunjung',
                'gambar' => 'publik-links',
                'alt' => 'Halaman link untuk umum',
                'teks' => 'Bagikan alamat **/links** kepada siapa saja. Perubahan langsung tampil di halaman ini.',
            ],
        ],
    ],

    'generator-qr' => [
        'judul' => 'Generator QR Code',
        'grup' => 'Layanan',
        'izin' => 'generator_qr',
        'pengantar' => 'Ubah link apa pun menjadi QR Code yang bisa diunduh.',
        'bagian' => [
            [
                'judul' => 'Membuat QR Code',
                'gambar' => 'qr-form',
                'alt' => 'Form buat QR Code',
                'langkah' => [
                    'Klik **Layanan > Generator QR Code**, lalu **Buat QR Code**.',
                    'Isi nama yang mudah dikenali.',
                    'Isi **Link Tujuan**, diawali https:// atau http://.',
                    'Klik **Buat QR Code**.',
                ],
            ],
            [
                'judul' => 'Mengunduh QR Code',
                'gambar' => 'qr-daftar',
                'alt' => 'Daftar QR Code',
                'langkah' => [
                    'Klik **Unduh PNG** pada kartu QR Code.',
                    'Klik ikon **pensil** untuk mengubah, atau ikon **tempat sampah** untuk menghapus.',
                ],
            ],
        ],
    ],

    'golongan-darah' => [
        'judul' => 'Golongan Darah',
        'grup' => 'Layanan',
        'izin' => 'tim_tanggap',
        'pengantar' => 'Saat dibutuhkan donor darah, cari calon pendonor dari seluruh anggota dan hubungi mereka lewat WhatsApp.',
        'bagian' => [
            [
                'judul' => 'Mencari dan menghubungi pendonor',
                'gambar' => 'golongan-darah',
                'alt' => 'Halaman golongan darah dengan pesan WhatsApp dan filter',
                'langkah' => [
                    'Klik menu **Golongan Darah**.',
                    'Sesuaikan **Pesan WhatsApp**, misalnya dengan menambahkan nama rumah sakit. Bagian {nama} dan {golongan} terisi otomatis.',
                    'Saring berdasarkan golongan, rhesus, dan jemaat. Centang **Hanya yang punya nomor telepon** agar yang tampil bisa langsung dihubungi.',
                    'Klik tombol WhatsApp pada anggota yang ingin dihubungi, lalu kirim pesannya.',
                ],
            ],
        ],
    ],

    // ---------- Administrator ----------
    'manajemen-role' => [
        'judul' => 'Manajemen Role',
        'grup' => 'Administrator',
        'izin' => 'admin',
        'pengantar' => 'Role menentukan menu apa saja yang bisa dibuka oleh sebuah akun.',
        'bagian' => [
            [
                'judul' => 'Melihat role',
                'gambar' => 'role-daftar',
                'alt' => 'Daftar role dan menunya',
                'langkah' => [
                    'Klik **Administrasi > Manajemen Role**.',
                    'Lihat menu dan jumlah pengguna setiap role.',
                ],
                'catatan' => 'Role Admin terkunci. Role yang masih dipakai pengguna tidak bisa dihapus.',
            ],
            [
                'judul' => 'Menambah role',
                'gambar' => 'role-form',
                'alt' => 'Form tambah role dengan pilihan menu',
                'langkah' => [
                    'Klik **Tambah Role**.',
                    'Isi **Nama Role** dan centang menu yang boleh dibuka.',
                    'Klik **Simpan**.',
                ],
                'teks' => 'Role dengan **Menu Jemaat** membutuhkan akun yang terikat ke satu jemaat. Role dengan **Menu Kepengurusan** atau Persetujuan Artikel membutuhkan akun yang terikat ke satu periode.',
            ],
        ],
    ],

    'master-periode' => [
        'judul' => 'Master Periode',
        'grup' => 'Administrator',
        'izin' => 'admin',
        'pengantar' => 'Periode adalah masa kepengurusan klasis. Bidang, jabatan, pengurus, dan program kerja tersimpan per periode.',
        'bagian' => [
            [
                'judul' => 'Membuat periode',
                'gambar' => 'periode-form',
                'alt' => 'Form tambah periode dengan pilihan bidang',
                'langkah' => [
                    'Klik **Administrasi > Master Periode**, lalu **Tambah Periode**.',
                    'Isi **Tahun Mulai** dan **Tahun Selesai**.',
                    'Pilih bidang yang langsung dibuat. Minimal satu.',
                    'Klik **Simpan**.',
                ],
            ],
            [
                'judul' => 'Mengelola periode',
                'gambar' => 'periode-daftar',
                'alt' => 'Daftar periode',
                'langkah' => [
                    'Klik **Kelola** untuk masuk ke kepengurusan periode tersebut.',
                    'Gunakan ikon **pensil** untuk mengubah tahunnya.',
                ],
                'catatan' => 'Periode yang masih dipakai pengguna atau masih memiliki bidang dan jabatan tidak bisa dihapus.',
            ],
        ],
    ],

    'master-pengguna' => [
        'judul' => 'Master Pengguna',
        'grup' => 'Administrator',
        'izin' => 'admin',
        'pengantar' => 'Semua akun dibuat oleh Administrator di sini.',
        'bagian' => [
            [
                'judul' => 'Membuat akun',
                'gambar' => 'pengguna-form',
                'alt' => 'Form tambah pengguna',
                'langkah' => [
                    'Klik **Administrasi > Master Pengguna**, lalu **Tambah Pengguna**.',
                    'Isi **Nama**, **Email**, dan **Password** (minimal 6 karakter).',
                    'Pilih **Role**. Kolom **Periode** atau **Jemaat** akan muncul jika role tersebut membutuhkannya.',
                    'Klik **Simpan**, lalu berikan email dan password kepada pemilik akun.',
                ],
            ],
            [
                'judul' => 'Melihat dan mengubah akun',
                'gambar' => 'pengguna-daftar',
                'alt' => 'Daftar pengguna',
                'langkah' => [
                    'Klik ikon **pensil** untuk mengubah data, role, atau password.',
                    'Klik ikon **tempat sampah** untuk menghapus akun.',
                ],
            ],
        ],
    ],

    'audit-log' => [
        'judul' => 'Audit Log',
        'grup' => 'Administrator',
        'izin' => 'admin',
        'pengantar' => 'Audit Log mencatat siapa mengubah apa dan kapan. Catatan ini hanya bisa dibaca.',
        'bagian' => [
            [
                'judul' => 'Menelusuri riwayat',
                'gambar' => 'audit-log',
                'alt' => 'Daftar riwayat aktivitas',
                'langkah' => [
                    'Klik **Administrasi > Audit Log**.',
                    'Cari berdasarkan pelaku, nama data, atau jenis data.',
                    'Saring berdasarkan jenis aksi.',
                    'Klik **Lihat rincian** untuk melihat data yang berubah.',
                ],
            ],
        ],
    ],

    // ---------- Rekap Pendampingan ----------
    'rekap-pendampingan' => [
        'judul' => 'Rekap Pendampingan',
        'grup' => 'Klasis',
        'izin' => 'pendampingan',
        'pengantar' => 'Simpan siapa saja pengurus yang ikut mendampingi kegiatan. Rekap dikirim lewat bot Telegram atau ditempel di halaman ini.',
        'bagian' => [
            [
                'judul' => 'Menulis rekap',
                'langkah' => [
                    'Tulis judul bulan, misalnya **November 2026**, supaya tahunnya terbaca.',
                    'Mulai setiap hari dengan **TANGGAL 02 NOVEMBER**.',
                    'Tulis nama kegiatan di baris yang diawali **\***.',
                    'Tulis yang hadir setelah **Hadir :**, dipisah koma. Kalau terlalu panjang, akhiri baris dengan koma lalu lanjutkan di baris berikutnya.',
                ],
                'catatan' => 'Nama disimpan persis seperti ditulis. "Kak Mikel" dan "Mikel" dihitung sebagai dua nama berbeda.',
            ],
            [
                'judul' => 'Mengirim lewat Telegram',
                'langkah' => [
                    'Kirim **/id** ke bot, lalu berikan ID yang dibalas kepada Administrator untuk didaftarkan.',
                    'Setelah terdaftar, kirim teks rekap ke bot. Bot membalas jumlah kegiatan yang tersimpan.',
                    'Mengirim ulang atau mengedit pesan rekap akan memperbarui daftar hadir, bukan menggandakan kegiatan.',
                ],
                'teks' => 'Tanpa Telegram, tempel teks yang sama di kotak **Tempel rekap** pada menu **Rekap Pendampingan**.',
            ],
        ],
    ],

    // ---------- Pemilihan ----------
    'pemilihan-jemaat' => [
        'judul' => 'Pemilihan Jemaat',
        'grup' => 'Jemaat',
        'izin' => 'jemaat_pemilihan',
        'pengantar' => 'Jalankan pemungutan suara Rapat Anggota. Pemilih tetap maju satu per satu, lalu memilih di layar bilik suara memakai kode di kartunya.',
        'bagian' => [
            [
                'judul' => 'Menyiapkan pemilih',
                'langkah' => [
                    'Buka menu **Pemilihan**, isi nama kegiatan, lalu klik **Buat Kegiatan**.',
                    'Di tab **Peserta**, klik **Pilih anggota** lalu cari dan centang namanya, atau klik **Ambil semua anggota aktif**.',
                    'Hapus nama yang tidak hadir.',
                    'Klik **Unduh Kartu Kode (PDF)**, cetak, gunting, lalu bagikan langsung kepada pemilihnya.',
                ],
                'catatan' => 'Kartu yang hilang atau salah orang: klik **Ubah** pada nama itu, lalu **Ganti kode**. Kode lama langsung tidak berlaku.',
            ],
            [
                'judul' => 'Memilih per jabatan',
                'langkah' => [
                    'Klik **Buka Bilik Suara** di laptop atau HP yang disiapkan di depan ruangan.',
                    'Di tab **Jabatan**, ketik nama jabatan, misalnya Ketua, lalu klik **Buat**.',
                    'Di halaman jabatan, buat tahap pertama. Semua nama sudah tercentang, hapus yang tidak sesuai.',
                    'Klik **Buka Pemungutan Suara**. Pemilih maju satu per satu, mengetik kode, lalu memilih satu nama.',
                    'Setelah semua memilih, klik **Tutup dan Lihat Hasil**.',
                    'Klik **Tetapkan** kalau sudah ada satu nama yang naik. Kalau belum, buat tahap berikutnya: calon tahap sebelumnya sudah tercentang, hapus yang tidak lanjut.',
                    'Setelah jabatan ditetapkan, kembali ke tab **Jabatan** untuk jabatan berikutnya.',
                ],
                'catatan' => 'Pilihan bersifat rahasia. Sistem hanya mencatat siapa yang sudah memilih, bukan siapa yang dipilihnya.',
            ],
        ],
    ],

    'pemilihan-konferensi' => [
        'judul' => 'Pemilihan Konferensi',
        'grup' => 'Klasis',
        'izin' => 'admin',
        'pengantar' => 'Jalankan pemungutan suara Konferensi. Hanya utusan penuh yang memilih, sedangkan calonnya utusan penuh dan pengurus demisioner.',
        'bagian' => [
            [
                'judul' => 'Menyiapkan utusan',
                'langkah' => [
                    'Buka menu **Pemilihan Konferensi**, isi nama kegiatan, lalu klik **Buat Kegiatan**.',
                    'Di tab **Peserta**, klik **+ Utusan** pada kartu jemaat, pilih Utusan Penuh atau Cadangan, lalu cari dan centang nama dari data anggota jemaat itu. Tiap jemaat biasanya punya 3 utusan penuh dan 3 cadangan.',
                    'Pada kartu Pengurus Demisioner, pilih periode lalu klik **Ambil pengurus**.',
                    'Klik **Unduh Kartu Kode (PDF)**. Tiap jemaat ada di halaman sendiri, jadi mudah dibagikan per jemaat. Tombol PDF di kartu jemaat mengunduh kartu jemaat itu saja. Kartu hanya dibuat untuk utusan penuh.',
                ],
                'catatan' => 'Kalau cadangan menggantikan utusan penuh: klik **Ubah** pada keduanya dan tukar perannya, lalu cetak kartu untuk yang naik.',
            ],
            [
                'judul' => 'Memilih per jabatan',
                'langkah' => [
                    'Klik **Buka Bilik Suara** di laptop atau HP yang disiapkan di depan ruangan.',
                    'Di tab **Jabatan**, buat jabatan pertama, misalnya Ketua.',
                    'Di halaman jabatan, buat tahap Penjaringan Bakal Calon. Semua utusan penuh dan pengurus demisioner sudah tercentang.',
                    'Klik **Buka Pemungutan Suara**, lalu utusan penuh maju satu per satu.',
                    'Klik **Tutup dan Lihat Hasil**. Hasil baru tampil setelah ditutup.',
                    'Pilih **Tetapkan** kalau sudah ada satu nama yang naik, atau **Lanjut ke tahap berikutnya** dan hapus centang yang tidak lanjut.',
                    'Ulangi sampai jabatan ditetapkan, lalu buat jabatan berikutnya seperti Sekretaris.',
                ],
                'catatan' => 'Sesuai AD Pasal 12, label **Mutlak** muncul untuk suara setengah ditambah satu. Kalau suara sama setelah dua kali pemungutan, pimpinan sidang yang memutuskan.',
            ],
        ],
    ],

];
