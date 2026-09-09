# JARA — Advanced Todo List

Aplikasi web berbasis Laravel untuk mengelola tugas pribadi maupun tim. Pengguna dapat membuat daftar tugas (list), menambahkan tugas dengan prioritas dan tenggat waktu, menandai tugas sebagai selesai, mengundang pengguna lain untuk berkolaborasi dalam satu daftar, serta memantau progres penyelesaian. Admin bertanggung jawab mengelola akun pengguna dalam sistem.

## User Story

**Sebagai pengguna**, saya ingin mengelompokkan tugas saya ke dalam beberapa daftar dengan prioritas dan tenggat waktu, sehingga saya dapat memantau pekerjaan pribadi maupun tim dalam satu tempat.

**Sebagai pemilik daftar**, saya ingin menambahkan pengguna lain ke dalam daftar tugas saya, sehingga tugas tersebut dapat dikerjakan bersama dan progresnya terpantau.

**Sebagai admin**, saya ingin menambah dan menghapus akun pengguna, sehingga hanya pengguna yang berhak yang dapat mengakses sistem.

## Daftar SRS

| Kode | Deskripsi | Acceptance Criteria |
|---|---|---|
| SRS-001 | Fondasi proyek: skema database, model & relasi, seeder, layout, dan struktur route. | - Migrasi menghasilkan 4 tabel: `users`, `lists`, `list_members`, `tasks`<br>- Model `User`, `TaskList`, `ListMember`, `Task` beserta seluruh relasi Eloquent tersedia<br>- Seeder menghasilkan 3 user, 2 list, dan minimal 5 task<br>- `layouts/app.blade.php` memuat navbar dan blok flash message terpusat<br>- Route terpisah per modul (`auth.php`, `list.php`, `task.php`)<br>- `php artisan migrate:fresh --seed` berjalan tanpa error |
| SRS-002 | Autentikasi pengguna dan proteksi halaman. | - Form login memuat field email dan password<br>- Kredensial salah menampilkan pesan error, bukan halaman kosong<br>- Password tersimpan dalam bentuk hash, bukan plain text<br>- Pengguna belum login diarahkan otomatis ke halaman login<br>- Logout mengakhiri sesi dan kembali ke halaman login |
| SRS-003 | Manajemen akun pengguna oleh admin. | - Halaman `/admin/users` menampilkan seluruh pengguna beserta role-nya<br>- Form tambah akun memuat nama, email, password, dan dropdown role<br>- Email duplikat ditolak dengan pesan validasi<br>- Akun dapat dihapus dari daftar<br>- Pengguna non-admin ditolak saat mengakses halaman admin |
| SRS-004 | Membuat dan mengelola daftar tugas (list). | - Halaman `/lists` menampilkan list milik sendiri dan list yang diikuti<br>- Membuat list otomatis mencatat pembuat di `list_members` dengan `role = owner`<br>- Nama list dapat diubah oleh owner<br>- Menghapus list ikut menghapus seluruh task dan keanggotaannya<br>- Tombol edit dan hapus tidak muncul bagi non-owner |
| SRS-005 | Kolaborasi: menambah dan mengeluarkan anggota daftar. | - Halaman detail list menampilkan daftar anggota beserta role<br>- Owner dapat menambah anggota melalui dropdown pengguna<br>- Pengguna yang sudah menjadi anggota tidak dapat ditambahkan dua kali<br>- Owner dapat mengeluarkan anggota, tetapi owner sendiri tidak dapat dikeluarkan<br>- Pengguna bukan anggota ditolak saat mengakses detail list |
| SRS-006 | Membuat, mengubah, menghapus, dan menandai tugas selesai. | - Form tambah task memuat judul, deskripsi, prioritas, dan tenggat waktu<br>- Judul wajib diisi, tenggat dan deskripsi boleh dikosongkan<br>- Task tampil dalam halaman detail list sesuai list-nya<br>- Task dapat diubah dan dihapus<br>- Status selesai dapat di-toggle dan perubahannya tersimpan di database |
| SRS-007 | Pemantauan progres penyelesaian tugas. | - Progress bar menampilkan jumlah task selesai dibanding total beserta persentase<br>- List tanpa task menampilkan 0% tanpa error pembagian nol<br>- Prioritas ditampilkan sebagai badge dengan warna berbeda<br>- Task yang melewati tenggat dan belum selesai diberi penanda visual<br>- Daftar task dapat difilter berdasarkan status dan prioritas |

## Pembagian Tugas

| Anggota | Peran | SRS | Branch |
|---|---|---|---|
| — | Project Manager | SRS-001 | `main` |
| — | Programmer 1 | SRS-002, SRS-003 | `feature/auth-admin` |
| — | Programmer 2 | SRS-004, SRS-005 | `feature/list` |
| — | Programmer 3 | SRS-006, SRS-007 | `feature/task` |

## Struktur Database

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

**Keterangan kolom enum:**

- `users.role` — `admin` | `user`
- `list_members.role` — `owner` | `member`, dengan unique composite `[list_id, user_id]`
- `tasks.priority` — `low` | `medium` | `high`

**Relasi Eloquent:**

| Model | Relasi |
|---|---|
| `User` | `lists()`, `memberships()` |
| `TaskList` | `owner()`, `members()`, `tasks()` |
| `ListMember` | `list()`, `user()` |
| `Task` | `list()` |

> Model untuk tabel `lists` dinamai `TaskList` karena `List` merupakan reserved word di PHP.

## Menjalankan Proyek

**Prasyarat:** PHP 8.2+, Composer.

```bash
# Clone repository
git clone https://github.com/username/todoList.git
cd todoList

# Install dependency
composer install

# Siapkan environment
cp .env.example .env
php artisan key:generate

# Siapkan database dan data awal
php artisan migrate:fresh --seed

# Jalankan
php artisan serve
```

Buka `http://127.0.0.1:8000` di browser.

Proyek menggunakan **SQLite**, sehingga tidak memerlukan MySQL maupun XAMPP. Styling menggunakan **Bootstrap 5 via CDN**, sehingga tidak ada proses build dan tidak memerlukan npm.

### Akun untuk pengujian

| Email | Password | Role |
|---|---|---|
| `admin@jara.test` | `password` | admin |
| `budi@jara.test` | `password` | user |
| `sari@jara.test` | `password` | user |

## Struktur Folder

```
todoList/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php          # SRS-002
│   │   │   ├── AdminUserController.php     # SRS-003
│   │   │   ├── ListController.php          # SRS-004
│   │   │   ├── ListMemberController.php    # SRS-005
│   │   │   └── TaskController.php          # SRS-006, SRS-007
│   │   └── Middleware/
│   └── Models/
│       ├── User.php
│       ├── TaskList.php
│       ├── ListMember.php
│       └── Task.php
├── database/
│   ├── migrations/                         # skema 4 tabel
│   └── seeders/                            # data awal
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php                   # layout bersama
│   ├── auth/                               # SRS-002
│   ├── admin/                              # SRS-003
│   ├── lists/
│   │   ├── show.blade.php                  # halaman detail list
│   │   └── partials/
│   │       ├── members.blade.php           # SRS-005
│   │       └── tasks.blade.php             # SRS-006, SRS-007
│   └── tasks/                              # SRS-006
├── routes/
│   ├── web.php                             # entry point
│   ├── auth.php                            # SRS-002, SRS-003
│   ├── list.php                            # SRS-004, SRS-005
│   └── task.php                            # SRS-006, SRS-007
├── .gitignore
└── README.md
```

Route dipisah per modul untuk menghindari merge conflict, karena `routes/web.php` merupakan file yang paling sering disentuh banyak orang secara bersamaan.

## Konvensi Pengembangan

**Branching.** Branch `main` dijaga sebagai versi stabil. Setiap fitur dikerjakan di branch terpisah dengan prefix `feature/`, lalu di-merge oleh Project Manager.

**Commit message.** Menggunakan format Conventional Commits:

```
<type>(<scope>): <deskripsi singkat>

<body opsional>
```

Type yang digunakan: `feat`, `fix`, `refactor`, `style`, `docs`, `chore`.

```bash
git commit -m "feat(list): tambah halaman daftar dan form buat list"
git commit -m "fix(member): perbaiki validasi user duplikat di list"
```

**Penamaan variabel.** Plural untuk koleksi (`$tasks`, `$members`, `$lists`), singular untuk objek tunggal (`$task`, `$member`, `$list`).

**View.** Seluruh view meng-`@extends('layouts.app')`. Flash message ditangani terpusat di layout melalui `session('success')` dan `$errors`.
