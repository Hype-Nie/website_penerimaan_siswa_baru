<?php
$currentPage = $page ?? ($_GET['page'] ?? 'beranda');
$currentRole = $role ?? 'admin';

$sidebars = [
    'siswa' => [
        'brand' => 'PENDAFTAR',
        'icon' => 'fa-user-graduate',
        'menus' => [
            ['page' => 'siswa-dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt'],
            ['page' => 'siswa-formulir', 'label' => 'Formulir Pendaftaran', 'icon' => 'fa-file-alt'],
            ['page' => 'siswa-upload-berkas', 'label' => 'Upload Berkas', 'icon' => 'fa-upload'],
            ['page' => 'siswa-status', 'label' => 'Status Pendaftaran', 'icon' => 'fa-tasks'],
            ['page' => 'siswa-hasil', 'label' => 'Hasil Seleksi', 'icon' => 'fa-bullhorn'],
            ['page' => 'siswa-profil', 'label' => 'Profil', 'icon' => 'fa-user'],
        ],
    ],
    'admin' => [
        'brand' => 'ADMIN',
        'icon' => 'fa-user-shield',
        'menus' => [
            ['page' => 'admin-dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'aliases' => ['dashboard']],
            ['page' => 'admin-data-pendaftaran', 'label' => 'Data Pendaftaran', 'icon' => 'fa-table'],
            ['page' => 'admin-verifikasi-berkas', 'label' => 'Verifikasi Berkas', 'icon' => 'fa-clipboard-check'],
            ['page' => 'admin-proses-seleksi', 'label' => 'Proses Seleksi', 'icon' => 'fa-check-double'],
            ['page' => 'admin-hasil-seleksi', 'label' => 'Hasil Seleksi', 'icon' => 'fa-award'],
            ['page' => 'admin-pengumuman', 'label' => 'Pengumuman', 'icon' => 'fa-bullhorn'],
        ],
    ],
    'kepsek' => [
        'brand' => 'KEPALA SEKOLAH',
        'icon' => 'fa-user-tie',
        'menus' => [
            ['page' => 'kepsek-dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt'],
            ['page' => 'kepsek-laporan', 'label' => 'Laporan PSB', 'icon' => 'fa-chart-bar'],
            ['page' => 'kepsek-publikasi', 'label' => 'Publikasi Hasil', 'icon' => 'fa-broadcast-tower'],
        ],
    ],
];

$sidebar = $sidebars[$currentRole] ?? $sidebars['admin'];
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= e(url_for($sidebar['menus'][0]['page'])) ?>">
        <div class="sidebar-brand-icon">
            <i class="fas <?= e($sidebar['icon']) ?>"></i>
        </div>
        <div class="sidebar-brand-text mx-3"><?= e($sidebar['brand']) ?></div>
    </a>

    <hr class="sidebar-divider my-0">

    <?php foreach ($sidebar['menus'] as $index => $menu): ?>
        <?php
        $activePages = array_merge([$menu['page']], $menu['aliases'] ?? []);
        $isActive = in_array($currentPage, $activePages, true);
        ?>
        <?php if ($index === 1): ?>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Menu</div>
        <?php endif; ?>
        <li class="nav-item <?= $isActive ? 'active' : '' ?>">
            <a class="nav-link" href="<?= e(url_for($menu['page'])) ?>">
                <i class="fas fa-fw <?= e($menu['icon']) ?>"></i>
                <span><?= e($menu['label']) ?></span>
            </a>
        </li>
    <?php endforeach; ?>

    <hr class="sidebar-divider">

    <li class="nav-item">
        <a class="nav-link" href="<?= e(url_for('logout')) ?>">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
