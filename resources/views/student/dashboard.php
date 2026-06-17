<?php $student = $data['student']; ?>

<div class="container-fluid">
    <?php if (($_GET['status'] ?? '') === 'registered'): ?>
        <div class="alert alert-success">Registrasi berhasil. Silakan lengkapi formulir pendaftaran dan upload berkas.</div>
    <?php endif; ?>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Formulir</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= e($student['form_status']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Berkas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= e($student['document_status']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-folder-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Hasil Seleksi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= e($student['selection_status']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bullhorn fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status Pendaftaran</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($data['timeline'] as $item): ?>
                        <div class="status-row">
                            <span><?= e($item['step']) ?></span>
                            <span class="badge badge-<?= e(status_class($item['status'])) ?>"><?= e($item['status']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Menu Cepat</h6>
                </div>
                <div class="card-body quick-menu">
                    <a href="<?= e(url_for('siswa-formulir')) ?>"><i class="fas fa-edit"></i> Isi Formulir</a>
                    <a href="<?= e(url_for('siswa-upload-berkas')) ?>"><i class="fas fa-upload"></i> Upload Berkas</a>
                    <a href="<?= e(url_for('siswa-hasil')) ?>"><i class="fas fa-award"></i> Hasil Seleksi</a>
                </div>
            </div>
        </div>
    </div>
</div>
