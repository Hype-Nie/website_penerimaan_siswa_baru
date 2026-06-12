<?php

return [
    'name' => env_value('APP_NAME', 'Penerimaan Siswa Baru'),
    'env' => env_value('APP_ENV', 'local'),
    'debug' => filter_var(env_value('APP_DEBUG', true), FILTER_VALIDATE_BOOLEAN),
    'url' => env_value('APP_URL', 'http://localhost:8000'),
];

