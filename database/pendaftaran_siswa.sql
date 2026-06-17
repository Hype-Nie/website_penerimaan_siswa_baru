CREATE DATABASE IF NOT EXISTS pendaftaran_siswa
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE pendaftaran_siswa;

DROP TABLE IF EXISTS selection_scores;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('siswa', 'admin', 'kepsek') NOT NULL DEFAULT 'siswa',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    registration_no VARCHAR(30) NOT NULL UNIQUE,
    nisn VARCHAR(30) NULL,
    gender ENUM('Laki-laki', 'Perempuan') NULL,
    birth_place VARCHAR(100) NULL,
    birth_date DATE NULL,
    religion VARCHAR(30) NULL,
    address TEXT NULL,
    father_name VARCHAR(150) NULL,
    mother_name VARCHAR(150) NULL,
    parent_phone VARCHAR(30) NULL,
    origin_school VARCHAR(150) NULL,
    school_year VARCHAR(20) NOT NULL DEFAULT '2026/2027',
    form_status ENUM('Belum Mengisi', 'Sudah Dikirim', 'Menunggu Verifikasi') NOT NULL DEFAULT 'Belum Mengisi',
    document_status ENUM('Belum Upload', 'Menunggu Verifikasi', 'Berkas Lengkap', 'Berkas Tidak Lengkap') NOT NULL DEFAULT 'Belum Upload',
    selection_status ENUM('Belum Diproses', 'Diterima', 'Tidak Diterima', 'Cadangan') NOT NULL DEFAULT 'Belum Diproses',
    re_registration_status ENUM('Belum Daftar Ulang', 'Dikirim', 'Dikonfirmasi') NOT NULL DEFAULT 'Belum Daftar Ulang',
    student_identity_no VARCHAR(30) NULL,
    admin_note TEXT NULL,
    selection_note TEXT NULL,
    submitted_at DATETIME NULL,
    selected_at DATETIME NULL,
    re_registered_at DATETIME NULL,
    re_registration_confirmed_at DATETIME NULL,
    re_registration_confirmed_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT registrations_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT registrations_re_registration_confirmed_by_foreign FOREIGN KEY (re_registration_confirmed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_registration_school_year (school_year),
    INDEX idx_registration_status (selection_status),
    INDEX idx_registration_student_identity_no (student_identity_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    registration_id INT UNSIGNED NOT NULL,
    document_type VARCHAR(100) NOT NULL,
    file_name VARCHAR(255) NULL,
    original_name VARCHAR(255) NULL,
    file_path VARCHAR(255) NULL,
    status ENUM('Belum Upload', 'Menunggu Verifikasi', 'Berkas Lengkap', 'Berkas Tidak Lengkap') NOT NULL DEFAULT 'Belum Upload',
    note TEXT NULL,
    verified_by INT UNSIGNED NULL,
    verified_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY documents_registration_type_unique (registration_id, document_type),
    CONSTRAINT documents_registration_id_foreign FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    CONSTRAINT documents_verified_by_foreign FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS announcements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    content TEXT NOT NULL,
    announcement_date DATE NOT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT announcements_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(80) NOT NULL UNIQUE,
    value TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS requirements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    requirement_text VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS schedules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    activity VARCHAR(150) NOT NULL,
    date_label VARCHAR(100) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT IGNORE INTO users (id, name, username, email, phone, password, role) VALUES
(1, 'Administrator', 'admin', 'admin@psb.test', '081111111111', '$2y$10$euFVclUnojtx265N0uoVk.sORkfMVIWzxzo/IUbSQly9si/S0yTfG', 'admin'),
(2, 'Kepala Sekolah', 'kepsek', 'kepsek@psb.test', '082222222222', '$2y$10$euFVclUnojtx265N0uoVk.sORkfMVIWzxzo/IUbSQly9si/S0yTfG', 'kepsek'),
(3, 'Chika Nabila', 'siswa', 'siswa@psb.test', '083333333333', '$2y$10$euFVclUnojtx265N0uoVk.sORkfMVIWzxzo/IUbSQly9si/S0yTfG', 'siswa');

INSERT IGNORE INTO registrations (
    id, user_id, registration_no, nisn, gender, birth_place, birth_date, religion, address,
    father_name, mother_name, parent_phone, origin_school, school_year,
    form_status, document_status, selection_status, admin_note, selection_note, submitted_at
) VALUES
(1, 3, 'PSB-2026-0001', '0123456789', 'Perempuan', 'Lamongan', '2018-08-19', 'Islam', 'Waduk 12',
 'Bapak Ahmad', 'Ibu Sari', '081298765432', 'TK Melati', '2026/2027',
 'Sudah Dikirim', 'Menunggu Verifikasi', 'Belum Diproses',
 'Data sudah masuk dan menunggu verifikasi panitia.', NULL, NOW());

INSERT IGNORE INTO documents (registration_id, document_type, file_name, original_name, file_path, status, note) VALUES
(1, 'Akta Kelahiran', NULL, NULL, NULL, 'Belum Upload', NULL),
(1, 'Kartu Keluarga', NULL, NULL, NULL, 'Belum Upload', NULL),
(1, 'Pas Foto', NULL, NULL, NULL, 'Belum Upload', NULL),
(1, 'Ijazah / SKL', NULL, NULL, NULL, 'Belum Upload', NULL),
(1, 'KTP Orang Tua', NULL, NULL, NULL, 'Belum Upload', NULL);

INSERT IGNORE INTO re_registration_documents (registration_id, document_type, file_name, original_name, file_path, status, note) VALUES
(1, 'Surat Pernyataan Daftar Ulang', NULL, NULL, NULL, 'Belum Upload', NULL),
(1, 'Bukti Transfer Daftar Ulang', NULL, NULL, NULL, 'Belum Upload', NULL),
(1, 'Pas Foto Terbaru', NULL, NULL, NULL, 'Belum Upload', NULL);

INSERT IGNORE INTO announcements (id, title, content, announcement_date, created_by) VALUES
(1, 'Pendaftaran Dibuka', 'Pendaftaran siswa baru dibuka mulai 1 Juni 2026.', '2026-06-01', 1),
(2, 'Batas Upload Berkas', 'Calon siswa wajib mengunggah dokumen persyaratan sebelum batas waktu.', '2026-06-20', 1),
(3, 'Hasil Seleksi', 'Hasil seleksi dapat dilihat melalui akun masing-masing setelah dipublikasikan.', '2026-06-30', 1);

INSERT IGNORE INTO settings (key_name, value) VALUES
('app_name', 'Penerimaan Siswa Baru'),
('school_name', 'MI IRSYADUL ATHFAL'),
('school_short_name', 'MI Irsyadul Athfal'),
('school_year', '2026/2027'),
('registration_period', '01 Juni 2026 - 30 Juni 2026'),
('student_quota', '40'),
('re_registration_fee', '1000000'),
('contact_phone', '0812-3456-7890'),
('contact_email', 'psb@miirsyadulathfal.sch.id'),
('school_address', 'Jl. Pendidikan No. 12, Kota Contoh'),
('results_published', '0');

INSERT IGNORE INTO schedules (id, activity, date_label, sort_order) VALUES
(1, 'Registrasi Akun', '01 - 15 Juni 2026', 1),
(2, 'Pengisian Formulir', '01 - 18 Juni 2026', 2),
(3, 'Upload dan Verifikasi Berkas', '01 - 20 Juni 2026', 3),
(4, 'Seleksi Administrasi', '21 - 25 Juni 2026', 4),
(5, 'Publikasi Hasil', '30 Juni 2026', 5);

INSERT IGNORE INTO requirements (id, requirement_text, sort_order) VALUES
(1, 'Akta kelahiran', 1),
(2, 'Kartu keluarga', 2),
(3, 'Pas foto terbaru', 3),
(4, 'Ijazah atau surat keterangan lulus', 4),
(5, 'KTP orang tua/wali', 5);
