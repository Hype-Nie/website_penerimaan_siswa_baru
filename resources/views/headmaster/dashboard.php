<?php $stats = $data['stats']; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pendaftar</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= e($stats['total_pendaftar']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Diterima</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= e($stats['diterima']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Tidak Diterima</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= e($stats['tidak_diterima']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Menunggu Verifikasi</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= e($stats['menunggu_verifikasi']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Rekap Status Berkas</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="small mb-1">Berkas Lengkap</div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 60%">60%</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="small mb-1">Menunggu Verifikasi</div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: 30%">30%</div>
                        </div>
                    </div>
                    <div>
                        <div class="small mb-1">Tidak Lengkap</div>
                        <div class="progress">
                            <div class="progress-bar bg-danger" style="width: 10%">10%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aksi Kepala Sekolah</h6>
                </div>
                <div class="card-body quick-menu">
                    <a href="<?= e(url_for('kepsek-laporan')) ?>"><i class="fas fa-chart-bar"></i> Lihat Laporan PSB</a>
                    <a href="<?= e(url_for('kepsek-publikasi')) ?>"><i class="fas fa-broadcast-tower"></i> Publikasi Hasil</a>
                </div>
            </div>
        </div>
    </div>
</div>

