<div class="container-fluid">
    <div class="publish-panel shadow mb-4">
        <div>
            <span>Status Publikasi</span>
            <h2><?= $data['results_published'] ? 'Sudah Dipublikasikan' : 'Belum Dipublikasikan' ?></h2>
            <p>Setelah hasil dipublikasikan, calon siswa dapat melihat hasil seleksi melalui akun masing-masing.</p>
        </div>
        <form action="<?= e(url_for('kepsek-publikasi')) ?>" method="post" data-confirm="<?= $data['results_published'] ? 'Batalkan publikasi hasil seleksi? Calon siswa tidak dapat melihat hasil seleksi sampai dipublikasikan kembali.' : 'Pastikan hasil seleksi sudah final sebelum dipublikasikan ke calon siswa. Lanjutkan publikasi?' ?>">
            <?php if ($data['results_published']): ?>
                <input type="hidden" name="action" value="unpublish">
                <button class="btn btn-warning btn-lg" type="submit"><i class="fas fa-times-circle"></i> Batalkan Publikasi</button>
            <?php else: ?>
                <button class="btn btn-success btn-lg" type="submit"><i class="fas fa-broadcast-tower"></i> Publikasikan</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Hasil Seleksi Siap Publikasi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No Pendaftaran</th>
                            <th>Nama Siswa</th>
                            <th>Status Seleksi</th>
                            <th>Daftar Ulang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['applicants'] as $applicant): ?>
                            <tr>
                                <td><?= e($applicant['no']) ?></td>
                                <td><?= e($applicant['name']) ?></td>
                                <td><span class="badge badge-<?= e(status_class($applicant['selection_status'])) ?>"><?= e($applicant['selection_status']) ?></span></td>
                                <td>
                                    <?php if ($applicant['selection_status'] === 'Diterima'): ?>
                                        <span class="badge badge-<?= e(status_class($applicant['re_registration_status'])) ?>"><?= e($applicant['re_registration_status']) ?></span>
                                    <?php else: ?>
                                        -
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
