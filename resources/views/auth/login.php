<?php $school = $data['school']; ?>

<main class="auth-page">
    <section class="auth-card auth-card-login">
        <img class="auth-logo" src="<?= asset('img/logo-sekolah.svg') ?>" alt="Logo sekolah">
        <h1>Aplikasi Pendaftaran Siswa</h1>

        <?php if (($_GET['status'] ?? '') === 'logout'): ?>
            <div class="alert alert-info py-2">Anda sudah logout.</div>
        <?php endif; ?>

        <form action="<?= e(url_for('login')) ?>" method="post" data-no-confirm>
            <div class="form-group">
                <input type="email" class="form-control auth-input" name="email" placeholder="Masukkan Email..." required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control auth-input" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block auth-button">Login</button>
        </form>

        <p class="auth-hint">Akun awal: <strong>siswa@psb.test</strong>, <strong>admin@psb.test</strong>, atau <strong>kepsek@psb.test</strong>. Password: <strong>password</strong>.</p>
        <a class="auth-link" href="<?= e(url_for('registrasi')) ?>">Registrasi Siswa Baru!</a>
        <a class="auth-link muted" href="<?= e(url_for('beranda')) ?>">Kembali ke Beranda</a>
    </section>
</main>
