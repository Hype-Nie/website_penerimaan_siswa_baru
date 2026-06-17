<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="m-0 font-weight-bold text-primary">Kelola Data Pendaftaran</h6>
                </div>
                <div class="col-md-6">
                    <form class="form-inline justify-content-md-end mt-3 mt-md-0" method="get">
                        <input type="hidden" name="page" value="admin-data-pendaftaran">
                        <input class="form-control mr-2 mb-2 mb-sm-0" type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Cari nama / NISN">
                        <select class="form-control" name="status">
                            <option value="">Semua Status</option>
                            <?php foreach (['Menunggu Verifikasi', 'Berkas Lengkap', 'Berkas Tidak Lengkap', 'Diterima', 'Tidak Diterima', 'Cadangan'] as $filterStatus): ?>
                                <option value="<?= e($filterStatus) ?>" <?= ($_GET['status'] ?? '') === $filterStatus ? 'selected' : '' ?>><?= e($filterStatus) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary ml-2" type="submit">Cari</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No Pendaftaran</th>
                            <th>Nama Siswa</th>
                            <th>NISN</th>
                            <th>Jenis Kelamin</th>
                            <th>Tanggal Daftar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['applicants'] as $applicant): ?>
                            <tr>
                                <td><?= e($applicant['no']) ?></td>
                                <td><?= e($applicant['name']) ?></td>
                                <td><?= e($applicant['nisn']) ?></td>
                                <td><?= e($applicant['gender']) ?></td>
                                <td><?= e($applicant['date']) ?></td>
                                <td><span class="badge badge-<?= e(status_class($applicant['form_status'])) ?>"><?= e($applicant['form_status']) ?></span></td>
                                <td class="table-actions">
                                    <a class="btn btn-info btn-sm" href="<?= e(url_for('admin-detail-pendaftaran', ['id' => $applicant['id'] ?? 0])) ?>">Detail</a>
                                    <form action="<?= e(url_for('admin-data-pendaftaran')) ?>" method="post">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="registration_id" value="<?= e($applicant['id'] ?? 0) ?>">
                                        <button class="btn btn-danger btn-sm" type="submit" onclick="return confirm('Apakah Anda yakin ingin menghapus data pendaftaran siswa ini?')">Hapus</button>
                                    </form>
                                    <a class="btn btn-success btn-sm" href="<?= e(url_for('admin-verifikasi-berkas', ['id' => $applicant['id'] ?? 0])) ?>">Verifikasi</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
