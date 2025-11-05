<?php
// NEVER commit real production secrets. For local dev this is fine.
define('DB_HOST', '127.0.0.1');     // or 'localhost'
define('DB_NAME', 'blogapp');
define('DB_USER', 'bloguser');
define('DB_PASS', 'Blog@12345!');   // the same password you set
define('BASE_URL', 'http://localhost/blog-app'); // used later for links
