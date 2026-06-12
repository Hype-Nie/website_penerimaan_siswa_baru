<?php

if (! function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return BASE_PATH . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

if (! function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (! function_exists('asset')) {
    function asset(string $path): string
    {
        $baseUrl = rtrim(config('app.url', ''), '/');
        return $baseUrl . '/assets/' . ltrim($path, '/');
    }
}

if (! function_exists('url_for')) {
    function url_for(string $page, array $params = []): string
    {
        return '?' . http_build_query(array_merge(['page' => $page], $params));
    }
}

if (! function_exists('config')) {
    function config(string $key, $default = null)
    {
        static $items = [];

        if ($items === []) {
            $items['app'] = require base_path('config/app.php');
            $items['database'] = require base_path('config/database.php');
        }

        [$file, $name] = array_pad(explode('.', $key, 2), 2, null);

        if ($name === null) {
            return $items[$file] ?? $default;
        }

        return $items[$file][$name] ?? $default;
    }
}

if (! function_exists('sample_data')) {
    function sample_data(string $key = null, $default = null)
    {
        static $data = null;

        if ($data === null) {
            $data = require base_path('app/Data/sample.php');
        }

        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? $default;
    }
}

if (! function_exists('status_class')) {
    function status_class(string $status): string
    {
        $normalized = strtolower($status);

        if (strpos($normalized, 'tidak') !== false || strpos($normalized, 'tolak') !== false) {
            return 'danger';
        }

        if (strpos($normalized, 'diterima') !== false || strpos($normalized, 'lengkap') !== false || strpos($normalized, 'selesai') !== false || strpos($normalized, 'lulus') !== false) {
            return 'success';
        }

        if (strpos($normalized, 'menunggu') !== false || strpos($normalized, 'belum') !== false || strpos($normalized, 'cadangan') !== false) {
            return 'warning';
        }

        return 'primary';
    }
}

if (! function_exists('view')) {
    function view(string $view, array $vars = [], string $layout = 'layouts/admin'): void
    {
        $viewFile = base_path('resources/views/' . str_replace('.', '/', $view) . '.php');
        $layoutFile = base_path('resources/views/' . str_replace('.', '/', $layout) . '.php');

        if (! file_exists($viewFile)) {
            http_response_code(404);
            $viewFile = base_path('resources/views/errors/404.php');
        }

        extract($vars, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }
}

if (! function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('env_value')) {
    function env_value(string $key, $default = null)
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}
