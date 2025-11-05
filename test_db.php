<?php
require __DIR__ . '/config/db.php';

$db = pdo();

// 1) sanity check
$one = $db->query('SELECT 1 AS ok')->fetch();
echo "SELECT 1 → ok = " . $one['ok'] . "<br>";

// 2) users count
$count = $db->query('SELECT COUNT(*) AS c FROM users')->fetch();
echo "users table count = " . $count['c'] . "<br>";

// 3) show admin user (email + role)
$row = $db->query("SELECT username, email, role FROM users ORDER BY id ASC LIMIT 1")->fetch();
echo "first user → {$row['username']} / {$row['email']} / role={$row['role']}";
