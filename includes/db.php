<?php
/**
 * Veritabanı Bağlantı Yönetimi
 */

// Kurulum yapılmış mı kontrol et (config.php veya environment variables)
$envConfigExists = getenv('DB_HOST') && getenv('DB_NAME') && getenv('DB_USER');

if (!file_exists(__DIR__ . '/../config.php') && !$envConfigExists) {
    header('Location: install.php');
    exit;
}

// Config dosyası varsa yükle, yoksa environment variables kullan
if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
} else {
    // Environment variables'dan tanımla
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_NAME', getenv('DB_NAME'));
    define('DB_USER', getenv('DB_USER'));
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
}

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

