const path = require('path');
const fs = require('fs');
const { test, expect } = require('@playwright/test');

const student = {
  name: 'Bima Blackbox',
  email: 'blackbox.student@example.test',
  password: 'Password123!',
  nisn: '9988776655',
};

const admin = {
  email: 'admin@psb.test',
  password: 'password',
};

const headmaster = {
  email: 'kepsek@psb.test',
  password: 'password',
};

const fixtureFile = path.resolve(__dirname, '../fixtures/files/blackbox-document.pdf');

async function confirmAction(page, acceptName = /^Ya/) {
  await expect(page.locator('[data-confirm-overlay].is-visible')).toBeVisible();
  await page.getByRole('button', { name: acceptName }).click();
}

async function logout(page) {
  await page.goto('/?page=logout');
  await expect(page).toHaveURL(/page=login/);
}

async function login(page, email, password, expectedText) {
  await page.goto('/?page=login');
  await page.getByPlaceholder('Masukkan Email...').fill(email);
  await page.getByPlaceholder('Password').fill(password);
  await page.getByRole('button', { name: 'Login' }).click();
  await expect(page.locator('[data-confirm-overlay].is-visible')).toHaveCount(0);
  await expect(page.getByText(expectedText).first()).toBeVisible();
}

async function setAllFileInputs(page) {
  const fileInputs = page.locator('input[type="file"]');
  const count = await fileInputs.count();

  expect(count).toBeGreaterThan(0);

  for (let index = 0; index < count; index++) {
    await fileInputs.nth(index).setInputFiles(fixtureFile);
  }
}

async function submitWithConfirm(page, buttonName, acceptName = /^Ya/) {
  await page.getByRole('button', { name: buttonName }).click();
  await confirmAction(page, acceptName);
}

test.describe.serial('Blackbox alur utama PSB', () => {
  test('halaman publik dan validasi login gagal berjalan', async ({ page }) => {
    await page.goto('/');
    await expect(page.getByRole('heading', { name: /Selamat Datang/i })).toBeVisible();
    await expect(page.getByText('Alur Pendaftaran')).toBeVisible();

    await page.goto('/?page=informasi-psb');
    await expect(page.getByRole('heading', { name: /Jadwal, Persyaratan, dan Tahapan Seleksi/i })).toBeVisible();

    await page.goto('/?page=login');
    await page.getByPlaceholder('Masukkan Email...').fill('salah@example.test');
    await page.getByPlaceholder('Password').fill('password-salah');
    await page.getByRole('button', { name: 'Login' }).click();
    await expect(page.locator('[data-confirm-overlay].is-visible')).toHaveCount(0);
    await expect(page.getByText('Email atau password salah.')).toBeVisible();
  });

  test('siswa registrasi, isi formulir, dan upload berkas', async ({ page }) => {
    await page.goto('/?page=registrasi');
    await page.locator('[name="name"]').fill(student.name);
    await page.locator('[name="birth_place"]').fill('Lamongan');
    await page.locator('[name="birth_date"]').fill('2018-08-19');
    await page.locator('label[for="genderMale"]').click();
    await expect(page.locator('#genderMale')).toBeChecked();
    await page.locator('[name="religion"]').selectOption({ label: 'Islam' });
    await page.locator('[name="address"]').fill('Jl. Blackbox No. 1');
    await page.locator('[name="email"]').fill(student.email);
    await page.locator('[name="phone"]').fill('081234560001');
    await page.locator('[name="password"]').fill(student.password);
    await page.locator('[name="password_confirmation"]').fill(student.password);
    await submitWithConfirm(page, 'Registrasi');
    await expect(page.getByText('Registrasi berhasil.')).toBeVisible();

    await page.getByRole('link', { name: /Isi Formulir/ }).click();
    await page.locator('[name="name"]').fill(student.name);
    await page.locator('[name="nisn"]').fill(student.nisn);
    await page.locator('[name="gender"]').selectOption('Laki-laki');
    await page.locator('[name="birth_place"]').fill('Lamongan');
    await page.locator('[name="birth_date"]').fill('2018-08-19');
    await page.locator('[name="religion"]').fill('Islam');
    await page.locator('[name="phone"]').fill('081234560001');
    await page.locator('[name="address"]').fill('Jl. Blackbox No. 1');
    await page.locator('[name="father_name"]').fill('Ayah Blackbox');
    await page.locator('[name="mother_name"]').fill('Ibu Blackbox');
    await page.locator('[name="parent_phone"]').fill('081234560002');
    await page.locator('[name="origin_school"]').fill('TK Blackbox');
    await page.locator('[name="school_year"]').fill('2026/2027');
    await submitWithConfirm(page, /Simpan Formulir/);
    await expect(page.getByText('Formulir pendaftaran berhasil disimpan.')).toBeVisible();
    await expect(page.getByText('Formulir pendaftaran sudah tersimpan.')).toBeVisible();

    await page.goto('/?page=siswa-upload-berkas');
    await setAllFileInputs(page);
    await submitWithConfirm(page, 'Simpan Upload Berkas', /^Ya, Kirim/);
    await expect(page.getByText(/berkas berhasil diupload/i)).toBeVisible();
  });

  test('admin verifikasi berkas, proses seleksi, dan kelola pengumuman', async ({ page }) => {
    await logout(page);
    await login(page, admin.email, admin.password, 'Total Pendaftar');

    await page.goto('/?page=admin-data-pendaftaran');
    await page.getByPlaceholder('Cari nama / NISN').fill(student.name);
    await page.getByRole('button', { name: 'Cari' }).click();
    const dataRow = page.getByRole('row').filter({ hasText: student.name });
    await expect(dataRow).toBeVisible();
    await dataRow.getByRole('link', { name: 'Verifikasi' }).click();

    await page.locator('label[for="verify-all-documents"]').click();
    await expect(page.locator('#verify-all-documents')).toBeChecked();
    await page.locator('[name="document_status"]').selectOption('Berkas Lengkap');
    await page.locator('[name="note"]').fill('Semua dokumen sudah sesuai.');
    await submitWithConfirm(page, /Simpan & Kirim/);
    await expect(page.getByText('Status berkas berhasil diperbarui.')).toBeVisible();
    await expect(page.getByText(/email belum terkirim/i)).toBeVisible();

    await page.goto('/?page=admin-proses-seleksi');
    const selectionRow = page.getByRole('row').filter({ hasText: student.name });
    await selectionRow.locator('[name="selection_status"]').selectOption('Diterima');
    await selectionRow.getByRole('button', { name: 'Simpan' }).click();
    await confirmAction(page);
    await expect(page.getByText('Status seleksi berhasil diperbarui.')).toBeVisible();
    await expect(page.getByRole('row').filter({ hasText: student.name })).toContainText('Diterima');

    await page.goto('/?page=admin-pengumuman');
    await expect(page.locator('[name="announcement_date"]')).toHaveAttribute('type', 'date');
    const title = 'Pengumuman Blackbox';
    await page.locator('[name="title"]').fill(title);
    await page.locator('[name="announcement_date"]').fill('2026-07-01');
    await page.locator('[name="content"]').fill('Konten pengumuman dari blackbox test.');
    await submitWithConfirm(page, 'Simpan Pengumuman');
    await expect(page.getByText('Pengumuman berhasil disimpan.')).toBeVisible();
    await expect(page.getByText(title)).toBeVisible();

    const announcement = page.locator('.announcement-list-item').filter({ hasText: title });
    await announcement.getByRole('link', { name: 'Edit' }).click();
    await expect(page.locator('[name="title"]')).toHaveValue(title);
    await page.locator('[name="content"]').fill('Konten pengumuman sudah diedit.');
    await submitWithConfirm(page, 'Update Pengumuman', /^Ya, Update/);
    await expect(page.getByText('Pengumuman berhasil diperbarui.')).toBeVisible();
    await expect(page.getByText('Konten pengumuman sudah diedit.')).toBeVisible();

    await page.locator('.announcement-list-item').filter({ hasText: title }).getByRole('button', { name: 'Hapus' }).click();
    await confirmAction(page, /^Ya, Hapus/);
    await expect(page.getByText('Pengumuman berhasil dihapus.')).toBeVisible();
    await expect(page.getByText(title)).toHaveCount(0);
  });

  test('hasil seleksi menunggu publikasi lalu kepsek mempublikasikan', async ({ page }) => {
    await logout(page);
    await login(page, student.email, student.password, 'Formulir');
    await page.goto('/?page=siswa-hasil');
    await expect(page.locator('.selection-status-highlight')).toHaveText('Belum Diumumkan');
    await expect(page.getByText(/Jika dinyatakan diterima/i)).toBeVisible();

    await logout(page);
    await login(page, headmaster.email, headmaster.password, 'Rekap Status Berkas');
    await page.goto('/?page=kepsek-laporan');
    const csvDownloadPromise = page.waitForEvent('download');
    await page.getByRole('link', { name: /Export CSV/ }).click();
    const csvDownload = await csvDownloadPromise;
    expect(csvDownload.suggestedFilename()).toBe('Laporan_Penerimaan_Siswa_Baru.csv');
    const csvPath = await csvDownload.path();
    expect(fs.readFileSync(csvPath, 'utf8')).toContain('No Pendaftaran');

    await page.goto('/?page=kepsek-publikasi');
    await page.getByRole('button', { name: /Publikasikan/ }).click();
    await confirmAction(page);
    await expect(page.getByText('Hasil penerimaan berhasil dipublikasikan.')).toBeVisible();
    await expect(page.getByText('Sudah Dipublikasikan')).toBeVisible();
  });

  test('siswa daftar ulang dan admin konfirmasi menjadi siswa terdaftar', async ({ page }) => {
    await logout(page);
    await login(page, student.email, student.password, 'Formulir');
    await page.goto('/?page=siswa-hasil');
    await expect(page.locator('.selection-status-highlight')).toHaveText('Diterima');
    await expect(page.getByText(/Biaya daftar ulang:\s*Rp\s?1\.000\.000/)).toBeVisible();
    await expect(page.getByRole('link', { name: 'di sini' }).first()).toBeVisible();

    await setAllFileInputs(page);
    await submitWithConfirm(page, /Simpan Data Daftar Ulang/, /^Ya, Kirim/);
    await expect(page.getByText(/Data daftar ulang sudah dikirim/i)).toBeVisible();

    await logout(page);
    await login(page, admin.email, admin.password, 'Total Pendaftar');
    await page.goto('/?page=admin-hasil-seleksi');
    const resultRow = page.getByRole('row').filter({ hasText: student.name });
    await expect(resultRow).toBeVisible();
    await resultRow.getByRole('link', { name: /Cek Berkas Daftar Ulang/ }).click();
    await expect(page.getByRole('heading', { name: new RegExp(`Berkas Daftar Ulang - ${student.name}`) })).toBeVisible();
    await page.getByRole('button', { name: /Konfirmasi Daftar Ulang/ }).click();
    await confirmAction(page);
    await expect(page.getByText(/Daftar ulang sudah dikonfirmasi/i)).toBeVisible();
    await expect(page.locator('.alert-success.mb-0')).toContainText(/Nomor induk siswa/i);

    await logout(page);
    await login(page, student.email, student.password, 'Formulir');
    await page.goto('/?page=siswa-hasil');
    await expect(page.getByText(/Status Anda sudah menjadi siswa terdaftar/i)).toBeVisible();
    await expect(page.getByText('Nomor Induk Siswa')).toBeVisible();
  });
});
