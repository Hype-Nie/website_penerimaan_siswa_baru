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
    <section class="hero-section">
        <div class="hero-copy">
            <span class="eyebrow">Penerimaan Siswa Baru</span>
            <h1><?= e($school['name']) ?></h1>
            <p>Tahun ajaran <?= e($school['year']) ?> sudah dibuka. Calon siswa dapat membuat akun, mengisi formulir, upload berkas, dan memantau hasil seleksi secara online.</p>
            <div class="hero-actions">
                <a class="btn btn-primary btn-lg" href="<?= e(url_for('registrasi')) ?>">Daftar Sekarang</a>
                <a class="btn btn-outline-primary btn-lg" href="<?= e(url_for('login')) ?>">Login</a>
            </div>
        </div>
        <div class="hero-panel">
            <img src="<?= asset('img/logo-sekolah.svg') ?>" alt="Logo sekolah">
            <h2>PSB Online</h2>
            <p>Periode <?= e($school['period']) ?></p>
            <div class="quota-box">
                <span>Kuota Siswa</span>
                <strong><?= e($school['quota']) ?></strong>
            </div>
        </div>
    </section>

    <section class="guest-section">
        <div class="section-title">
            <h2>Alur Pendaftaran</h2>
            <p>Ikuti tahapan berikut sampai hasil seleksi diumumkan.</p>
        </div>
        <div class="process-grid">
            <?php foreach (['Daftar Akun', 'Isi Formulir', 'Upload Berkas', 'Verifikasi', 'Lihat Hasil'] as $index => $step): ?>
                <div class="process-card">
                    <span><?= $index + 1 ?></span>
                    <strong><?= e($step) ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="guest-section">
        <div class="section-title">
            <h2>Pengumuman</h2>
            <p>Informasi terbaru dari panitia PSB.</p>
        </div>
        <div class="announcement-grid">
            <?php foreach ($data['announcements'] as $announcement): ?>
                <article class="announcement-card">
                    <small><?= e($announcement['date']) ?></small>
                    <h3><?= e($announcement['title']) ?></h3>
                    <p><?= e($announcement['content']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>

