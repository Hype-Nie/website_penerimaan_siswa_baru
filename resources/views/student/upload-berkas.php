<?php
$student = $data['student'];
$canUploadDocuments = student_can_upload_documents($student);
$isAccepted = registration_is_accepted($student);
?>

<div class="container-fluid">
    <?php if ($isAccepted): ?>
        <div class="alert alert-info">
            Upload berkas sudah dikunci karena status seleksi Anda sudah diterima.
        </div>
    <?php elseif (! $canUploadDocuments): ?>
        <div class="alert alert-success">
            Berkas Anda sudah dinyatakan lengkap. Upload ulang hanya dibuka jika panitia menandai berkas tidak lengkap.
        </div>
    <?php elseif (($student['document_status'] ?? '') === 'Berkas Tidak Lengkap'): ?>
        <div class="alert alert-warning">
            Berkas Anda belum lengkap. Silakan unggah ulang dokumen yang diminta panitia.
            <?php if (! empty($student['admin_note'])): ?>
                <br><strong>Catatan panitia:</strong> <?= e($student['admin_note']) ?>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Unggah berkas persyaratan dalam format PDF, JPG, JPEG, atau PNG. Setelah disimpan, panitia akan melakukan verifikasi.
        </div>
    <?php endif; ?>

    <?php if ($canUploadDocuments): ?>
        <form action="<?= e(url_for('siswa-upload-berkas')) ?>" method="post" enctype="multipart/form-data">
    <?php endif; ?>
        <div class="row">
            <?php foreach ($data['documents'] as $document): ?>
                <?php
                $documentIsComplete = ($document['status'] ?? '') === 'Berkas Lengkap' || ($document['status'] ?? '') === 'Lengkap';
                $documentEditable = $canUploadDocuments && ! $documentIsComplete;
                ?>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="font-weight-bold text-gray-800 mb-1"><?= e($document['name']) ?></h5>
                                    <small class="text-muted">
                                        <?= e($document['file']) ?>
                                        <?php if (!empty($document['file_path'])): ?>
                                            (<a href="<?= e(url_for('view-berkas', ['id' => $document['id']])) ?>" target="_blank">Lihat</a>)
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <span class="badge badge-<?= e(status_class($document['status'])) ?>"><?= e($document['status']) ?></span>
                            </div>

                            <?php if ($documentEditable): ?>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="documents[<?= e($document['id'] ?? 0) ?>]" id="file-<?= e(md5($document['name'])) ?>">
                                    <label class="custom-file-label" for="file-<?= e(md5($document['name'])) ?>">Pilih file</label>
                                </div>
                            <?php else: ?>
                                <div class="form-control bg-light text-muted">
                                    <?= $documentIsComplete ? 'Berkas sudah lengkap' : 'Upload terkunci' ?>
                                </div>
                            <?php endif; ?>

                            <p class="small text-muted mt-3 mb-0">Catatan: <?= e($document['note']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($canUploadDocuments): ?>
            <button class="btn btn-primary" type="submit">Simpan Upload Berkas</button>
        <?php endif; ?>
    <?php if ($canUploadDocuments): ?>
        </form>
    <?php endif; ?>
</div>
