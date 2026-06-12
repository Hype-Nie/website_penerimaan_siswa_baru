<?php $student = $data['student']; ?>

<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Isi Formulir Pendaftaran</h6>
        </div>
        <div class="card-body">
            <form action="<?= e(url_for('siswa-formulir')) ?>" method="post">
                <h5 class="form-section-title">Identitas Calon Siswa</h5>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control strong-input" name="name" value="<?= e($student['name']) ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label>NISN</label>
                        <input type="text" class="form-control strong-input" name="nisn" value="<?= e($student['nisn'] ?? '') ?>" placeholder="Masukkan NISN">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Jenis Kelamin</label>
                        <select class="form-control strong-input" name="gender">
                            <option value="">Pilih jenis kelamin</option>
                            <option value="Laki-laki" <?= ($student['gender'] ?? '') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= ($student['gender'] ?? '') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Tempat Lahir</label>
                        <input type="text" class="form-control strong-input" name="birth_place" value="<?= e($student['birth_place']) ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Tanggal Lahir</label>
                        <input type="text" class="form-control strong-input" name="birth_date" value="<?= e($student['birth_date']) ?>" placeholder="dd-mm-yyyy">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Agama</label>
                        <input type="text" class="form-control strong-input" name="religion" value="<?= e($student['religion']) ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Nomor HP Siswa / Orang Tua</label>
                        <input type="text" class="form-control strong-input" name="phone" value="<?= e($student['phone']) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea class="form-control strong-input" name="address" rows="3"><?= e($student['address']) ?></textarea>
                </div>

                <h5 class="form-section-title">Data Orang Tua / Wali</h5>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Nama Ayah</label>
                        <input type="text" class="form-control strong-input" name="father_name" value="<?= e($student['father']) ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Nama Ibu</label>
                        <input type="text" class="form-control strong-input" name="mother_name" value="<?= e($student['mother']) ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Nomor HP Orang Tua</label>
                        <input type="text" class="form-control strong-input" name="parent_phone" value="<?= e($student['parent_phone']) ?>">
                    </div>
                </div>

                <h5 class="form-section-title">Asal Sekolah</h5>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Asal Sekolah</label>
                        <input type="text" class="form-control strong-input" name="origin_school" value="<?= e($student['origin_school']) ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Tahun Ajaran</label>
                        <input type="text" class="form-control strong-input" name="school_year" value="<?= e($data['school']['year']) ?>">
                    </div>
                </div>

                <button class="btn btn-primary" type="submit">Simpan Formulir</button>
                <button class="btn btn-danger" type="reset">Batal</button>
            </form>
        </div>
    </div>
</div>
