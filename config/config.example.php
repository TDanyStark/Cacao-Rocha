<?php
/*
 * Copy this file to config/config.php (or create config/config.local.php) and
 * fill in your real database credentials OR set environment variables:
 * DB_HOST, DB_NAME, DB_USER, DB_PASS
 */

ini_set("session.cookie_lifetime", "604800"); // 7 días
ini_set("session.gc_maxlifetime", "604800"); // 7 días
session_start();

// Replace the placeholders below with your values when creating config/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'database_name_here');
define('DB_USER', 'db_user_here');
define('DB_PASS', 'db_password_here');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

?>
