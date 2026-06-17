<?php
$student = $data['student'];
$formSubmitted = student_has_submitted_form($student);
$canEditForm = student_can_edit_form($student);
$showForm = ! $formSubmitted || ($canEditForm && ($_GET['edit'] ?? '') === '1');
$schoolYear = $student['school_year'] ?? $data['school']['year'];
?>

<div class="container-fluid">
    <?php if (! $showForm): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Formulir Pendaftaran</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <strong>Formulir pendaftaran sudah tersimpan.</strong>
                    Silakan lanjutkan ke menu Upload Berkas untuk melengkapi dokumen persyaratan.
                </div>

                <?php if (! $canEditForm): ?>
                    <div class="alert alert-info">
                        Data formulir sudah dikunci karena status seleksi Anda sudah diterima.
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Selama status seleksi belum diterima, Anda masih dapat memperbaiki data formulir jika ada kesalahan.
                    </div>
                <?php endif; ?>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 220px;">Nomor Pendaftaran</th>
                                <td><?= e($student['registration_no'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Nama Lengkap</th>
                                <td><?= e($student['name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>NISN</th>
                                <td><?= e($student['nisn'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Status Formulir</th>
                                <td><span class="badge badge-<?= e(status_class($student['form_status'] ?? 'Belum Mengisi')) ?>"><?= e($student['form_status'] ?? 'Belum Mengisi') ?></span></td>
                            </tr>
                            <tr>
                                <th>Status Berkas</th>
                                <td><span class="badge badge-<?= e(status_class($student['document_status'] ?? 'Belum Upload')) ?>"><?= e($student['document_status'] ?? 'Belum Upload') ?></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <a class="btn btn-primary" href="<?= e(url_for('siswa-upload-berkas')) ?>">
                    <i class="fas fa-upload"></i> Upload Berkas
                </a>
                <?php if ($canEditForm): ?>
                    <a class="btn btn-outline-primary" href="<?= e(url_for('siswa-formulir', ['edit' => 1])) ?>">
                        <i class="fas fa-edit"></i> Edit Formulir
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><?= $formSubmitted ? 'Edit Formulir Pendaftaran' : 'Isi Formulir Pendaftaran' ?></h6>
            </div>
            <div class="card-body">
                <?php if ($formSubmitted): ?>
                    <div class="alert alert-warning">
                        Anda sedang mengedit formulir yang sudah tersimpan. Pastikan data terbaru sudah benar sebelum disimpan.
                    </div>
                <?php endif; ?>

                <form action="<?= e(url_for('siswa-formulir')) ?>" method="post" data-confirm="Pastikan data formulir sudah benar sebelum disimpan. Lanjutkan simpan formulir?">
                    <h5 class="form-section-title">Identitas Calon Siswa</h5>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Nama Lengkap</label>
                            <input type="text" class="form-control strong-input" name="name" value="<?= e($student['name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>NISN</label>
                            <input type="text" class="form-control strong-input" name="nisn" value="<?= e($student['nisn'] ?? '') ?>" placeholder="Masukkan NISN" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Jenis Kelamin</label>
                            <select class="form-control strong-input" name="gender" required>
                                <option value="">Pilih jenis kelamin</option>
                                <option value="Laki-laki" <?= ($student['gender'] ?? '') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="Perempuan" <?= ($student['gender'] ?? '') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Tempat Lahir</label>
                            <input type="text" class="form-control strong-input" name="birth_place" value="<?= e($student['birth_place'] ?? '') ?>" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Tanggal Lahir</label>
                            <input type="date" class="form-control strong-input" name="birth_date" value="<?= e($student['birth_date'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Agama</label>
                            <input type="text" class="form-control strong-input" name="religion" value="<?= e($student['religion'] ?? '') ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nomor HP Siswa / Orang Tua</label>
                            <input type="text" class="form-control strong-input" name="phone" value="<?= e($student['phone'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea class="form-control strong-input" name="address" rows="3" required><?= e($student['address'] ?? '') ?></textarea>
                    </div>

                    <h5 class="form-section-title">Data Orang Tua / Wali</h5>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Nama Ayah</label>
                            <input type="text" class="form-control strong-input" name="father_name" value="<?= e($student['father'] ?? '') ?>" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Nama Ibu</label>
                            <input type="text" class="form-control strong-input" name="mother_name" value="<?= e($student['mother'] ?? '') ?>" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Nomor HP Orang Tua</label>
                            <input type="text" class="form-control strong-input" name="parent_phone" value="<?= e($student['parent_phone'] ?? '') ?>" required>
                        </div>
                    </div>

                    <h5 class="form-section-title">Asal Sekolah</h5>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Asal Sekolah</label>
                            <input type="text" class="form-control strong-input" name="origin_school" value="<?= e($student['origin_school'] ?? '') ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Tahun Ajaran</label>
                            <input type="text" class="form-control strong-input" name="school_year" value="<?= e($schoolYear) ?>" required>
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit"><?= $formSubmitted ? 'Simpan Perubahan' : 'Simpan Formulir' ?></button>
                    <button class="btn btn-danger" type="reset">Batal</button>
                    <?php if ($formSubmitted): ?>
                        <a class="btn btn-outline-secondary" href="<?= e(url_for('siswa-formulir')) ?>">Kembali</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
