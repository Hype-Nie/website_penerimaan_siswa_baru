<div class="container-fluid">
    <div class="alert alert-warning">Hasil seleksi belum tampil ke calon siswa sampai kepala sekolah melakukan publikasi.</div>

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
                            <th>Keterangan</th>
                            <th>Tanggal Diproses</th>
                            <th>Siap Publikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['applicants'] as $applicant): ?>
                            <tr>
                                <td><?= e($applicant['name']) ?></td>
                                <td><?= e($applicant['no']) ?></td>
                                <td><span class="badge badge-<?= e(status_class($applicant['selection_status'])) ?>"><?= e($applicant['selection_status']) ?></span></td>
                                <td><?= $applicant['selection_status'] === 'Diterima' ? 'Lulus seleksi administrasi.' : '-' ?></td>
                                <td>25 Juni 2026</td>
                                <td>
                                    <form action="<?= e(url_for('admin-hasil-seleksi')) ?>" method="post" class="form-inline">
                                        <input type="hidden" name="registration_id" value="<?= e($applicant['id'] ?? 0) ?>">
                                        <select class="form-control form-control-sm mr-2" name="selection_status">
                                            <?php foreach (['Belum Diproses', 'Diterima', 'Tidak Diterima', 'Cadangan'] as $status): ?>
                                                <option value="<?= e($status) ?>" <?= $applicant['selection_status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="selection_note" value="<?= e($applicant['selection_note'] ?? '') ?>">
                                        <button class="btn btn-primary btn-sm" type="submit">Simpan</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
