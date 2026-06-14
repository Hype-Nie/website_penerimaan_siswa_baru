<?php
$selectedApplicant = find_registration_by_id($_GET['id'] ?? 0) ?: ($data['applicants'][0] ?? null);
$documents = $selectedApplicant ? documents_for_registration($selectedApplicant['id'], sample_data()) : [];
?>

<div class="container-fluid">
    <?php if (! $selectedApplicant): ?>
        <div class="alert alert-warning">Data pendaftaran belum tersedia.</div>
    <?php else: ?>
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
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Verifikasi</h6>
                </div>
                <div class="card-body">
                    <form action="<?= e(url_for('admin-verifikasi-berkas')) ?>" method="post">
                        <input type="hidden" name="registration_id" value="<?= e($selectedApplicant['id']) ?>">
                        <div class="form-group">
                            <label>Dokumen</label>
                            <select class="form-control strong-input" name="document_id">
                                <option value="">Semua dokumen</option>
                                <?php foreach ($documents as $document): ?>
                                    <option value="<?= e($document['id']) ?>"><?= e($document['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
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
                            <textarea class="form-control strong-input" name="note" rows="5" placeholder="Contoh: Foto KK kurang jelas"></textarea>
                        </div>
                        <button class="btn btn-success btn-block" type="submit">Simpan dan Kirim Email</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
