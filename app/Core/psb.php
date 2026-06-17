<?php

function redirect_to(string $page, array $params = []): void
{
    header('Location: ' . url_for($page, $params));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pull_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $messages;
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id']) || ! db_available()) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND status = "active" AND deleted_at IS NULL LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function route_home_for_role(string $role): string
{
    if ($role === 'admin') {
        return 'admin-dashboard';
    }

    if ($role === 'kepsek') {
        return 'kepsek-dashboard';
    }

    return 'siswa-dashboard';
}

function require_role(string $routeRole): void
{
    if ($routeRole === 'guest') {
        return;
    }

    $user = current_user();

    if (! $user) {
        flash('warning', 'Silakan login terlebih dahulu.');
        redirect_to('login');
    }

    if ($user['role'] !== $routeRole) {
        flash('warning', 'Anda tidak memiliki akses ke halaman tersebut.');
        redirect_to(route_home_for_role($user['role']));
    }
}

function auth_login(string $email, string $password): bool
{
    if (! db_available()) {
        flash('danger', 'Database belum bisa diakses. Periksa konfigurasi .env dan MySQL.');
        return false;
    }

    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('danger', 'Format email tidak valid.');
        return false;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND status = "active" AND deleted_at IS NULL LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (! $user || ! password_verify($password, $user['password'])) {
        flash('danger', 'Email atau password salah.');
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role'] = $user['role'];

    return true;
}

function auth_logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function register_student_account(array $input): bool
{
    if (! db_available()) {
        flash('danger', 'Database belum bisa diakses. Import file SQL dan periksa konfigurasi database.');
        return false;
    }

    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $password = $input['password'] ?? '';
    $confirmation = $input['password_confirmation'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        flash('danger', 'Nama, email, dan password wajib diisi.');
        return false;
    }

    if ($password !== $confirmation) {
        flash('danger', 'Konfirmasi password tidak sesuai.');
        return false;
    }

    $emailExists = db()->prepare('SELECT id FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1');
    $emailExists->execute([$email]);

    if ($emailExists->fetch()) {
        flash('danger', 'Email sudah terdaftar. Silakan login atau gunakan email lain.');
        return false;
    }

    $username = strtolower(preg_replace('/[^a-z0-9]+/i', '', strtok($email, '@')));
    $username = $username !== '' ? $username : 'siswa';

    $exists = db()->prepare('SELECT id FROM users WHERE username = ? AND deleted_at IS NULL LIMIT 1');
    $exists->execute([$username]);

    if ($exists->fetch()) {
        $username .= random_int(100, 999);
    }

    try {
        db()->beginTransaction();

        $stmt = db()->prepare('INSERT INTO users (name, username, email, phone, password, role) VALUES (?, ?, ?, ?, ?, "siswa")');
        $stmt->execute([$name, $username, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]);
        $userId = (int) db()->lastInsertId();

        $registrationNo = generate_registration_no();
        $stmt = db()->prepare('
            INSERT INTO registrations (
                user_id, registration_no, gender, birth_place, birth_date, religion, address, school_year
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $userId,
            $registrationNo,
            normalize_gender($input['gender'] ?? null),
            trim($input['birth_place'] ?? ''),
            normalize_date($input['birth_date'] ?? null),
            trim($input['religion'] ?? ''),
            trim($input['address'] ?? ''),
            app_setting('school_year', '2026/2027'),
        ]);

        ensure_registration_documents((int) db()->lastInsertId());

        db()->commit();

        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = 'siswa';
        flash('success', 'Registrasi berhasil. Silakan lengkapi formulir pendaftaran.');

        return true;
    } catch (Throwable $exception) {
        db()->rollBack();
        flash('danger', 'Registrasi gagal: ' . $exception->getMessage());
        return false;
    }
}

function generate_registration_no(): string
{
    $year = date('Y');
    $stmt = db()->prepare('SELECT registration_no FROM registrations WHERE registration_no LIKE ? ORDER BY id DESC LIMIT 1');
    $stmt->execute(['PSB-' . $year . '-%']);
    $lastNo = $stmt->fetchColumn();

    if ($lastNo) {
        $parts = explode('-', $lastNo);
        $total = (int) end($parts) + 1;
    } else {
        $total = 1;
    }

    return sprintf('PSB-%s-%04d', $year, $total);
}

function normalize_gender($gender): ?string
{
    if ($gender === 'Laki Laki' || $gender === 'Laki-laki') {
        return 'Laki-laki';
    }

    if ($gender === 'Perempuan') {
        return 'Perempuan';
    }

    return null;
}

function normalize_date($date): ?string
{
    $date = trim((string) $date);

    if ($date === '') {
        return null;
    }

    foreach (['Y-m-d', 'd-m-Y', 'd/m/Y'] as $format) {
        $parsed = DateTime::createFromFormat($format, $date);

        if ($parsed instanceof DateTime) {
            return $parsed->format('Y-m-d');
        }
    }

    return null;
}

function format_date_id($date): string
{
    if (! $date) {
        return '-';
    }

    $parsed = DateTime::createFromFormat('Y-m-d', substr((string) $date, 0, 10));

    return $parsed ? $parsed->format('d-m-Y') : (string) $date;
}

function app_setting(string $key, $default = null)
{
    if (! db_available()) {
        return $default;
    }

    $stmt = db()->prepare('SELECT value FROM settings WHERE key_name = ? LIMIT 1');
    $stmt->execute([$key]);
    $row = $stmt->fetch();

    return $row['value'] ?? $default;
}

function set_app_setting(string $key, string $value): void
{
    $stmt = db()->prepare('
        INSERT INTO settings (key_name, value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP
    ');
    $stmt->execute([$key, $value]);
}

function get_settings_map(): array
{
    if (! db_available()) {
        return [];
    }

    $rows = db()->query('SELECT key_name, value FROM settings')->fetchAll();
    $settings = [];

    foreach ($rows as $row) {
        $settings[$row['key_name']] = $row['value'];
    }

    return $settings;
}

function get_school_data(array $sample): array
{
    $settings = get_settings_map();

    return [
        'name' => $settings['school_name'] ?? $sample['school']['name'],
        'short_name' => $settings['school_short_name'] ?? $sample['school']['short_name'],
        'year' => $settings['school_year'] ?? $sample['school']['year'],
        'period' => $settings['registration_period'] ?? $sample['school']['period'],
        'quota' => $settings['student_quota'] ?? $sample['school']['quota'],
        'contact' => $settings['contact_phone'] ?? $sample['school']['contact'],
        'email' => $settings['contact_email'] ?? $sample['school']['email'],
        'address' => $settings['school_address'] ?? $sample['school']['address'],
    ];
}

function psb_stats(array $sample): array
{
    if (! db_available()) {
        return $sample['stats'];
    }

    $row = db()->query('
        SELECT
            COUNT(*) AS total_pendaftar,
            SUM(document_status = "Belum Upload") AS belum_upload,
            SUM(document_status = "Menunggu Verifikasi") AS menunggu_verifikasi,
            SUM(document_status = "Berkas Lengkap") AS berkas_lengkap,
            SUM(document_status = "Berkas Tidak Lengkap") AS berkas_tidak_lengkap,
            SUM(selection_status = "Diterima") AS diterima,
            SUM(selection_status = "Tidak Diterima") AS tidak_diterima,
            SUM(selection_status = "Cadangan") AS cadangan
        FROM registrations
        WHERE deleted_at IS NULL
    ')->fetch();

    return [
        'total_pendaftar' => (int) ($row['total_pendaftar'] ?? 0),
        'belum_upload' => (int) ($row['belum_upload'] ?? 0),
        'menunggu_verifikasi' => (int) ($row['menunggu_verifikasi'] ?? 0),
        'berkas_lengkap' => (int) ($row['berkas_lengkap'] ?? 0),
        'berkas_tidak_lengkap' => (int) ($row['berkas_tidak_lengkap'] ?? 0),
        'diterima' => (int) ($row['diterima'] ?? 0),
        'tidak_diterima' => (int) ($row['tidak_diterima'] ?? 0),
        'cadangan' => (int) ($row['cadangan'] ?? 0),
    ];
}

function applicant_rows(array $sample): array
{
    if (! db_available()) {
        return $sample['applicants'];
    }

    $where = ['r.deleted_at IS NULL', 'u.deleted_at IS NULL'];
    $params = [];

    $query = trim($_GET['q'] ?? '');
    $status = trim($_GET['status'] ?? '');

    if ($query !== '') {
        $where[] = '(u.name LIKE ? OR r.nisn LIKE ? OR r.registration_no LIKE ?)';
        $like = '%' . $query . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    if ($status !== '') {
        $where[] = '(r.form_status = ? OR r.document_status = ? OR r.selection_status = ?)';
        $params[] = $status;
        $params[] = $status;
        $params[] = $status;
    }

    $sql = '
        SELECT r.*, u.name, u.email, u.phone
        FROM registrations r
        JOIN users u ON u.id = r.user_id
    ';

    if ($where !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY r.created_at DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    return array_map('map_registration_row', $rows);
}

function map_registration_row(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'user_id' => (int) $row['user_id'],
        'no' => $row['registration_no'],
        'registration_no' => $row['registration_no'],
        'name' => $row['name'],
        'email' => $row['email'] ?? '',
        'phone' => $row['phone'] ?? '',
        'nisn' => $row['nisn'] ?? '',
        'gender' => $row['gender'] ?? '-',
        'birth_place' => $row['birth_place'] ?? '',
        'birth_date' => $row['birth_date'] ?? null,
        'religion' => $row['religion'] ?? '',
        'address' => $row['address'] ?? '',
        'father' => $row['father_name'] ?? '',
        'mother' => $row['mother_name'] ?? '',
        'parent_phone' => $row['parent_phone'] ?? '',
        'origin_school' => $row['origin_school'] ?? '',
        'school_year' => $row['school_year'] ?? '',
        'date' => format_date_id($row['created_at'] ?? null),
        'form_status' => $row['form_status'],
        'document_status' => $row['document_status'],
        'selection_status' => $row['selection_status'],
        're_registration_status' => $row['re_registration_status'] ?? 'Belum Daftar Ulang',
        'student_identity_no' => $row['student_identity_no'] ?? '',
        'admin_note' => $row['admin_note'] ?? '',
        'selection_note' => $row['selection_note'] ?? '',
        'submitted_at' => $row['submitted_at'] ?? null,
        'selected_at' => $row['selected_at'] ?? null,
        're_registered_at' => $row['re_registered_at'] ?? null,
        're_registration_confirmed_at' => $row['re_registration_confirmed_at'] ?? null,
    ];
}

function find_registration_by_id($id): ?array
{
    if (! db_available()) {
        return null;
    }

    $stmt = db()->prepare('
        SELECT r.*, u.name, u.email, u.phone
        FROM registrations r
        JOIN users u ON u.id = r.user_id
        WHERE r.id = ? AND r.deleted_at IS NULL AND u.deleted_at IS NULL
        LIMIT 1
    ');
    $stmt->execute([(int) $id]);
    $row = $stmt->fetch();

    return $row ? map_registration_row($row) : null;
}

function current_student_data(array $sample): array
{
    if (! db_available()) {
        return $sample['student'];
    }

    $user = current_user();
    $registration = null;

    if ($user && $user['role'] === 'siswa') {
        $registration = ensure_registration_for_user((int) $user['id']);
    }

    if (! $registration) {
        $row = db()->query('
            SELECT r.*, u.name, u.email, u.phone
            FROM registrations r
            JOIN users u ON u.id = r.user_id
            WHERE r.deleted_at IS NULL AND u.deleted_at IS NULL
            ORDER BY r.id ASC
            LIMIT 1
        ')->fetch();
        $registration = $row ? map_registration_row($row) : null;
    }

    if (! $registration) {
        return $sample['student'];
    }

    return [
        'id' => $registration['id'],
        'registration_no' => $registration['registration_no'],
        'name' => $registration['name'],
        'email' => $registration['email'],
        'phone' => $registration['phone'],
        'nisn' => $registration['nisn'],
        'gender' => $registration['gender'],
        'birth_place' => $registration['birth_place'],
        'birth_date' => $registration['birth_date'],
        'religion' => $registration['religion'],
        'address' => $registration['address'],
        'father' => $registration['father'],
        'mother' => $registration['mother'],
        'parent_phone' => $registration['parent_phone'],
        'origin_school' => $registration['origin_school'],
        'form_status' => $registration['form_status'],
        'document_status' => $registration['document_status'],
        'raw_selection_status' => $registration['selection_status'],
        'selection_status' => published_results() ? $registration['selection_status'] : 'Belum Diumumkan',
        're_registration_status' => $registration['re_registration_status'] ?? 'Belum Daftar Ulang',
        'student_identity_no' => $registration['student_identity_no'] ?? '',
        'admin_note' => $registration['admin_note'] ?? '',
        'selection_note' => $registration['selection_note'] ?? '',
    ];
}

function registration_for_user(int $userId): ?array
{
    $stmt = db()->prepare('
        SELECT r.*, u.name, u.email, u.phone
        FROM registrations r
        JOIN users u ON u.id = r.user_id
        WHERE r.user_id = ? AND r.deleted_at IS NULL AND u.deleted_at IS NULL
        LIMIT 1
    ');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    return $row ? map_registration_row($row) : null;
}

function ensure_registration_for_user(int $userId): ?array
{
    $registration = registration_for_user($userId);

    if ($registration) {
        ensure_registration_documents((int) $registration['id']);
        return $registration;
    }

    $stmt = db()->prepare('INSERT INTO registrations (user_id, registration_no, school_year) VALUES (?, ?, ?)');
    $stmt->execute([$userId, generate_registration_no(), app_setting('school_year', '2026/2027')]);
    $registrationId = (int) db()->lastInsertId();
    ensure_registration_documents($registrationId);

    return registration_for_user($userId);
}

function ensure_registration_documents(int $registrationId): void
{
    $types = ['Akta Kelahiran', 'Kartu Keluarga', 'Pas Foto', 'Ijazah / SKL', 'KTP Orang Tua'];

    foreach ($types as $type) {
        $stmt = db()->prepare('SELECT id FROM documents WHERE registration_id = ? AND document_type = ? LIMIT 1');
        $stmt->execute([$registrationId, $type]);

        if (! $stmt->fetch()) {
            $insert = db()->prepare('INSERT INTO documents (registration_id, document_type) VALUES (?, ?)');
            $insert->execute([$registrationId, $type]);
        }
    }
}

function documents_for_registration(?int $registrationId, array $sample): array
{
    if (! db_available() || ! $registrationId) {
        return $sample['documents'];
    }

    ensure_registration_documents($registrationId);

    $stmt = db()->prepare('SELECT * FROM documents WHERE registration_id = ? ORDER BY id ASC');
    $stmt->execute([$registrationId]);

    return array_map(static function ($row) {
        return [
            'id' => (int) $row['id'],
            'name' => $row['document_type'],
            'file' => $row['original_name'] ?: ($row['file_name'] ?: '-'),
            'file_path' => $row['file_path'],
            'status' => $row['status'],
            'note' => $row['note'] ?: '-',
        ];
    }, $stmt->fetchAll());
}

function re_registration_document_types(): array
{
    return [
        'Surat Pernyataan Daftar Ulang',
        'Bukti Transfer Daftar Ulang',
        'Pas Foto Terbaru',
    ];
}

function re_registration_legacy_document_types(): array
{
    return [
        'Bukti Pembayaran / Administrasi' => 'Bukti Transfer Daftar Ulang',
    ];
}

function re_registration_fee_amount(): int
{
    return (int) app_setting('re_registration_fee', 1000000);
}

function format_rupiah(int $amount): string
{
    return 'Rp' . number_format($amount, 0, ',', '.');
}

function re_registration_statement_template_html(bool $includeActions = true): string
{
    $sample = sample_data();
    $school = get_school_data($sample);
    $schoolName = e($school['name'] ?? 'MI IRSYADUL ATHFAL');
    $schoolYear = e($school['year'] ?? app_setting('school_year', '2026/2027'));
    $fee = e(format_rupiah(re_registration_fee_amount()));
    $today = e(format_date_id(date('Y-m-d')));
    $downloadUrl = e(url_for('download-template-surat-pernyataan-daftar-ulang'));
    $actions = $includeActions ? <<<HTML
    <div class="actions">
        <a href="{$downloadUrl}">Download Template</a>
        <button type="button" onclick="window.print()">Cetak</button>
    </div>
HTML : '';

    return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Template Surat Pernyataan Daftar Ulang</title>
    <style>
        body { background: #f4f6f9; color: #111827; font-family: Arial, sans-serif; margin: 0; padding: 32px; }
        .paper { background: #fff; border: 1px solid #d1d5db; margin: 0 auto; max-width: 760px; min-height: 960px; padding: 48px; }
        h1 { font-size: 18px; margin: 0 0 24px; text-align: center; text-decoration: underline; }
        p { font-size: 14px; line-height: 1.7; margin: 12px 0; }
        table { border-collapse: collapse; font-size: 14px; margin: 14px 0 22px; width: 100%; }
        td { padding: 6px 0; vertical-align: top; }
        td:first-child { width: 190px; }
        .signature { margin-top: 48px; text-align: right; }
        .signature-space { height: 72px; }
        .actions { margin: 0 auto 16px; max-width: 760px; text-align: right; }
        .actions a, .actions button { background: #2563eb; border: 0; border-radius: 4px; color: #fff; cursor: pointer; display: inline-block; font-size: 14px; margin-left: 8px; padding: 10px 14px; text-decoration: none; }
        @media print {
            body { background: #fff; padding: 0; }
            .paper { border: 0; max-width: none; min-height: 0; padding: 24px; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    {$actions}
    <div class="paper">
        <h1>SURAT PERNYATAAN DAFTAR ULANG</h1>
        <p>Yang bertanda tangan di bawah ini:</p>
        <table>
            <tr><td>Nama Calon Siswa</td><td>: ............................................................</td></tr>
            <tr><td>Nomor Pendaftaran</td><td>: ............................................................</td></tr>
            <tr><td>NISN</td><td>: ............................................................</td></tr>
            <tr><td>Nama Orang Tua/Wali</td><td>: ............................................................</td></tr>
            <tr><td>Alamat</td><td>: ............................................................</td></tr>
        </table>
        <p>Dengan ini menyatakan bahwa saya bersedia melakukan daftar ulang sebagai calon siswa di {$schoolName} Tahun Pelajaran {$schoolYear}.</p>
        <p>Saya menyatakan telah melengkapi berkas daftar ulang dan melakukan pembayaran biaya daftar ulang sebesar <strong>{$fee}</strong>.</p>
        <p>Apabila di kemudian hari terdapat data atau berkas yang tidak benar, saya bersedia mengikuti ketentuan yang berlaku di sekolah.</p>
        <div class="signature">
            <p>...................., {$today}</p>
            <p>Orang Tua/Wali,</p>
            <div class="signature-space"></div>
            <p>(........................................)</p>
        </div>
    </div>
</body>
</html>
HTML;
}

function re_registration_schema_ready(): bool
{
    static $ready = null;

    if ($ready !== null) {
        return $ready;
    }

    if (! db_available()) {
        $ready = false;
        return $ready;
    }

    try {
        db()->query('SELECT student_identity_no, re_registered_at, re_registration_confirmed_at, re_registration_confirmed_by FROM registrations LIMIT 0');
        db()->query('SELECT id FROM re_registration_documents LIMIT 0');
        $ready = true;
    } catch (Throwable $exception) {
        $ready = false;
    }

    return $ready;
}

function ensure_re_registration_documents(int $registrationId): void
{
    if (! re_registration_schema_ready()) {
        return;
    }

    foreach (re_registration_legacy_document_types() as $oldType => $newType) {
        $oldStmt = db()->prepare('SELECT * FROM re_registration_documents WHERE registration_id = ? AND document_type = ? LIMIT 1');
        $oldStmt->execute([$registrationId, $oldType]);
        $oldDocument = $oldStmt->fetch();

        if (! $oldDocument) {
            continue;
        }

        $newStmt = db()->prepare('SELECT * FROM re_registration_documents WHERE registration_id = ? AND document_type = ? LIMIT 1');
        $newStmt->execute([$registrationId, $newType]);
        $newDocument = $newStmt->fetch();

        if (! $newDocument) {
            $renameStmt = db()->prepare('UPDATE re_registration_documents SET document_type = ? WHERE id = ?');
            $renameStmt->execute([$newType, $oldDocument['id']]);
            continue;
        }

        if (empty($newDocument['file_path']) && ! empty($oldDocument['file_path'])) {
            $copyStmt = db()->prepare('
                UPDATE re_registration_documents
                SET file_name = ?, original_name = ?, file_path = ?, status = ?, note = ?, uploaded_at = ?
                WHERE id = ?
            ');
            $copyStmt->execute([
                $oldDocument['file_name'],
                $oldDocument['original_name'],
                $oldDocument['file_path'],
                $oldDocument['status'],
                $oldDocument['note'],
                $oldDocument['uploaded_at'],
                $newDocument['id'],
            ]);
        }
    }

    foreach (re_registration_document_types() as $type) {
        $stmt = db()->prepare('SELECT id FROM re_registration_documents WHERE registration_id = ? AND document_type = ? LIMIT 1');
        $stmt->execute([$registrationId, $type]);

        if (! $stmt->fetch()) {
            $insert = db()->prepare('INSERT INTO re_registration_documents (registration_id, document_type) VALUES (?, ?)');
            $insert->execute([$registrationId, $type]);
        }
    }
}

function re_registration_documents_for_registration(?int $registrationId): array
{
    if (! db_available() || ! $registrationId || ! re_registration_schema_ready()) {
        return [];
    }

    ensure_re_registration_documents($registrationId);

    $stmt = db()->prepare('SELECT * FROM re_registration_documents WHERE registration_id = ? ORDER BY id ASC');
    $stmt->execute([$registrationId]);
    $rows = $stmt->fetchAll();
    $rowsByType = [];

    foreach ($rows as $row) {
        $rowsByType[$row['document_type']] = $row;
    }

    $orderedRows = [];

    foreach (re_registration_document_types() as $type) {
        if (isset($rowsByType[$type])) {
            $orderedRows[] = $rowsByType[$type];
        }
    }

    return array_map(static function ($row) {
        return [
            'id' => (int) $row['id'],
            'name' => $row['document_type'],
            'file' => $row['original_name'] ?: ($row['file_name'] ?: '-'),
            'file_path' => $row['file_path'],
            'status' => $row['status'],
            'note' => $row['note'] ?: '-',
        ];
    }, $orderedRows);
}

function timeline_for_student(array $student): array
{
    $timeline = [
        ['step' => 'Registrasi Akun', 'status' => 'Selesai'],
        ['step' => 'Formulir Pendaftaran', 'status' => $student['form_status'] ?? 'Belum Mengisi'],
        ['step' => 'Upload Berkas', 'status' => $student['document_status'] ?? 'Belum Upload'],
        ['step' => 'Seleksi', 'status' => ($student['selection_status'] ?? '') === 'Belum Diumumkan' ? 'Belum Diproses' : $student['selection_status']],
        ['step' => 'Hasil Seleksi', 'status' => published_results() ? ($student['selection_status'] ?? 'Belum Tersedia') : 'Belum Tersedia'],
    ];

    if (published_results() && actual_selection_status($student) === 'Diterima') {
        $timeline[] = ['step' => 'Daftar Ulang', 'status' => $student['re_registration_status'] ?? 'Belum Daftar Ulang'];
    }

    return $timeline;
}

function announcements(array $sample): array
{
    if (! db_available()) {
        return $sample['announcements'];
    }

    $rows = db()->query('
        SELECT * FROM announcements
        WHERE is_published = 1
        ORDER BY announcement_date DESC, id DESC
    ')->fetchAll();

    return array_map(static function ($row) {
        return [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'date' => format_date_id($row['announcement_date']),
            'content' => $row['content'],
        ];
    }, $rows);
}

function schedules(array $sample): array
{
    if (! db_available()) {
        return $sample['schedule'];
    }

    $rows = db()->query('SELECT activity, date_label FROM schedules ORDER BY sort_order ASC, id ASC')->fetchAll();

    return array_map(static function ($row) {
        return [
            'activity' => $row['activity'],
            'date' => $row['date_label'],
        ];
    }, $rows);
}

function requirements_list(array $sample): array
{
    if (! db_available()) {
        return $sample['requirements'];
    }

    $rows = db()->query('SELECT requirement_text FROM requirements ORDER BY sort_order ASC, id ASC')->fetchAll();

    return array_map(static function ($row) {
        return $row['requirement_text'];
    }, $rows);
}

function published_results(): bool
{
    return app_setting('results_published', '0') === '1';
}

function actual_selection_status(array $registration): string
{
    return $registration['raw_selection_status']
        ?? $registration['selection_status']
        ?? 'Belum Diproses';
}

function registration_is_accepted(array $registration): bool
{
    return actual_selection_status($registration) === 'Diterima';
}

function student_has_submitted_form(array $student): bool
{
    return ($student['form_status'] ?? 'Belum Mengisi') !== 'Belum Mengisi';
}

function student_can_edit_form(array $student): bool
{
    return ! registration_is_accepted($student);
}

function student_can_upload_documents(array $student): bool
{
    if (registration_is_accepted($student)) {
        return false;
    }

    return ($student['document_status'] ?? 'Belum Upload') !== 'Berkas Lengkap';
}

function psb_data(): array
{
    $sample = sample_data();
    $student = current_student_data($sample);
    $documents = documents_for_registration($student['id'] ?? null, $sample);
    $reRegistrationDocuments = re_registration_documents_for_registration($student['id'] ?? null);

    return [
        'school' => get_school_data($sample),
        'stats' => psb_stats($sample),
        'student' => $student,
        'applicants' => applicant_rows($sample),
        'documents' => $documents,
        're_registration_documents' => $reRegistrationDocuments,
        'timeline' => timeline_for_student($student),
        'announcements' => announcements($sample),
        'schedule' => schedules($sample),
        'requirements' => requirements_list($sample),
        'results_published' => published_results(),
        'current_user' => current_user(),
        'db_connected' => db_available(),
    ];
}

function save_student_form(array $input): bool
{
    $user = current_user();

    if (! $user || ! db_available()) {
        flash('danger', 'Sesi tidak valid atau database belum tersedia.');
        return false;
    }

    $registration = ensure_registration_for_user((int) $user['id']);

    if (! $registration) {
        flash('danger', 'Data registrasi tidak ditemukan.');
        return false;
    }

    if (registration_is_accepted($registration)) {
        flash('danger', 'Formulir pendaftaran sudah dikunci karena status seleksi Anda sudah diterima.');
        return false;
    }

    $requiredFields = [
        'name' => 'Nama lengkap',
        'nisn' => 'NISN',
        'gender' => 'Jenis kelamin',
        'birth_place' => 'Tempat lahir',
        'birth_date' => 'Tanggal lahir',
        'religion' => 'Agama',
        'phone' => 'Nomor HP',
        'address' => 'Alamat',
        'father_name' => 'Nama ayah',
        'mother_name' => 'Nama ibu',
        'parent_phone' => 'Nomor HP orang tua',
        'origin_school' => 'Asal sekolah',
    ];

    foreach ($requiredFields as $field => $label) {
        if (trim((string) ($input[$field] ?? '')) === '') {
            flash('danger', $label . ' wajib diisi.');
            return false;
        }
    }

    if (! normalize_gender($input['gender'] ?? null)) {
        flash('danger', 'Jenis kelamin tidak valid.');
        return false;
    }

    if (! normalize_date($input['birth_date'] ?? null)) {
        flash('danger', 'Tanggal lahir tidak valid.');
        return false;
    }

    $stmt = db()->prepare('
        UPDATE registrations
        SET nisn = ?, gender = ?, birth_place = ?, birth_date = ?, religion = ?, address = ?,
            father_name = ?, mother_name = ?, parent_phone = ?, origin_school = ?, school_year = ?,
            form_status = "Sudah Dikirim", submitted_at = COALESCE(submitted_at, NOW())
        WHERE id = ?
    ');

    $stmt->execute([
        trim($input['nisn'] ?? ''),
        normalize_gender($input['gender'] ?? null),
        trim($input['birth_place'] ?? ''),
        normalize_date($input['birth_date'] ?? null),
        trim($input['religion'] ?? ''),
        trim($input['address'] ?? ''),
        trim($input['father_name'] ?? ''),
        trim($input['mother_name'] ?? ''),
        trim($input['parent_phone'] ?? ''),
        trim($input['origin_school'] ?? ''),
        trim($input['school_year'] ?? app_setting('school_year', '2026/2027')),
        $registration['id'],
    ]);

    $updateUser = db()->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
    $updateUser->execute([
        trim($input['name'] ?? $user['name']),
        trim($input['phone'] ?? $user['phone']),
        $user['id'],
    ]);

    flash('success', 'Formulir pendaftaran berhasil disimpan.');
    return true;
}

function save_student_profile(array $input): bool
{
    $user = current_user();

    if (! $user || ! db_available()) {
        flash('danger', 'Sesi tidak valid atau database belum tersedia.');
        return false;
    }

    $registration = ensure_registration_for_user((int) $user['id']);

    $email = trim($input['email'] ?? $user['email']);
    $emailExists = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ? AND deleted_at IS NULL LIMIT 1');
    $emailExists->execute([$email, $user['id']]);

    if ($emailExists->fetch()) {
        flash('danger', 'Email sudah dipakai oleh akun lain.');
        return false;
    }

    $stmt = db()->prepare('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?');
    $stmt->execute([
        trim($input['name'] ?? $user['name']),
        $email,
        trim($input['phone'] ?? $user['phone']),
        $user['id'],
    ]);

    if ($registration) {
        $stmt = db()->prepare('UPDATE registrations SET birth_place = ?, birth_date = ?, address = ? WHERE id = ?');
        $stmt->execute([
            trim($input['birth_place'] ?? ''),
            normalize_date($input['birth_date'] ?? null),
            trim($input['address'] ?? ''),
            $registration['id'],
        ]);
    }

    if (! empty($input['new_password'])) {
        if (($input['new_password'] ?? '') !== ($input['new_password_confirmation'] ?? '')) {
            flash('danger', 'Konfirmasi password baru tidak sesuai.');
            return false;
        }

        $stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([password_hash($input['new_password'], PASSWORD_DEFAULT), $user['id']]);
    }

    flash('success', 'Profil berhasil diperbarui.');
    return true;
}

function save_upload_documents(array $files): bool
{
    $user = current_user();

    if (! $user || ! db_available()) {
        flash('danger', 'Sesi tidak valid atau database belum tersedia.');
        return false;
    }

    $registration = ensure_registration_for_user((int) $user['id']);

    if (! $registration) {
        flash('danger', 'Data registrasi tidak ditemukan.');
        return false;
    }

    if (registration_is_accepted($registration)) {
        flash('danger', 'Upload berkas sudah dikunci karena status seleksi Anda sudah diterima.');
        return false;
    }

    if (($registration['document_status'] ?? '') === 'Berkas Lengkap') {
        flash('info', 'Berkas Anda sudah dinyatakan lengkap. Upload ulang hanya dibuka jika panitia menandai berkas tidak lengkap.');
        return false;
    }

    ensure_registration_documents((int) $registration['id']);

    $uploaded = 0;
    $skippedComplete = 0;
    $targetDir = base_path('storage/uploads');

    if (! is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    $documentStatusStmt = db()->prepare('SELECT status FROM documents WHERE id = ? AND registration_id = ? LIMIT 1');

    foreach (($files['documents']['name'] ?? []) as $documentId => $originalName) {
        if (($files['documents']['error'][$documentId] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            continue;
        }

        $documentStatusStmt->execute([(int) $documentId, $registration['id']]);
        $currentDocumentStatus = $documentStatusStmt->fetchColumn();

        if ($currentDocumentStatus === 'Berkas Lengkap') {
            $skippedComplete++;
            continue;
        }

        if (! $currentDocumentStatus) {
            continue;
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];

        if (! in_array($extension, $allowed, true)) {
            flash('danger', 'Format file hanya boleh PDF, JPG, JPEG, atau PNG.');
            continue;
        }

        $fileName = 'doc-' . $registration['id'] . '-' . (int) $documentId . '-' . time() . '.' . $extension;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        if (move_uploaded_file($files['documents']['tmp_name'][$documentId], $targetPath)) {
            $stmt = db()->prepare('
                UPDATE documents
                SET file_name = ?, original_name = ?, file_path = ?, status = "Menunggu Verifikasi", note = NULL
                WHERE id = ? AND registration_id = ?
            ');
            $stmt->execute([
                $fileName,
                $originalName,
                'storage/uploads/' . $fileName,
                (int) $documentId,
                $registration['id'],
            ]);
            $uploaded++;
        }
    }

    if ($skippedComplete > 0) {
        flash('warning', $skippedComplete . ' berkas yang sudah lengkap tidak dapat diubah.');
    }

    if ($uploaded > 0) {
        $stmt = db()->prepare('UPDATE registrations SET document_status = "Menunggu Verifikasi" WHERE id = ?');
        $stmt->execute([$registration['id']]);
        flash('success', $uploaded . ' berkas berhasil diupload dan menunggu verifikasi.');
        return true;
    }

    flash('warning', 'Tidak ada file baru yang diupload.');
    return false;
}

function update_document_verification(array $input): bool
{
    $registrationId = (int) ($input['registration_id'] ?? 0);
    $status = $input['document_status'] ?? 'Menunggu Verifikasi';
    $note = trim($input['note'] ?? '');
    $user = current_user();

    if (! in_array($status, ['Berkas Lengkap', 'Berkas Tidak Lengkap', 'Menunggu Verifikasi'], true)) {
        $status = 'Menunggu Verifikasi';
    }

    $stmt = db()->prepare('UPDATE registrations SET document_status = ?, admin_note = ? WHERE id = ?');
    $stmt->execute([$status, $note, $registrationId]);

    if (! empty($input['document_id'])) {
        $stmt = db()->prepare('
            UPDATE documents
            SET status = ?, note = ?, verified_by = ?, verified_at = NOW()
            WHERE id = ?
        ');
        $stmt->execute([$status, $note, $user['id'] ?? null, (int) $input['document_id']]);
    } else {
        $stmt = db()->prepare('
            UPDATE documents
            SET status = ?, note = ?, verified_by = ?, verified_at = NOW()
            WHERE registration_id = ? AND file_path IS NOT NULL
        ');
        $stmt->execute([$status, $note, $user['id'] ?? null, $registrationId]);
    }

    flash('success', 'Status berkas berhasil diperbarui.');
    if (in_array($status, ['Berkas Lengkap', 'Berkas Tidak Lengkap'], true)) {
        if (send_document_status_email($registrationId, $status, $note)) {
            flash('success', 'Email informasi status berkas berhasil dikirim ke pendaftar.');
        } else {
            $mailError = psb_mail_error();
            flash('warning', 'Status berkas tersimpan, tetapi email belum terkirim.' . ($mailError !== '' ? ' ' . $mailError : ' Periksa konfigurasi SMTP email.'));
        }
    }
    return true;
}

function send_document_status_email(int $registrationId, string $status, string $note = ''): bool
{
    psb_mail_error('');

    if (! filter_var(env_value('MAIL_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
        psb_mail_error('MAIL_ENABLED masih nonaktif.');
        return false;
    }

    $registration = find_registration_by_id($registrationId);

    if (! $registration || empty($registration['email'])) {
        psb_mail_error('Email pendaftar tidak ditemukan.');
        return false;
    }

    $subject = 'Status Berkas Pendaftaran PSB';
    $message = $status === 'Berkas Lengkap'
        ? 'Berkas Anda sudah lengkap dan akan masuk ke proses seleksi.'
        : 'Berkas Anda belum lengkap, silakan upload ulang dokumen yang diminta.';

    if ($note !== '') {
        $message .= "\n\nCatatan panitia: " . $note;
    }

    $mailer = strtolower((string) env_value('MAIL_MAILER', env_value('MAIL_HOST') ? 'smtp' : 'mail'));

    if ($mailer === 'smtp') {
        return send_smtp_email($registration['email'], $subject, $message);
    }

    return send_native_email($registration['email'], $subject, $message);
}

function psb_mail_error(?string $message = null): string
{
    if ($message !== null) {
        $GLOBALS['psb_mail_error'] = $message;
    }

    return $GLOBALS['psb_mail_error'] ?? '';
}

function send_native_email(string $to, string $subject, string $message): bool
{
    $from = trim((string) env_value('MAIL_FROM', 'psb@example.test'));
    $fromName = trim((string) env_value('MAIL_FROM_NAME', config('app.name', 'Penerimaan Siswa Baru')));

    if (! filter_var($to, FILTER_VALIDATE_EMAIL) || ! filter_var($from, FILTER_VALIDATE_EMAIL)) {
        psb_mail_error('Alamat email tujuan atau pengirim tidak valid.');
        return false;
    }

    $headers = [
        'From: ' . format_mail_address($from, $fromName),
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    if (@mail($to, encode_mail_subject($subject), $message, implode("\r\n", $headers))) {
        return true;
    }

    psb_mail_error('Fungsi mail() PHP gagal. Gunakan MAIL_MAILER=smtp untuk Gmail.');
    return false;
}

function send_smtp_email(string $to, string $subject, string $message): bool
{
    $host = trim((string) env_value('MAIL_HOST', ''));
    $port = (int) env_value('MAIL_PORT', 587);
    $encryption = strtolower(trim((string) env_value('MAIL_ENCRYPTION', 'tls')));
    $username = trim((string) env_value('MAIL_USERNAME', ''));
    $password = (string) env_value('MAIL_PASSWORD', '');
    $from = trim((string) env_value('MAIL_FROM', $username));
    $fromName = trim((string) env_value('MAIL_FROM_NAME', config('app.name', 'Penerimaan Siswa Baru')));

    if (stripos($host, 'gmail.com') !== false) {
        $password = str_replace(' ', '', $password);
    }

    if ($host === '' || $username === '' || $password === '' || $from === '') {
        psb_mail_error('Konfigurasi SMTP belum lengkap.');
        return false;
    }

    if (! filter_var($to, FILTER_VALIDATE_EMAIL) || ! filter_var($from, FILTER_VALIDATE_EMAIL)) {
        psb_mail_error('Alamat email tujuan atau pengirim tidak valid.');
        return false;
    }

    if (in_array($encryption, ['ssl', 'smtps', 'tls'], true) && ! extension_loaded('openssl')) {
        psb_mail_error('Ekstensi OpenSSL PHP belum aktif.');
        return false;
    }

    $timeout = (int) env_value('MAIL_TIMEOUT', 20);
    $remote = in_array($encryption, ['ssl', 'smtps'], true) ? 'ssl://' . $host : $host;
    $socket = @stream_socket_client($remote . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);

    if (! is_resource($socket)) {
        psb_mail_error('Tidak bisa terhubung ke SMTP: ' . $errstr);
        return false;
    }

    stream_set_timeout($socket, $timeout);

    if (! smtp_expect($socket, [220], 'greeting')) {
        fclose($socket);
        return false;
    }

    $localHost = parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';

    if (! smtp_command($socket, 'EHLO ' . $localHost, [250], 'EHLO')) {
        fclose($socket);
        return false;
    }

    if ($encryption === 'tls' || $encryption === 'starttls') {
        if (! smtp_command($socket, 'STARTTLS', [220], 'STARTTLS')) {
            fclose($socket);
            return false;
        }

        if (! @stream_socket_enable_crypto($socket, true, smtp_crypto_method())) {
            psb_mail_error('Gagal mengaktifkan enkripsi TLS SMTP.');
            fclose($socket);
            return false;
        }

        if (! smtp_command($socket, 'EHLO ' . $localHost, [250], 'EHLO setelah STARTTLS')) {
            fclose($socket);
            return false;
        }
    }

    if (! smtp_command($socket, 'AUTH LOGIN', [334], 'AUTH LOGIN')
        || ! smtp_command($socket, base64_encode($username), [334], 'username SMTP')
        || ! smtp_command($socket, base64_encode($password), [235], 'password SMTP')
        || ! smtp_command($socket, 'MAIL FROM:<' . $from . '>', [250], 'MAIL FROM')
        || ! smtp_command($socket, 'RCPT TO:<' . $to . '>', [250, 251], 'RCPT TO')
        || ! smtp_command($socket, 'DATA', [354], 'DATA')) {
        fclose($socket);
        return false;
    }

    $payload = build_email_payload($to, $from, $fromName, $subject, $message);
    fwrite($socket, $payload . "\r\n.\r\n");

    if (! smtp_expect($socket, [250], 'mengirim isi email')) {
        fclose($socket);
        return false;
    }

    smtp_command($socket, 'QUIT', [221], 'QUIT');
    fclose($socket);

    return true;
}

function smtp_crypto_method(): int
{
    $method = 0;

    foreach (['STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT', 'STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT', 'STREAM_CRYPTO_METHOD_TLS_CLIENT'] as $constant) {
        if (defined($constant)) {
            $method |= constant($constant);
        }
    }

    return $method ?: STREAM_CRYPTO_METHOD_TLS_CLIENT;
}

function smtp_command($socket, string $command, array $expectedCodes, string $label): bool
{
    fwrite($socket, $command . "\r\n");
    return smtp_expect($socket, $expectedCodes, $label);
}

function smtp_expect($socket, array $expectedCodes, string $label): bool
{
    [$code, $response] = smtp_read_response($socket);

    if (in_array($code, $expectedCodes, true)) {
        return true;
    }

    psb_mail_error('SMTP gagal saat ' . $label . ': ' . trim($response));
    return false;
}

function smtp_read_response($socket): array
{
    $response = '';

    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;

        if (preg_match('/^\d{3}\s/', $line)) {
            break;
        }
    }

    if ($response === '') {
        return [0, 'Tidak ada respons dari server SMTP.'];
    }

    return [(int) substr($response, 0, 3), $response];
}

function build_email_payload(string $to, string $from, string $fromName, string $subject, string $message): string
{
    $domain = substr(strrchr($from, '@') ?: '@localhost', 1) ?: 'localhost';
    $body = str_replace(["\r\n", "\r"], "\n", $message);
    $body = str_replace("\n", "\r\n", $body);
    $body = preg_replace('/^\./m', '..', $body);

    $headers = [
        'Date: ' . date(DATE_RFC2822),
        'From: ' . format_mail_address($from, $fromName),
        'To: <' . $to . '>',
        'Subject: ' . encode_mail_subject($subject),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $domain . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ];

    return implode("\r\n", $headers) . "\r\n\r\n" . $body;
}

function format_mail_address(string $email, string $name = ''): string
{
    $name = trim(str_replace(["\r", "\n"], '', $name));

    if ($name === '') {
        return '<' . $email . '>';
    }

    return '"' . addcslashes($name, '"\\') . '" <' . $email . '>';
}

function encode_mail_subject(string $subject): string
{
    return '=?UTF-8?B?' . base64_encode(str_replace(["\r", "\n"], '', $subject)) . '?=';
}

function delete_registration(array $input): bool
{
    $registrationId = (int) ($input['registration_id'] ?? 0);

    if ($registrationId <= 0) {
        flash('danger', 'Data pendaftaran tidak valid.');
        return false;
    }

    $stmt = db()->prepare('UPDATE registrations SET deleted_at = NOW() WHERE id = ?');
    $stmt->execute([$registrationId]);

    flash('success', 'Data pendaftaran berhasil dihapus.');
    return true;
}

function save_re_registration(array $files): bool
{
    $user = current_user();

    if (! $user || ! db_available()) {
        flash('danger', 'Sesi tidak valid atau database belum tersedia.');
        return false;
    }

    $registration = registration_for_user((int) $user['id']);

    if (! $registration) {
        flash('danger', 'Data pendaftaran tidak ditemukan.');
        return false;
    }

    if (! published_results() || $registration['selection_status'] !== 'Diterima') {
        flash('danger', 'Anda tidak dapat melakukan daftar ulang karena tidak dinyatakan diterima.');
        return false;
    }

    if (! re_registration_schema_ready()) {
        flash('danger', 'Struktur database daftar ulang belum diperbarui. Jalankan database/update_daftar_ulang.sql terlebih dahulu.');
        return false;
    }

    if (($registration['re_registration_status'] ?? '') === 'Dikonfirmasi') {
        flash('info', 'Daftar ulang Anda sudah dikonfirmasi.');
        return false;
    }

    if (($registration['re_registration_status'] ?? '') === 'Dikirim') {
        flash('info', 'Data daftar ulang sudah dikirim dan sedang menunggu konfirmasi admin.');
        return false;
    }

    ensure_re_registration_documents((int) $registration['id']);

    $uploaded = 0;
    $targetDir = base_path('storage/uploads');

    if (! is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    foreach (($files['re_registration_documents']['name'] ?? []) as $documentId => $originalName) {
        if (($files['re_registration_documents']['error'][$documentId] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            continue;
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];

        if (! in_array($extension, $allowed, true)) {
            flash('danger', 'Format file daftar ulang hanya boleh PDF, JPG, JPEG, atau PNG.');
            return false;
        }
    }

    foreach (($files['re_registration_documents']['name'] ?? []) as $documentId => $originalName) {
        if (($files['re_registration_documents']['error'][$documentId] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            continue;
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $fileName = 're-registration-' . $registration['id'] . '-' . (int) $documentId . '-' . time() . '.' . $extension;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        if (move_uploaded_file($files['re_registration_documents']['tmp_name'][$documentId], $targetPath)) {
            $stmt = db()->prepare('
                UPDATE re_registration_documents
                SET file_name = ?, original_name = ?, file_path = ?, status = "Dikirim", note = NULL, uploaded_at = NOW()
                WHERE id = ? AND registration_id = ?
            ');
            $stmt->execute([
                $fileName,
                $originalName,
                'storage/uploads/' . $fileName,
                (int) $documentId,
                $registration['id'],
            ]);
            $uploaded++;
        }
    }

    $documents = re_registration_documents_for_registration((int) $registration['id']);
    $missing = array_filter($documents, static function ($document) {
        return empty($document['file_path']);
    });

    if ($missing !== []) {
        flash('danger', 'Semua berkas daftar ulang wajib diupload sebelum disimpan.');
        return false;
    }

    if ($uploaded === 0) {
        flash('warning', 'Tidak ada file daftar ulang baru yang diupload.');
        return false;
    }

    $stmt = db()->prepare('UPDATE registrations SET re_registration_status = "Dikirim", re_registered_at = NOW() WHERE id = ?');
    $stmt->execute([$registration['id']]);

    flash('success', 'Data daftar ulang berhasil dikirim. Silakan tunggu konfirmasi admin.');
    return true;
}

function confirm_re_registration_by_admin(array $input): bool
{
    $registrationId = (int) ($input['registration_id'] ?? 0);
    $user = current_user();

    if ($registrationId <= 0) {
        flash('danger', 'Data pendaftaran tidak valid.');
        return false;
    }

    $registration = find_registration_by_id($registrationId);

    if (! $registration) {
        flash('danger', 'Data pendaftaran tidak ditemukan.');
        return false;
    }

    if (! re_registration_schema_ready()) {
        flash('danger', 'Struktur database daftar ulang belum diperbarui. Jalankan database/update_daftar_ulang.sql terlebih dahulu.');
        return false;
    }

    if (($registration['re_registration_status'] ?? '') !== 'Dikirim') {
        flash('warning', 'Daftar ulang hanya bisa dikonfirmasi jika statusnya sudah Dikirim.');
        return false;
    }

    $documents = re_registration_documents_for_registration($registrationId);
    $missing = array_filter($documents, static function ($document) {
        return empty($document['file_path']);
    });

    if ($missing !== []) {
        flash('danger', 'Berkas daftar ulang belum lengkap.');
        return false;
    }

    $studentIdentityNo = trim((string) ($registration['student_identity_no'] ?? ''));

    if ($studentIdentityNo === '') {
        $studentIdentityNo = generate_student_identity_no();
    }

    $stmt = db()->prepare('
        UPDATE registrations
        SET re_registration_status = "Dikonfirmasi",
            student_identity_no = ?,
            re_registration_confirmed_at = NOW(),
            re_registration_confirmed_by = ?
        WHERE id = ?
    ');
    $stmt->execute([$studentIdentityNo, $user['id'] ?? null, $registrationId]);

    $docStmt = db()->prepare('
        UPDATE re_registration_documents
        SET status = "Dikonfirmasi", verified_by = ?, verified_at = NOW()
        WHERE registration_id = ? AND file_path IS NOT NULL
    ');
    $docStmt->execute([$user['id'] ?? null, $registrationId]);

    flash('success', 'Daftar ulang berhasil dikonfirmasi. Nomor induk siswa: ' . $studentIdentityNo);
    return true;
}

function generate_student_identity_no(): string
{
    $year = date('Y');
    $prefix = 'NIS-' . $year . '-';
    $stmt = db()->prepare('SELECT student_identity_no FROM registrations WHERE student_identity_no LIKE ? ORDER BY student_identity_no DESC LIMIT 1');
    $stmt->execute([$prefix . '%']);
    $lastNo = $stmt->fetchColumn();

    if ($lastNo) {
        $parts = explode('-', $lastNo);
        $total = (int) end($parts) + 1;
    } else {
        $total = 1;
    }

    return sprintf('%s%04d', $prefix, $total);
}

function update_selection_status(array $input): bool
{
    $registrationId = (int) ($input['registration_id'] ?? 0);
    $status = $input['selection_status'] ?? 'Belum Diproses';
    $note = trim($input['selection_note'] ?? '');

    if (! in_array($status, ['Belum Diproses', 'Diterima', 'Tidak Diterima', 'Cadangan'], true)) {
        $status = 'Belum Diproses';
    }

    $stmt = db()->prepare('
        UPDATE registrations
        SET selection_status = ?, selection_note = ?, selected_at = CASE WHEN ? != "Belum Diproses" THEN NOW() ELSE selected_at END
        WHERE id = ?
    ');
    $stmt->execute([$status, $note, $status, $registrationId]);

    flash('success', 'Status seleksi berhasil diperbarui.');
    return true;
}

function save_announcement(array $input): bool
{
    $title = trim($input['title'] ?? '');
    $content = trim($input['content'] ?? '');
    $date = normalize_date($input['announcement_date'] ?? date('Y-m-d')) ?: date('Y-m-d');
    $user = current_user();

    if ($title === '' || $content === '') {
        flash('danger', 'Judul dan isi pengumuman wajib diisi.');
        return false;
    }

    $stmt = db()->prepare('INSERT INTO announcements (title, content, announcement_date, created_by) VALUES (?, ?, ?, ?)');
    $stmt->execute([$title, $content, $date, $user['id'] ?? null]);

    flash('success', 'Pengumuman berhasil disimpan.');
    return true;
}

function delete_announcement(array $input): bool
{
    $stmt = db()->prepare('DELETE FROM announcements WHERE id = ?');
    $stmt->execute([(int) ($input['id'] ?? 0)]);

    flash('success', 'Pengumuman berhasil dihapus.');
    return true;
}

function publish_results(): void
{
    set_app_setting('results_published', '1');
    flash('success', 'Hasil penerimaan berhasil dipublikasikan.');
}

function unpublish_results(): void
{
    set_app_setting('results_published', '0');
    flash('warning', 'Publikasi hasil penerimaan dibatalkan.');
}

function handle_post(string $page): void
{
    if ($page === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (auth_login($email, $password)) {
            $user = current_user();
            redirect_to(route_home_for_role($user['role']));
        }

        redirect_to('login');
    }

    if ($page === 'registrasi') {
        if (register_student_account($_POST)) {
            redirect_to('siswa-dashboard');
        }

        redirect_to('registrasi');
    }

    $routeRoles = [
        'siswa-formulir' => 'siswa',
        'pendaftaran' => 'siswa',
        'siswa-upload-berkas' => 'siswa',
        'siswa-profil' => 'siswa',
        'siswa-hasil' => 'siswa',
        'admin-data-pendaftaran' => 'admin',
        'admin-verifikasi-berkas' => 'admin',
        'admin-proses-seleksi' => 'admin',
        'admin-hasil-seleksi' => 'admin',
        'admin-pengumuman' => 'admin',
        'kepsek-publikasi' => 'kepsek',
    ];

    if (isset($routeRoles[$page])) {
        require_role($routeRoles[$page]);
    }

    if (! db_available()) {
        flash('danger', 'Database belum bisa diakses.');
        redirect_to($page);
    }

    if ($page === 'admin-data-pendaftaran') {
        if (($_POST['action'] ?? '') === 'delete') {
            delete_registration($_POST);
        }
        redirect_to('admin-data-pendaftaran');
    }

    if ($page === 'siswa-formulir' || $page === 'pendaftaran') {
        save_student_form($_POST);
        redirect_to('siswa-formulir');
    }

    if ($page === 'siswa-upload-berkas') {
        save_upload_documents($_FILES);
        redirect_to('siswa-upload-berkas');
    }

    if ($page === 'siswa-profil') {
        save_student_profile($_POST);
        redirect_to('siswa-profil');
    }

    if ($page === 'siswa-hasil') {
        if (($_POST['action'] ?? '') === 're_register') {
            save_re_registration($_FILES);
        }
        redirect_to('siswa-hasil');
    }

    if ($page === 'admin-verifikasi-berkas') {
        update_document_verification($_POST);
        redirect_to('admin-verifikasi-berkas', ['id' => (int) ($_POST['registration_id'] ?? 0)]);
    }

    if ($page === 'admin-proses-seleksi') {
        update_selection_status($_POST);
        redirect_to($page);
    }

    if ($page === 'admin-hasil-seleksi') {
        if (($_POST['action'] ?? '') === 'confirm_re_registration') {
            confirm_re_registration_by_admin($_POST);
        }
        redirect_to('admin-hasil-seleksi', ['id' => (int) ($_POST['registration_id'] ?? 0)]);
    }

    if ($page === 'admin-pengumuman') {
        if (($_POST['action'] ?? '') === 'delete') {
            delete_announcement($_POST);
        } else {
            save_announcement($_POST);
        }
        redirect_to('admin-pengumuman');
    }

    if ($page === 'kepsek-publikasi') {
        if (($_POST['action'] ?? '') === 'unpublish') {
            unpublish_results();
        } else {
            publish_results();
        }
        redirect_to('kepsek-publikasi');
    }
}
