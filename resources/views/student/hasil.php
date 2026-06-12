<?php $student = $data['student']; ?>

<div class="container-fluid">
    <div class="selection-result-card shadow">
        <div class="selection-icon">
            <i class="fas fa-bullhorn"></i>
        </div>
        <h2><?= e($student['selection_status']) ?></h2>
        <p>Hasil seleksi akan tampil setelah admin menyelesaikan proses seleksi dan kepala sekolah mempublikasikan hasil penerimaan siswa baru.</p>
        <div class="result-detail">
            <span>Nomor Pendaftaran</span>
            <strong><?= e($student['registration_no']) ?></strong>
        </div>
        <div class="result-detail">
            <span>Nama Calon Siswa</span>
            <strong><?= e($student['name']) ?></strong>
        </div>
        <div class="alert alert-info mb-0">Jika dinyatakan diterima, informasi daftar ulang akan muncul pada halaman ini.</div>
    </div>
</div>

