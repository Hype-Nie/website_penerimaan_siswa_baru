<?php $stats = $data['stats']; ?>

<div class="container-fluid">
    <div class="row">
        <?php
        $cards = [
            ['label' => 'Total Pendaftar', 'value' => $stats['total_pendaftar'], 'color' => 'primary', 'icon' => 'fa-users'],
            ['label' => 'Menunggu Verifikasi', 'value' => $stats['menunggu_verifikasi'], 'color' => 'warning', 'icon' => 'fa-clock'],
            ['label' => 'Berkas Lengkap', 'value' => $stats['berkas_lengkap'], 'color' => 'success', 'icon' => 'fa-check-circle'],
            ['label' => 'Diterima', 'value' => $stats['diterima'], 'color' => 'info', 'icon' => 'fa-award'],
            ['label' => 'Tidak Diterima', 'value' => $stats['tidak_diterima'], 'color' => 'danger', 'icon' => 'fa-times-circle'],
        ];
        ?>

        <?php foreach ($cards as $card): ?>
            <div class="col-xl col-md-6 mb-4">
                <div class="card border-left-<?= e($card['color']) ?> shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-<?= e($card['color']) ?> text-uppercase mb-1"><?= e($card['label']) ?></div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= e($card['value']) ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas <?= e($card['icon']) ?> fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Pendaftar Baru</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>UTS</th>
                            <th>UAS</th>
                            <th>UN</th>
                            <th>Rata-rata</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['applicants'] as $index => $applicant): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= e($applicant['name']) ?></td>
                                <td><?= e($applicant['address']) ?></td>
                                <td><?= e($applicant['uts']) ?></td>
                                <td><?= e($applicant['uas']) ?></td>
                                <td><?= e($applicant['un']) ?></td>
                                <td><?= e(number_format($applicant['average'], 2)) ?></td>
                                <td><span class="badge badge-info">Baru</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

