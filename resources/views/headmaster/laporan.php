<?php $stats = $data['stats']; ?>

<div class="container-fluid">
    <div class="report-summary shadow mb-4">
        <div>
            <span>Total Pendaftar</span>
            <strong><?= e($stats['total_pendaftar']) ?></strong>
        </div>
        <div>
            <span>Berkas Lengkap</span>
            <strong><?= e($stats['berkas_lengkap']) ?></strong>
        </div>
        <div>
            <span>Diterima</span>
            <strong><?= e($stats['diterima']) ?></strong>
        </div>
        <div>
            <span>Tidak Diterima</span>
            <strong><?= e($stats['tidak_diterima']) ?></strong>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Laporan Penerimaan Siswa Baru</h6>
            <div class="d-print-none">
                <a href="<?= e(url_for('cetak-laporan')) ?>" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-print"></i> Cetak</a>
                <a href="<?= e(url_for('export-pdf')) ?>" target="_blank" class="btn btn-outline-danger btn-sm"><i class="fas fa-file-pdf"></i> Export PDF</a>
                <a href="<?= e(url_for('export-csv')) ?>" target="_blank" class="btn btn-outline-success btn-sm"><i class="fas fa-file-csv"></i> Export CSV</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No Pendaftaran</th>
                            <th>Nama</th>
                            <th>Status Berkas</th>
                            <th>Status Seleksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['applicants'] as $applicant): ?>
                            <tr>
                                <td><?= e($applicant['date']) ?></td>
                                <td><?= e($applicant['no']) ?></td>
                                <td><?= e($applicant['name']) ?></td>
                                <td><span class="badge badge-<?= e(status_class($applicant['document_status'])) ?>"><?= e($applicant['document_status']) ?></span></td>
                                <td><span class="badge badge-<?= e(status_class($applicant['selection_status'])) ?>"><?= e($applicant['selection_status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

