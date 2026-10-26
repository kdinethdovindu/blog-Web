<?php
// Vercel uses environment variables only; local PHP can still use .env.php.
if (getenv('VERCEL') !== '1' && is_file(__DIR__ . '/.env.php')) {
    require_once __DIR__ . '/.env.php';
}

function app_config(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value !== false ? $value : (defined($key) ? (string)constant($key) : $default);
}

function pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = app_config('DB_HOST');
    $name = app_config('DB_NAME');
    $user = app_config('DB_USER');
    $port = app_config('DB_PORT', '3306');
    if ($host === '' || $name === '' || $user === ''
        || strpbrk($host . $name, ";\r\n\0") !== false
        || !ctype_digit($port) || (int)$port < 1 || (int)$port > 65535) {
        throw new RuntimeException('Invalid database configuration.');
    }

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10,
        PDO::ATTR_PERSISTENT => false,
    ];

    // Aiven's CA is supplied as a multiline environment variable on Vercel.
    $certificate = str_replace('\\n', "\n", app_config('DB_SSL_CA_CERT'));
    $caPath = app_config('DB_SSL_CA');
    if ($certificate !== '') {
        if (!openssl_x509_read($certificate)) {
            throw new RuntimeException('Invalid database CA certificate.');
        }
        $caPath = sys_get_temp_dir() . '/blog-ca-' . hash('sha256', $certificate) . '.pem';
        if (!is_file($caPath) && file_put_contents($caPath, $certificate, LOCK_EX) === false) {
            throw new RuntimeException('Unable to prepare database CA certificate.');
        }
    }
    if (getenv('VERCEL') === '1' && $caPath === '') {
        throw new RuntimeException('DB_SSL_CA_CERT is required on Vercel.');
    }
    if ($caPath !== '') {
        if (!is_readable($caPath)) {
            throw new RuntimeException('Database CA certificate is not readable.');
        }
        $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }

    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        app_config('DB_PASS'),
        $options
    );
    // Fail closed if a driver/server ever negotiates an unencrypted connection.
    if ($caPath !== '') {
        $cipher = $pdo->query("SHOW SESSION STATUS LIKE 'Ssl_cipher'")->fetch();
        if (empty($cipher['Value'])) {
            $pdo = null;
            throw new RuntimeException('Database TLS was not negotiated.');
        }
    }
    return $pdo;
}
