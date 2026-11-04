# Desain: Kepengurusan per Periode dan Program Kerja

## Tujuan

Aplikasi PPGT saat ini mengelola inventaris, peminjaman, data anggota, dan QR. Fitur ini menambahkan pencatatan kepengurusan dan program kerja untuk setiap periode, misalnya 2020-2022.

Setiap periode dikelola oleh akun penggunanya sendiri, sehingga pengurus satu periode tidak bisa melihat atau mengubah data periode lain. Bidang dan jabatan bisa berbeda di tiap periode, jadi keduanya disimpan per periode, bukan sebagai daftar global.

## Yang termasuk

- Master Periode dan Master Pengguna, dikelola admin utama.
- Master Bidang, Master Jabatan, dan Pengurus, diatur per periode.
- Program Kerja dan kegiatannya, per bidang dan per tahun.
- Satu foto dan satu link dokumentasi untuk setiap kegiatan.
- Pembatasan akses per periode.
- Penghapusan halaman register.
- Manajemen Role: admin membuat role dan mencentang menu yang boleh dibuka.

## Yang tidak termasuk

- Laporan program kerja (diputuskan pemilik).
- Tampilan program kerja atau pengurus di halaman publik.
- Penyalinan program kerja antartahun atau antarperiode.
- Rekap atau total anggaran.
- Galeri foto. Dokumentasi lain cukup lewat link, misalnya Google Drive.

## Yang tidak boleh terjadi

- Pengguna periode bisa membaca atau mengubah data periode lain, termasuk dengan mengganti ID di URL atau mengirim ID milik periode lain lewat form.
- Pengguna periode bisa membuka Inventaris, Peminjaman, Data Anggota, QR, Master Periode, atau Master Pengguna.
- Pengguna periode bisa melihat data pribadi anggota (NIK, alamat, telepon) saat memilih pengurus.
- Data riwayat hilang diam-diam karena data induknya dihapus.

## Keputusan pemilik

1. Periode dan akun pengguna hanya dibuat oleh admin utama.
2. Satu akun memegang satu periode. Satu periode boleh punya beberapa akun.
3. Pengguna periode hanya melihat Bidang, Jabatan, Pengurus, dan Program Kerja untuk periodenya. Admin utama bisa membuka semuanya.
4. Master Pengurus tidak punya daftar nama terpisah. Pengurus dibuat dengan memilih jabatan, lalu memilih anggota dari Data Anggota. Untuk jabatan seperti Ketua Bidang dan Anggota Bidang, pilih juga bidangnya.
5. Satu jabatan boleh diisi beberapa orang, tetapi satu anggota hanya boleh memegang satu jabatan dalam satu periode.
6. KSB masuk sebagai bidang, biasanya bernama Bidang Umum.
7. Program kerja diinput per tahun, dan tahunnya harus berada dalam rentang periode.
8. Satu program bisa punya beberapa kegiatan. Setiap kegiatan punya nama, deskripsi, anggaran, dan bulan pelaksanaan.
9. Bulan pelaksanaan dipilih dengan centang, dan boleh lebih dari satu bulan.
10. Setelah kegiatan dibuat, bisa ditambahkan satu foto dan satu link.
11. Halaman register dihapus.
12. Kolom `password_view` tetap ada.
13. Ada halaman Manajemen Role: admin membuat role sendiri dan mencentang menu yang boleh dibuka role itu.

## Struktur data

| Tabel | Kolom utama | Aturan |
|---|---|---|
| `periodes` | `tahun_mulai`, `tahun_selesai` | Pasangan tahun unik; `tahun_mulai` <= `tahun_selesai` |
| `roles` | `nama`, `is_admin` (boolean), `menus` (JSON daftar kunci menu) | Nama unik; role `is_admin` tidak bisa diubah atau dihapus |
| `users` (perubahan) | `role_id`, `periode_id` nullable; kolom enum `roles` dihapus | `periode_id` wajib jika role memuat menu kepengurusan |
| `bidangs` | `periode_id`, `nama` | Nama unik dalam satu periode |
| `jabatans` | `periode_id`, `nama`, `terkait_bidang` (boolean) | Nama unik dalam satu periode |
| `pengurus` | `periode_id`, `jabatan_id`, `data_anggota_id`, `bidang_id` nullable | `periode_id` + `data_anggota_id` unik: satu anggota hanya memegang satu jabatan dalam satu periode |
| `program_kerjas` | `bidang_id`, `tahun`, `nama` | Nama unik dalam satu bidang dan tahun |
| `kegiatans` | `program_kerja_id`, `nama`, `deskripsi`, `anggaran`, `bulan` (JSON), `foto` nullable, `link` nullable | Lihat validasi |

Validasi yang wajib ada:

- `pengurus.bidang_id` wajib jika jabatannya `terkait_bidang`, dan harus kosong jika tidak.
- `jabatan_id` dan `bidang_id` harus milik periode yang sedang dibuka.
- `program_kerjas.tahun` harus berada dalam rentang periode.
- `kegiatans.bulan` berisi minimal satu angka 1-12, tanpa duplikat.
- `kegiatans.anggaran` berupa bilangan bulat Rupiah, minimal 0.
- `kegiatans.foto` mengikuti aturan foto Inventaris: `jpeg,png,jpg`, maksimal 2 MB, disimpan di disk `public`.
- `kegiatans.link` harus berupa URL dengan skema `http` atau `https`.

## Hak akses

### Manajemen Role

- Migrasi membuat dua role awal: **Admin** (`is_admin`, semua menu dan semua periode) dan **Pengguna Periode** (Bidang, Jabatan, Pengurus, Program Kerja). `roles = admin` dipetakan ke Admin, `roles = user` ke Pengguna Periode, lalu kolom `roles` dihapus. Saat ini `roles` hanya dipakai di `$fillable` dan `AdminSeeder`.
- Menu yang bisa dicentang:
  - **Umum:** Dashboard, Inventaris, Peminjaman, Data Anggota, Generator QR.
  - **Kepengurusan:** Bidang, Jabatan, Pengurus, Program Kerja.
- Master Periode, Master Pengguna, dan Manajemen Role **khusus role Admin** dan tidak bisa dicentang, supaya role lain tidak bisa menaikkan haknya sendiri.
- Role yang masih dipakai pengguna tidak bisa dihapus.
- Satu pengguna memegang satu role.

### Pembatasan

- Rute umum memakai middleware `menu:<kunci>`, yang mengizinkan role Admin atau role yang memuat menu itu. Selain itu, respons 403.
- Rute periode memakai pola `/kepengurusan/{periode}/...` dan dua syarat: role memuat menu tersebut, dan pengguna adalah Admin atau `periode_id`-nya sama dengan periode di URL. Selain itu, respons 403.
- Semua pencarian data anak dilakukan melalui periode dari URL, misalnya `$periode->bidangs()->findOrFail($id)`. Rantai `Kegiatan → ProgramKerja → Bidang → Periode` juga harus dicek.
- Setelah login atau saat membuka `/dashboard` tanpa izin Dashboard, pengguna diarahkan ke menu pertama yang boleh dibuka. Untuk role Pengguna Periode, menu Program Kerja didahulukan.
- Pengguna tanpa menu yang bisa dibuka, termasuk akun lama tanpa `periode_id` padahal rolenya membutuhkan, mendapat pesan yang jelas sampai admin membetulkannya.
- Sidebar hanya menampilkan menu yang diizinkan.
- Admin masuk ke sebuah periode lewat tombol "Kelola" di Master Periode.

## Pemilihan anggota

Pengguna periode tidak punya akses ke Data Anggota, jadi Pengurus perlu endpoint pencarian sendiri. Endpoint ini mengembalikan maksimal 20 hasil dan hanya berisi `id`, `nama`, `no_anggota`, dan nama jemaat. Pencarian memakai `nama` dan `no_anggota`.

## Penghapusan data

Semua penghapusan yang masih punya data turunan ditolak dengan pesan yang jelas (respons 422, seperti Inventaris yang sedang dipinjam):

- Periode yang masih punya pengguna atau bidang/jabatan.
- Bidang yang masih punya pengurus atau program kerja.
- Jabatan yang masih punya pengurus.
- Program kerja yang masih punya kegiatan.
- Data Anggota yang tercatat sebagai pengurus. Ini perlu tambahan di `AnggotaController::destroy`, ditambah foreign key `restrict` di database.
- Rentang periode yang diperkecil sehingga ada program kerja di luar rentang.

File foto ikut dihapus saat kegiatan dihapus atau fotonya diganti.

## Penghapusan register

Hapus rute `register` di `routes/auth.php`, `RegisteredUserController`, `resources/views/auth/register.blade.php`, dan `tests/Feature/Auth/RegistrationTest.php`.

## `password_view`

- Master Pengguna menampilkan `password_view`, hanya kepada admin.
- Membuat atau mengubah password lewat Master Pengguna mengisi `password` dan `password_view` bersamaan.
- `NewPasswordController` dan `PasswordController` saat ini tidak mengisi `password_view`. Keduanya perlu diperbarui agar nilainya tidak basi setelah reset atau ganti password.

## Mengikuti kode yang ada

- Laravel + Blade + Tailwind, dengan halaman di `resources/views/pages/admin/`.
- Pola `index` + `getData` + aksi JSON seperti `InventarisController`.
- Menu ditambahkan di `resources/views/includes/sidebar.blade.php`.
- Tes memakai Pest. `UserFactory` belum mengisi `password_view`, jadi perlu ditambahkan agar tes yang membuat pengguna bisa berjalan.

## Tes minimum

Tes fitur yang gagal jika pembatasan rusak:

- Pengguna periode A mendapat 403 atau 404 saat mengakses data periode B.
- Pengguna periode ditolak dari menu khusus admin.
- `bidang_id` atau `jabatan_id` milik periode lain ditolak saat disimpan.
- Tahun di luar rentang periode ditolak.
- `/register` mengembalikan 404.
- Role tanpa centang sebuah menu ditolak dari menu itu, dan langsung diizinkan setelah dicentang.
- Role selain Admin ditolak dari Master Periode, Master Pengguna, dan Manajemen Role.
- Satu anggota tidak bisa memegang dua jabatan dalam satu periode.

## Keputusan pembuat desain (mohon diperiksa)

1. Pengguna periode diarahkan ke Program Kerja setelah login, karena Dashboard berisi statistik inventaris dan anggota.
2. Bidang Umum diinput manual seperti bidang lain, tidak dibuat otomatis, karena namanya hanya "biasanya" Bidang Umum.
3. Jabatan punya centang "terkait bidang", supaya nama jabatan tetap bebas dan form tahu kapan harus meminta bidang.
4. Batas satu jabatan berlaku per periode, jadi anggota yang sama boleh memegang jabatan lain di periode berbeda.
5. Periode yang sudah lewat tetap bisa diubah, tidak dikunci.
6. Tahun antarperiode boleh bertumpuk, misalnya 2020-2022 dan 2022-2024.
7. Foto dan link tidak wajib, dan bisa diisi kapan saja setelah kegiatan dibuat, tanpa memeriksa bulan pelaksanaan.
8. Data Anggota yang menjadi pengurus tidak bisa dihapus, supaya riwayat kepengurusan tetap utuh.
9. Master Pengguna mengelola semua akun selain role Admin. Akun dengan role buatan sendiri yang tidak memuat menu kepengurusan tidak perlu punya periode.
10. Satu pengguna memegang satu role, dan tiga menu master (Periode, Pengguna, Role) khusus Admin.

## Diserahkan kepada pembuat

Nama controller dan rute, urutan kolom di tabel, serta tampilan detail form, selama mengikuti gaya halaman admin yang sudah ada.
