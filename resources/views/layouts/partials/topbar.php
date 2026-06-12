<?php
$roleLabels = [
    'siswa' => 'Calon Siswa',
    'admin' => 'Administrator',
    'kepsek' => 'Kepala Sekolah',
];
$roleName = $roleLabels[$role ?? 'admin'] ?? 'Pengguna';
$displayName = $user['name'] ?? $roleName;
?>

<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow-sm">
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <h1 class="h5 mb-0 text-gray-800"><?= e($title ?? 'Dashboard') ?></h1>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= e($displayName) ?></span>
                <i class="fas fa-user-circle fa-lg"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profil
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?= e(url_for('logout')) ?>">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>
    </ul>
</nav>
