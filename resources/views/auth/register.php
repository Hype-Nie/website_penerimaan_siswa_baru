<?php $school = $data['school']; ?>

<main class="auth-page auth-page-register">
    <section class="auth-card auth-card-register">
        <h1>Registrasi Siswa Baru</h1>
        <h2><?= e($school['name']) ?></h2>

        <form action="<?= e(url_for('registrasi')) ?>" method="post">
            <div class="form-group">
                <label>Nama</label>
                <input type="text" class="form-control strong-input" name="name" placeholder="Masukkan Nama" required>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Tempat Lahir</label>
                    <input type="text" class="form-control strong-input" name="birth_place" placeholder="Tempat Lahir">
                </div>
                <div class="form-group col-md-6">
                    <label>Tanggal Lahir</label>
                    <input type="date" class="form-control strong-input" name="birth_date" placeholder="Tanggal Lahir">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Jenis Kelamin</label>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="genderMale" name="gender" class="custom-control-input" value="Laki Laki">
                        <label class="custom-control-label" for="genderMale">Laki Laki</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="genderFemale" name="gender" class="custom-control-input" value="Perempuan">
                        <label class="custom-control-label" for="genderFemale">Perempuan</label>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label>Agama</label>
                    <select class="form-control strong-input" name="religion">
                        <option>Pilih Agama</option>
                        <option>Islam</option>
                        <option>Kristen</option>
                        <option>Katolik</option>
                        <option>Hindu</option>
                        <option>Buddha</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <input type="text" class="form-control strong-input" name="address">
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Email</label>
                    <input type="email" class="form-control strong-input" name="email" placeholder="Email" required>
                </div>
                <div class="form-group col-md-6">
                    <label>Telepon</label>
                    <input type="text" class="form-control strong-input" name="phone" placeholder="Telepon" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Password</label>
                    <input type="password" class="form-control strong-input" name="password" placeholder="Password" required>
                </div>
                <div class="form-group col-md-6">
                    <label>Ulangi Password</label>
                    <input type="password" class="form-control strong-input" name="password_confirmation" placeholder="Ulangi Password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block auth-button">Registrasi</button>
        </form>

        <a class="auth-link" href="<?= e(url_for('login')) ?>">Sudah punya akun? Login</a>
    </section>
</main>

