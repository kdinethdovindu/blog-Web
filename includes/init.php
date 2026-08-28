<?php
// Bootstraps sessions, the database connection, and shared helpers.

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../config/db.php';

function base_url(string $path = ''): string
{
    $root = rtrim(BASE_URL, '/') . '/public';
    if ($path === '') {
        return $root . '/';
    }

    return $root . '/' . ltrim($path, '/');
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string
{
    $text = trim(mb_strtolower($text, 'UTF-8'));
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');

    if ($text === '') {
        $text = 'post';
    }

    return $text . '-' . substr(sha1(uniqid('', true)), 0, 6);
}

function db(): PDO
{
    return pdo();
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!empty($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }

    return null;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        flash('error', 'Please log in first.');
        header('Location: ' . base_url('login.php'));
        exit;
    }
}

function csrf_token(): string
{
    return $_SESSION['csrf'];
}

function require_valid_csrf(): void
{
    $submittedToken = $_POST['csrf'] ?? '';

    if (!is_string($submittedToken) || !hash_equals(csrf_token(), $submittedToken)) {
        http_response_code(400);
        exit('Invalid request token. Please go back and try again.');
    }
}
