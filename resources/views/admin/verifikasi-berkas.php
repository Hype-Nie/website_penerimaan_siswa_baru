<?php
$selectedId = (int) ($_GET['id'] ?? 0);
$selectedApplicant = $selectedId > 0 ? find_registration_by_id($selectedId) : null;
$documents = $selectedApplicant ? documents_for_registration($selectedApplicant['id'], sample_data()) : [];
?>

<div class="container-fluid">
    <?php if ($selectedId > 0 && ! $selectedApplicant): ?>
        <div class="alert alert-danger">Data pendaftaran yang dipilih tidak ditemukan.</div>
    <?php endif; ?>

    <?php if (! $selectedApplicant): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Pilih Pendaftar untuk Verifikasi Berkas</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    Silakan pilih pendaftar terlebih dahulu agar proses verifikasi berkas jelas milik siswa yang mana.
                </div>

                <?php if (empty($data['applicants'])): ?>
                    <div class="alert alert-warning">Data pendaftaran belum tersedia.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Siswa</th>
                                    <th>No Pendaftaran</th>
                                    <th>Status Formulir</th>
                                    <th>Status Berkas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['applicants'] as $applicant): ?>
                                    <tr>
                                        <td><?= e($applicant['name']) ?></td>
                                        <td><?= e($applicant['no']) ?></td>
                                        <td><span class="badge badge-<?= e(status_class($applicant['form_status'])) ?>"><?= e($applicant['form_status']) ?></span></td>
                                        <td><span class="badge badge-<?= e(status_class($applicant['document_status'])) ?>"><?= e($applicant['document_status']) ?></span></td>
                                        <td>
                                            <?php if (! empty($applicant['id'])): ?>
                                                <a class="btn btn-primary btn-sm" href="<?= e(url_for('admin-verifikasi-berkas', ['id' => $applicant['id']])) ?>">Verifikasi</a>
                                            <?php else: ?>
                                                <span class="text-muted">Tidak tersedia</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pendaftar yang Diverifikasi</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="status-row">
                        <span>Nama Siswa</span>
                        <strong><?= e($selectedApplicant['name']) ?></strong>
                    </div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="status-row">
                        <span>No Pendaftaran</span>
                        <strong><?= e($selectedApplicant['no']) ?></strong>
                    </div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="status-row">
                        <span>Status Formulir</span>
                        <span class="badge badge-<?= e(status_class($selectedApplicant['form_status'])) ?>"><?= e($selectedApplicant['form_status']) ?></span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="status-row">
                        <span>Status Berkas</span>
                        <span class="badge badge-<?= e(status_class($selectedApplicant['document_status'])) ?>"><?= e($selectedApplicant['document_status']) ?></span>
                    </div>
                </div>
            </div>
            <a class="btn btn-outline-primary btn-sm mt-3" href="<?= e(url_for('admin-verifikasi-berkas')) ?>">Pilih Pendaftar Lain</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Berkas Calon Siswa</h6>
                </div>
                <div class="card-body">
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
                                <?php foreach ($documents as $document): ?>
                                    <tr>
                                        <td><?= e($document['name']) ?></td>
                                        <td><?= e($document['file']) ?></td>
                                        <td><span class="badge badge-<?= e(status_class($document['status'])) ?>"><?= e($document['status']) ?></span></td>
                                        <td>
                                            <?php if ($document['file_path']): ?>
                                                <a class="btn btn-info btn-sm" href="<?= e(url_for('view-berkas', ['id' => $document['id']])) ?>" target="_blank">Lihat Berkas</a>
                                            <?php else: ?>
                                                <span class="text-muted">Belum Upload</span>
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
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Verifikasi</h6>
                </div>
                <div class="card-body">
                    <form
                        action="<?= e(url_for('admin-verifikasi-berkas')) ?>"
                        method="post"
                        data-loading
                        data-loading-title="Mengirim Email"
                        data-loading-message="Mohon tunggu, sistem sedang menyimpan verifikasi berkas dan mengirim email ke pendaftar. Jangan tutup halaman ini sampai proses selesai."
                    >
                        <input type="hidden" name="registration_id" value="<?= e($selectedApplicant['id']) ?>">
                        <input type="hidden" name="document_selection_mode" value="multi">
                        <div class="form-group">
                            <label>Dokumen yang Diverifikasi</label>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="verify-all-documents" name="verify_all" value="1">
                                <label class="custom-control-label" for="verify-all-documents">Semua dokumen yang sudah diupload</label>
                            </div>
                            <div class="border rounded p-2">
                                <?php foreach ($documents as $document): ?>
                                    <div class="custom-control custom-checkbox mb-1">
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="document-<?= e($document['id']) ?>"
                                            name="document_ids[]"
                                            value="<?= e($document['id']) ?>"
                                            <?= empty($document['file_path']) ? 'disabled' : '' ?>
                                        >
                                        <label class="custom-control-label" for="document-<?= e($document['id']) ?>">
                                            <?= e($document['name']) ?>
                                            <span class="badge badge-<?= e(status_class($document['status'])) ?> ml-1"><?= e($document['status']) ?></span>
                                            <?php if (empty($document['file_path'])): ?>
                                                <span class="text-muted small">(belum upload)</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <small class="form-text text-muted">Pilih beberapa dokumen sekaligus, atau centang semua dokumen yang sudah diupload.</small>
                        </div>
                        <div class="form-group">
                            <label>Status Berkas</label>
                            <select class="form-control strong-input" name="document_status">
                                <option value="Berkas Lengkap">Lengkap</option>
                                <option value="Berkas Tidak Lengkap">Tidak Lengkap</option>
                                <option value="Menunggu Verifikasi">Menunggu Verifikasi</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Catatan Perbaikan</label>
                            <textarea class="form-control strong-input" name="note" rows="3" placeholder="Contoh: Foto KK kurang jelas"></textarea>
                        </div>
                        <button class="btn btn-success btn-block" type="submit" data-loading-button-text="Mengirim Email...">Simpan & Kirim</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
