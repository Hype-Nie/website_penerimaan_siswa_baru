# Website Penerimaan Siswa Baru

Website penerimaan siswa baru berbasis PHP native, MySQL, dan Bootstrap SB Admin 2. Aplikasi mendukung alur pendaftar, admin/panitia, dan kepala sekolah mulai dari registrasi akun sampai publikasi hasil seleksi dan daftar ulang.

## Fitur Utama

- Login memakai email dan password.
- Registrasi akun calon siswa.
- Pengisian formulir pendaftaran dengan konfirmasi sebelum simpan.
- Upload berkas pendaftaran dengan status verifikasi.
- Verifikasi berkas admin, termasuk multi-verifikasi beberapa dokumen dalam satu submit dan satu proses email.
- Proses seleksi admin: `Diterima`, `Tidak Diterima`, atau `Cadangan`.
- Publikasi hasil oleh kepala sekolah sebelum hasil tampil ke pendaftar.
- Daftar ulang untuk pendaftar diterima, termasuk:
  - informasi biaya daftar ulang Rp1.000.000,
  - template surat pernyataan,
  - upload Surat Pernyataan Daftar Ulang, Bukti Transfer Daftar Ulang, dan Pas Foto Terbaru,
  - konfirmasi admin,
  - nomor induk siswa setelah dikonfirmasi.
- Pengumuman admin dengan create, edit, date picker, dan delete.
- Dashboard kepala sekolah dengan rekap status berkas dari database.
- Loading global untuk semua aksi backend.
- Popup konfirmasi global untuk aksi simpan/update/hapus/upload/publikasi. Login hanya memakai loading tanpa konfirmasi.
- Logo website memakai `public/assets/img/logo_utama.png`.

## Struktur Folder

```text
app/
  Core/
  Data/
  Helpers/
bootstrap/
config/
database/
public/
  assets/
    css/
    img/
    js/
    sb-admin/
resources/
  views/
routes/
storage/
  sessions/
  uploads/
tests/
  blackbox/
  fixtures/
  scripts/
docs/
```

## Menjalankan Project

```bash
php -S localhost:8000 -t public
```

Buka:

```text
http://localhost:8000
```

Jika memakai Laragon, project juga bisa dijalankan dari virtual host Laragon selama document root mengarah ke folder `public`.

## Setup Database

1. Buat database:

```text
pendaftaran_siswa
```

2. Import file:

```text
database/pendaftaran_siswa.sql
```

3. Buat `.env` dari `.env.example`, lalu sesuaikan:

```text
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pendaftaran_siswa
DB_USERNAME=root
DB_PASSWORD=
```

Jika database lama sudah pernah dipakai sebelum fitur daftar ulang, jalankan file update berikut sekali melalui phpMyAdmin:

```text
database/update_daftar_ulang.sql
database/update_daftar_ulang_berkas.sql
```

Untuk instalasi baru, cukup import `database/pendaftaran_siswa.sql`.

## Konfigurasi Email

Email dikirim saat admin mengubah status berkas menjadi `Berkas Lengkap` atau `Berkas Tidak Lengkap`.

Contoh konfigurasi Gmail SMTP di `.env`:

```text
MAIL_ENABLED=true
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=akunpsb@gmail.com
MAIL_PASSWORD=app_password_gmail_16_digit
MAIL_FROM=akunpsb@gmail.com
MAIL_FROM_NAME="PSB MI Irsyadul Athfal"
MAIL_TIMEOUT=20
```

Untuk Gmail, aktifkan 2-Step Verification lalu buat App Password. Gunakan App Password pada `MAIL_PASSWORD`, bukan password login Gmail.

Catatan: logo tidak dipasang di template email karena beberapa email client memblokir gambar eksternal atau tidak bisa mengakses URL lokal.

## Akun Demo

Akun awal setelah import SQL:

```text
Admin
email: admin@psb.test
password: password

Kepala Sekolah
email: kepsek@psb.test
password: password

Calon Siswa
email: siswa@psb.test
password: password
```

## Route Halaman

Halaman umum:

```text
?page=beranda
?page=informasi-psb
?page=login
?page=registrasi
```

Calon siswa:

```text
?page=siswa-dashboard
?page=siswa-formulir
?page=siswa-upload-berkas
?page=siswa-hasil
?page=siswa-profil
```

Admin / Panitia:

```text
?page=admin-dashboard
?page=admin-data-pendaftaran
?page=admin-detail-pendaftaran
?page=admin-verifikasi-berkas
?page=admin-proses-seleksi
?page=admin-hasil-seleksi
?page=admin-pengumuman
```

Kepala sekolah:

```text
?page=kepsek-dashboard
?page=kepsek-laporan
?page=kepsek-publikasi
```

Route utilitas:

```text
?page=logout
?page=view-berkas&id={document_id}
?page=view-daftar-ulang-berkas&id={document_id}
?page=template-surat-pernyataan-daftar-ulang
?page=download-template-surat-pernyataan-daftar-ulang
?page=cetak-laporan
?page=export-pdf
?page=export-csv
```

## Alur Singkat

1. Pendaftar registrasi akun dan login.
2. Pendaftar mengisi formulir pendaftaran.
3. Pendaftar upload berkas.
4. Admin memverifikasi berkas dan sistem mencoba mengirim email status berkas.
5. Admin memproses seleksi.
6. Kepala sekolah mempublikasikan hasil.
7. Pendaftar melihat hasil seleksi.
8. Jika diterima, pendaftar melakukan daftar ulang dan upload berkas tambahan.
9. Admin mengonfirmasi daftar ulang.
10. Pendaftar melihat status siswa terdaftar dan nomor induk siswa.

## Blackbox Testing

Project menyediakan automation blackbox berbasis Playwright Chrome Desktop dan dokumen skenario pengujian.

Setup pertama:

```bash
copy .env.blackbox.example .env.blackbox
npm install
npx playwright install chromium
```

Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` di `.env.blackbox`. Jika `php` atau `mysql` belum ada di PATH, isi `PHP_BIN` dan `MYSQL_BIN` dengan path executable Laragon.

Jalankan test:

```bash
npm run test:blackbox
```

Lihat report:

```bash
npm run test:blackbox:report
```

Test akan mereset database `pendaftaran_siswa_blackbox`, menjalankan server PHP lokal di `http://127.0.0.1:8010`, lalu menguji alur utama dari UI. Detail skenario ada di:

```text
docs/blackbox-test-cases.md
```

## Catatan Teknis

- `.env` tidak ikut commit dan dipakai untuk konfigurasi lokal.
- `.env.blackbox` tidak ikut commit dan dipakai untuk test otomatis.
- `storage/uploads` dan `storage/sessions` diabaikan oleh Git.
- Asset template SB Admin berada di `public/assets/sb-admin`.
- Layout utama admin berada di `resources/views/layouts/admin.php`.
- Layout guest berada di `resources/views/layouts/guest.php`.
