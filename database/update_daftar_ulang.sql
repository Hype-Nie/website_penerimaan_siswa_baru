USE pendaftaran_siswa;

INSERT INTO settings (key_name, value)
VALUES ('re_registration_fee', '1000000')
ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP;

ALTER TABLE registrations
    MODIFY re_registration_status ENUM('Belum Daftar Ulang', 'Sudah Daftar Ulang', 'Dikirim', 'Dikonfirmasi') NOT NULL DEFAULT 'Belum Daftar Ulang';

UPDATE registrations
SET re_registration_status = 'Belum Daftar Ulang'
WHERE re_registration_status = 'Sudah Daftar Ulang';

ALTER TABLE registrations
    MODIFY re_registration_status ENUM('Belum Daftar Ulang', 'Dikirim', 'Dikonfirmasi') NOT NULL DEFAULT 'Belum Daftar Ulang';

ALTER TABLE registrations
    ADD COLUMN student_identity_no VARCHAR(30) NULL AFTER re_registration_status,
    ADD COLUMN re_registered_at DATETIME NULL AFTER selected_at,
    ADD COLUMN re_registration_confirmed_at DATETIME NULL AFTER re_registered_at,
    ADD COLUMN re_registration_confirmed_by INT UNSIGNED NULL AFTER re_registration_confirmed_at,
    ADD INDEX idx_registration_student_identity_no (student_identity_no),
    ADD CONSTRAINT registrations_re_registration_confirmed_by_foreign FOREIGN KEY (re_registration_confirmed_by) REFERENCES users(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS re_registration_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    registration_id INT UNSIGNED NOT NULL,
    document_type VARCHAR(100) NOT NULL,
    file_name VARCHAR(255) NULL,
    original_name VARCHAR(255) NULL,
    file_path VARCHAR(255) NULL,
    status ENUM('Belum Upload', 'Dikirim', 'Dikonfirmasi') NOT NULL DEFAULT 'Belum Upload',
    note TEXT NULL,
    uploaded_at DATETIME NULL,
    verified_by INT UNSIGNED NULL,
    verified_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY re_registration_documents_registration_type_unique (registration_id, document_type),
    CONSTRAINT re_registration_documents_registration_id_foreign FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    CONSTRAINT re_registration_documents_verified_by_foreign FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE re_registration_documents
SET document_type = 'Bukti Transfer Daftar Ulang'
WHERE document_type = 'Bukti Pembayaran / Administrasi';

INSERT IGNORE INTO re_registration_documents (registration_id, document_type)
SELECT id, 'Surat Pernyataan Daftar Ulang' FROM registrations WHERE deleted_at IS NULL;

INSERT IGNORE INTO re_registration_documents (registration_id, document_type)
SELECT id, 'Bukti Transfer Daftar Ulang' FROM registrations WHERE deleted_at IS NULL;

INSERT IGNORE INTO re_registration_documents (registration_id, document_type)
SELECT id, 'Pas Foto Terbaru' FROM registrations WHERE deleted_at IS NULL;
