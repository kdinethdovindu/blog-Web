<?php
require_once __DIR__ . '/.env.php';

function pdo(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // Minimal safe error message for user; detailed logs can be added later.
        http_response_code(500);
        exit('Database connection failed.');
    }
}

