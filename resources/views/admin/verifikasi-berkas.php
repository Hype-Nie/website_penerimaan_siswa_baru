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
            <div class="card shadow mb-4">
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
                            <textarea class="form-control strong-input" name="note" rows="3" placeholder="Contoh: Foto KK kurang jelas"></textarea>
                        </div>
                        <button class="btn btn-success btn-block" type="submit">Simpan & Kirim Email</button>
                    </form>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Input Nilai & Seleksi</h6>
                </div>
                <div class="card-body">
                    <form action="<?= e(url_for('admin-verifikasi-berkas')) ?>" method="post">
                        <input type="hidden" name="action" value="update_scores">
                        <input type="hidden" name="registration_id" value="<?= e($selectedApplicant['id']) ?>">
                        
                        <div class="form-group">
                            <label>Nilai UTS</label>
                            <input type="number" step="0.01" class="form-control strong-input" name="uts" value="<?= e($selectedApplicant['uts']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Nilai UAS</label>
                            <input type="number" step="0.01" class="form-control strong-input" name="uas" value="<?= e($selectedApplicant['uas']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Nilai UN</label>
                            <input type="number" step="0.01" class="form-control strong-input" name="un" value="<?= e($selectedApplicant['un']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Nilai Rata-rata</label>
                            <input type="text" class="form-control strong-input bg-light" readonly value="<?= e($selectedApplicant['average']) ?>">
                            <small class="text-muted">Dihitung otomatis oleh sistem (MySQL)</small>
                        </div>
                        
                        <hr>
                        
                        <div class="form-group">
                            <label>Status Seleksi</label>
                            <select class="form-control strong-input" name="selection_status">
                                <option value="Belum Diproses" <?= $selectedApplicant['selection_status'] === 'Belum Diproses' ? 'selected' : '' ?>>Belum Diproses</option>
                                <option value="Diterima" <?= $selectedApplicant['selection_status'] === 'Diterima' ? 'selected' : '' ?>>Diterima</option>
                                <option value="Tidak Diterima" <?= $selectedApplicant['selection_status'] === 'Tidak Diterima' ? 'selected' : '' ?>>Tidak Diterima</option>
                                <option value="Cadangan" <?= $selectedApplicant['selection_status'] === 'Cadangan' ? 'selected' : '' ?>>Cadangan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Catatan Seleksi</label>
                            <textarea class="form-control strong-input" name="selection_note" rows="3"><?= e($selectedApplicant['selection_note']) ?></textarea>
                        </div>
                        
                        <button class="btn btn-primary btn-block" type="submit">Simpan Nilai & Keputusan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
