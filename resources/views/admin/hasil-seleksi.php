<?php
$selectedApplicant = find_registration_by_id($_GET['id'] ?? 0);
$reRegistrationSchemaReady = re_registration_schema_ready();
$reRegistrationDocuments = $selectedApplicant && $reRegistrationSchemaReady ? re_registration_documents_for_registration($selectedApplicant['id']) : [];
?>

<div class="container-fluid">
    <div class="alert alert-warning">Hasil seleksi belum tampil ke calon siswa sampai kepala sekolah melakukan publikasi.</div>

    <?php if ($selectedApplicant): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Berkas Daftar Ulang - <?= e($selectedApplicant['name']) ?></h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="status-row">
                            <span>Status Daftar Ulang</span>
                            <span class="badge badge-<?= e(status_class($selectedApplicant['re_registration_status'])) ?>"><?= e($selectedApplicant['re_registration_status']) ?></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="status-row">
                            <span>No Pendaftaran</span>
                            <strong><?= e($selectedApplicant['no']) ?></strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="status-row">
                            <span>Nomor Induk</span>
                            <strong><?= e($selectedApplicant['student_identity_no'] ?: '-') ?></strong>
                        </div>
                    </div>
                </div>

                <?php if (! $reRegistrationSchemaReady): ?>
                    <div class="alert alert-danger mb-0">
                        Struktur database daftar ulang belum diperbarui. Jalankan file <strong>database/update_daftar_ulang.sql</strong> terlebih dahulu.
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Dokumen</th>
                                <th>File</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reRegistrationDocuments as $document): ?>
                                <tr>
                                    <td><?= e($document['name']) ?></td>
                                    <td><?= e($document['file']) ?></td>
                                    <td><span class="badge badge-<?= e(status_class($document['status'])) ?>"><?= e($document['status']) ?></span></td>
                                    <td>
                                        <?php if (! empty($document['file_path'])): ?>
                                            <a class="btn btn-info btn-sm" href="<?= e(url_for('view-daftar-ulang-berkas', ['id' => $document['id']])) ?>" target="_blank">Lihat Berkas</a>
                                        <?php else: ?>
                                            <span class="text-muted">Belum Upload</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($selectedApplicant['re_registration_status'] ?? '') === 'Dikirim'): ?>
                    <form action="<?= e(url_for('admin-hasil-seleksi')) ?>" method="post" class="mt-3" data-confirm="Pastikan berkas daftar ulang peserta sudah sesuai sebelum dikonfirmasi. Lanjutkan konfirmasi daftar ulang?">
                        <input type="hidden" name="action" value="confirm_re_registration">
                        <input type="hidden" name="registration_id" value="<?= e($selectedApplicant['id']) ?>">
                        <button class="btn btn-success" type="submit">
                            <i class="fas fa-check"></i> Konfirmasi Daftar Ulang
                        </button>
                    </form>
                <?php elseif (($selectedApplicant['re_registration_status'] ?? '') === 'Dikonfirmasi'): ?>
                    <div class="alert alert-success mb-0">
                        Daftar ulang sudah dikonfirmasi. Nomor induk siswa: <strong><?= e($selectedApplicant['student_identity_no']) ?></strong>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">Peserta belum mengirim data daftar ulang.</div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Kelola Hasil Seleksi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th>No Pendaftaran</th>
                            <th>Status Seleksi</th>
                            <th>Daftar Ulang</th>
                            <th>Tanggal Diproses</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['applicants'] as $applicant): ?>
                            <tr>
                                <td><?= e($applicant['name']) ?></td>
                                <td><?= e($applicant['no']) ?></td>
                                <td><span class="badge badge-<?= e(status_class($applicant['selection_status'])) ?>"><?= e($applicant['selection_status']) ?></span></td>
                                <td>
                                    <?php if ($applicant['selection_status'] === 'Diterima'): ?>
                                        <span class="badge badge-<?= e(status_class($applicant['re_registration_status'])) ?>"><?= e($applicant['re_registration_status']) ?></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= e(format_date_id($applicant['selected_at'])) ?></td>
                                <td>
                                    <?php if ($applicant['selection_status'] === 'Diterima'): ?>
                                        <a class="btn btn-info btn-sm" href="<?= e(url_for('admin-hasil-seleksi', ['id' => $applicant['id']])) ?>">Cek Berkas Daftar Ulang</a>
                                    <?php else: ?>
                                        <span class="text-muted">Tidak tersedia</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
