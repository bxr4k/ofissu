<?php
/**
 * Giriş Sayfası
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

startSession();

// Zaten giriş yapılmışsa dashboard'a yönlendir
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone_number = trim($_POST['phone_number'] ?? '');
    
    if (empty($phone_number)) {
        $error = 'Cep telefonu numarası boş olamaz';
    } elseif (!validatePhone($phone_number)) {
        $error = 'Geçersiz telefon numarası (05XX XXX XX XX formatında 11 hane olmalı)';
    } else {
        // Kullanıcıyı kontrol et
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone_number = ?");
        $stmt->execute([$phone_number]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Giriş başarılı
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_phone'] = $user['phone_number'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            addLog('Kullanıcı giriş yaptı', null, $user['id']);
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Bu telefon numarası ile kayıtlı kullanıcı bulunamadı';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Ofis Su Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2>💧 Ofis Su Takip</h2>
                            <p class="text-muted">Cep telefonu numaranız ile giriş yapın</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <?= showError($error) ?>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Cep Telefonu Numarası</label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="phone_number" 
                                       name="phone_number" 
                                       placeholder="05XX XXX XX XX"
                                       maxlength="11"
                                       pattern="0[0-9]{10}"
                                       value="<?= isset($_POST['phone_number']) ? e($_POST['phone_number']) : '' ?>"
                                       required
                                       autofocus>
                                <small class="form-text text-muted">0 ile başlayan 11 haneli telefon numaranız</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                Giriş Yap
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

