<?php $student = $data['student']; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Profil</h6>
                </div>
                <div class="card-body">
                    <form action="<?= e(url_for('siswa-profil')) ?>" method="post">
                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" class="form-control strong-input" name="name" value="<?= e($student['name']) ?>">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Tempat Lahir</label>
                                <input type="text" class="form-control strong-input" name="birth_place" value="<?= e($student['birth_place']) ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Tanggal Lahir</label>
                                <input type="date" class="form-control strong-input" name="birth_date" value="<?= e($student['birth_date']) ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" class="form-control strong-input" name="email" value="<?= e($student['email']) ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Nomor HP</label>
                                <input type="text" class="form-control strong-input" name="phone" value="<?= e($student['phone']) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Alamat</label>
                            <input type="text" class="form-control strong-input" name="address" value="<?= e($student['address']) ?>">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Password Baru</label>
                                <input type="password" class="form-control strong-input" name="new_password">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Ulangi Password Baru</label>
                                <input type="password" class="form-control strong-input" name="new_password_confirmation">
                            </div>
                        </div>
                        <button class="btn btn-primary">Simpan</button>
                        <button class="btn btn-danger" type="reset">Batal</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow profile-preview">
                <div class="card-body text-center">
                    <div class="profile-avatar-large">
                        <i class="fas fa-user"></i>
                    </div>
                    <h5 class="font-weight-bold text-gray-800"><?= e($student['name']) ?></h5>
                    <p class="text-muted mb-3"><?= e($student['registration_no']) ?></p>
                    <div class="status-row">
                        <span>Formulir</span>
                        <span class="badge badge-<?= e(status_class($student['form_status'])) ?>"><?= e($student['form_status']) ?></span>
                    </div>
                    <div class="status-row">
                        <span>Berkas</span>
                        <span class="badge badge-<?= e(status_class($student['document_status'])) ?>"><?= e($student['document_status']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
