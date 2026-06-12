<?php $school = $data['school']; ?>

<header class="guest-navbar">
    <a class="guest-brand" href="<?= e(url_for('beranda')) ?>">
        <img src="<?= asset('img/logo-sekolah.svg') ?>" alt="Logo sekolah">
        <span><?= e($school['short_name']) ?></span>
    </a>
    <nav>
        <a href="<?= e(url_for('beranda')) ?>">Beranda</a>
        <a href="<?= e(url_for('informasi-psb')) ?>">Informasi PSB</a>
        <a href="<?= e(url_for('login')) ?>">Login</a>
    </nav>
</header>

<main class="guest-main">
    <section class="page-heading">
        <span class="eyebrow">Informasi PSB</span>
        <h1>Jadwal, Persyaratan, dan Tahapan Seleksi</h1>
        <p>Semua informasi pendaftaran dapat diakses online sehingga calon siswa dan orang tua tidak perlu datang ke sekolah hanya untuk mengecek jadwal.</p>
    </section>

    <section class="info-grid">
        <div class="info-card">
            <h2>Jadwal Pendaftaran</h2>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <?php foreach ($data['schedule'] as $schedule): ?>
                            <tr>
                                <th><?= e($schedule['activity']) ?></th>
                                <td><?= e($schedule['date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="info-card">
            <h2>Persyaratan</h2>
            <ul class="check-list">
                <?php foreach ($data['requirements'] as $requirement): ?>
                    <li><?= e($requirement) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="info-card">
            <h2>Tahapan Seleksi</h2>
            <ol class="number-list">
                <li>Calon siswa melakukan registrasi akun.</li>
                <li>Calon siswa mengisi formulir pendaftaran.</li>
                <li>Calon siswa mengunggah dokumen persyaratan.</li>
                <li>Admin melakukan verifikasi data dan berkas.</li>
                <li>Kepala sekolah mempublikasikan hasil penerimaan.</li>
            </ol>
        </div>

        <div class="info-card">
            <h2>Kontak Panitia</h2>
            <p><strong>Telepon:</strong> <?= e($school['contact']) ?></p>
            <p><strong>Email:</strong> <?= e($school['email']) ?></p>
            <p><strong>Alamat:</strong> <?= e($school['address']) ?></p>
        </div>
    </section>
</main>

