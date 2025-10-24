<?php
/**
 * Çıkış Yap
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

startSession();

if (isLoggedIn()) {
    addLog('Kullanıcı çıkış yaptı');
    
    // Session'ı temizle
    session_destroy();
}

header('Location: login.php');
exit;

