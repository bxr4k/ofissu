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
                
                <!-- Sipariş ve Ödeme Bilgileri -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card shadow">
                            <div class="card-header bg-warning text-dark text-center">
                                <h5 class="mb-0">📞 Su Siparişi</h5>
                            </div>
                            <div class="card-body text-center">
                                <p class="mb-3 h5">+90 541 531 90 74</p>
                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                    <a href="tel:+905415319074" class="btn btn-success">
                                        📞 Ara
                                    </a>
                                    <a href="https://wa.me/905415319074?text=Merhaba.%20G%C3%B6lc%C3%BCk%20Belediyesi%20Bas%C4%B1n%20Yay%C4%B1n%20M%C3%BCd%C3%BCrl%C3%BC%C4%9F%C3%BC%20i%C3%A7in%20su%20sipari%C5%9Fi%20vermek%20istiyorum." 
                                       target="_blank" 
                                       class="btn btn-whatsapp">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16" style="margin-right: 5px;">
                                            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                                        </svg>
                                        WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card shadow">
                            <div class="card-header bg-info text-white text-center">
                                <h5 class="mb-0">💳 Ödeme Bilgileri</h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <p class="mb-2"><strong>Gürpınar Su</strong></p>
                                    <p class="mb-3">Erol Eren</p>
                                    <p class="mb-1"><small class="text-muted">IBAN:</small></p>
                                    <p class="mb-2">
                                        <code class="bg-light p-2 d-inline-block" style="font-size: 0.85rem;">
                                            TR23 0020 5000 0948 6271 4000 01
                                        </code>
                                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyIBAN()" title="IBAN'ı Kopyala">
                                            📋
                                        </button>
                                    </p>
                                    <p class="mb-0"><small class="text-muted">Kuveyt Türk Katılım Bankası A.Ş.</small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // IBAN kopyalama fonksiyonu
    function copyIBAN() {
        const iban = "TR23 0020 5000 0948 6271 4000 01";
        navigator.clipboard.writeText(iban).then(function() {
            alert('IBAN kopyalandı!');
        }).catch(function(err) {
            // Fallback
            const textarea = document.createElement('textarea');
            textarea.value = iban;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('IBAN kopyalandı!');
        });
    }
    </script>
</body>
</html>

