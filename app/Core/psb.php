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

    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND status = "active" LIMIT 1');
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

function auth_login(string $username, string $password): bool
{
    if (! db_available()) {
        flash('danger', 'Database belum bisa diakses. Periksa konfigurasi .env dan MySQL.');
        return false;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE (username = ? OR email = ?) AND status = "active" LIMIT 1');
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if (! $user || ! password_verify($password, $user['password'])) {
        flash('danger', 'Username/email atau password salah.');
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

    $emailExists = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $emailExists->execute([$email]);

    if ($emailExists->fetch()) {
        flash('danger', 'Email sudah terdaftar. Silakan login atau gunakan email lain.');
        return false;
    }

    $username = strtolower(preg_replace('/[^a-z0-9]+/i', '', strtok($email, '@')));
    $username = $username !== '' ? $username : 'siswa';

    $exists = db()->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
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
    $stmt = db()->query('SELECT COUNT(*) AS total FROM registrations');
    $total = (int) ($stmt->fetch()['total'] ?? 0) + 1;

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
            SUM(document_status = "Menunggu Verifikasi") AS menunggu_verifikasi,
            SUM(document_status = "Berkas Lengkap") AS berkas_lengkap,
            SUM(selection_status = "Diterima") AS diterima,
            SUM(selection_status = "Tidak Diterima") AS tidak_diterima,
            SUM(selection_status = "Cadangan") AS cadangan
        FROM registrations
    ')->fetch();

    return [
        'total_pendaftar' => (int) ($row['total_pendaftar'] ?? 0),
        'menunggu_verifikasi' => (int) ($row['menunggu_verifikasi'] ?? 0),
        'berkas_lengkap' => (int) ($row['berkas_lengkap'] ?? 0),
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

    $where = [];
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
        'birth_date' => format_date_id($row['birth_date'] ?? null),
        'religion' => $row['religion'] ?? '',
        'address' => $row['address'] ?? '',
        'father' => $row['father_name'] ?? '',
        'mother' => $row['mother_name'] ?? '',
        'parent_phone' => $row['parent_phone'] ?? '',
        'origin_school' => $row['origin_school'] ?? '',
        'school_year' => $row['school_year'] ?? '',
        'date' => format_date_id($row['created_at'] ?? null),
        'uts' => 0,
        'uas' => 0,
        'un' => 0,
        'average' => 0,
        'form_status' => $row['form_status'],
        'document_status' => $row['document_status'],
        'selection_status' => $row['selection_status'],
        'admin_note' => $row['admin_note'] ?? '',
        'selection_note' => $row['selection_note'] ?? '',
        'submitted_at' => $row['submitted_at'] ?? null,
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
        WHERE r.id = ?
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
        'selection_status' => published_results() ? $registration['selection_status'] : 'Belum Diumumkan',
    ];
}

function registration_for_user(int $userId): ?array
{
    $stmt = db()->prepare('
        SELECT r.*, u.name, u.email, u.phone
        FROM registrations r
        JOIN users u ON u.id = r.user_id
        WHERE r.user_id = ?
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

function timeline_for_student(array $student): array
{
    return [
        ['step' => 'Registrasi Akun', 'status' => 'Selesai'],
        ['step' => 'Formulir Pendaftaran', 'status' => $student['form_status'] ?? 'Belum Mengisi'],
        ['step' => 'Upload Berkas', 'status' => $student['document_status'] ?? 'Belum Upload'],
        ['step' => 'Seleksi', 'status' => ($student['selection_status'] ?? '') === 'Belum Diumumkan' ? 'Belum Diproses' : $student['selection_status']],
        ['step' => 'Hasil Seleksi', 'status' => published_results() ? ($student['selection_status'] ?? 'Belum Tersedia') : 'Belum Tersedia'],
    ];
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

function psb_data(): array
{
    $sample = sample_data();
    $student = current_student_data($sample);
    $documents = documents_for_registration($student['id'] ?? null, $sample);

    return [
        'school' => get_school_data($sample),
        'stats' => psb_stats($sample),
        'student' => $student,
        'applicants' => applicant_rows($sample),
        'documents' => $documents,
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
    $emailExists = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
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

    ensure_registration_documents((int) $registration['id']);

    $uploaded = 0;
    $targetDir = base_path('storage/uploads');

    if (! is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    foreach (($files['documents']['name'] ?? []) as $documentId => $originalName) {
        if (($files['documents']['error'][$documentId] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
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
    }

    send_document_status_email($registrationId, $status, $note);

    flash('success', 'Status berkas berhasil diperbarui.');
    return true;
}

function send_document_status_email(int $registrationId, string $status, string $note = ''): void
{
    if (! filter_var(env_value('MAIL_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
        return;
    }

    $registration = find_registration_by_id($registrationId);

    if (! $registration || empty($registration['email'])) {
        return;
    }

    $subject = 'Status Berkas Pendaftaran PSB';
    $message = $status === 'Berkas Lengkap'
        ? 'Berkas Anda sudah lengkap dan akan masuk ke proses seleksi.'
        : 'Berkas Anda belum lengkap, silakan upload ulang dokumen yang diminta.';

    if ($note !== '') {
        $message .= "\n\nCatatan panitia: " . $note;
    }

    $headers = 'From: ' . env_value('MAIL_FROM', 'psb@example.test');
    @mail($registration['email'], $subject, $message, $headers);
}

function delete_registration(array $input): bool
{
    $registrationId = (int) ($input['registration_id'] ?? 0);

    if ($registrationId <= 0) {
        flash('danger', 'Data pendaftaran tidak valid.');
        return false;
    }

    $stmt = db()->prepare('DELETE FROM registrations WHERE id = ?');
    $stmt->execute([$registrationId]);

    flash('success', 'Data pendaftaran berhasil dihapus.');
    return true;
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
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (auth_login($username, $password)) {
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

    if ($page === 'admin-verifikasi-berkas') {
        update_document_verification($_POST);
        redirect_to('admin-verifikasi-berkas', ['id' => (int) ($_POST['registration_id'] ?? 0)]);
    }

    if ($page === 'admin-proses-seleksi' || $page === 'admin-hasil-seleksi') {
        update_selection_status($_POST);
        redirect_to($page);
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
