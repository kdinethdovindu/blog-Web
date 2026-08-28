<?php
$environmentFile = __DIR__ . '/.env.php';

if (!is_file($environmentFile)) {
    http_response_code(500);
    exit('Missing configuration. Copy config/.env.example.php to config/.env.php and add your local settings.');
}

require_once $environmentFile;

function pdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return $pdo;
    } catch (PDOException) {
        http_response_code(500);
        exit('Database connection failed.');
    }
}
