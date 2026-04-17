<?php

declare(strict_types=1);

use App\Helpers\Response;

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = dirname(__DIR__);
        return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR . '/');
    }
}

if (!function_exists('config_path')) {
    function config_path(string $file = ''): string
    {
        $dir = base_path('config');
        return $file === '' ? $dir : $dir . DIRECTORY_SEPARATOR . $file;
    }
}

if (!function_exists('config')) {
    /**
     * @return array<string, mixed>|mixed
     */
    function config(string $key, mixed $default = null): mixed
    {
        static $cache = [];
        $segments = explode('.', $key);
        $file = array_shift($segments) ?? '';
        if ($file === '') {
            return $default;
        }
        if (!isset($cache[$file])) {
            $path = config_path($file . '.php');
            $cache[$file] = file_exists($path) ? require $path : [];
        }
        $value = $cache[$file];
        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $code = 302): never
    {
        Response::redirect($url, $code);
    }
}

if (!function_exists('abort')) {
    function abort(int $code = 404, string $message = ''): never
    {
        http_response_code($code);
        echo $message !== '' ? htmlspecialchars($message, ENT_QUOTES, 'UTF-8') : '';
        exit;
    }
}

if (!function_exists('auth_check')) {
    function auth_check(): bool
    {
        return !empty($_SESSION['user_id']);
    }
}

if (!function_exists('auth_id')) {
    function auth_id(): ?int
    {
        if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
            return null;
        }
        return (int) $_SESSION['user_id'];
    }
}

if (!function_exists('can')) {
    function can(string $permission): bool
    {
        $roles = $_SESSION['role_slugs'] ?? [];
        if (is_array($roles) && in_array('master', $roles, true)) {
            return true;
        }

        $perms = $_SESSION['permissions'] ?? [];
        if (!is_array($perms)) {
            return false;
        }

        return in_array($permission, $perms, true);
    }
}

if (!function_exists('lumis_current_path')) {
    function lumis_current_path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/') ?: '/';
        }
        return $path;
    }
}

if (!function_exists('lumis_nav_active')) {
    function lumis_nav_active(string $href, string $match = 'prefix'): bool
    {
        if ($href === '#' || $href === '') {
            return false;
        }
        $current = lumis_current_path();
        if ($match === 'exact') {
            return $current === $href;
        }
        if ($current === $href) {
            return true;
        }
        return str_starts_with($current, $href . '/');
    }
}
