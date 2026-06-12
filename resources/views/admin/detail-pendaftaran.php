<?php
$applicant = find_registration_by_id($_GET['id'] ?? 0) ?: ($data['applicants'][0] ?? null);
?>

<div class="container-fluid">
    <?php if (! $applicant): ?>
        <div class="alert alert-warning">Data pendaftaran belum tersedia.</div>
    <?php else: ?>
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Data Lengkap Calon Siswa</h6>
                </div>
                <div class="card-body detail-list">
                    <div><span>No Pendaftaran</span><strong><?= e($applicant['no']) ?></strong></div>
                    <div><span>Nama Lengkap</span><strong><?= e($applicant['name']) ?></strong></div>
                    <div><span>NISN</span><strong><?= e($applicant['nisn']) ?></strong></div>
                    <div><span>Jenis Kelamin</span><strong><?= e($applicant['gender']) ?></strong></div>
                    <div><span>Alamat</span><strong><?= e($applicant['address']) ?></strong></div>
                    <div><span>Asal Sekolah</span><strong><?= e($applicant['origin_school']) ?></strong></div>
                    <div><span>Nama Ayah</span><strong><?= e($applicant['father']) ?></strong></div>
                    <div><span>Nama Ibu</span><strong><?= e($applicant['mother']) ?></strong></div>
                    <div><span>No HP Orang Tua</span><strong><?= e($applicant['parent_phone']) ?></strong></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status Pemeriksaan</h6>
                </div>
                <div class="card-body">
                    <div class="status-row">
                        <span>Formulir</span>
                        <span class="badge badge-<?= e(status_class($applicant['form_status'])) ?>"><?= e($applicant['form_status']) ?></span>
                    </div>
                    <div class="status-row">
                        <span>Berkas</span>
                        <span class="badge badge-<?= e(status_class($applicant['document_status'])) ?>"><?= e($applicant['document_status']) ?></span>
                    </div>
                    <div class="form-group mt-3">
                        <label>Catatan Admin</label>
                        <textarea class="form-control strong-input" rows="5"><?= e($applicant['admin_note']) ?></textarea>
                    </div>
                    <a class="btn btn-primary btn-block" href="<?= e(url_for('admin-verifikasi-berkas', ['id' => $applicant['id']])) ?>">Lanjut Verifikasi</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
