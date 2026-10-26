<?php

// Only explicitly listed pages/assets can be served, never arbitrary PHP paths.
ob_start();
set_exception_handler(function (Throwable $error): void {
    ob_clean();
    $detail = get_class($error) === RuntimeException::class ? ': ' . $error->getMessage() : '';
    error_log('Blog request failed: ' . get_class($error) . ' (code ' . $error->getCode() . ')' . $detail);
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    echo 'The site is temporarily unavailable. Check database configuration and migrations.';
});

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$assets = [
    '/assets/css/app.css' => 'text/css; charset=utf-8',
    '/assets/js/app.js' => 'application/javascript; charset=utf-8',
    '/assets/js/editor.js' => 'application/javascript; charset=utf-8',
];
if (isset($assets[$path])) {
    header('Content-Type: ' . $assets[$path]);
    header('X-Content-Type-Options: nosniff');
    readfile(__DIR__ . '/..' . $path);
    return;
}

header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (in_array($path, ['/', '/index.php', '/public', '/public/'], true)) {
    $page = 'index';
} elseif (is_string($path) && preg_match('~^/public/(index|login|register|logout|new|edit|delete|mine|post|404)\.php$~D', $path, $match)) {
    $page = $match[1];
} else {
    // A private path must return 404 even when the database is unavailable.
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Page not found.';
    return;
}

$_SERVER['PHP_SELF'] = '/public/' . $page . '.php';
require __DIR__ . '/../public/' . $page . '.php';
