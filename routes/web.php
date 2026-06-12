<?php

return [
    'beranda' => [
        'title' => 'Beranda',
        'view' => 'public.home',
        'layout' => 'layouts/guest',
        'role' => 'guest',
    ],
    'informasi-psb' => [
        'title' => 'Informasi PSB',
        'view' => 'public.info',
        'layout' => 'layouts/guest',
        'role' => 'guest',
    ],
    'login' => [
        'title' => 'Login',
        'view' => 'auth.login',
        'layout' => 'layouts/guest',
        'role' => 'guest',
    ],
    'registrasi' => [
        'title' => 'Registrasi Siswa',
        'view' => 'auth.register',
        'layout' => 'layouts/guest',
        'role' => 'guest',
    ],

    'dashboard' => [
        'title' => 'Dashboard Admin',
        'view' => 'admin.dashboard',
        'role' => 'admin',
    ],
    'admin-dashboard' => [
        'title' => 'Dashboard Admin',
        'view' => 'admin.dashboard',
        'role' => 'admin',
    ],
    'admin-data-pendaftaran' => [
        'title' => 'Data Pendaftaran',
        'view' => 'admin.data-pendaftaran',
        'role' => 'admin',
    ],
    'admin-detail-pendaftaran' => [
        'title' => 'Detail Pendaftaran',
        'view' => 'admin.detail-pendaftaran',
        'role' => 'admin',
    ],
    'admin-verifikasi-berkas' => [
        'title' => 'Verifikasi Berkas',
        'view' => 'admin.verifikasi-berkas',
        'role' => 'admin',
    ],
    'admin-proses-seleksi' => [
        'title' => 'Proses Seleksi',
        'view' => 'admin.proses-seleksi',
        'role' => 'admin',
    ],
    'admin-hasil-seleksi' => [
        'title' => 'Hasil Seleksi',
        'view' => 'admin.hasil-seleksi',
        'role' => 'admin',
    ],
    'admin-pengumuman' => [
        'title' => 'Pengumuman',
        'view' => 'admin.pengumuman',
        'role' => 'admin',
    ],

    'siswa-dashboard' => [
        'title' => 'Dashboard Calon Siswa',
        'view' => 'student.dashboard',
        'role' => 'siswa',
    ],
    'siswa-formulir' => [
        'title' => 'Formulir Pendaftaran',
        'view' => 'student.formulir',
        'role' => 'siswa',
    ],
    'pendaftaran' => [
        'title' => 'Formulir Pendaftaran',
        'view' => 'student.formulir',
        'role' => 'siswa',
    ],
    'siswa-upload-berkas' => [
        'title' => 'Upload Berkas',
        'view' => 'student.upload-berkas',
        'role' => 'siswa',
    ],
    'siswa-status' => [
        'title' => 'Status Pendaftaran',
        'view' => 'student.status',
        'role' => 'siswa',
    ],
    'siswa-hasil' => [
        'title' => 'Hasil Seleksi',
        'view' => 'student.hasil',
        'role' => 'siswa',
    ],
    'siswa-profil' => [
        'title' => 'Profil Siswa',
        'view' => 'student.profil',
        'role' => 'siswa',
    ],
    'siswa' => [
        'title' => 'Profil Siswa',
        'view' => 'student.profil',
        'role' => 'siswa',
    ],

    'kepsek-dashboard' => [
        'title' => 'Dashboard Kepala Sekolah',
        'view' => 'headmaster.dashboard',
        'role' => 'kepsek',
    ],
    'kepsek-laporan' => [
        'title' => 'Laporan PSB',
        'view' => 'headmaster.laporan',
        'role' => 'kepsek',
    ],
    'kepsek-publikasi' => [
        'title' => 'Publikasi Hasil',
        'view' => 'headmaster.publikasi',
        'role' => 'kepsek',
    ],
];

