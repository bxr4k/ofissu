<?php
/**
 * Su Kaydı Düzenleme
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$currentUser = getCurrentUser();
$error = '';
$success = '';

// Kayıt ID'sini al
$recordId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($recordId <= 0) {
    header('Location: view_records.php');
    exit;
}

// Kaydı getir
$stmt = $pdo->prepare("
    SELECT wr.* 
    FROM water_records wr 
    WHERE wr.id = ?
");
$stmt->execute([$recordId]);
$record = $stmt->fetch();

if (!$record) {
    header('Location: view_records.php');
    exit;
}

// Eski değerleri sakla (log için)
$oldValues = $record;

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id'] ?? 0);
    $date = trim($_POST['date'] ?? '');
    $brandId = intval($_POST['brand_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $totalPrice = floatval($_POST['total_price'] ?? 0);
    $paymentMethodId = intval($_POST['payment_method_id'] ?? 0);
    
    // Birim fiyat hesapla
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
            // Kaydı güncelle
            $stmt = $pdo->prepare("
                UPDATE water_records 
                SET user_id = ?, date = ?, brand_id = ?, quantity = ?, 
                    unit_price = ?, total_price = ?, payment_method_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$userId, $date, $brandId, $quantity, $unitPrice, $totalPrice, $paymentMethodId, $recordId]);
            
            // Kullanıcı adlarını al
            $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $newUserName = $stmt->fetch()['name'];
            
            $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
            $stmt->execute([$oldValues['user_id']]);
            $oldUserName = $stmt->fetch()['name'];
            
            // Değişiklikleri logla
            $changes = [];
            if ($oldValues['user_id'] != $userId) $changes[] = "Kullanıcı: $oldUserName → $newUserName";
            if ($oldValues['date'] != $date) $changes[] = "Tarih: {$oldValues['date']} → $date";
            if ($oldValues['quantity'] != $quantity) $changes[] = "Miktar: {$oldValues['quantity']} → $quantity";
            if ($oldValues['total_price'] != $totalPrice) $changes[] = "Toplam: {$oldValues['total_price']} → $totalPrice";
            
            $logMessage = "Kayıt düzenlendi (ID: $recordId)";
            if (!empty($changes)) {
                $logMessage .= " - " . implode(", ", $changes);
            }
            
            addLog($logMessage, json_encode([
                'record_id' => $recordId,
                'old_values' => $oldValues,
                'new_values' => [
                    'user_id' => $userId,
                    'date' => $date,
                    'quantity' => $quantity,
                    'total_price' => $totalPrice
                ]
            ], JSON_UNESCAPED_UNICODE));
            
            $success = 'Kayıt başarıyla güncellendi!';
            
            // Kaydı yeniden yükle
            $stmt = $pdo->prepare("SELECT * FROM water_records WHERE id = ?");
            $stmt->execute([$recordId]);
            $record = $stmt->fetch();
            $oldValues = $record;
            
        } catch (PDOException $e) {
            $error = 'Güncelleme hatası: ' . $e->getMessage();
        }
    }
}

// Verileri getir
$users = getAllUsers();
$brands = getActiveBrands();
$paymentMethods = getActivePaymentMethods();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Düzenle - Ofis Su Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">✏️ Kayıt Düzenle</h5>
                        <a href="view_records.php" class="btn btn-sm btn-secondary">← Geri Dön</a>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <?= showError($error) ?>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <?= showSuccess($success) ?>
                        <?php endif; ?>
                        
                        <div class="alert alert-info mb-4">
                            <strong>ℹ️ Bilgi:</strong> Bu kaydı düzenliyorsunuz. Tüm değişiklikler loglanacaktır.
                        </div>
                        
                        <form method="POST" action="">
                            <!-- Kullanıcı Seçimi -->
                            <div class="mb-3">
                                <label for="user_id" class="form-label">Kim Aldı? <span class="text-danger">*</span></label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>" <?= $record['user_id'] == $user['id'] ? 'selected' : '' ?>>
                                            <?= e($user['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Tarih -->
                            <div class="mb-3">
                                <label for="date" class="form-label">Tarih <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control" 
                                       id="date" 
                                       name="date" 
                                       value="<?= $record['date'] ?>"
                                       required>
                            </div>
                            
                            <!-- Marka -->
                            <div class="mb-3">
                                <label for="brand_id" class="form-label">Su Markası <span class="text-danger">*</span></label>
                                <select class="form-select" id="brand_id" name="brand_id" required>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?= $brand['id'] ?>" <?= $record['brand_id'] == $brand['id'] ? 'selected' : '' ?>>
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
                                       value="<?= $record['quantity'] ?>"
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
                                       value="<?= $record['total_price'] ?>"
                                       oninput="calculateFromTotal()"
                                       required>
                            </div>
                            
                            <!-- Birim Fiyat (Otomatik) -->
                            <div class="mb-3">
                                <label class="form-label">Birim Fiyat</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control bg-light" 
                                           id="unit_price_display" 
                                           value="<?= number_format($record['unit_price'], 2) ?>" 
                                           readonly>
                                    <span class="input-group-text">TL/adet</span>
                                </div>
                                <small class="form-text text-muted">Otomatik hesaplanır (Toplam ÷ Miktar)</small>
                            </div>
                            
                            <!-- Ödeme Yöntemi -->
                            <div class="mb-3">
                                <label for="payment_method_id" class="form-label">Ödeme Yöntemi <span class="text-danger">*</span></label>
                                <select class="form-select" id="payment_method_id" name="payment_method_id" required>
                                    <?php foreach ($paymentMethods as $method): ?>
                                        <option value="<?= $method['id'] ?>" <?= $record['payment_method_id'] == $method['id'] ? 'selected' : '' ?>>
                                            <?= e($method['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    💾 Değişiklikleri Kaydet
                                </button>
                                <a href="view_records.php" class="btn btn-secondary">
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

