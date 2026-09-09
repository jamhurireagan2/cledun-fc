<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'cledun_fc');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'CLEDUN FC');
define('SITE_URL', 'http://localhost/cledun-fc/');
define('SITE_EMAIL', 'info@cledunfc.com');

// Paths
define('BASE_PATH', __DIR__ . '/../');
define('UPLOAD_PATH', BASE_PATH . 'uploads/');

// Error reporting (disable in production)
error_reporting(0);
ini_set('display_errors', 0);

// Session - Check if session is already active before starting
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Africa/Nairobi');
?>