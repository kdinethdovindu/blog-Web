<?php
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

require_valid_csrf();

$_SESSION = [];
session_regenerate_id(true);
$_SESSION['csrf'] = bin2hex(random_bytes(32));
flash('ok', 'You have been logged out.');
header('Location: ' . base_url());
exit;
