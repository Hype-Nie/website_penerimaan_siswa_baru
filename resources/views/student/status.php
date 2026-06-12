<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Progres Pendaftaran</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tahapan</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['timeline'] as $item): ?>
                            <tr>
                                <td><?= e($item['step']) ?></td>
                                <td><span class="badge badge-<?= e(status_class($item['status'])) ?>"><?= e($item['status']) ?></span></td>
                                <td><?= $item['status'] === 'Menunggu Verifikasi' ? 'Menunggu pengecekan panitia.' : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

