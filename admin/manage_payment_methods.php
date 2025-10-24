<?php
/**
 * Ödeme Yöntemi Yönetimi
 */

require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

$error = '';
$success = '';

// Ödeme yöntemi silme (gerçek silme)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $methodId = intval($_GET['id']);
    
    try {
        // Önce bu ödeme yöntemine ait kayıt var mı kontrol et
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM water_records WHERE payment_method_id = ?");
        $stmt->execute([$methodId]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $error = "Bu ödeme yöntemi silinemez! {$result['count']} adet kayıt bu yöntemi kullanıyor. Önce pasif yapabilirsiniz.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM payment_methods WHERE id = ?");
            $stmt->execute([$methodId]);
            addLog("Ödeme yöntemi silindi: ID=$methodId");
            $success = 'Ödeme yöntemi başarıyla silindi';
        }
    } catch (PDOException $e) {
        $error = 'Silme hatası: ' . $e->getMessage();
    }
}

// Ödeme yöntemi durumu değiştir (aktif/pasif)
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $methodId = intval($_GET['id']);
    
    try {
        $stmt = $pdo->prepare("UPDATE payment_methods SET active = NOT active WHERE id = ?");
        $stmt->execute([$methodId]);
        addLog("Ödeme yöntemi durumu değiştirildi: ID=$methodId");
        $success = 'Ödeme yöntemi durumu güncellendi';
    } catch (PDOException $e) {
        $error = 'Güncelleme hatası: ' . $e->getMessage();
    }
}

// Ödeme yöntemi ekleme/düzenleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        $error = 'Ödeme yöntemi adı boş olamaz';
    } else {
        try {
            if ($id > 0) {
                // Güncelleme
                $stmt = $pdo->prepare("UPDATE payment_methods SET name = ? WHERE id = ?");
                $stmt->execute([$name, $id]);
                addLog("Ödeme yöntemi güncellendi: $name");
                $success = 'Ödeme yöntemi başarıyla güncellendi';
            } else {
                // Yeni ekleme
                $stmt = $pdo->prepare("INSERT INTO payment_methods (name, active) VALUES (?, 1)");
                $stmt->execute([$name]);
                addLog("Yeni ödeme yöntemi eklendi: $name");
                $success = 'Ödeme yöntemi başarıyla eklendi';
            }
        } catch (PDOException $e) {
            $error = 'Kayıt hatası: ' . $e->getMessage();
        }
    }
}

// Düzenleme için ödeme yöntemi getir
$editMethod = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE id = ?");
    $stmt->execute([intval($_GET['id'])]);
    $editMethod = $stmt->fetch();
}

// Tüm ödeme yöntemlerini getir
$methods = $pdo->query("SELECT * FROM payment_methods ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme Yöntemi Yönetimi - Ofis Su Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2 class="mb-4">💳 Ödeme Yöntemi Yönetimi</h2>
        
        <div class="row">
            <!-- Form -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?= $editMethod ? 'Ödeme Yöntemi Düzenle' : 'Yeni Ödeme Yöntemi Ekle' ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <?= showError($error) ?>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <?= showSuccess($success) ?>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="id" value="<?= $editMethod ? $editMethod['id'] : 0 ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Ödeme Yöntemi Adı</label>
                                <input type="text" 
                                       name="name" 
                                       class="form-control"
                                       value="<?= $editMethod ? e($editMethod['name']) : '' ?>"
                                       placeholder="Örn: Nakit, Kredi Kartı"
                                       required
                                       autofocus>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <?= $editMethod ? 'Güncelle' : 'Ekle' ?>
                                </button>
                                <?php if ($editMethod): ?>
                                    <a href="manage_payment_methods.php" class="btn btn-secondary">İptal</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Liste -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Ödeme Yöntemi Listesi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ödeme Yöntemi</th>
                                        <th>Durum</th>
                                        <th>Kullanım</th>
                                        <th>Eklenme</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($methods as $method): ?>
                                        <?php
                                        // Bu ödeme yöntemini kullanan kayıt sayısı
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM water_records WHERE payment_method_id = ?");
                                        $stmt->execute([$method['id']]);
                                        $usageCount = $stmt->fetch()['count'];
                                        ?>
                                        <tr>
                                            <td><strong><?= e($method['name']) ?></strong></td>
                                            <td>
                                                <?php if ($method['active']): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Pasif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($usageCount > 0): ?>
                                                    <span class="badge bg-info"><?= $usageCount ?> kayıt</span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted small"><?= formatDateTime($method['created_at']) ?></td>
                                            <td>
                                                <a href="?action=edit&id=<?= $method['id'] ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                                <a href="?action=toggle&id=<?= $method['id'] ?>" 
                                                   class="btn btn-sm <?= $method['active'] ? 'btn-secondary' : 'btn-success' ?>">
                                                    <?= $method['active'] ? 'Pasif Yap' : 'Aktif Yap' ?>
                                                </a>
                                                <?php if ($usageCount == 0): ?>
                                                    <a href="?action=delete&id=<?= $method['id'] ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Bu ödeme yöntemini tamamen silmek istediğinizden emin misiniz?')">
                                                        Sil
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-danger" 
                                                            disabled 
                                                            title="Bu ödeme yöntemi kullanımda olduğu için silinemez">
                                                        Sil
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

