<?php

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/bootstrap/app.php';

$routes = require base_path('routes/web.php');
$page = $_GET['page'] ?? 'beranda';

if ($page === 'logout') {
    auth_logout();
    session_start();
    flash('info', 'Anda sudah logout.');
    header('Location: ' . url_for('login', ['status' => 'logout']));
    exit;
}

if ($page === 'template-surat-pernyataan-daftar-ulang' || $page === 'download-template-surat-pernyataan-daftar-ulang') {
    $download = $page === 'download-template-surat-pernyataan-daftar-ulang';

    if ($download) {
        header('Content-Type: application/msword; charset=UTF-8');
        header('Content-Disposition: attachment; filename="template-surat-pernyataan-daftar-ulang.doc"');
    } else {
        header('Content-Type: text/html; charset=UTF-8');
    }

    echo re_registration_statement_template_html(! $download);
    exit;
}

if ($page === 'view-berkas') {
    $user = current_user();
    if (!$user || !in_array($user['role'], ['admin', 'kepsek', 'siswa'], true)) {
        http_response_code(403);
        exit('Forbidden');
    }

    $docId = (int)($_GET['id'] ?? 0);
    $stmt = db()->prepare('
        SELECT d.file_path, d.original_name, r.user_id 
        FROM documents d 
        JOIN registrations r ON r.id = d.registration_id 
        WHERE d.id = ? LIMIT 1
    ');
    $stmt->execute([$docId]);
    $doc = $stmt->fetch();

    if (!$doc || ($user['role'] === 'siswa' && (int)$user['id'] !== (int)$doc['user_id'])) {
        http_response_code(404);
        exit('Berkas tidak ditemukan atau Anda tidak memiliki akses.');
    }

    $path = base_path($doc['file_path']);
    if (file_exists($path)) {
        header('Content-Type: ' . mime_content_type($path));
        header('Content-Disposition: inline; filename="' . $doc['original_name'] . '"');
        readfile($path);
        exit;
    }

    http_response_code(404);
    exit('File fisik tidak ditemukan.');
}

if ($page === 'view-daftar-ulang-berkas') {
    $user = current_user();
    if (!$user || !in_array($user['role'], ['admin', 'kepsek', 'siswa'], true)) {
        http_response_code(403);
        exit('Forbidden');
    }

    if (!db_available() || !re_registration_schema_ready()) {
        http_response_code(404);
        exit('Struktur database daftar ulang belum tersedia.');
    }

    $docId = (int)($_GET['id'] ?? 0);
    $stmt = db()->prepare('
        SELECT d.file_path, d.original_name, r.user_id
        FROM re_registration_documents d
        JOIN registrations r ON r.id = d.registration_id
        WHERE d.id = ? LIMIT 1
    ');
    $stmt->execute([$docId]);
    $doc = $stmt->fetch();

    if (!$doc || empty($doc['file_path']) || ($user['role'] === 'siswa' && (int)$user['id'] !== (int)$doc['user_id'])) {
        http_response_code(404);
        exit('Berkas tidak ditemukan atau Anda tidak memiliki akses.');
    }

    $path = base_path($doc['file_path']);
    if (file_exists($path)) {
        header('Content-Type: ' . mime_content_type($path));
        header('Content-Disposition: inline; filename="' . $doc['original_name'] . '"');
        readfile($path);
        exit;
    }

    http_response_code(404);
    exit('File fisik tidak ditemukan.');
}

if ($page === 'export-csv') {
    require_role('kepsek');
    $sample = sample_data();
    $applicants = applicant_rows($sample);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="Laporan_Penerimaan_Siswa_Baru.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Tanggal', 'No Pendaftaran', 'Nama Siswa', 'Status Berkas', 'Status Seleksi']);
    foreach ($applicants as $row) {
        fputcsv($out, [$row['date'], $row['no'], $row['name'], $row['document_status'], $row['selection_status']]);
    }
    fclose($out);
    exit;
}

if ($page === 'export-pdf') {
    require_role('kepsek');
    $sample = sample_data();
    $applicants = applicant_rows($sample);
    
    echo '<!DOCTYPE html><html><head><title>Exporting PDF...</title>';
    echo '<style>body { font-family: sans-serif; padding: 20px; } table { width: 100%; border-collapse: collapse; margin-top: 20px; } th, td { border: 1px solid #ddd; padding: 12px 8px; text-align: left; } th { background-color: #f8f9fa; } h2 { text-align: center; margin-bottom: 5px; } .subtitle { text-align: center; color: #666; margin-bottom: 30px; }</style>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>';
    echo '</head><body>';
    echo '<div style="text-align: center; margin-top: 50px;" id="loading">Memproses PDF, harap tunggu...</div>';
    echo '<div id="report-content">';
    echo '<h2>Laporan Penerimaan Siswa Baru</h2>';
    echo '<div class="subtitle">Data rekapitulasi pendaftar</div>';
    echo '<table><thead><tr><th>Tanggal</th><th>No Pendaftaran</th><th>Nama Siswa</th><th>Status Berkas</th><th>Status Seleksi</th></tr></thead><tbody>';
    foreach ($applicants as $row) {
        echo '<tr><td>' . e($row['date']) . '</td><td>' . e($row['no']) . '</td><td>' . e($row['name']) . '</td><td>' . e($row['document_status']) . '</td><td>' . e($row['selection_status']) . '</td></tr>';
    }
    echo '</tbody></table>';
    echo '</div>';
    echo '<script>
        window.onload = function() {
            var element = document.getElementById("report-content");
            var opt = {
              margin:       0.5,
              filename:     "Laporan_Penerimaan_Siswa_Baru.pdf",
              image:        { type: "jpeg", quality: 0.98 },
              html2canvas:  { scale: 2 },
              jsPDF:        { unit: "in", format: "letter", orientation: "portrait" }
            };
            html2pdf().set(opt).from(element).save().then(function() {
                document.getElementById("loading").innerText = "Selesai diunduh! Menutup jendela...";
                setTimeout(function() { window.close(); }, 1500);
            });
        };
    </script>';
    echo '</body></html>';
    exit;
}

if ($page === 'cetak-laporan') {
    require_role('kepsek');
    $sample = sample_data();
    $applicants = applicant_rows($sample);
    
    echo '<!DOCTYPE html><html><head><title>Laporan Penerimaan Siswa Baru</title>';
    echo '<style>body { font-family: sans-serif; padding: 20px; } table { width: 100%; border-collapse: collapse; margin-top: 20px; } th, td { border: 1px solid #ddd; padding: 12px 8px; text-align: left; } th { background-color: #f8f9fa; } h2 { text-align: center; margin-bottom: 5px; } .subtitle { text-align: center; color: #666; margin-bottom: 30px; } @media print { .no-print { display: none; } }</style>';
    echo '</head><body onload="window.print()">';
    echo '<div class="no-print" style="margin-bottom: 20px; text-align: center;"><button onclick="window.print()" style="padding: 8px 16px; margin-right: 10px; cursor: pointer;">Cetak / Simpan PDF</button> <button onclick="window.close()" style="padding: 8px 16px; cursor: pointer;">Tutup</button></div>';
    echo '<h2>Laporan Penerimaan Siswa Baru</h2>';
    echo '<div class="subtitle">Data rekapitulasi pendaftar</div>';
    echo '<table><thead><tr><th>Tanggal</th><th>No Pendaftaran</th><th>Nama Siswa</th><th>Status Berkas</th><th>Status Seleksi</th></tr></thead><tbody>';
    foreach ($applicants as $row) {
        echo '<tr><td>' . e($row['date']) . '</td><td>' . e($row['no']) . '</td><td>' . e($row['name']) . '</td><td>' . e($row['document_status']) . '</td><td>' . e($row['selection_status']) . '</td></tr>';
    }
    echo '</tbody></table>';
    echo '</body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_post($page);
}

if (! isset($routes[$page])) {
    http_response_code(404);
    view('errors.404', [
        'title' => 'Halaman Tidak Ditemukan',
        'page' => $page,
        'role' => 'guest',
        'data' => psb_data(),
        'flashMessages' => pull_flash(),
    ], 'layouts/guest');
    exit;
}

$route = $routes[$page];
require_role($route['role'] ?? 'guest');

view($routes[$page]['view'], [
    'title' => $route['title'],
    'page' => $page,
    'route' => $route,
    'role' => $route['role'] ?? 'guest',
    'user' => current_user(),
    'data' => psb_data(),
    'flashMessages' => pull_flash(),
], $route['layout'] ?? 'layouts/admin');
