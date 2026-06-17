<?php
$student = $data['student'];
$selectionStatus = $student['selection_status'] ?? 'Belum Diumumkan';
$reRegistrationStatus = $student['re_registration_status'] ?? 'Belum Daftar Ulang';
$reRegistrationSchemaReady = re_registration_schema_ready();
$reRegistrationDocuments = $data['re_registration_documents'] ?? [];
$reRegistrationFee = format_rupiah(re_registration_fee_amount());
$statementTemplateUrl = url_for('template-surat-pernyataan-daftar-ulang');
$statementTemplateDownloadUrl = url_for('download-template-surat-pernyataan-daftar-ulang');
$selectionStatusClass = status_class($selectionStatus);
?>

<div class="container-fluid">
    <div class="selection-result-card shadow">
        <div class="selection-icon">
            <i class="fas fa-bullhorn"></i>
        </div>
        <div class="selection-status-highlight selection-status-<?= e($selectionStatusClass) ?>">
            <span><?= e($selectionStatus) ?></span>
        </div>
        <p>Hasil seleksi akan tampil setelah admin menyelesaikan proses seleksi dan kepala sekolah mempublikasikan hasil penerimaan siswa baru.</p>
        <div class="result-detail">
            <span>Nomor Pendaftaran</span>
            <strong><?= e($student['registration_no']) ?></strong>
        </div>
        <div class="result-detail">
            <span>Nama Calon Siswa</span>
            <strong><?= e($student['name']) ?></strong>
        </div>

        <?php if ($selectionStatus === 'Diterima' && published_results()): ?>
            <div class="mt-4">
                <hr>
                <h4 class="mb-3">Daftar Ulang</h4>

                <?php if (! $reRegistrationSchemaReady): ?>
                    <div class="alert alert-danger mb-0">
                        Struktur database daftar ulang belum diperbarui. Jalankan file <strong>database/update_daftar_ulang.sql</strong> terlebih dahulu.
                    </div>
                <?php else: ?>
                    <div class="alert alert-primary text-left">
                        <div><strong>Biaya daftar ulang: <?= e($reRegistrationFee) ?></strong></div>
                        <div>Template surat pernyataan bisa dilihat <a href="<?= e($statementTemplateUrl) ?>" target="_blank">di sini</a> dan didownload <a href="<?= e($statementTemplateDownloadUrl) ?>">di sini</a>.</div>
                    </div>

                <?php if ($reRegistrationStatus === 'Dikonfirmasi'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Daftar ulang Anda sudah dikonfirmasi. Status Anda sudah menjadi siswa terdaftar.
                    </div>
                    <div class="result-detail">
                        <span>Nomor Induk Siswa</span>
                        <strong><?= e($student['student_identity_no'] ?: '-') ?></strong>
                    </div>
                    <div class="result-detail">
                        <span>Bukti Pendaftaran</span>
                        <strong><?= e($student['registration_no']) ?></strong>
                    </div>
                <?php elseif ($reRegistrationStatus === 'Dikirim'): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-clock"></i> Data daftar ulang sudah dikirim dan sedang menunggu konfirmasi admin.
                    </div>
                    <?php foreach ($reRegistrationDocuments as $document): ?>
                        <div class="status-row">
                            <span><?= e($document['name']) ?></span>
                            <span>
                                <?= e($document['file']) ?>
                                <?php if (! empty($document['file_path'])): ?>
                                    (<a href="<?= e(url_for('view-daftar-ulang-berkas', ['id' => $document['id']])) ?>" target="_blank">Lihat</a>)
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle"></i> Anda wajib mengisi daftar ulang dan mengunggah berkas tambahan berikut.
                    </div>
                    <form action="<?= e(url_for('siswa-hasil')) ?>" method="post" enctype="multipart/form-data" data-confirm="Pastikan data daftar ulang dan berkas tambahan sudah benar sebelum dikirim ke admin. Lanjutkan kirim daftar ulang?">
                        <input type="hidden" name="action" value="re_register">
                        <?php foreach ($reRegistrationDocuments as $document): ?>
                            <div class="form-group text-left">
                                <label class="font-weight-bold"><?= e($document['name']) ?></label>
                                <?php if ($document['name'] === 'Surat Pernyataan Daftar Ulang'): ?>
                                    <div class="small text-muted mb-2">
                                        Template surat pernyataan bisa dilihat <a href="<?= e($statementTemplateUrl) ?>" target="_blank">di sini</a> dan didownload <a href="<?= e($statementTemplateDownloadUrl) ?>">di sini</a>, kemudian unggah file yang sudah diisi dan ditandatangani.
                                    </div>
                                <?php endif; ?>
                                <?php if (! empty($document['file_path'])): ?>
                                    <div class="small text-muted mb-2">
                                        File saat ini: <?= e($document['file']) ?>
                                        (<a href="<?= e(url_for('view-daftar-ulang-berkas', ['id' => $document['id']])) ?>" target="_blank">Lihat</a>)
                                    </div>
                                <?php endif; ?>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="re_registration_documents[<?= e($document['id']) ?>]" id="re-registration-file-<?= e($document['id']) ?>" <?= empty($document['file_path']) ? 'required' : '' ?>>
                                    <label class="custom-file-label" for="re-registration-file-<?= e($document['id']) ?>">Pilih file</label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-save"></i> Simpan Data Daftar Ulang
                        </button>
                    </form>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info mb-0 mt-4">Jika dinyatakan diterima, informasi daftar ulang akan muncul pada halaman ini setelah hasil dipublikasikan.</div>
        <?php endif; ?>
    </div>
</div>
