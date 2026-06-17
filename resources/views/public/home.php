<?php $school = $data['school']; ?>

<header class="guest-navbar">
    <a class="guest-brand" href="<?= e(url_for('beranda')) ?>">
        <img src="<?= asset('img/logo_utama.png') ?>" alt="Logo sekolah">
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
            <span class="eyebrow"><i class="fas fa-graduation-cap mr-2"></i> Penerimaan Siswa Baru</span>
            <h1>Selamat Datang di<br><?= e($school['name']) ?></h1>
            <p>Tahun ajaran <?= e($school['year']) ?> sudah dibuka. Bergabunglah bersama kami untuk masa depan yang lebih gemilang. Buat akun, isi formulir, dan pantau hasil seleksi secara online.</p>
            <div class="hero-actions">
                <a class="btn btn-primary btn-lg" href="<?= e(url_for('registrasi')) ?>">Daftar Sekarang</a>
                <a class="btn btn-outline-primary btn-lg" href="<?= e(url_for('login')) ?>">Login Calon Siswa</a>
            </div>
            
            <div class="quota-glass-box">
                <span>Kuota Tersedia</span>
                <strong><?= e($school['quota']) ?></strong>
            </div>
        </div>
        <div class="hero-illustration-container">
            <div class="hero-image-wrapper">
                <img src="<?= asset('img/hero.png') ?>" alt="Ilustrasi Sekolah">
            </div>
        </div>
    </section>

    <section class="guest-section">
        <div class="section-title text-center" style="margin-bottom: 2rem;">
            <h2>Alur Pendaftaran</h2>
            <p>Ikuti tahapan berikut dengan saksama untuk menyelesaikan pendaftaran Anda.</p>
        </div>
        <div class="process-grid">
            <?php 
            $steps = [
                ['title' => 'Daftar Akun', 'icon' => '1'],
                ['title' => 'Isi Formulir', 'icon' => '2'],
                ['title' => 'Upload Berkas', 'icon' => '3'],
                ['title' => 'Verifikasi', 'icon' => '4'],
                ['title' => 'Lihat Hasil', 'icon' => '5']
            ];
            foreach ($steps as $step): 
            ?>
                <div class="process-card">
                    <span><?= e($step['icon']) ?></span>
                    <strong><?= e($step['title']) ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="guest-section" style="margin-top: 5rem;">
        <div class="section-title text-center" style="margin-bottom: 2rem;">
            <h2>Pengumuman Terbaru</h2>
            <p>Informasi resmi dan update terbaru dari panitia penerimaan siswa baru.</p>
        </div>
        <div class="announcement-grid">
            <?php foreach ($data['announcements'] as $announcement): ?>
                <article class="announcement-card">
                    <small><i class="fas fa-calendar-alt" style="margin-right: 0.3rem;"></i> <?= e($announcement['date']) ?></small>
                    <h3><?= e($announcement['title']) ?></h3>
                    <p><?= e($announcement['content']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<footer class="guest-footer">
    <div class="footer-grid">
        <div class="footer-col">
            <a class="footer-brand" href="<?= e(url_for('beranda')) ?>">
                <img src="<?= asset('img/logo_utama.png') ?>" alt="Logo sekolah">
                <span><?= e($school['short_name']) ?></span>
            </a>
            <p><?= e($school['name']) ?> berkomitmen untuk mencetak generasi yang unggul, berprestasi, dan berakhlak mulia melalui pendidikan berkualitas.</p>
        </div>
        <div class="footer-col">
            <h4>Informasi</h4>
            <ul>
                <li><a href="<?= e(url_for('beranda')) ?>">Beranda</a></li>
                <li><a href="<?= e(url_for('informasi-psb')) ?>">Informasi Pendaftaran</a></li>
                <li><a href="<?= e(url_for('login')) ?>">Login Siswa</a></li>
                <li><a href="<?= e(url_for('registrasi')) ?>">Registrasi Akun</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Kontak Kami</h4>
            <ul>
                <li><i class="fas fa-map-marker-alt mr-2" style="width: 20px;"></i> Jl. Pendidikan No. 123, Kota Pelajar</li>
                <li><i class="fas fa-phone-alt mr-2" style="width: 20px;"></i> (021) 1234-5678</li>
                <li><i class="fas fa-envelope mr-2" style="width: 20px;"></i> info@psbonline.sch.id</li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?= date('Y') ?> <?= e($school['name']) ?>. Semua hak cipta dilindungi.
    </div>
</footer>
