# Website Penerimaan Siswa Baru

Project web pendaftaran siswa menggunakan PHP native, MySQL, dan Bootstrap SB Admin 2.

## Struktur Folder

```text
app/
  Controllers/
  Core/
  Data/
  Helpers/
  Models/
bootstrap/
config/
database/
  migrations/
  seeds/
public/
  assets/
    css/
    img/
    js/
    sb-admin/
    vendor/
  index.php
resources/
  views/
    admin/
    auth/
    errors/
    headmaster/
    layouts/
    public/
    student/
routes/
storage/
  logs/
  uploads/
```

## Menjalankan Project

```bash
php -S localhost:8000 -t public
```

Lalu buka:

```text
http://localhost:8000
```

## Setup Database

1. Buka phpMyAdmin.
2. Buat database bernama:

```text
pendaftaran_siswa
```

3. Import file SQL berikut:

```text
database/pendaftaran_siswa.sql
```

Konfigurasi default database ada di:

```text
.env.example
```

Jika konfigurasi MySQL berbeda, buat file `.env` dari `.env.example`, lalu sesuaikan `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`.

Jika database sudah pernah di-import sebelumnya, jalankan file update berikut sekali melalui phpMyAdmin:

```text
database/update_daftar_ulang.sql
```

Jika file update daftar ulang sebelumnya sudah sempat dijalankan, lalu ingin menyesuaikan nama berkas menjadi `Bukti Transfer Daftar Ulang`, jalankan:

```text
database/update_daftar_ulang_berkas.sql
```

## Email Status Berkas

Saat admin mengubah status berkas menjadi `Berkas Lengkap` atau `Berkas Tidak Lengkap`, sistem akan mencoba mengirim email ke pendaftar. Aktifkan pengiriman email di `.env`:

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
```

Untuk Gmail, aktifkan 2-Step Verification di akun Gmail, lalu buat App Password. Gunakan App Password tersebut pada `MAIL_PASSWORD`, bukan password login Gmail biasa.

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
?page=siswa-status
?page=siswa-hasil
?page=siswa-profil
```

Admin / Panitia PSB:

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

## Demo Login

Akun awal setelah import SQL:

```text
Admin:
email: admin@psb.test
password: password

Kepala Sekolah:
email: kepsek@psb.test
password: password

Calon Siswa:
email: siswa@psb.test
password: password
```

Login memakai email dan password dari database, lalu mengarahkan user ke dashboard sesuai role.

## Integrasi SB Admin

Asset template SB Admin disimpan di:

```text
public/assets/sb-admin/
```

Layout utama:

```text
resources/views/layouts/admin.php
```

Jika folder download template di root project sudah tidak diperlukan, folder berikut boleh dihapus:

```text
startbootstrap-sb-admin-2-gh-pages/
```
