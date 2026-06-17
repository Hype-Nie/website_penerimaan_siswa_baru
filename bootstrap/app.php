<?php

require BASE_PATH . '/app/Helpers/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    $sessionPath = base_path('storage/sessions');

    if (! is_dir($sessionPath)) {
        mkdir($sessionPath, 0775, true);
    }

    session_save_path($sessionPath);
    session_start();
}

$envFileName = getenv('PSB_ENV_FILE') ?: '.env';
$envFile = preg_match('/^(?:[A-Za-z]:[\/\\\\]|[\/\\\\])/', $envFileName)
    ? $envFileName
    : BASE_PATH . '/' . ltrim($envFileName, '/\\');

if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, "\"' ");

        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }
}

date_default_timezone_set('Asia/Jakarta');

require BASE_PATH . '/app/Core/database.php';
require BASE_PATH . '/app/Core/psb.php';
