<!doctype html>
<html lang="id">
<head>
    <?php require base_path('resources/views/layouts/partials/head.php'); ?>
</head>
<body id="page-top" class="psb-admin-shell psb-role-<?= e($role ?? 'guest') ?>">
    <div id="wrapper">
        <?php require base_path('resources/views/layouts/partials/sidebar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php require base_path('resources/views/layouts/partials/topbar.php'); ?>

                <main>
                    <div class="container-fluid">
                        <?php require base_path('resources/views/layouts/partials/flash.php'); ?>
                    </div>
                    <?= $content ?>
                </main>
            </div>

            <?php require base_path('resources/views/layouts/partials/footer.php'); ?>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php require base_path('resources/views/layouts/partials/scripts.php'); ?>
</body>
</html>
