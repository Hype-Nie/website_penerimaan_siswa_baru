<?php

return [
    'host' => env_value('DB_HOST', '127.0.0.1'),
    'port' => env_value('DB_PORT', '3306'),
    'database' => env_value('DB_DATABASE', 'pendaftaran_siswa'),
    'username' => env_value('DB_USERNAME', 'root'),
    'password' => env_value('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
];

