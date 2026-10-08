<?php
// ---- CONFIG ----
session_start();
define('BASE_URL', '/gocarrental');          // folder name inside htdocs
define('DB_HOST', 'localhost');
define('DB_NAME', 'gocarrental');
define('DB_USER', 'root');                   // XAMPP default
define('DB_PASS', '');                       // XAMPP default
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('PRIVATE_DIR', __DIR__ . '/private_uploads/');   // ID photos (not web accessible)
define('CATEGORIES', ['Economy','Compact','SUV','Van','Luxury','Sports']);

function db(): PDO {
    static $pdo = null;
    if (!$pdo) {
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}
require_once __DIR__ . '/includes/functions.php';
