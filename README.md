# JARA — Advanced Todo List

Aplikasi web pengelola tugas pribadi maupun tim. Dibuat untuk Praktikum PPK Pertemuan 2.

**Baca dokumen ini sampai habis sebelum menulis satu baris kode pun.** Sebagian besar masalah merge conflict di sesi singkat seperti ini berasal dari orang yang langsung ngoding tanpa tahu batas wilayahnya.

---

## Daftar Isi

1. [Studi Kasus](#studi-kasus)
2. [Stack & Aturan Teknis](#stack--aturan-teknis)
3. [Cara Setup di Device Kalian](#cara-setup-di-device-kalian)
4. [Struktur Database](#struktur-database)
5. [Pembagian Tugas](#pembagian-tugas)
6. [Peta Kepemilikan File](#peta-kepemilikan-file)
7. [Kontrak Route](#kontrak-route)
8. [Konvensi Penamaan](#konvensi-penamaan)
9. [Alur Kerja Git](#alur-kerja-git)
10. [Format Commit Message](#format-commit-message)
11. [Larangan Keras](#larangan-keras)
12. [Prioritas Pengerjaan](#prioritas-pengerjaan)
13. [Checklist Sebelum Push](#checklist-sebelum-push)

---

## Studi Kasus

Sebuah aplikasi web untuk mengelola tugas pribadi maupun tim. Pengguna dapat membuat, mengelompokkan, dan mengatur tugas ke dalam beberapa daftar (list/project), menetapkan prioritas dan tenggat waktu, serta menandai tugas sebagai selesai. Pemilik daftar dapat menambahkan pengguna lain ke dalam daftar tugasnya agar dapat dikerjakan bersama, dan memantau progres penyelesaian tugas dalam daftar tersebut. Admin bertanggung jawab menambah dan menghapus akun pengguna dalam sistem.

### Keputusan desain yang sudah final

Ini sudah diputuskan PM. Jangan diubah, jangan ditawar, jangan diimprovisasi sendiri.

| Pertanyaan | Keputusan |
|---|---|
| Ada registrasi mandiri? | **Tidak.** Akun hanya dibuat oleh admin. |
| Owner dicatat di `list_members`? | **Ya**, dengan `role = 'owner'`. |
| Anggota boleh CRUD task? | **Boleh.** |
| Anggota boleh edit/hapus list atau kelola anggota? | **Tidak.** Hanya owner. |
| Admin punya dashboard lihat semua list? | **Tidak.** Admin hanya kelola akun. |
| Ada fitur edit user? | **Tidak.** Hanya tambah dan hapus. |
| Task bisa berdiri tanpa list? | **Tidak.** `list_id` wajib diisi. |
| Progres dilihat siapa? | Semua anggota list, bukan hanya owner. |
| Admin bisa buat akun admin lain? | **Bisa**, lewat dropdown role di form tambah user. |

---

## Stack & Aturan Teknis

- **Laravel** + **Blade polos**
- **Bootstrap 5 via CDN** untuk styling
- **SQLite** untuk database (tidak perlu setup MySQL/XAMPP)
- Bahasa UI: **Indonesia**. Bahasa kode dan variabel: **Inggris**.

Yang **tidak** dipakai, dan tidak boleh ditambahkan:

- Breeze, Jetstream, Fortify, Livewire, atau starter kit apa pun
- npm, Vite, Tailwind, atau proses build apa pun
- Package tambahan lewat `composer require`

Alasannya sederhana: setiap tambahan berarti setiap orang harus install ulang, dan `composer.json` / `composer.lock` adalah file yang paling menyakitkan kalau conflict.

---

## Cara Setup di Device Kalian

```bash
# 1. Clone repo
git clone <url-repo>
cd todoList

# 2. Install dependency (ini yang paling lama, jalankan duluan)
composer install

# 3. Siapkan environment
cp .env.example .env
php artisan key:generate

# 4. Siapkan database + data awal
php artisan migrate:fresh --seed

# 5. Jalankan
php artisan serve
```

Buka `http://127.0.0.1:8000`.

### Akun untuk testing

Semua password: `password`

| Email | Role |
|---|---|
| `admin@jara.test` | admin |
| `budi@jara.test` | user |
| `sari@jara.test` | user |

### Catatan penting: login sementara

Selama fitur login (P1) belum di-merge, ada middleware `DummyAuth` yang otomatis me-login-kan kalian sebagai user id 2. Jadi `auth()->user()` sudah bisa dipakai sejak sekarang tanpa menunggu P1 selesai.

Middleware ini akan **dihapus PM** setelah branch `feature/auth-admin` masuk. Jangan bergantung padanya secara permanen, dan jangan menghapusnya sendiri.

---

## Struktur Database

Keempat tabel **sudah dibuat PM**. Migrasi, model, dan seluruh relasi Eloquent sudah lengkap. Kalian tidak perlu — dan tidak boleh — membuat migrasi baru.

```
users                lists                list_members         tasks
─────                ─────                ────────────         ─────
id                   id                   id                   id
name                 name                 list_id   → lists    list_id  → lists
email                owner_id  → users    user_id   → users    title
password             timestamps           role                 description
role                                      timestamps           priority
timestamps                                                     due_date
                                                               is_done
                                                               timestamps
```

**Detail kolom:**

- `users.role` — enum `admin` | `user`, default `user`
- `lists.owner_id` — user yang membuat list, cascade on delete
- `list_members.role` — enum `owner` | `member`, default `member`
- `list_members` — punya unique composite `[list_id, user_id]`, jadi satu user tidak bisa dobel di satu list
- `tasks.priority` — enum `low` | `medium` | `high`, default `medium`
- `tasks.due_date` — date, boleh null
- `tasks.is_done` — boolean, default `false`

### Model dan relasi

> **Nama model untuk tabel `lists` adalah `TaskList`, bukan `List`.** `List` adalah reserved word di PHP dan akan menyebabkan error. Model `TaskList` sudah diberi `protected $table = 'lists';`

| Model | Relasi yang tersedia |
|---|---|
| `User` | `lists()`, `memberships()` |
| `TaskList` | `owner()`, `members()`, `tasks()` |
| `ListMember` | `list()`, `user()` |
| `Task` | `list()` |

Semua relasi ini **sudah ditulis PM**. Kalau kalian merasa butuh relasi tambahan, **jangan tulis sendiri** — bilang ke PM. File model bisa jadi milik orang lain, dan ini penyebab conflict yang gampang dihindari.

---

## Pembagian Tugas

### P1 — Autentikasi & Manajemen Akun

Branch: `feature/auth-admin` · Route file: `routes/auth.php`

1. **Login** — form email + password, proses pakai `Auth::attempt()`
2. **Logout**
3. **Proteksi halaman** — user belum login diarahkan ke halaman login
4. **Hak akses admin** — non-admin ditolak dari halaman admin
5. **Daftar user** — tabel seluruh user di sistem
6. **Tambah akun user** — nama, email, password (di-hash), dropdown role
7. **Hapus akun user**

Jangan bikin form edit user. Studi kasus hanya minta tambah dan hapus.

### P2 — Manajemen Daftar & Kolaborasi

Branch: `feature/list` · Route file: `routes/list.php`

1. **Daftar list** — list milik sendiri + list yang diikuti
2. **Buat list** — pembuat otomatis tercatat di `list_members` dengan `role = 'owner'`
3. **Detail list** — isi controllernya saja, blade-nya sudah disiapkan PM
4. **Edit list**
5. **Hapus list**
6. **Daftar anggota** — di `lists/partials/members.blade.php`
7. **Tambah anggota** — pilih user dari dropdown
8. **Keluarkan anggota**
9. **Hak akses owner** — edit/hapus list dan kelola anggota hanya untuk owner

### P3 — Manajemen Tugas & Progres

Branch: `feature/task` · Route file: `routes/task.php`

1. **Daftar task dalam list** — di `lists/partials/tasks.blade.php`
2. **Tambah task** — judul, deskripsi, prioritas, deadline
3. **Edit task**
4. **Hapus task**
5. **Tandai selesai** — toggle `is_done`
6. **Progress bar** — jumlah selesai / total + persentase
7. **Badge prioritas & penanda lewat tenggat**
8. **Filter task** — berdasarkan status dan prioritas

Poin 7 dan 8 murni tampilan. Kerjakan paling akhir, boleh gugur kalau waktu mepet.

### PM — Fondasi Proyek

Branch: `main`

Inisialisasi Laravel, skema database, model + relasi, seeder, layout & navigasi, struktur route terpisah, dan manajemen merge.

---

## Peta Kepemilikan File

**Aturan mutlak: kalian hanya boleh menyentuh file di baris kalian sendiri.** Kalau butuh sesuatu di file orang lain, bilang ke PM. Jangan edit sendiri, sekecil apa pun perubahannya.

| Pemilik | File |
|---|---|
| **PM — DIKUNCI** | `routes/web.php`, `bootstrap/app.php`, `composer.json`, `composer.lock`, semua migrasi, semua seeder, `layouts/app.blade.php`, `lists/show.blade.php`, method `ListController@show` |
| **P1** | `routes/auth.php`, `AuthController`, `AdminUserController`, `Models/User.php`, `views/auth/*`, `views/admin/*` |
| **P2** | `routes/list.php`, `ListController` (kecuali `show`), `ListMemberController`, `Models/TaskList.php`, `Models/ListMember.php`, `views/lists/index`, `views/lists/create`, `views/lists/edit`, `views/lists/partials/members.blade.php` |
| **P3** | `routes/task.php`, `TaskController`, `Models/Task.php`, `views/tasks/*`, `views/lists/partials/tasks.blade.php` |

### Titik paling rawan: halaman detail list

`lists/show.blade.php` adalah satu-satunya layar yang dipakai bersama P2 dan P3. Karena itu file ini **dikunci PM** dan strukturnya seperti ini:

```blade
<div class="row">
    <div class="col-md-8">
        @include('lists.partials.tasks')    {{-- wilayah P3 --}}
    </div>
    <div class="col-md-4">
        @include('lists.partials.members')  {{-- wilayah P2 --}}
    </div>
</div>
```

P2 hanya menulis di `partials/members.blade.php`. P3 hanya menulis di `partials/tasks.blade.php`. Kalian tidak akan pernah menyentuh file yang sama.

### Kontrak variabel di halaman detail list

Method `ListController@show` **sudah diisi PM dan tidak boleh diubah siapa pun**, termasuk P2. Isinya:

```php
public function show(TaskList $list)
{
    $list->load(['members.user', 'tasks']);

    return view('lists.show', [
        'list'    => $list,
        'members' => $list->members,
        'tasks'   => $list->tasks,
    ]);
}
```

Artinya di dalam partial kalian, variabel yang tersedia adalah `$list`, `$members`, dan `$tasks`. Kalau P3 butuh data hasil filter, olah dari `$tasks` di dalam partial sendiri — jangan mengubah controller.

---

## Kontrak Route

Semua route **sudah didaftarkan PM** dengan nama seperti di bawah. Kalian tinggal mengisi badan method di controller masing-masing. **Jangan mengubah nama route**, karena navbar dan halaman orang lain memanggil nama-nama ini.

| Method + URI | Nama route | Controller | Pemilik |
|---|---|---|---|
| GET `/login` | `login` | AuthController@showLogin | P1 |
| POST `/login` | `login.store` | AuthController@login | P1 |
| POST `/logout` | `logout` | AuthController@logout | P1 |
| GET `/admin/users` | `admin.users.index` | AdminUserController@index | P1 |
| GET `/admin/users/create` | `admin.users.create` | AdminUserController@create | P1 |
| POST `/admin/users` | `admin.users.store` | AdminUserController@store | P1 |
| DELETE `/admin/users/{user}` | `admin.users.destroy` | AdminUserController@destroy | P1 |
| GET `/lists` | `lists.index` | ListController@index | P2 |
| GET `/lists/create` | `lists.create` | ListController@create | P2 |
| POST `/lists` | `lists.store` | ListController@store | P2 |
| GET `/lists/{list}` | `lists.show` | ListController@show | **PM** |
| GET `/lists/{list}/edit` | `lists.edit` | ListController@edit | P2 |
| PUT `/lists/{list}` | `lists.update` | ListController@update | P2 |
| DELETE `/lists/{list}` | `lists.destroy` | ListController@destroy | P2 |
| POST `/lists/{list}/members` | `members.store` | ListMemberController@store | P2 |
| DELETE `/lists/{list}/members/{user}` | `members.destroy` | ListMemberController@destroy | P2 |
| GET `/lists/{list}/tasks/create` | `tasks.create` | TaskController@create | P3 |
| POST `/lists/{list}/tasks` | `tasks.store` | TaskController@store | P3 |
| GET `/tasks/{task}/edit` | `tasks.edit` | TaskController@edit | P3 |
| PUT `/tasks/{task}` | `tasks.update` | TaskController@update | P3 |
| DELETE `/tasks/{task}` | `tasks.destroy` | TaskController@destroy | P3 |
| PATCH `/tasks/{task}/toggle` | `tasks.toggle` | TaskController@toggle | P3 |

Cek kapan saja dengan `php artisan route:list`.

---

## Konvensi Penamaan

**Variabel:** plural untuk koleksi, singular untuk objek tunggal.

```php
$tasks, $members, $lists, $users     // koleksi
$task,  $member,  $list,  $user      // objek tunggal
```

**View:** setiap view wajib extend layout bersama. Jangan ada yang menulis tag `<html>` sendiri.

```blade
@extends('layouts.app')
@section('title', 'Judul Halaman')

@section('content')
    {{-- isi halaman --}}
@endsection
```

**Flash message:** blok alert sudah ada di layout. Setelah memproses form, cukup redirect dengan pesan — jangan bikin alert manual di view.

```php
return redirect()->route('lists.index')->with('success', 'List berhasil dibuat.');
```

Error validasi otomatis tertangkap layout lewat `$errors`, jadi cukup pakai `$request->validate([...])` seperti biasa.

**Branch:** `feature/nama-fitur`, huruf kecil, pemisah tanda hubung.

---

## Alur Kerja Git

```bash
# 1. Clone (sekali di awal)
git clone <url-repo>
cd todoList

# 2. Buat branch kalian sendiri
git checkout -b feature/auth-admin     # P1
git checkout -b feature/list           # P2
git checkout -b feature/task           # P3

# 3. Kerjakan, lalu commit BERTAHAP (jangan sekali borongan di akhir)
git add app/Http/Controllers/ListController.php resources/views/lists/
git commit -m "feat(list): tambah halaman daftar dan form buat list"

# 4. Push ke branch KALIAN, bukan main
git push -u origin feature/list        # push pertama
git push                               # push berikutnya

# 5. Kabari PM kalau fitur sudah siap di-merge
```

**Jangan `git merge` sendiri ke main.** Merge adalah tugas PM. Kalau kalian merge sendiri dan terjadi conflict, PM tidak punya gambaran apa yang terjadi.

**Kalau PM bilang ada update di main** yang kalian butuhkan:

```bash
git add .
git commit -m "wip: simpan progres sebelum sync"
git pull origin main
```

Kalau muncul conflict, **jangan panik dan jangan asal pilih**. Panggil PM.

### Commit bertahap, bukan borongan

Ini bukan saran, ini yang dinilai. Satu commit besar di menit terakhir jauh lebih buruk daripada lima commit kecil yang menceritakan progres. Idealnya kalian commit setiap kali satu bagian kecil selesai dan aplikasi masih jalan.

---

## Format Commit Message

Pakai **Conventional Commits**:

```
<type>(<scope>): <deskripsi singkat>

<body opsional>
```

**Type yang dipakai di proyek ini:**

| Type | Kapan |
|---|---|
| `feat` | menambah fitur baru |
| `fix` | memperbaiki bug |
| `refactor` | rapikan kode tanpa ubah behavior |
| `style` | perubahan tampilan/format saja |
| `docs` | perubahan dokumentasi |
| `chore` | config, dependency, tooling |

**Aturan:**

- Deskripsi pakai kalimat perintah: "tambah fitur login", bukan "menambahkan" atau "sudah menambah"
- Huruf kecil semua, tanpa titik di akhir
- Di bawah 72 karakter
- Satu commit = satu perubahan logis. Jangan gabung banyak fitur dalam satu commit
- Jangan campur type berbeda dalam satu commit

**Contoh yang benar:**

```bash
git commit -m "feat(auth): tambah halaman login dan proses autentikasi"
git commit -m "feat(list): tambah fitur edit dan hapus list"
git commit -m "feat(task): tambah progress bar penyelesaian list"
git commit -m "fix(member): perbaiki validasi user duplikat di list"
```

**Contoh dengan body**, untuk perubahan yang perlu penjelasan:

```bash
git commit -m "fix(list): batasi aksi hapus list hanya untuk owner" -m "Sebelumnya semua anggota bisa menghapus list. Sekarang dicek dulu apakah user adalah owner lewat relasi list_members."
```

Body dipakai kalau perubahannya tidak jelas hanya dari headernya. Isinya menjelaskan **apa** yang berubah dan **kenapa**, bukan mengulang kode dalam bentuk kalimat.

### Contoh rencana commit tiap orang

**P1:**
```
feat(auth): tambah halaman login dan proses autentikasi
feat(auth): tambah logout dan proteksi halaman
feat(admin): tampilkan daftar user
feat(admin): tambah dan hapus akun user
fix(admin): batasi akses halaman admin hanya untuk role admin
```

**P2:**
```
feat(list): tambah halaman daftar dan form buat list
feat(list): tambah edit dan hapus list
feat(member): tampilkan dan tambah anggota ke list
feat(member): tambah fitur keluarkan anggota
fix(list): batasi aksi kelola list hanya untuk owner
```

**P3:**
```
feat(task): tampilkan daftar task dalam list
feat(task): tambah form buat dan simpan task
feat(task): tambah edit, hapus, dan toggle status task
feat(task): tambah progress bar penyelesaian list
style(task): tambah badge prioritas dan penanda lewat tenggat
```

---

## Larangan Keras

1. **Jangan push ke branch `main`.** Push hanya ke branch kalian sendiri.
2. **Jangan sentuh file milik orang lain.** Butuh sesuatu di sana? Bilang ke PM.
3. **Jangan `composer require` apa pun.** Conflict di `composer.lock` sangat menyakitkan.
4. **Jangan bikin migrasi baru.** Skema sudah final. Butuh kolom tambahan? Bilang ke PM.
5. **Jangan commit `.env`.** Isinya rahasia dan bikin conflict.
6. **Jangan commit `database/database.sqlite`.** File binary, conflict-nya tidak bisa diselesaikan manual.
7. **Jangan pakai co-author di commit.** Commit harus murni dari yang mengerjakan.
8. **Jangan merge sendiri ke main.** Itu tugas PM.
9. **Jangan ubah nama route.** Halaman orang lain memanggil nama itu.
10. **Jangan hapus middleware `DummyAuth`.** PM yang akan menghapusnya setelah auth di-merge.

---

## Prioritas Pengerjaan

Kalau waktu habis, yang terpotong harus bagian paling ujung. Urutan dari yang paling wajib:

1. Login (P1)
2. CRUD list (P2)
3. CRUD task (P3)
4. Tandai task selesai (P3)
5. Tambah anggota ke list (P2)
6. CRUD user oleh admin (P1)
7. Progress bar (P3)
8. Badge, filter, penanda tenggat (P3) — **boleh gugur**

### Yang paling penting dipahami

Di dokumen praktikum tertulis jelas: **aplikasi jalan atau tidak bukan parameter utama.** Penilaian tertinggi ada di kerapian project — branching, commit message, dan pembagian kerja.

Artinya kalau di menit ke-50 kalian harus memilih antara "fitur selesai tapi commit berantakan" versus "fitur setengah jalan tapi git rapi", **pilih yang kedua tanpa ragu.**

### Kalau selesai lebih cepat

Jangan bantu di file orang lain — itu justru mengundang conflict di menit rawan. Kerjakan yang aman di wilayah sendiri:

- Lengkapi validasi input yang belum ada
- Perbaiki pesan error supaya lebih jelas
- Tambah konfirmasi sebelum aksi hapus
- Rapikan tampilan halaman sendiri
- Mulai tulis flow kode di kertas lebih awal

---

## Checklist Sebelum Push

Jalankan ini setiap kali mau push:

```bash
php artisan migrate:fresh --seed   # pastikan tidak error
php artisan serve                  # pastikan halaman kalian jalan
git status                         # pastikan tidak ada .env atau .sqlite
git log --oneline                  # pastikan commit message rapi
```

Cek juga:

- [ ] Saya di branch sendiri, bukan `main` (`git branch` — tanda `*` ada di branch saya)
- [ ] Semua file yang saya ubah adalah milik saya (cek peta kepemilikan)
- [ ] Commit message pakai format Conventional Commits
- [ ] Commit terpecah bertahap, bukan satu commit borongan
- [ ] Tidak ada `dd()`, `dump()`, atau `console.log` yang tertinggal
- [ ] View saya `@extends('layouts.app')`, bukan bikin HTML sendiri

---

## Deliverable Perorangan

Selain kode, tiap programmer wajib **menulis tangan flow kode bagian yang dikerjakan**, dikumpulkan ke asisten praktikum di akhir sesi. Sisihkan waktu untuk ini, jangan dikerjakan di menit terakhir sambil panik.

---

**Ada yang tidak jelas? Tanya PM sebelum ngoding, bukan setelah conflict.**
