# JARA — Advanced Todo List

Aplikasi web berbasis Laravel untuk mengelola tugas pribadi maupun tim. Pengguna dapat membuat daftar tugas, menambahkan tugas dengan prioritas dan tenggat waktu, menandai tugas selesai, mengundang pengguna lain untuk berkolaborasi dalam satu daftar, serta memantau progres penyelesaian. Admin bertanggung jawab mengelola akun pengguna dalam sistem.

## User Story

**Sebagai pengguna**, saya ingin mengelompokkan tugas ke dalam beberapa daftar dengan prioritas dan tenggat waktu, sehingga saya dapat memantau pekerjaan pribadi maupun tim dalam satu tempat.

**Sebagai pemilik daftar**, saya ingin menambahkan pengguna lain ke dalam daftar tugas saya, sehingga tugas tersebut dapat dikerjakan bersama dan progresnya terpantau.

**Sebagai admin**, saya ingin menambah dan menghapus akun pengguna, sehingga hanya pengguna yang berhak yang dapat mengakses sistem.

## Daftar SRS

| Kode | Deskripsi | Acceptance Criteria |
|---|---|---|
| SRS-01 | Autentikasi pengguna: login, logout, dan proteksi akses halaman. | - Form login memuat field email dan password<br>- Kredensial salah menampilkan pesan error, bukan halaman kosong<br>- Password tersimpan dalam bentuk hash, bukan plain text<br>- Pengguna belum login diarahkan otomatis ke halaman login<br>- Logout mengakhiri sesi dan kembali ke halaman login |
| SRS-02 | Manajemen akun pengguna oleh admin. | - Halaman daftar user menampilkan seluruh pengguna beserta role-nya<br>- Form tambah akun memuat nama, email, password, dan pilihan role<br>- Email duplikat ditolak dengan pesan validasi<br>- Akun dapat dihapus dari daftar<br>- Pengguna non-admin ditolak saat mengakses halaman admin |
| SRS-03 | Membuat dan menghapus daftar tugas secara atomik, dengan otorisasi owner. | - Membuat list menulis ke `todo_lists` dan `list_user` (role `owner`) dalam satu transaksi<br>- Menghapus list menghapus `tasks`, `list_user`, dan `todo_lists` terkait dalam satu transaksi<br>- Kegagalan di tengah proses membatalkan seluruh perubahan (rollback)<br>- Hanya owner yang dapat menghapus list; pengguna lain ditolak dengan 403<br>- Tidak menyisakan data yatim setelah penghapusan |
| SRS-04 | Kolaborasi: menambah dan mengeluarkan anggota daftar. | - Halaman detail list menampilkan daftar anggota beserta role<br>- Owner dapat menambah anggota melalui pilihan pengguna<br>- Pengguna yang sudah menjadi anggota tidak dapat ditambahkan dua kali<br>- Owner dapat mengeluarkan anggota, tetapi owner sendiri tidak dapat dikeluarkan<br>- Hanya owner yang dapat mengelola anggota; pengguna lain ditolak dengan 403 |
| SRS-05 | CRUD tugas, penandaan selesai, dan filter daftar tugas. | - Form tugas memuat judul, deskripsi, prioritas, dan tenggat waktu<br>- Judul wajib diisi; deskripsi dan tenggat boleh dikosongkan<br>- Prioritas dibatasi pada nilai `low`, `medium`, `high`<br>- Tugas dapat diubah, dihapus, dan status selesainya di-toggle<br>- Filter status dan prioritas menghasilkan data yang benar<br>- Nilai filter yang tidak dikenali diperlakukan sebagai "semua" tanpa error<br>- Pengguna yang bukan anggota list ditolak dengan 403 di seluruh aksi tugas |
| SRS-06 | Pemantauan progres dan penanda prioritas tugas. | - Progres menampilkan jumlah tugas selesai dibanding total beserta persentase<br>- List tanpa tugas menampilkan 0% tanpa error pembagian nol<br>- Progres dihitung dari seluruh tugas, tidak berubah saat filter diterapkan<br>- Prioritas ditampilkan sebagai badge dengan warna berbeda<br>- Tugas yang melewati tenggat dan belum selesai diberi penanda<br>- Tugas yang sudah selesai tidak diberi penanda tenggat meski tanggalnya lewat |

## Pembagian Tugas

| Programmer | SRS | Cakupan | Branch |
|---|---|---|---|
| **Haydar** | SRS-01, SRS-02 | Autentikasi & manajemen akun | `feature/auth`, `feature/admin` |
| **Syair** | SRS-03, SRS-04 | Daftar tugas & kolaborasi | `feature/list`, `feature/list-member` |
| **Banyuputra** | SRS-05, SRS-06 | Tugas & progres | `feature/task`, `feature/task-ui` |

## Aspek Keamanan & Integritas Data

Tiga hal ini berlaku lintas seluruh SRS, bukan hanya pada satu fitur.

**Atomisitas.** Proses yang menulis ke lebih dari satu tabel dibungkus `DB::transaction()`. Pembuatan list menulis ke `todo_lists` dan `list_user` sebagai satu unit kerja; penghapusan list menghapus `tasks`, `list_user`, dan `todo_lists` sebagai satu unit. Kegagalan di langkah mana pun membatalkan seluruh perubahan.

**Otorisasi sisi server.** Pengecekan hak akses dilakukan di controller, bukan sekadar menyembunyikan tombol di tampilan, karena permintaan dapat dikirim langsung ke URL tanpa melalui antarmuka.

- Seluruh route berada dalam grup middleware `auth`
- Aksi pengelolaan list dan anggota dibatasi untuk owner
- Aksi tugas dibatasi untuk anggota list terkait, dicek melalui relasi `$task->list`, karena route tugas tidak membawa id list pada URL-nya
- Penolakan menggunakan `abort` dengan status 403

**Validasi input dan pencegahan SQL injection.** Seluruh input form melewati `$request->validate()` sebelum diproses, termasuk pembatasan nilai enum untuk prioritas. Seluruh akses database menggunakan Eloquent ORM dan Query Builder yang memakai prepared statement di baliknya. Tidak ada query yang disusun dengan menyambung string input secara manual.

## Struktur Database

```
users                todo_lists           list_user            tasks
─────                ──────────           ─────────            ─────
id                   id                   id                   id
name                 name                 todo_list_id         todo_list_id
email                owner_id  → users    user_id   → users    title
password             timestamps           role                 description
role                                      timestamps           priority
timestamps                                                     due_date
                                                               is_done
                                                               timestamps
```

**Model dan relasi**

| Model | Keterangan |
|---|---|
| `User` | Pengguna sistem, memiliki kolom `role` untuk membedakan admin dan user biasa |
| `TodoList` | Model utama untuk tabel `todo_lists`, memuat relasi `owner()`, `members()`, dan `tasks()` |
| `TaskList` | Turunan dari `TodoList` dengan nama kelas alternatif, menunjuk tabel yang sama |
| `ListMember` | Keanggotaan pengguna dalam sebuah list beserta role-nya |
| `Task` | Tugas yang selalu berada di dalam sebuah list |

## Menjalankan Proyek

**Prasyarat:** PHP 8.2+ dan Composer.

```bash
git clone https://github.com/ImVinnn/ppk-pertemuan-1.git
cd ppk-pertemuan-1

composer install
cp .env.example .env
php artisan key:generate

php artisan migrate --seed
php artisan serve
```

Buka `http://127.0.0.1:8000` di browser.

Styling menggunakan Tailwind melalui CDN, sehingga tidak ada proses build dan tidak memerlukan npm.

## Struktur Folder

```
ppk-pertemuan-1/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php          # SRS-01
│   │   ├── AdminUserController.php     # SRS-02
│   │   ├── ListController.php          # SRS-03
│   │   ├── ListMemberController.php    # SRS-04
│   │   └── TaskController.php          # SRS-05, SRS-06
│   └── Models/
│       ├── User.php
│       ├── TodoList.php
│       ├── TaskList.php
│       ├── ListMember.php
│       └── Task.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/views/
│   ├── layouts/
│   ├── auth/                           # SRS-01
│   ├── admin/                          # SRS-02
│   ├── lists/
│   │   ├── show.blade.php
│   │   └── partials/
│   │       ├── members.blade.php       # SRS-04
│   │       └── tasks.blade.php         # SRS-05, SRS-06
│   └── tasks/                          # SRS-05
├── routes/
│   ├── web.php                         # entry point
│   ├── auth.php                        # SRS-01, SRS-02
│   ├── list.php                        # SRS-03, SRS-04
│   └── task.php                        # SRS-05, SRS-06
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

**Pembagian wilayah file.** Setiap programmer hanya menyentuh file dalam cakupannya sendiri. Perubahan yang dibutuhkan di luar cakupan dikoordinasikan melalui Project Manager.

**Penamaan variabel.** Plural untuk koleksi (`$tasks`, `$members`, `$lists`), singular untuk objek tunggal (`$task`, `$member`, `$list`).