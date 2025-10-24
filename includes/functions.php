<?php
/**
 * Yardımcı Fonksiyonlar
 */

/**
 * Session başlatma
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Kullanıcı giriş yapmış mı kontrol et
 */
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

/**
 * Admin kullanıcı mı kontrol et
 */
function isAdmin() {
    startSession();
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Giriş yapılmamışsa login sayfasına yönlendir
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Admin yetkisi gerekliyse kontrol et
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php?error=yetki_yok');
        exit;
    }
}

/**
 * Mevcut kullanıcı bilgilerini al
 */
function getCurrentUser() {
    global $pdo;
    startSession();
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Log kaydı ekle
 */
function addLog($action, $details = null, $userId = null) {
    global $pdo;
    
    if ($userId === null && isLoggedIn()) {
        startSession();
        $userId = $_SESSION['user_id'];
    }
    
    $detailsJson = is_array($details) ? json_encode($details, JSON_UNESCAPED_UNICODE) : $details;
    
    $stmt = $pdo->prepare("INSERT INTO logs (user_id, action, details) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $action, $detailsJson]);
}

/**
 * Cep telefonu numarası doğrulama
 */
function validatePhone($phone) {
    // 11 hane olmalı
    if (strlen($phone) != 11) {
        return false;
    }
    
    // Sadece rakam olmalı
    if (!ctype_digit($phone)) {
        return false;
    }
    
    // 0 ile başlamalı
    if ($phone[0] != '0') {
        return false;
    }
    
    return true;
}

/**
 * Telefon numarasını temizle ve formatla
 * Örnek: "+90 534 929 17 82" veya "534 929 17 82" → "05349291782"
 */
function cleanPhoneNumber($phone) {
    // Sadece rakamları al
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // +90 ile başlıyorsa kaldır
    if (substr($phone, 0, 2) === '90' && strlen($phone) == 12) {
        $phone = '0' . substr($phone, 2);
    }
    
    // 0 ile başlamıyorsa ekle
    if (strlen($phone) == 10 && $phone[0] !== '0') {
        $phone = '0' . $phone;
    }
    
    return $phone;
}

/**
 * Güvenli çıktı
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Tarih formatla
 */
function formatDate($date) {
    $timestamp = strtotime($date);
    return date('d.m.Y', $timestamp);
}

/**
 * Tarih ve saat formatla
 */
function formatDateTime($datetime) {
    $timestamp = strtotime($datetime);
    return date('d.m.Y H:i', $timestamp);
}

/**
 * Tüm aktif markaları getir
 */
function getActiveBrands() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM brands WHERE active = 1 ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Tüm aktif ödeme yöntemlerini getir
 */
function getActivePaymentMethods() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM payment_methods WHERE active = 1 ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Tüm aktif kullanıcıları getir
 */
function getAllUsers() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM users ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Başarı mesajı göster
 */
function showSuccess($message) {
    return '<div class="alert alert-success alert-dismissible fade show" role="alert">' . 
           e($message) . 
           '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

/**
 * Hata mesajı göster
 */
function showError($message) {
    return '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . 
           e($message) . 
           '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

