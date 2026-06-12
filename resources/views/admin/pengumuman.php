<div class="container-fluid">
    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Buat Pengumuman</h6>
                </div>
                <div class="card-body">
                    <form action="<?= e(url_for('admin-pengumuman')) ?>" method="post">
                        <div class="form-group">
                            <label>Judul</label>
                            <input type="text" class="form-control strong-input" name="title" placeholder="Judul pengumuman">
                        </div>
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="text" class="form-control strong-input" name="announcement_date" placeholder="Contoh: 01-06-2026">
                        </div>
                        <div class="form-group">
                            <label>Isi Pengumuman</label>
                            <textarea class="form-control strong-input" name="content" rows="6"></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Simpan Pengumuman</button>
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
                                <button class="btn btn-primary btn-sm">Edit</button>
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
