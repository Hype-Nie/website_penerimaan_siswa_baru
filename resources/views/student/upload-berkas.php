<div class="container-fluid">
    <form action="<?= e(url_for('siswa-upload-berkas')) ?>" method="post" enctype="multipart/form-data">
        <div class="row">
            <?php foreach ($data['documents'] as $document): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="font-weight-bold text-gray-800 mb-1"><?= e($document['name']) ?></h5>
                                    <small class="text-muted"><?= e($document['file']) ?></small>
                                </div>
                                <span class="badge badge-<?= e(status_class($document['status'])) ?>"><?= e($document['status']) ?></span>
                            </div>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="documents[<?= e($document['id'] ?? 0) ?>]" id="file-<?= e(md5($document['name'])) ?>">
                                <label class="custom-file-label" for="file-<?= e(md5($document['name'])) ?>">Pilih file</label>
                            </div>
                            <p class="small text-muted mt-3 mb-0">Catatan: <?= e($document['note']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button class="btn btn-primary" type="submit">Simpan Upload Berkas</button>
    </form>
</div>
