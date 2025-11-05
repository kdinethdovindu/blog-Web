<?php
// One-time helper to create a bcrypt hash for a password you choose.
$plain = 'Admin@12345!'; // <-- change if you want
$hash = password_hash($plain, PASSWORD_DEFAULT);
echo "Paste this into the DB as password_hash:<br><code>{$hash}</code>";
