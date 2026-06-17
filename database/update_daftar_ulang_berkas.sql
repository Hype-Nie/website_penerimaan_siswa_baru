USE pendaftaran_siswa;

INSERT INTO settings (key_name, value)
VALUES ('re_registration_fee', '1000000')
ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP;

INSERT IGNORE INTO re_registration_documents (
    registration_id, document_type, file_name, original_name, file_path, status, note, uploaded_at
)
SELECT
    registration_id,
    'Bukti Transfer Daftar Ulang',
    file_name,
    original_name,
    file_path,
    status,
    note,
    uploaded_at
FROM re_registration_documents
WHERE document_type = 'Bukti Pembayaran / Administrasi';

UPDATE re_registration_documents new_doc
JOIN re_registration_documents old_doc
    ON old_doc.registration_id = new_doc.registration_id
    AND old_doc.document_type = 'Bukti Pembayaran / Administrasi'
SET
    new_doc.file_name = COALESCE(new_doc.file_name, old_doc.file_name),
    new_doc.original_name = COALESCE(new_doc.original_name, old_doc.original_name),
    new_doc.file_path = COALESCE(new_doc.file_path, old_doc.file_path),
    new_doc.status = CASE
        WHEN new_doc.file_path IS NULL AND old_doc.file_path IS NOT NULL THEN old_doc.status
        ELSE new_doc.status
    END,
    new_doc.note = COALESCE(new_doc.note, old_doc.note),
    new_doc.uploaded_at = COALESCE(new_doc.uploaded_at, old_doc.uploaded_at)
WHERE new_doc.document_type = 'Bukti Transfer Daftar Ulang';

DELETE FROM re_registration_documents
WHERE document_type = 'Bukti Pembayaran / Administrasi';

INSERT IGNORE INTO re_registration_documents (registration_id, document_type)
SELECT id, 'Surat Pernyataan Daftar Ulang'
FROM registrations
WHERE deleted_at IS NULL;

INSERT IGNORE INTO re_registration_documents (registration_id, document_type)
SELECT id, 'Bukti Transfer Daftar Ulang'
FROM registrations
WHERE deleted_at IS NULL;

INSERT IGNORE INTO re_registration_documents (registration_id, document_type)
SELECT id, 'Pas Foto Terbaru'
FROM registrations
WHERE deleted_at IS NULL;
