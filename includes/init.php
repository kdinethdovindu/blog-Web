<?php
// includes/init.php
// Bootstraps sessions, DB, and shared helpers for all pages.

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // sessions (used later for login)
}

require_once __DIR__ . '/../config/.env.php';
require_once __DIR__ . '/../config/db.php';

// quick helpers
function base_url(string $path = ''): string {
    // We'll serve pages from /public; this builds links consistently.
    $root = rtrim(BASE_URL, '/').'/public';
    if ($path === '') return $root . '/';
    return $root . '/' . ltrim($path, '/');
}

function e(string $v): string {
    // escape output for HTML
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string {
    // very simple slug (we'll generate unique slugs for posts)
    $text = trim(mb_strtolower($text, 'UTF-8'));
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);      // replace non letters/digits
    $text = trim($text, '-');
    if ($text === '') $text = 'post';
    // make it unique-ish to avoid collisions for now
    return $text . '-' . substr(sha1(uniqid('', true)), 0, 6);
}

// quick PDO handle (reuses function from config/db.php)
function db(): PDO { return pdo(); }

function flash(string $key, ?string $msg = null) {
    if ($msg !== null) { $_SESSION['flash'][$key] = $msg; return; }
    if (!empty($_SESSION['flash'][$key])) {
        $m = $_SESSION['flash'][$key]; unset($_SESSION['flash'][$key]); return $m;
    }
    return null;
}
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): void {
    if (!current_user()) { flash('error', 'Please log in first.'); header('Location: '.base_url('login.php')); exit; }
}
