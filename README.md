# File Management System (Lion Group Technical Test)

Aplikasi pengelolaan dokumen (FMS) perusahaan untuk tes teknis Laravel. Proyek ini berjalan secara lokal dan memakai `laravel-fms.zip` sebagai acuan alur folder, berkas, dan navigasi. Penyesuaian dari templat lama dijelaskan dalam [docs/template-baseline.md](docs/template-baseline.md).

## Fitur

- Autentikasi dan pendaftaran akun dengan Laravel Breeze. Akun baru otomatis berperan sebagai **Viewer**.
- **Administrator**: membuat, mengganti nama, dan menghapus folder kosong; mengelola departemen; mengunggah satu atau beberapa dokumen, mengubah metadata, dan menghapus dokumen.
- **Viewer**: melihat dasbor, folder, detail dokumen, mencari dokumen, menyaring departemen, dan mengunduh berkas. Server menolak perubahan data dari Viewer.
- Folder induk-anak tanpa batas tingkat, lengkap dengan jejak navigasi dan aturan pencegah siklus.
- Dasbor menampilkan 10 berkas terbaru serta jumlah folder, berkas, dan departemen.
- Validasi unggah membatasi ukuran berkas hingga 20 MB. Laravel Storage menyimpan berkas secara privat.
- Daftar dokumen dan departemen memakai paginasi.
- Pratinjau PDF dan gambar tersedia langsung pada halaman detail berkas.
- Folder, berkas, dan departemen memakai penghapusan lunak. Administrator dapat memulihkan atau menghapusnya secara permanen melalui halaman **Sampah**.
- Halaman **Aktivitas** mencatat setiap perubahan data beserta pelaku dan waktunya.

## Mengelola dokumen

Pada halaman **Dokumen**, pilih departemen dan folder, lalu seret satu atau beberapa file ke area upload. Judul awal mengikuti nama file. Untuk mengubah judul, klik nama file pada daftar dokumen, buka halaman detail, lalu ubah bagian **Judul**.

## Arsitektur

Logika bisnis folder, dokumen, dan departemen berada dalam `app/Services/` melalui `FolderService`, `DocumentService`, dan `DepartmentService`. Pengontrol memvalidasi permintaan lalu memanggil layanan terkait. Layanan mencegah siklus folder, melarang penghapusan folder atau departemen yang masih berisi berkas, membersihkan berkas ketika penyimpanan data gagal, serta mencatat aktivitas melalui `ActivityLogger`.

## Teknologi dan kebutuhan

- PHP 8.2+ dengan ekstensi `pdo_pgsql` dan `fileinfo`
- Composer 2
- Node.js 20+ dan npm
- Docker Desktop untuk PostgreSQL lokal (atau PostgreSQL 16 yang sudah tersedia)
- Laravel 11, Vue 3 + Inertia, Tailwind CSS 4 + daisyUI 5, PostgreSQL 16

## Instalasi dan menjalankan di Windows

Jalankan dari folder proyek ini:

```powershell
docker compose up -d --wait
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
php -d extension=pdo_pgsql artisan migrate --seed
npm run build
powershell -ExecutionPolicy Bypass -File .\scripts\run-local.ps1
```

Script `run-local.ps1` mengaktifkan `pdo_pgsql` hanya untuk proses server jika ekstensi belum aktif pada PHP CLI. Aplikasi tersedia di **http://127.0.0.1:8088**. Port dapat diganti dengan `-Port 8090` pada skrip, lalu sesuaikan `APP_URL` di `.env`.

Di macOS/Linux, aktifkan ekstensi `pdo_pgsql` pada PHP lalu jalankan `php artisan serve --host=127.0.0.1 --port=8088`.

## Konfigurasi

`.env.example` memakai PostgreSQL lokal dari `compose.yaml` dengan basis data `fms`, nama pengguna `fms`, dan kata sandi `local_fms_password`. Ganti nilai tersebut untuk lingkungan selain mesin pengembangan. Git tidak melacak `.env`. Pengaturan `FILESYSTEM_DISK=local` menyimpan berkas unggahan dalam penyimpanan privat.

## Akun demo

Setelah `php artisan migrate --seed`:

| Peran | Email | Kata sandi |
| --- | --- | --- |
| Administrator | `admin@example.com` | `password` |
| Viewer | `viewer@example.com` | `password` |

Akun demo hanya untuk pengujian lokal.

## Tes

```powershell
php artisan test
npm run build
```

Tes fitur memakai SQLite dalam memori agar tidak mengubah data PostgreSQL lokal. Verifikasi lokal juga menjalankan migrasi dan seeder pada PostgreSQL melalui `docker compose`.

## Dokumentasi API

Koleksi Postman dan panduan penggunaannya tersedia di [docs/api-documentation.md](docs/api-documentation.md).
