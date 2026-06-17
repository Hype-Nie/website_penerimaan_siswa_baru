<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Proses Seleksi Calon Siswa</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No Pendaftaran</th>
                            <th>Nama</th>
                            <th>Status Berkas</th>
                            <th>Status Seleksi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['applicants'] as $applicant): ?>
                            <?php $readyForSelection = registration_ready_for_selection($applicant); ?>
                            <tr>
                                <td><?= e($applicant['no']) ?></td>
                                <td><?= e($applicant['name']) ?></td>
                                <td><span class="badge badge-<?= e(status_class($applicant['document_status'])) ?>"><?= e($applicant['document_status']) ?></span></td>
                                <td><span class="badge badge-<?= e(status_class($applicant['selection_status'])) ?>"><?= e($applicant['selection_status']) ?></span></td>
                                <td>
                                    <?php if ($readyForSelection): ?>
                                        <form action="<?= e(url_for('admin-proses-seleksi')) ?>" method="post" class="form-inline" data-confirm="Pastikan status seleksi peserta sudah benar sebelum disimpan.">
                                            <input type="hidden" name="registration_id" value="<?= e($applicant['id'] ?? 0) ?>">
                                            <select class="form-control form-control-sm mr-2" name="selection_status">
                                                <?php foreach (['Belum Diproses', 'Diterima', 'Tidak Diterima', 'Cadangan'] as $status): ?>
                                                    <option value="<?= e($status) ?>" <?= $applicant['selection_status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button class="btn btn-primary btn-sm" type="submit">Simpan</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">Menunggu formulir dan verifikasi berkas</span>
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
