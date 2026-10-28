# Ruang Baca — Manajemen Buku

Aplikasi Laravel 12 untuk katalog buku dengan autentikasi Laravel Breeze, otorisasi berbasis role menggunakan Spatie Laravel Permission, dan antarmuka responsif bergaya macOS. UI memakai Bootstrap 6 alpha, Tailwind CSS, serta preferensi tampilan yang tersimpan di browser.

## Fitur

- Halaman pengenalan Ruang Baca sebelum login, dengan tautan masuk dan registrasi.
- Registrasi, login, reset password, dan logout melalui Laravel Breeze.
- Pesan autentikasi berbahasa Indonesia dan tautan kembali ke halaman utama.
- Role `Admin`, `Dosen`, dan `Mahasiswa` dengan pemeriksaan permission di server.
- CRUD buku, pencarian, paginasi, serta halaman pengelolaan role pengguna khusus Admin.
- Halaman Ringkasan terpisah dari katalog, dengan grafik koleksi dan distribusi akun yang dihitung dari data database.
- Profil pengguna dengan foto, nama, email, nomor telepon, dan penggantian password.
- Admin dapat membuat akun Admin, Dosen, dan Mahasiswa serta meninjau permintaan reset password. Pemohon melihat status menunggu, disetujui, atau ditolak pada halaman reset.
- Admin dapat membuat akun dengan password terkonfirmasi dan role pilihan; password disimpan menggunakan hashing Laravel.
- Admin dapat mengaktifkan atau menonaktifkan akun dengan toggle; akun nonaktif tidak dapat login dan sesi database-nya dicabut.
- Admin dapat melihat status sesi Online (aktivitas dalam 5 menit terakhir) dan waktu terakhir aktif setiap akun.
- Tema terang/gelap, warna aksen, pilihan font, skala UI, dan widget jam WIB yang dapat diatur.
- Notifikasi perubahan menghilang otomatis; preferensi UI yang rusak dipulihkan ke nilai bawaan.
- Seeder idempoten untuk role, permission, akun demo, dan contoh buku.

## Hak akses

| Fitur | Admin | Dosen | Mahasiswa |
|---|---:|---:|---:|
| Lihat koleksi | Ya | Ya | Ya |
| Tambah buku | Ya | Ya | Tidak |
| Ubah buku | Ya | Ya | Tidak |
| Hapus buku | Ya | Tidak | Tidak |
| Kelola role pengguna | Ya | Tidak | Tidak |

Tombol yang tidak diizinkan disembunyikan di UI dan setiap route mutasi juga dilindungi permission Laravel di sisi server.

## Menjalankan aplikasi

Persyaratan: PHP 8.2+, Composer, Node.js/npm, dan ekstensi PHP yang dibutuhkan Laravel.

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Secara default, `.env.example` menggunakan MySQL Laragon pada `127.0.0.1:3306`
dengan database `ruang_baca`, pengguna `root`, dan password kosong. Pastikan
MySQL Laragon aktif. Buat database `ruang_baca` terlebih dahulu lewat
phpMyAdmin (`http://localhost/phpmyadmin`) atau jalankan SQL berikut:

```sql
CREATE DATABASE ruang_baca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Setelah itu, jalankan migrasi dan data awal:

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run dev
```

### SMTP lokal untuk reset password

Email reset password memakai SMTP lokal Mailpit. Admin mengatur password awal
secara langsung saat membuat akun. Laragon menyediakan
`C:\laragon\bin\mailpit\mailpit.exe`; jalankan
Mailpit dari menu Laragon atau start-nya melalui Laragon. Jika Mailpit tidak
terdaftar sebagai perintah di terminal, jalankan executable tersebut langsung.
Alternatif jika Docker Desktop aktif:

```powershell
docker run --rm --name ruang-baca-mailpit -p 1025:1025 -p 8025:8025 axllent/mailpit
```

Buka inbox di `http://127.0.0.1:8025`; SMTP tersedia pada `127.0.0.1:1025`.
Konfigurasi `.env` proyek sudah diarahkan ke SMTP lokal. Jika perlu, gunakan:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS=noreply@ruangbaca.test
MAIL_FROM_NAME="Ruang Baca"
```

Setelah mengubah `.env`, jalankan `php artisan config:clear`. Permintaan dari
halaman lupa password masuk ke antrean tinjauan Admin; email berisi tautan reset
hanya dikirim setelah permintaan disetujui. Admin dapat membuka **Pengguna &
role → Akun & permintaan reset** untuk meninjau permintaan atau membuat akun.
Saat halaman reset tetap terbuka, status keputusan diperbarui otomatis. Jika
disetujui, pemohon membuka tautan di email untuk mengatur password baru; password
disimpan sebagai hash ke database aplikasi yang dikonfigurasi (MySQL/phpMyAdmin
di instalasi Laragon ini), bukan sebagai teks biasa.
Pembatasan permintaan reset diterapkan per alamat email, polling status per sesi,
dan tautan per token. Batas cadangan IP yang lebih longgar membantu mencegah
penyalahgunaan tanpa menerapkan batas rendah yang sama kepada peramban berbeda
pada jaringan yang sama.

Jika kredensial MySQL Laragon Anda berbeda, sesuaikan `DB_HOST`, `DB_PORT`,
`DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env`.

Di terminal lain:

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`. Aplikasi memakai MySQL sehingga schema dan data
dapat dilihat melalui phpMyAdmin.

## Akun demo

Semua akun demo menggunakan password `password`:

| Role | Email |
|---|---|
| Admin | `admin@buku.com` |
| Dosen | `dosen@buku.com` |
| Mahasiswa | `mahasiswa@buku.com` |

Akun ini hanya untuk pengembangan dan pengujian. Ganti atau hapus password demo sebelum aplikasi dipublikasikan.

## Pemeriksaan

```bash
php artisan test
npm run build
```

Dokumen tugas mandiri yang menjadi acuan fitur tetap tersedia di [Lembar_Tugas_Mandiri_Laravel_Breeze_Spatie.docx](./Lembar_Tugas_Mandiri_Laravel_Breeze_Spatie.docx).
