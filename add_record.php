<?php
/**
 * Su Kaydı Ekleme
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$currentUser = getCurrentUser();
$error = '';
$success = '';

// Hızlı kayıt mı? (Bugün Benim butonu)
$isQuick = isset($_GET['quick']) && $_GET['quick'] == '1';

// Verileri getir
$users = getAllUsers();
$brands = getActiveBrands();
$paymentMethods = getActivePaymentMethods();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id'] ?? 0);
    $date = trim($_POST['date'] ?? '');
    $brandId = intval($_POST['brand_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $totalPrice = floatval($_POST['total_price'] ?? 0);
    $paymentMethodId = intval($_POST['payment_method_id'] ?? 0);
    
    // Birim fiyat hesapla (toplam / miktar)
    $unitPrice = $quantity > 0 ? ($totalPrice / $quantity) : 0;
    
    // Validasyon
    if ($userId <= 0) {
        $error = 'Lütfen kullanıcı seçin';
    } elseif (empty($date)) {
        $error = 'Lütfen tarih seçin';
    } elseif ($brandId <= 0) {
        $error = 'Lütfen marka seçin';
    } elseif ($quantity <= 0) {
        $error = 'Miktar en az 1 olmalıdır';
    } elseif ($totalPrice < 0) {
        $error = 'Toplam fiyat 0 veya daha büyük olmalıdır';
    } elseif ($paymentMethodId <= 0) {
        $error = 'Lütfen ödeme yöntemi seçin';
    } else {
        try {
            // Kaydı ekle
            $stmt = $pdo->prepare("
                INSERT INTO water_records (user_id, date, brand_id, quantity, unit_price, total_price, payment_method_id, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $date, $brandId, $quantity, $unitPrice, $totalPrice, $paymentMethodId, $currentUser['id']]);
            
            // Kullanıcı adını al
            $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $selectedUser = $stmt->fetch();
            
            // Log ekle
            $logDetails = [
                'user_name' => $selectedUser['name'],
                'date' => $date,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'brand_id' => $brandId,
                'payment_method_id' => $paymentMethodId
            ];
            addLog("Su kaydı eklendi: {$selectedUser['name']} adına {$date} tarihinde {$quantity} adet ({$totalPrice} TL)", json_encode($logDetails, JSON_UNESCAPED_UNICODE));
            
            $success = 'Kayıt başarıyla eklendi!';
            
            // Formu temizle
            $_POST = [];
            
        } catch (PDOException $e) {
            $error = 'Kayıt eklenirken hata oluştu: ' . $e->getMessage();
        }
    }
}

// Bugün'ün tarihi
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ekle - Ofis Su Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">➕ Yeni Su Kaydı Ekle</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <?= showError($error) ?>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <?= showSuccess($success) ?>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <!-- Kullanıcı Seçimi -->
                            <div class="mb-3">
                                <label for="user_id" class="form-label">Kim Aldı? <span class="text-danger">*</span></label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">Seçiniz...</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>" 
                                            <?= ($isQuick && $user['id'] == $currentUser['id']) ? 'selected' : '' ?>
                                            <?= (isset($_POST['user_id']) && $_POST['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                            <?= e($user['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Tarih -->
                            <div class="mb-3">
                                <label for="date" class="form-label">Tarih <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="date" 
                                           class="form-control" 
                                           id="date" 
                                           name="date" 
                                           value="<?= $isQuick ? $today : (isset($_POST['date']) ? $_POST['date'] : '') ?>"
                                           required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('date').value='<?= $today ?>'">
                                        Bugün
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Marka -->
                            <div class="mb-3">
                                <label for="brand_id" class="form-label">Su Markası <span class="text-danger">*</span></label>
                                <select class="form-select" id="brand_id" name="brand_id" required>
                                    <option value="">Seçiniz...</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?= $brand['id'] ?>" 
                                            <?= (isset($_POST['brand_id']) && $_POST['brand_id'] == $brand['id']) ? 'selected' : '' ?>>
                                            <?= e($brand['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Miktar -->
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Miktar (Adet) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       id="quantity" 
                                       name="quantity" 
                                       min="1" 
                                       value="<?= isset($_POST['quantity']) ? $_POST['quantity'] : '1' ?>"
                                       oninput="calculateFromTotal()"
                                       required>
                            </div>
                            
                            <!-- Toplam Fiyat -->
                            <div class="mb-3">
                                <label for="total_price" class="form-label">Toplam Fiyat (TL) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       id="total_price" 
                                       name="total_price" 
                                       min="0" 
                                       step="0.01" 
                                       value="<?= isset($_POST['total_price']) ? $_POST['total_price'] : '0' ?>"
                                       oninput="calculateFromTotal()"
                                       required>
                                <small class="form-text text-muted">Ödenen toplam tutar</small>
                            </div>
                            
                            <!-- Birim Fiyat (Otomatik) -->
                            <div class="mb-3">
                                <label class="form-label">Birim Fiyat</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control bg-light" 
                                           id="unit_price_display" 
                                           value="0.00" 
                                           readonly>
                                    <span class="input-group-text">TL/adet</span>
                                </div>
                                <small class="form-text text-muted">Otomatik hesaplanır (Toplam ÷ Miktar)</small>
                            </div>
                            
                            <!-- Ödeme Yöntemi -->
                            <div class="mb-3">
                                <label for="payment_method_id" class="form-label">Ödeme Yöntemi <span class="text-danger">*</span></label>
                                <select class="form-select" id="payment_method_id" name="payment_method_id" required>
                                    <option value="">Seçiniz...</option>
                                    <?php foreach ($paymentMethods as $method): ?>
                                        <option value="<?= $method['id'] ?>"
                                            <?= (isset($_POST['payment_method_id']) && $_POST['payment_method_id'] == $method['id']) ? 'selected' : '' ?>>
                                            <?= e($method['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Kaydet
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/mascot-helper.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toplam fiyattan birim fiyat hesaplama
        function calculateFromTotal() {
            const quantity = parseFloat(document.getElementById('quantity').value) || 0;
            const totalPrice = parseFloat(document.getElementById('total_price').value) || 0;
            
            if (quantity > 0) {
                const unitPrice = totalPrice / quantity;
                document.getElementById('unit_price_display').value = unitPrice.toFixed(2);
            } else {
                document.getElementById('unit_price_display').value = '0.00';
            }
        }
        
        // Sayfa yüklendiğinde hesapla
        window.addEventListener('DOMContentLoaded', function() {
            calculateFromTotal();
        });
    </script>
</body>
</html>

