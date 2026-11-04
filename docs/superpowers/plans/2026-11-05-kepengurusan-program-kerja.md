# Kepengurusan per Periode dan Program Kerja Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan Manajemen Role, Master Periode dan Pengguna, serta Bidang, Jabatan, Pengurus, dan Program Kerja yang terisolasi per periode.

**Architecture:** Hak akses berbasis tabel `roles` dengan daftar menu JSON, ditegakkan oleh middleware `admin`, `menu:<kunci>`, dan `periode`. Data kepengurusan berada di bawah rute `/kepengurusan/{periode}/...` dan semua query anak dibatasi lewat periode dari URL. Halaman baru mengikuti pola `index` + `getData` + aksi JSON yang sudah ada.

**Tech Stack:** Laravel 12, PHP 8.3, MySQL, Blade + Tailwind, Pest 4.

**Spec:** `docs/superpowers/specs/2026-11-05-kepengurusan-program-kerja-design.md`

## Global Constraints

- **Jangan jalankan tes apa pun sebelum Task 1 Step 2 selesai.** `phpunit.xml` saat ini tidak mengatur database tes, dan `tests/Pest.php` memakai `RefreshDatabase`, sehingga tes akan **menghapus isi database `ppgtkmtr`**.
- CLI PHP tidak punya `pdo_sqlite`, jadi tes memakai MySQL dengan database `ppgtkmtr_testing`.
- Kerjakan di branch `feature/kepengurusan`. Jangan pernah `git add` file `vendor.zip`.
- Tanpa dependensi baru.
- Teks untuk pengguna berbahasa Indonesia.
- **CRUD standar**, dipakai semua master baru, mengikuti `app/Http/Controllers/Admin/InventarisController.php`:
  - `index` mengembalikan view.
  - `getData` mengembalikan JSON `data,total,from,to,current_page,last_page,per_page`, `paginate(25)`, dan menerima parameter `search`.
  - `store`, `show`, `update`, `destroy` mengembalikan JSON `{status, message}`.
  - Penolakan hapus memakai status 422 dengan `status: false`.
- **Halaman admin** ditaruh di `resources/views/pages/admin/`, dengan tabel, modal, dan fetch mengikuti gaya `resources/views/pages/admin/anggota.blade.php`.
- Semua nilai dari pengguna ditampilkan ter-escape.

## Review Focus

1. Jabatan yang sudah punya pengurus diubah centang `terkait_bidang`: harus ditolak, supaya pengurus tidak jadi tidak konsisten. Tes di Task 5.
2. Foto kegiatan diganti, dihapus, atau kegiatannya dihapus: file lama harus ikut hilang dari disk. Tes di Task 7.
3. Link dokumentasi berisi `javascript:alert(1)`: harus ditolak. Tes di Task 7.
4. Pengguna periode login setelah sebelumnya mencoba membuka menu admin: harus masuk ke halaman yang boleh dibuka, bukan 403. Tes di Task 2.
5. Anggaran berisi pemisah ribuan (`1.000.000`) atau melebihi batas kolom: harus ditolak dengan pesan validasi, bukan error 500. Tes di Task 7.

---

### Task 1: Fondasi aman, hapus register, sinkron `password_view`

**Files:**
- Modify: `phpunit.xml`, `routes/auth.php`, `database/factories/UserFactory.php`, `app/Http/Controllers/Auth/PasswordController.php`, `app/Http/Controllers/Auth/NewPasswordController.php`
- Delete: `app/Http/Controllers/Auth/RegisteredUserController.php`, `resources/views/auth/register.blade.php`, `tests/Feature/Auth/RegistrationTest.php`
- Test: `tests/Feature/Auth/RegisterDihapusTest.php`, `tests/Feature/Auth/PasswordViewTest.php`

**Interfaces:**
- Produces: `UserFactory` selalu mengisi `password_view = 'password'`.

- [ ] **Step 1: Buat branch**

Run: `git checkout -b feature/kepengurusan`

- [ ] **Step 2: Siapkan database tes**

Buat database dengan kredensial dari `.env` (Laragon biasanya `root` tanpa password):

```bash
php -r "(new PDO('mysql:host=127.0.0.1','root',''))->exec('CREATE DATABASE IF NOT EXISTS ppgtkmtr_testing');"
```

Di `phpunit.xml`, ganti dua baris SQLite yang dikomentari dengan:

```xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_DATABASE" value="ppgtkmtr_testing"/>
```

Lalu jalankan `php artisan config:clear`, karena config yang ter-cache membuat Laravel mengabaikan nilai dari `phpunit.xml`. Pastikan `bootstrap/cache/config.php` tidak ada sebelum menjalankan tes.

- [ ] **Step 3: Tambahkan `'password_view' => 'password'` di `UserFactory::definition`, lalu catat kondisi awal tes**

Run: `php vendor/bin/pest`
Catat tes yang sudah gagal. `ProfileTest` diperkirakan gagal karena rute profile memang tidak ada, dan kegagalan awal seperti ini di luar cakupan.

- [ ] **Step 4: Tulis tes yang gagal**

```php
test('halaman register sudah tidak ada', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register', [])->assertNotFound();
});

test('ganti password memperbarui password_view', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->from('/')->put('/password', [
        'current_password' => 'password',
        'password' => 'sandi-baru-123',
        'password_confirmation' => 'sandi-baru-123',
    ]);
    expect($user->fresh()->password_view)->toBe('sandi-baru-123');
});

test('reset password memperbarui password_view', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);
    $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'sandi-baru-123',
        'password_confirmation' => 'sandi-baru-123',
    ]);
    expect($user->fresh()->password_view)->toBe('sandi-baru-123');
});
```

- [ ] **Step 5: Pastikan gagal**

Run: `php vendor/bin/pest tests/Feature/Auth/RegisterDihapusTest.php tests/Feature/Auth/PasswordViewTest.php`
Expected: FAIL

- [ ] **Step 6: Implementasi**

Hapus rute register beserta import-nya dan tiga file di daftar Delete. Isi `password_view` dengan password baru di `PasswordController::update` dan di `forceFill` `NewPasswordController::store`.

- [ ] **Step 7: Pastikan lolos**

Run: `php vendor/bin/pest`
Expected: tes baru PASS, tidak ada kegagalan baru dibanding Step 3.

- [ ] **Step 8: Commit**

```bash
git add phpunit.xml routes/auth.php database/factories/UserFactory.php app/Http/Controllers/Auth resources/views/auth tests/Feature/Auth docs/superpowers
git commit -m "feat: hapus register dan sinkronkan password_view"
```

---

### Task 2: Role, periode, dan pembatasan akses

**Files:**
- Create: `database/migrations/2026_11_05_000001_create_roles_and_periodes_tables.php`, `app/Models/Role.php`, `app/Models/Periode.php`, `app/Http/Middleware/CekAdmin.php`, `app/Http/Middleware/CekMenu.php`, `app/Http/Middleware/CekPeriode.php`, `resources/views/pages/akses-kosong.blade.php`
- Modify: `app/Models/User.php`, `bootstrap/app.php`, `routes/web.php`, `resources/views/includes/sidebar.blade.php`, `app/Http/Controllers/Auth/AuthenticatedSessionController.php`, `app/Http/Controllers/Admin/DashboardController.php`, `database/seeders/AdminSeeder.php`, `database/factories/UserFactory.php`, `tests/Feature/Auth/AuthenticationTest.php`
- Test: `tests/Feature/AksesTest.php`

**Interfaces:**
- Produces:
  - `Role::MENU_UMUM`: `['dashboard'=>'Dashboard','inventaris'=>'Inventaris','peminjaman'=>'Peminjaman','data_anggota'=>'Data Anggota','generator_qr'=>'Generator QR']`
  - `Role::MENU_KEPENGURUSAN`: `['program_kerja'=>'Program Kerja','bidang'=>'Bidang','jabatan'=>'Jabatan','pengurus'=>'Pengurus']`
  - `Role` kolom: `nama`, `is_admin` (cast bool), `menus` (cast array); relasi `users()`
  - `Periode` kolom: `tahun_mulai`, `tahun_selesai`; relasi `users()`; accessor `nama` menghasilkan `"2020-2022"`
  - `User::role()`, `User::periode()`, `User::isAdmin(): bool`, `User::bisaMenu(string $menu): bool`, `User::butuhPeriode(): bool`, `User::homeUrl(): string`
  - Alias middleware `admin`, `menu`, `periode`. `periode` membaca parameter rute `{periode}` (route model binding `Periode`).
  - Rute `akses.kosong`, `GET /akses-kosong`, hanya butuh `auth`.
  - Factory: `User::factory()->admin()`, `User::factory()->untukPeriode(Periode $p)`; default role `Pengguna Periode`, `periode_id` null.
  - Rute periode pertama, stub untuk tes dan diisi di Task 5-7: nama rute berawalan `kepengurusan.`, prefix `/kepengurusan/{periode}`.

- [ ] **Step 1: Tulis tes yang gagal**

Helper di awal file: `function roleDengan(array $menus): Role`, membuat role dengan menu tersebut.

```php
test('role admin bisa membuka semua menu', function () {
    $admin = User::factory()->admin()->create();
    foreach (['/dashboard', '/inventaris', '/data-anggota', '/data-peminjaman', '/generator-qr-link'] as $url) {
        $this->actingAs($admin)->get($url)->assertOk();
    }
});

test('role tanpa centang ditolak dan diizinkan setelah dicentang', function () {
    $role = roleDengan([]);
    $user = User::factory()->create(['role_id' => $role->id]);
    $this->actingAs($user)->get('/inventaris')->assertForbidden();
    $role->update(['menus' => ['inventaris']]);
    $this->actingAs($user->fresh())->get('/inventaris')->assertOk();
});

test('pengguna periode dari dashboard diarahkan ke program kerja periodenya', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $user = User::factory()->untukPeriode($p)->create();
    $this->actingAs($user)->get('/dashboard')
        ->assertRedirect(url("/kepengurusan/{$p->id}/program-kerja"));
});

test('pengguna periode ditolak dari periode lain', function () {
    $a = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $b = Periode::create(['tahun_mulai' => 2023, 'tahun_selesai' => 2025]);
    $user = User::factory()->untukPeriode($a)->create();
    $this->actingAs($user)->get("/kepengurusan/{$b->id}/program-kerja")->assertForbidden();
});

test('pengguna tanpa menu yang bisa dibuka diarahkan ke halaman pesan', function () {
    $user = User::factory()->create(); // Pengguna Periode tanpa periode_id
    $this->actingAs($user)->get('/dashboard')->assertRedirect(route('akses.kosong'));
    $this->actingAs($user)->get(route('akses.kosong'))->assertOk()->assertSee('Hubungi admin');
});

test('login mengabaikan intended ke halaman terlarang', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $user = User::factory()->untukPeriode($p)->create();
    $this->get('/inventaris')->assertRedirect('/login');
    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(url("/kepengurusan/{$p->id}/program-kerja"));
});

test('peminjaman publik tetap terbuka tanpa login', function () {
    $this->get('/peminjaman')->assertOk();
});
```

- [ ] **Step 2: Pastikan gagal**

Run: `php vendor/bin/pest tests/Feature/AksesTest.php`
Expected: FAIL

- [ ] **Step 3: Buat migrasi**

Urutan di `up()`:
1. Buat tabel `roles` (`nama` unik, `is_admin` default false, `menus` JSON) dan `periodes` (`tahun_mulai`, `tahun_selesai` smallint, unik berpasangan).
2. Insert role `Admin` (`is_admin` true, `menus` `[]`) dan `Pengguna Periode` (`menus` `["program_kerja","bidang","jabatan","pengurus"]`).
3. Tambahkan `users.role_id` nullable dan `users.periode_id` nullable. Keduanya foreign key `restrictOnDelete`.
4. Isi `role_id` dari kolom `roles`: `admin` ke Admin, `user` ke Pengguna Periode.
5. Jadikan `role_id` wajib dan hapus kolom `roles`.

`down()` membalik urutannya: tambahkan lagi enum `roles`, isi dari `role_id`, hapus kolom dan tabel.

- [ ] **Step 4: Implementasi model dan middleware**

- `User`: di `$fillable` ganti `roles` dengan `role_id` dan `periode_id`.
- `isAdmin()`: `role->is_admin`.
- `bisaMenu($m)`: admin selalu true, selain itu `in_array($m, role->menus)`.
- `butuhPeriode()`: role non-admin yang memuat menu `MENU_KEPENGURUSAN`.
- `homeUrl()`:
  - Admin ke `route('dashboard')`.
  - Jika punya `periode_id`, menu pertama dari `MENU_KEPENGURUSAN` yang dimiliki, ke `/kepengurusan/{id}/<slug>` (`program_kerja` menjadi `program-kerja`).
  - Lalu menu pertama dari `MENU_UMUM` ke rutenya.
  - Selain itu `route('akses.kosong')`.
- `CekAdmin`: 403 jika bukan admin.
- `CekMenu`: 403 jika `!bisaMenu`.
- `CekPeriode`: 403 jika bukan admin dan `periode_id !== $periode->id`.
- `akses-kosong.blade.php`: berisi kalimat `Akun Anda belum memiliki akses. Hubungi admin.`

- [ ] **Step 5: Terapkan ke rute dan alur login**

- `routes/web.php`: `menu:inventaris`, `menu:peminjaman`, `menu:data_anggota`, `menu:generator_qr` pada grup masing-masing.
- `/dashboard` tetap hanya `auth`. `DashboardController::index` melakukan `redirect($user->homeUrl())` jika `!bisaMenu('dashboard')`.
- `AuthenticatedSessionController::store`: admin memakai `redirect()->intended($user->homeUrl())`, selain itu `redirect($user->homeUrl())`. Pengguna non-admin memang tidak mengikuti intended; ini mengatasi Review Focus 4.
- Tambahkan rute stub `GET /kepengurusan/{periode}/program-kerja` bernama `kepengurusan.program_kerja.index` dengan middleware `['periode','menu:program_kerja']`, mengembalikan respons kosong. Diganti di Task 7.
- `AdminSeeder`: ganti `roles` dengan `role_id` dari role `is_admin`.
- `UserFactory`: `role_id` default role `Pengguna Periode`; tambahkan state `admin()` dan `untukPeriode()`.
- `AuthenticationTest`: login memakai `User::factory()->admin()` agar tetap diarahkan ke dashboard.

- [ ] **Step 6: Batasi sidebar**

Bungkus tiap menu di `sidebar.blade.php` dengan `@if(auth()->user()->bisaMenu('<kunci>'))`. Submenu Inventaris & Peminjaman tampil jika salah satu dimiliki. Di atas `$page`, tambahkan `$periodeAktif = request()->route('periode') ?? auth()->user()->periode;`; dipakai Task 3-7.

- [ ] **Step 7: Pastikan lolos**

Run: `php vendor/bin/pest`
Expected: tes baru PASS, tidak ada kegagalan baru.

- [ ] **Step 8: Commit**

```bash
git add app bootstrap database resources routes tests
git commit -m "feat: role berbasis menu dan pembatasan akses per periode"
```

---

### Task 3: Halaman Manajemen Role

**Files:**
- Create: `app/Http/Controllers/Admin/RoleController.php`, `resources/views/pages/admin/role.blade.php`
- Modify: `routes/web.php`, `resources/views/includes/sidebar.blade.php`
- Test: `tests/Feature/RoleTest.php`

**Interfaces:**
- Consumes: `Role::MENU_UMUM`, `Role::MENU_KEPENGURUSAN`, middleware `admin`.
- Produces: rute `role.index|getData|store|show|update|destroy`, prefix `/role`, middleware `admin`.

- [ ] **Step 1: Tulis tes yang gagal**

```php
test('admin membuat role dengan menu tercentang', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->postJson('/role', ['nama' => 'Bendahara', 'menus' => ['inventaris']])->assertOk();
    expect(Role::firstWhere('nama', 'Bendahara')->menus)->toBe(['inventaris']);
});

test('menu tak dikenal ditolak', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->postJson('/role', ['nama' => 'X', 'menus' => ['role']])->assertUnprocessable();
});

test('role admin tidak bisa diubah atau dihapus', function () {
    $admin = User::factory()->admin()->create();
    $id = $admin->role_id;
    $this->actingAs($admin)->putJson("/role/{$id}", ['nama' => 'Y', 'menus' => []])->assertUnprocessable();
    $this->actingAs($admin)->deleteJson("/role/{$id}")->assertUnprocessable();
});

test('role yang masih dipakai tidak bisa dihapus', function () {
    $admin = User::factory()->admin()->create();
    $dipakai = User::factory()->create()->role_id;
    $this->actingAs($admin)->deleteJson("/role/{$dipakai}")->assertUnprocessable();
});

test('role selain admin ditolak', function () {
    $this->actingAs(User::factory()->create())->get('/role')->assertForbidden();
});
```

- [ ] **Step 2: Pastikan gagal**

Run: `php vendor/bin/pest tests/Feature/RoleTest.php`
Expected: FAIL

- [ ] **Step 3: Implementasi `RoleController` (CRUD standar) dan view**

Validasi: `nama` wajib dan unik, maksimal 100 karakter; `menus` array, boleh kosong, tiap item `Rule::in` gabungan kunci kedua konstanta. Form berisi centang dalam dua kelompok, Umum dan Kepengurusan. Tambahkan menu "Manajemen Role" di sidebar, `@if(auth()->user()->isAdmin())`.

- [ ] **Step 4: Pastikan lolos**

Run: `php vendor/bin/pest tests/Feature/RoleTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app routes resources tests
git commit -m "feat: halaman manajemen role"
```

---

### Task 4: Master Periode dan Master Pengguna

**Files:**
- Create: `app/Http/Controllers/Admin/PeriodeController.php`, `app/Http/Controllers/Admin/PenggunaController.php`, `resources/views/pages/admin/periode.blade.php`, `resources/views/pages/admin/pengguna.blade.php`
- Modify: `routes/web.php`, `resources/views/includes/sidebar.blade.php`
- Test: `tests/Feature/PeriodeTest.php`, `tests/Feature/PenggunaTest.php`

**Interfaces:**
- Consumes: `Periode`, `Role`, `User::butuhPeriode()`.
- Produces: rute `periode.*` prefix `/periode`, `pengguna.*` prefix `/pengguna`, keduanya CRUD standar dengan middleware `admin`.

- [ ] **Step 1: Tulis tes yang gagal**

```php
test('periode: pasangan tahun kembar dan tahun terbalik ditolak', function () {
    $admin = User::factory()->admin()->create();
    Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $this->actingAs($admin)->postJson('/periode', ['tahun_mulai' => 2020, 'tahun_selesai' => 2022])->assertUnprocessable();
    $this->actingAs($admin)->postJson('/periode', ['tahun_mulai' => 2025, 'tahun_selesai' => 2024])->assertUnprocessable();
    $this->actingAs($admin)->postJson('/periode', ['tahun_mulai' => 2022, 'tahun_selesai' => 2024])->assertOk();
});

test('periode yang masih punya pengguna tidak bisa dihapus', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    User::factory()->untukPeriode($p)->create();
    $this->actingAs(User::factory()->admin()->create())->deleteJson("/periode/{$p->id}")->assertUnprocessable();
});

test('pengguna: role kepengurusan wajib punya periode', function () {
    $role = Role::firstWhere('nama', 'Pengguna Periode');
    $this->actingAs(User::factory()->admin()->create())->postJson('/pengguna', [
        'name' => 'A', 'email' => 'a@x.id', 'password' => 'rahasia1', 'role_id' => $role->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('periode_id');
});

test('pengguna: dibuat dengan password_view dan password ter-hash', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $role = Role::firstWhere('nama', 'Pengguna Periode');
    $this->actingAs(User::factory()->admin()->create())->postJson('/pengguna', [
        'name' => 'A', 'email' => 'a@x.id', 'password' => 'rahasia1', 'role_id' => $role->id, 'periode_id' => $p->id,
    ])->assertOk();
    $u = User::firstWhere('email', 'a@x.id');
    expect($u->password_view)->toBe('rahasia1')
        ->and(Hash::check('rahasia1', $u->password))->toBeTrue();
});

test('pengguna: role admin tidak bisa diberikan', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->postJson('/pengguna', [
        'name' => 'A', 'email' => 'a@x.id', 'password' => 'rahasia1', 'role_id' => $admin->role_id,
    ])->assertUnprocessable();
});

test('pengguna: akun admin tidak bisa dibuka lewat master pengguna', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->getJson("/pengguna/{$admin->id}")->assertNotFound();
    $this->actingAs($admin)->deleteJson("/pengguna/{$admin->id}")->assertNotFound();
});

test('pengguna: non-admin ditolak dan tidak bisa membaca password_view', function () {
    $this->actingAs(User::factory()->create())->getJson('/pengguna/data')->assertForbidden();
});
```

- [ ] **Step 2: Pastikan gagal**

Run: `php vendor/bin/pest tests/Feature/PeriodeTest.php tests/Feature/PenggunaTest.php`
Expected: FAIL

- [ ] **Step 3: Implementasi `PeriodeController`**

Validasi: tahun integer 1900-2100, `tahun_selesai` >= `tahun_mulai`, pasangan unik. Halaman periode punya tombol "Kelola" menuju `/kepengurusan/{id}/program-kerja`. Hapus ditolak 422 jika punya pengguna. Pengecekan bidang, jabatan, dan rentang tahun program ditambahkan di Task 5 dan 7.

- [ ] **Step 4: Implementasi `PenggunaController`**

- Semua query memakai `User::whereHas('role', fn($q) => $q->where('is_admin', false))`.
- Validasi:
  - `name` wajib.
  - `email` email dan unik.
  - `password` wajib saat tambah, opsional saat ubah, `string|min:6`.
  - `role_id` `Rule::exists('roles','id')->where('is_admin', false)`.
  - `periode_id` nullable dan exists; wajib jika role memuat menu kepengurusan.
- Jika role tidak memuat menu kepengurusan, `periode_id` disimpan null.
- Jika password diisi, simpan ke `password` dan `password_view`.
- `getData` dan `show` menyertakan `password_view`, `role.nama`, dan periode.

Tambahkan menu "Master Periode" dan "Master Pengguna" di sidebar untuk admin.

- [ ] **Step 5: Pastikan lolos**

Run: `php vendor/bin/pest tests/Feature/PeriodeTest.php tests/Feature/PenggunaTest.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add app routes resources tests
git commit -m "feat: master periode dan master pengguna"
```

---

### Task 5: Bidang dan Jabatan per periode

**Files:**
- Create: `database/migrations/2026_11_05_000002_create_bidangs_and_jabatans_tables.php`, `app/Models/Bidang.php`, `app/Models/Jabatan.php`, `app/Http/Controllers/Kepengurusan/BidangController.php`, `app/Http/Controllers/Kepengurusan/JabatanController.php`, `resources/views/pages/admin/bidang.blade.php`, `resources/views/pages/admin/jabatan.blade.php`
- Modify: `app/Models/Periode.php`, `app/Http/Controllers/Admin/PeriodeController.php`, `routes/web.php`, `resources/views/includes/sidebar.blade.php`
- Test: `tests/Feature/BidangJabatanTest.php`

**Interfaces:**
- Consumes: middleware `periode`, `menu`; `$periodeAktif` di sidebar.
- Produces:
  - `Periode::bidangs()`, `Periode::jabatans()`
  - `Bidang`: `periode_id`, `nama`; `Jabatan`: `periode_id`, `nama`, `terkait_bidang` (cast bool)
  - Rute `kepengurusan.bidang.*` dan `kepengurusan.jabatan.*` di `/kepengurusan/{periode}/bidang|jabatan`. Metode controller menerima `Periode $periode, $id`.

- [ ] **Step 1: Tulis tes yang gagal**

```php
test('bidang periode lain tidak bisa dibuka lewat periode sendiri', function () {
    $a = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $b = Periode::create(['tahun_mulai' => 2023, 'tahun_selesai' => 2025]);
    $milikB = $b->bidangs()->create(['nama' => 'Spiritualitas']);
    $user = User::factory()->untukPeriode($a)->create();
    $this->actingAs($user)->getJson("/kepengurusan/{$a->id}/bidang/{$milikB->id}")->assertNotFound();
    $this->actingAs($user)->deleteJson("/kepengurusan/{$a->id}/bidang/{$milikB->id}")->assertNotFound();
});

test('nama bidang boleh sama antarperiode tapi unik dalam periode', function () {
    $a = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $b = Periode::create(['tahun_mulai' => 2023, 'tahun_selesai' => 2025]);
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->postJson("/kepengurusan/{$a->id}/bidang", ['nama' => 'Umum'])->assertOk();
    $this->actingAs($admin)->postJson("/kepengurusan/{$a->id}/bidang", ['nama' => 'Umum'])->assertUnprocessable();
    $this->actingAs($admin)->postJson("/kepengurusan/{$b->id}/bidang", ['nama' => 'Umum'])->assertOk();
});

test('periode yang punya bidang tidak bisa dihapus', function () {
    $p = Periode::create(['tahun_mulai' => 2020, 'tahun_selesai' => 2022]);
    $p->bidangs()->create(['nama' => 'Umum']);
    $this->actingAs(User::factory()->admin()->create())->deleteJson("/periode/{$p->id}")->assertUnprocessable();
});

test('terkait_bidang tidak bisa diubah saat jabatan sudah punya pengurus', function () {
    // Baris Pengurus memakai Pengurus::create; tes ini lolos penuh setelah Task 6.
})->todo();
```

Tes `todo` ini diisi di Task 6 Step 1 setelah model `Pengurus` ada.

- [ ] **Step 2: Pastikan gagal**

Run: `php vendor/bin/pest tests/Feature/BidangJabatanTest.php`
Expected: FAIL

- [ ] **Step 3: Implementasi migrasi, model, controller (CRUD standar), dan view**

- Foreign key `periode_id` memakai `restrictOnDelete`.
- Indeks unik `(periode_id, nama)` di kedua tabel.
- Query memakai `$periode->bidangs()->findOrFail($id)`.
- Validasi unik memakai `Rule::unique(...)->where('periode_id', $periode->id)`.
- Tolak penghapusan periode jika punya bidang atau jabatan.
- Menu sidebar tampil jika `$periodeAktif` ada dan `bisaMenu` terpenuhi.

- [ ] **Step 4: Pastikan lolos**

Run: `php vendor/bin/pest tests/Feature/BidangJabatanTest.php`
Expected: PASS (tes `todo` tetap tercatat)

- [ ] **Step 5: Commit**

```bash
git add app database routes resources tests
git commit -m "feat: master bidang dan jabatan per periode"
```

---

### Task 6: Pengurus

**Files:**
- Create: `database/migrations/2026_11_05_000003_create_pengurus_table.php`, `app/Models/Pengurus.php`, `app/Http/Controllers/Kepengurusan/PengurusController.php`, `resources/views/pages/admin/pengurus.blade.php`
- Modify: `app/Http/Controllers/Kepengurusan/BidangController.php`, `app/Http/Controllers/Kepengurusan/JabatanController.php`, `app/Http/Controllers/AnggotaController.php:238-247`, `routes/web.php`, `resources/views/includes/sidebar.blade.php`
- Test: `tests/Feature/PengurusTest.php`, `tests/Feature/BidangJabatanTest.php`

**Interfaces:**
- Consumes: `Periode::jabatans()`, `Periode::bidangs()`, `Jabatan::terkait_bidang`, `DataAnggota`, `Jemaat`.
- Produces:
  - `Pengurus` (`$table = 'pengurus'`): `periode_id`, `jabatan_id`, `data_anggota_id`, `bidang_id`; relasi `jabatan()`, `anggota()`, `bidang()`
  - `Periode::pengurus()`
  - Rute `kepengurusan.pengurus.*` ditambah `GET /kepengurusan/{periode}/pengurus/anggota?search=` bernama `kepengurusan.pengurus.anggota`, didaftarkan sebelum `/{id}`.

Setup bersama di file tes:
- `periodeLengkap(): array`, membuat periode 2020-2022, jabatan `Ketua` (`terkait_bidang` false), jabatan `Ketua Bidang` (true), dan bidang `Umum`.
- `anggota(string $nama = 'Budi'): DataAnggota`, membuat jemaat dan anggota.

- [ ] **Step 1: Tulis tes yang gagal, dan isi tes `todo` di `BidangJabatanTest`**

```php
test('jabatan terkait bidang wajib punya bidang, yang tidak terkait harus kosong', function () { /* POST tanpa bidang_id → 422 pada 'bidang_id'; Ketua dengan bidang_id → 422 pada 'bidang_id' */ });
test('satu anggota hanya satu jabatan dalam satu periode', function () { /* POST kedua anggota sama → 422 pada 'data_anggota_id' */ });
test('anggota yang sama boleh menjabat di periode berbeda', function () { /* POST ke periode kedua → 200 */ });
test('satu jabatan boleh diisi beberapa orang', function () { /* dua anggota, Ketua Bidang, bidang sama → 200 dua-duanya */ });
test('jabatan atau bidang milik periode lain ditolak', function () { /* jabatan_id periode B lewat URL periode A → 422 */ });
test('pencarian anggota tanpa data pribadi', function () {
    // anggota punya nik; respons memuat keys id,nama,no_anggota,jemaat dan tidak memuat nik/alamat/telepon; maksimal 20 baris dari 25 anggota
});
test('anggota yang menjadi pengurus tidak bisa dihapus', function () { /* DELETE /data-anggota/{id} oleh admin → 422; baris masih ada */ });
test('anggota bukan pengurus tetap bisa dihapus', function () { /* DELETE → 200 */ });
test('bidang dan jabatan yang punya pengurus tidak bisa dihapus', function () { /* DELETE keduanya → 422 */ });
```

Isi `terkait_bidang tidak bisa diubah...`: `PUT` jabatan yang sudah punya pengurus dengan centang berbeda menghasilkan 422 pada `terkait_bidang`, sedangkan mengganti `nama` tanpa mengubah centang menghasilkan 200.

- [ ] **Step 2: Pastikan gagal**

Run: `php vendor/bin/pest tests/Feature/PengurusTest.php tests/Feature/BidangJabatanTest.php`
Expected: FAIL

- [ ] **Step 3: Implementasi migrasi dan model**

Semua foreign key `restrictOnDelete`. Indeks unik `(periode_id, data_anggota_id)`.

- [ ] **Step 4: Implementasi `PengurusController`**

- CRUD standar melalui `$periode->pengurus()`.
- Validasi:
  - `jabatan_id` dan `bidang_id` memakai `Rule::exists(...)->where('periode_id', $periode->id)`.
  - `data_anggota_id` exists dan unik per periode, dengan `ignore` saat update.
  - `bidang_id` `required` jika jabatan `terkait_bidang`, `prohibited` jika tidak.
- `anggota()`:
  - Mengembalikan `DataAnggota::with('jemaat:id,nama')`, disaring `nama`/`no_anggota` LIKE, `limit(20)`.
  - Hasil dipetakan ke `{id, nama, no_anggota, jemaat}` dengan `jemaat` berisi nama jemaat.
  - Tolak akses lewat middleware `menu:pengurus`.
- View: input pencarian dengan daftar hasil dari endpoint ini. Field bidang tampil hanya untuk jabatan `terkait_bidang`.

- [ ] **Step 5: Tambahkan penolakan hapus dan penguncian centang**

- `BidangController::destroy` dan `JabatanController::destroy` menolak 422 jika punya pengurus.
- `JabatanController::update` menolak 422 pada `terkait_bidang` jika nilainya berubah dan sudah ada pengurus.
- `AnggotaController::destroy` menolak 422 dengan JSON `{success:false, message}` jika ada `Pengurus`, sesuai bentuk respons controller itu.

- [ ] **Step 6: Pastikan lolos**

Run: `php vendor/bin/pest tests/Feature/PengurusTest.php tests/Feature/BidangJabatanTest.php`
Expected: PASS, tanpa `todo`

- [ ] **Step 7: Commit**

```bash
git add app database routes resources tests
git commit -m "feat: pengurus per periode dari data anggota"
```

---

### Task 7: Program Kerja dan Kegiatan

**Files:**
- Create: `database/migrations/2026_11_05_000004_create_program_kerjas_and_kegiatans_tables.php`, `app/Models/ProgramKerja.php`, `app/Models/Kegiatan.php`, `app/Http/Controllers/Kepengurusan/ProgramKerjaController.php`, `app/Http/Controllers/Kepengurusan/KegiatanController.php`, `resources/views/pages/admin/program-kerja.blade.php`
- Modify: `app/Models/Periode.php`, `app/Http/Controllers/Kepengurusan/BidangController.php`, `app/Http/Controllers/Admin/PeriodeController.php`, `routes/web.php`, `resources/views/includes/sidebar.blade.php`
- Test: `tests/Feature/ProgramKerjaTest.php`

**Interfaces:**
- Consumes: `Periode::bidangs()`, rute stub `kepengurusan.program_kerja.index` (diganti).
- Produces:
  - `ProgramKerja`: `bidang_id`, `tahun`, `nama`; `ProgramKerja::scopeMilikPeriode($q, Periode $p)`; relasi `bidang()`, `kegiatans()`
  - `Kegiatan`: `program_kerja_id`, `nama`, `deskripsi`, `anggaran`, `bulan` (cast array), `foto`, `link`; `Kegiatan::scopeMilikPeriode($q, Periode $p)`
  - Rute di `/kepengurusan/{periode}`, semua middleware `['periode','menu:program_kerja']`:
    - `GET program-kerja`, `GET program-kerja/data?tahun=&bidang_id=`, `POST program-kerja`, `PUT|DELETE program-kerja/{id}`
    - `POST program-kerja/{id}/kegiatan`, `PUT|DELETE kegiatan/{id}`, `POST kegiatan/{id}/dokumentasi`

Setup tes: helper `programDi(Periode $p): ProgramKerja`, membuat bidang dan program tahun 2021; payload kegiatan valid: `nama`, `deskripsi`, `anggaran = 1000000`, `bulan = [3,4]`.

- [ ] **Step 1: Tulis tes yang gagal**

```php
test('tahun program harus dalam rentang periode', function () { /* periode 2020-2022: 2021 → 200; 2023 → 422 pada 'tahun' */ });
test('bidang dari periode lain ditolak saat membuat program', function () { /* → 422 pada 'bidang_id' */ });
test('program periode lain tidak bisa dibuka atau diisi', function () { /* user periode A: PUT/DELETE program B dan POST kegiatan ke program B lewat URL A → 404 */ });
test('kegiatan periode lain tidak bisa diubah', function () { /* PUT/DELETE/POST dokumentasi kegiatan B lewat URL A → 404 */ });
test('bulan dicentang lebih dari satu tersimpan', function () { /* bulan [3,4] → fresh()->bulan === [3,4] */ });
test('bulan tidak valid ditolak', function () {
    // masing-masing 422 pada 'bulan' atau 'bulan.0': [], [13], [0], [3,3]
});
test('anggaran dengan pemisah ribuan atau terlalu besar ditolak', function () {
    // '1.000.000' → 422 pada 'anggaran'; 9999999999999 → 422 pada 'anggaran'; -1 → 422
});
test('link javascript ditolak, link https diterima', function () {
    // POST dokumentasi link 'javascript:alert(1)' → 422; 'https://drive.google.com/x' → 200
});
test('foto: mengganti menghapus file lama, hapus_foto menghapusnya', function () {
    // Storage::fake('public'); upload a.jpg → ada; upload b.jpg → a hilang, b ada; hapus_foto=1 → b hilang dan foto null
});
test('menghapus kegiatan ikut menghapus fotonya', function () { /* Storage::fake; DELETE kegiatan → file hilang */ });
test('foto bukan gambar atau lebih dari 2 MB ditolak', function () { /* UploadedFile::fake()->create('a.pdf') dan ->image('b.jpg')->size(3000) → 422 */ });
test('program yang punya kegiatan tidak bisa dihapus', function () { /* → 422 */ });
test('bidang yang punya program tidak bisa dihapus', function () { /* → 422 */ });
test('rentang periode tidak bisa dipersempit melewati program', function () {
    // program tahun 2022; PUT periode jadi 2020-2021 → 422; jadi 2020-2023 → 200
});
```

- [ ] **Step 2: Pastikan gagal**

Run: `php vendor/bin/pest tests/Feature/ProgramKerjaTest.php`
Expected: FAIL

- [ ] **Step 3: Implementasi migrasi dan model**

- `program_kerjas`: indeks unik `(bidang_id, tahun, nama)`.
- `kegiatans`: `anggaran` `unsignedBigInteger`, `bulan` JSON, `deskripsi` text, `foto` dan `link` nullable.
- Foreign key `restrictOnDelete`.
- `scopeMilikPeriode` memakai `whereHas` lewat `bidang`.

- [ ] **Step 4: Implementasi `ProgramKerjaController`**

- `index` mengirim `$periode`, daftar bidang, dan daftar tahun dari rentang periode.
- `getData` memuat `bidang` dan `kegiatans`, memfilter `tahun` dan `bidang_id`.
- Validasi: `tahun` integer di antara `tahun_mulai` dan `tahun_selesai`; `bidang_id` `Rule::exists` terbatas periode; `nama` wajib dan unik dalam bidang + tahun.
- `destroy` ditolak 422 jika punya kegiatan.

- [ ] **Step 5: Implementasi `KegiatanController`**

Semua pencarian memakai `milikPeriode($periode)`. Validasi:
- `nama` wajib, `deskripsi` wajib.
- `anggaran` `required|integer|min:0|max:999999999999`.
- `bulan` `required|array|min:1`, `bulan.*` `integer|between:1,12|distinct`.

`dokumentasi()`:
- `foto` `nullable|image|mimes:jpeg,png,jpg|max:2048`, disimpan di folder `kegiatan` pada disk `public`.
- `link` `nullable|url:http,https|max:2048`.
- `hapus_foto` boolean.
- Foto lama dihapus saat diganti atau `hapus_foto` true. `destroy` juga menghapus file.

- [ ] **Step 6: Tambahkan pengecekan silang**

- `BidangController::destroy`: tolak 422 jika punya program.
- `PeriodeController::update`: tolak 422 jika ada program periode itu di luar rentang baru.

- [ ] **Step 7: Implementasi view dan sidebar**

- Filter tahun dan bidang di bagian atas, daftar program, dan kegiatan di dalamnya.
- Form kegiatan memuat 12 centang bulan (Januari-Desember).
- Anggaran memakai `<input type="number" min="0">`, tampilan tabel `Rp` terformat.
- Tombol "Dokumentasi" pada kegiatan untuk foto dan link. Link tampil sebagai `<a target="_blank" rel="noopener">`.
- Menu "Program Kerja" di sidebar, di atas Bidang.

- [ ] **Step 8: Pastikan semua tes lolos**

Run: `php vendor/bin/pest`
Expected: tes baru PASS, tidak ada kegagalan baru dibanding Task 1 Step 3.

- [ ] **Step 9: Coba langsung di browser**

Backup dulu database `ppgtkmtr`, lalu jalankan `php artisan migrate` (jangan `migrate:fresh`). Migrasi ini mengubah `users`, dan pemetaan akun lama ke role hanya teruji di langkah ini karena database tes selalu kosong saat migrasi berjalan. Periksa alur ini:
1. Pastikan akun `admin@gmail.com` masih bisa login sebagai Admin. Lalu buat periode 2020-2022 dan buat pengguna untuk periode itu.
2. Login sebagai pengguna itu, pastikan sidebar hanya menampilkan empat menu.
3. Isi Bidang Umum, jabatan Ketua dan Ketua Bidang, serta pengurusnya.
4. Buat program Perlawatan, kegiatan dengan dua bulan, lalu tambahkan foto dan link.
5. Pastikan foto tampil. Jika tidak, jalankan `php artisan storage:link`.

- [ ] **Step 10: Commit**

```bash
git add app database routes resources tests
git commit -m "feat: program kerja dan kegiatan per bidang"
```
