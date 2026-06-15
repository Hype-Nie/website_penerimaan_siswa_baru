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
        <?php if (($student['selection_status'] ?? '') === 'Diterima' && published_results()): ?>
            <div class="mt-4">
                <hr>
                <h4 class="mb-3">Daftar Ulang</h4>
                <?php if (($student['re_registration_status'] ?? '') === 'Sudah Daftar Ulang'): ?>
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> Anda telah berhasil melakukan daftar ulang online. Harap tunggu informasi selanjutnya.
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle"></i> Anda diwajibkan untuk melakukan konfirmasi daftar ulang online.
                    </div>
                    <form action="<?= e(url_for('siswa-hasil')) ?>" method="post">
                        <input type="hidden" name="action" value="re_register">
                        <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fas fa-check"></i> Konfirmasi Daftar Ulang Sekarang</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info mb-0 mt-4">Jika dinyatakan diterima, informasi daftar ulang akan muncul pada halaman ini.</div>
        <?php endif; ?>
    </div>
</div>
