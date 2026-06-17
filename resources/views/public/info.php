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
    <section class="page-heading text-center mx-auto mb-5" style="max-width: 800px;">
        <span class="eyebrow"><i class="fas fa-info-circle mr-2"></i> Informasi PSB</span>
        <h1 class="gradient-text">Jadwal, Persyaratan, dan Tahapan Seleksi</h1>
        <p class="text-muted">Semua informasi pendaftaran dapat diakses online sehingga calon siswa dan orang tua tidak perlu datang ke sekolah hanya untuk mengecek jadwal.</p>
    </section>

    <section class="info-grid">
        <div class="info-card modern-card">
            <h2 class="card-title-modern"><i class="fas fa-calendar-check text-primary mr-2"></i> Jadwal Pendaftaran</h2>
            <div class="table-responsive mt-3">
                <table class="table modern-table">
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

        <div class="info-card modern-card">
            <h2 class="card-title-modern"><i class="fas fa-list-ul text-primary mr-2"></i> Persyaratan</h2>
            <ul class="check-list modern-list mt-3">
                <?php foreach ($data['requirements'] as $requirement): ?>
                    <li><i class="fas fa-check-circle text-success mr-2"></i> <?= e($requirement) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="info-card modern-card">
            <h2 class="card-title-modern"><i class="fas fa-layer-group text-primary mr-2"></i> Tahapan Seleksi</h2>
            <ol class="number-list modern-list mt-3">
                <li>Calon siswa melakukan registrasi akun.</li>
                <li>Calon siswa mengisi formulir pendaftaran.</li>
                <li>Calon siswa mengunggah dokumen persyaratan.</li>
                <li>Admin melakukan verifikasi data dan berkas.</li>
                <li>Kepala sekolah mempublikasikan hasil penerimaan.</li>
            </ol>
        </div>

        <div class="info-card modern-card">
            <h2 class="card-title-modern"><i class="fas fa-phone-alt text-primary mr-2"></i> Kontak Panitia</h2>
            <div class="contact-info mt-3">
                <p><i class="fas fa-phone-alt mr-2" style="width: 20px;"></i> <strong>Telepon:</strong> <?= e($school['contact']) ?></p>
                <p><i class="fas fa-envelope mr-2" style="width: 20px;"></i> <strong>Email:</strong> <?= e($school['email']) ?></p>
                <p><i class="fas fa-map-marker-alt mr-2" style="width: 20px;"></i> <strong>Alamat:</strong> <?= e($school['address']) ?></p>
            </div>
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
