<?php

declare(strict_types=1);

namespace ClickCart\Helpers;

use ClickCart\Config\Env;
use ClickCart\Config\Database;
use PDO;

function base_path(string $path = ''): string
{
    $base = dirname(__DIR__, 2);
    return $path ? $base . '/' . ltrim($path, '/') : $base;
}

function app_url(string $path = ''): string
{
    $baseUrl = (string)Env::get('BASE_URL', 'http://localhost');
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function session_start_secure(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name((string)Env::get('SESSION_NAME', 'clickcart_session'));
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function csrf_token(): string
{
    session_start_secure();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool
{
    session_start_secure();
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function flash(string $key, ?string $message = null): ?string
{
    session_start_secure();
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    $msg = $_SESSION['flash'][$key] ?? null;
    if (isset($_SESSION['flash'][$key])) {
        unset($_SESSION['flash'][$key]);
    }

    return $msg;
}

function auth_user(): ?array
{
    session_start_secure();
    return $_SESSION['auth_user'] ?? null;
}

function require_auth(?string $role = null): void
{
    $user = auth_user();
    if (!$user) {
        redirect(app_url('auth/login.php'));
    }

    if ($role && $user['role'] !== $role) {
        redirect(app_url('auth/login.php'));
    }
}

function set_auth_user(array $user): void
{
    session_start_secure();
    $_SESSION['auth_user'] = $user;
}

function logout(): void
{
    session_start_secure();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function db(): PDO
{
    return Database::getConnection();
}

function respond_json(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function sanitize(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function validate_upload(array $file, array $allowedTypes = ['image/jpeg', 'image/png', 'image/webp']): bool
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return false;
    }

    $maxSize = (int)Env::get('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
    if (($file['size'] ?? 0) > $maxSize) {
        return false;
    }

    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    return in_array($mime, $allowedTypes, true);
}

function store_upload(array $file, string $directory = ''): ?string
{
    if (!validate_upload($file)) {
        return null;
    }

    $uploadPath = (string)Env::get('UPLOAD_PATH', base_path('public/uploads'));
    if ($directory !== '') {
        $uploadPath .= '/' . trim($directory, '/');
    }

    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0775, true);
    }

    $extension = pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION);
    $filename = uniqid('upload_', true) . ($extension ? '.' . $extension : '');
    $destination = $uploadPath . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return null;
    }

    return str_replace(base_path('public'), '', $destination);
}

function paginate(int $total, int $perPage, int $currentPage): array
{
    $pages = (int)ceil($total / $perPage);
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'pages' => $pages,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $pages,
    ];
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function rate_limit(string $key, int $maxAttempts = 5, int $decaySeconds = 60): bool
{
    session_start_secure();
    $entry = $_SESSION['rate_limit'][$key] ?? ['attempts' => 0, 'expires' => time() + $decaySeconds];

    if (time() > $entry['expires']) {
        $entry = ['attempts' => 0, 'expires' => time() + $decaySeconds];
    }

    if ($entry['attempts'] >= $maxAttempts) {
        $_SESSION['rate_limit'][$key] = $entry;
        return false;
    }

    $entry['attempts']++;
    $_SESSION['rate_limit'][$key] = $entry;
    return true;
}

function clear_rate_limit(string $key): void
{
    session_start_secure();
    unset($_SESSION['rate_limit'][$key]);
}
