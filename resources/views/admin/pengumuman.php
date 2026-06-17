<?php
$editId = (int) ($_GET['edit'] ?? 0);
$editAnnouncement = null;

foreach ($data['announcements'] as $announcement) {
    if ((int) ($announcement['id'] ?? 0) === $editId) {
        $editAnnouncement = $announcement;
        break;
    }
}

$isEditing = $editAnnouncement !== null;
$announcementTitle = $isEditing ? $editAnnouncement['title'] : '';
$announcementDate = $isEditing ? ($editAnnouncement['date_value'] ?? date('Y-m-d')) : date('Y-m-d');
$announcementContent = $isEditing ? $editAnnouncement['content'] : '';
?>

<div class="container-fluid">
    <?php if ($editId > 0 && ! $editAnnouncement): ?>
        <div class="alert alert-warning">Pengumuman yang dipilih untuk diedit tidak ditemukan.</div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?= $isEditing ? 'Edit Pengumuman' : 'Buat Pengumuman' ?></h6>
                </div>
                <div class="card-body">
                    <form action="<?= e(url_for('admin-pengumuman')) ?>" method="post">
                        <?php if ($isEditing): ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= e($editAnnouncement['id']) ?>">
                        <?php endif; ?>
                        <div class="form-group">
                            <label>Judul</label>
                            <input type="text" class="form-control strong-input" name="title" value="<?= e($announcementTitle) ?>" placeholder="Judul pengumuman" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" class="form-control strong-input" name="announcement_date" value="<?= e($announcementDate) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Isi Pengumuman</label>
                            <textarea class="form-control strong-input" name="content" rows="6" required><?= e($announcementContent) ?></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit"><?= $isEditing ? 'Update Pengumuman' : 'Simpan Pengumuman' ?></button>
                        <?php if ($isEditing): ?>
                            <a class="btn btn-outline-secondary ml-2" href="<?= e(url_for('admin-pengumuman')) ?>">Batal Edit</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Pengumuman</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($data['announcements'] as $announcement): ?>
                        <div class="announcement-list-item">
                            <div>
                                <small><?= e($announcement['date']) ?></small>
                                <h5><?= e($announcement['title']) ?></h5>
                                <p><?= e($announcement['content']) ?></p>
                            </div>
                            <div class="table-actions">
                                <a class="btn btn-primary btn-sm" href="<?= e(url_for('admin-pengumuman', ['edit' => $announcement['id'] ?? 0])) ?>">Edit</a>
                                <form action="<?= e(url_for('admin-pengumuman')) ?>" method="post">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= e($announcement['id'] ?? 0) ?>">
                                    <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
