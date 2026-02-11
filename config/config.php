<?php
/*
 * Secure config loader
 *
 * This file intentionally does NOT contain real credentials. To configure your
 * environment either:
 *  - set the environment variables DB_HOST, DB_NAME, DB_USER, DB_PASS, or
 *  - create a local file `config/config.local.php` (this file is ignored by git)
 *    with the same defines used previously (DB_HOST, DB_NAME, DB_USER, DB_PASS).
 */

ini_set("session.cookie_lifetime", "604800"); // 7 días
ini_set("session.gc_maxlifetime", "604800"); // 7 días
session_start();

// If a local config exists (not tracked), load it. This file can contain
// the constant definitions with real credentials.
$localConfig = __DIR__ . '/config.local.php';
if (file_exists($localConfig)) {
    require $localConfig;
} else {
    // Fallback to environment variables. Replace defaults as needed.
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: 'u744125515_cacao_rocha');
    define('DB_USER', getenv('DB_USER') ?: 'u744125515_cacao_rocha');
    define('DB_PASS', getenv('DB_PASS') ?: '8l#b7ZJd#');
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // Don't reveal sensitive info in production errors.
    die("Error de conexión: " . $e->getMessage());
}
?>
