<!doctype html>
<html lang="id">
<head>
    <?php require base_path('resources/views/layouts/partials/head.php'); ?>
</head>
<body class="psb-guest-shell psb-page-<?= e($page ?? 'beranda') ?>">
    <?php if (! empty($flashMessages)): ?>
        <div class="guest-flash">
            <?php require base_path('resources/views/layouts/partials/flash.php'); ?>
        </div>
    <?php endif; ?>

    <?= $content ?>

    <?php require base_path('resources/views/layouts/partials/scripts.php'); ?>
</body>
</html>
