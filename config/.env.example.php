<?php

define('DB_HOST', 'your_database_host');
define('DB_PORT', '3306'); // Aiven uses its own port: copy it from the console.
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');

define('BASE_URL', 'http://localhost/blog-Web');

// For local connections to Aiven, download its CA certificate and set this path.
// define('DB_SSL_CA', 'C:/path/to/ca.pem');
// define('SESSION_DRIVER', 'database'); // Requires database/sessions.sql.
// On Vercel, configure environment variables instead; this file is not deployed.
