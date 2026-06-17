# Blackbox Test Cases Website PSB

Dokumen ini mendefinisikan skenario pengujian dari sudut pandang pengguna. Pengujian otomatis memakai Playwright Chrome Desktop, sedangkan kolom manual dipakai untuk bagian yang perlu dicek visual atau bergantung layanan eksternal.

## Cara Menjalankan Automation

1. Salin `.env.blackbox.example` menjadi `.env.blackbox`.
2. Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` jika MySQL lokal berbeda.
3. Install dependency Node:

```bash
npm install
npx playwright install chromium
```

4. Jalankan test:

```bash
npm run test:blackbox
```

5. Lihat report:

```bash
npm run test:blackbox:report
```

Database test otomatis direset dari `database/pendaftaran_siswa.sql`. Gunakan database khusus yang berakhiran `_blackbox` atau `_test`.

## Test Matrix

| ID | Area | Skenario | Expected Result | Mode |
| --- | --- | --- | --- | --- |
| BB-001 | Public | Buka beranda | Hero, alur pendaftaran, dan pengumuman tampil | Auto |
| BB-002 | Public | Buka informasi PSB | Jadwal, persyaratan, tahapan, dan kontak tampil | Auto |
| BB-003 | Auth | Login dengan email/password salah | Tetap di login dan muncul pesan gagal | Auto |
| BB-004 | Auth | Login siswa/admin/kepsek | Masuk ke dashboard sesuai role | Auto |
| BB-005 | Auth | Login submit | Tidak muncul popup konfirmasi, hanya loading | Auto |
| BB-006 | Registrasi | Daftar akun calon siswa baru | Akun dibuat dan diarahkan ke dashboard siswa | Auto |
| BB-007 | Formulir | Siswa isi formulir lengkap | Popup konfirmasi tampil, data tersimpan, halaman berubah jadi ringkasan | Auto |
| BB-008 | Upload Berkas | Siswa upload semua dokumen wajib | Popup konfirmasi dan loading tampil, status menjadi menunggu verifikasi | Auto |
| BB-009 | Admin Data | Admin cari pendaftar | Data pendaftar yang dicari tampil di tabel | Auto |
| BB-010 | Verifikasi Berkas | Admin verifikasi multi dokumen sekaligus | Status berkas tersimpan dan hanya satu submit email diproses | Auto |
| BB-011 | Email | SMTP nonaktif saat verifikasi | Status tetap tersimpan dan muncul warning email belum terkirim | Auto |
| BB-012 | Seleksi | Admin ubah status seleksi menjadi Diterima | Status seleksi tersimpan | Auto |
| BB-013 | Hasil Siswa | Siswa cek hasil sebelum publikasi | Status tampil Belum Diumumkan | Auto |
| BB-014 | Kepsek | Kepsek publikasi hasil | Popup konfirmasi tampil dan status publikasi menjadi Sudah Dipublikasikan | Auto |
| BB-015 | Hasil Siswa | Siswa diterima cek hasil setelah publikasi | Highlight Diterima tampil jelas, daftar ulang tersedia | Auto |
| BB-016 | Daftar Ulang | Siswa upload berkas tambahan daftar ulang | Status daftar ulang menjadi Dikirim | Auto |
| BB-017 | Admin Daftar Ulang | Admin cek dan konfirmasi daftar ulang | Status menjadi Dikonfirmasi dan nomor induk siswa dibuat | Auto |
| BB-018 | Status Akhir | Siswa cek hasil setelah konfirmasi daftar ulang | Muncul info siswa terdaftar dan nomor induk siswa | Auto |
| BB-019 | Pengumuman | Admin buat pengumuman | Data baru muncul di daftar pengumuman | Auto |
| BB-020 | Pengumuman | Admin edit pengumuman | Form edit terisi data lama dan update tersimpan | Auto |
| BB-021 | Pengumuman | Admin hapus pengumuman | Popup hapus tampil dan data hilang dari daftar | Auto |
| BB-022 | UI Global | Form POST selain login | Popup konfirmasi muncul sebelum loading | Auto |
| BB-023 | UI Global | Form GET pencarian | Tidak muncul popup konfirmasi/loading | Auto |
| BB-024 | Laporan | Kepsek download CSV | File CSV terdownload/response CSV dan berisi data pendaftar | Auto |
| BB-025 | Laporan | Kepsek cetak/export PDF | Tampilan laporan terbuka dan browser memproses print/PDF | Manual |
| BB-026 | Responsive | Halaman utama di mobile | Elemen tidak overlap dan form tetap bisa digunakan | Manual/Next |
| BB-027 | Email Real | SMTP Gmail aktif | Email benar-benar masuk ke inbox tujuan | Manual |

## Catatan Batasan

- Test otomatis tidak menguji inbox email asli agar tidak bergantung jaringan/Gmail.
- Test otomatis tidak memakai database manual; semua data dibuat ulang di database blackbox.
- Assertion otomatis fokus ke hasil yang terlihat oleh pengguna: teks, status, redirect, popup, dan download/link.
