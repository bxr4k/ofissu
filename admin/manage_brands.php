<?php
/**
 * Marka Yönetimi
 */

require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

$error = '';
$success = '';

// Marka silme (gerçek silme)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $brandId = intval($_GET['id']);
    
    try {
        // Önce bu markaya ait kayıt var mı kontrol et
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM water_records WHERE brand_id = ?");
        $stmt->execute([$brandId]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $error = "Bu marka silinemez! {$result['count']} adet kayıt bu markayı kullanıyor. Önce pasif yapabilirsiniz.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
            $stmt->execute([$brandId]);
            addLog("Marka silindi: ID=$brandId");
            $success = 'Marka başarıyla silindi';
        }
    } catch (PDOException $e) {
        $error = 'Silme hatası: ' . $e->getMessage();
    }
}

// Marka aktif/pasif yapma
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $brandId = intval($_GET['id']);
    
    try {
        $stmt = $pdo->prepare("UPDATE brands SET active = NOT active WHERE id = ?");
        $stmt->execute([$brandId]);
        addLog("Marka durumu değiştirildi: ID=$brandId");
        $success = 'Marka durumu güncellendi';
    } catch (PDOException $e) {
        $error = 'Güncelleme hatası: ' . $e->getMessage();
    }
}

// Marka ekleme/düzenleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        $error = 'Marka adı boş olamaz';
    } else {
        try {
            if ($id > 0) {
                // Güncelleme
                $stmt = $pdo->prepare("UPDATE brands SET name = ? WHERE id = ?");
                $stmt->execute([$name, $id]);
                addLog("Marka güncellendi: $name");
                $success = 'Marka başarıyla güncellendi';
            } else {
                // Yeni ekleme
                $stmt = $pdo->prepare("INSERT INTO brands (name, active) VALUES (?, 1)");
                $stmt->execute([$name]);
                addLog("Yeni marka eklendi: $name");
                $success = 'Marka başarıyla eklendi';
            }
        } catch (PDOException $e) {
            $error = 'Kayıt hatası: ' . $e->getMessage();
        }
    }
}

// Düzenleme için marka getir
$editBrand = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
    $stmt->execute([intval($_GET['id'])]);
    $editBrand = $stmt->fetch();
}

// Tüm markaları getir
$brands = $pdo->query("SELECT * FROM brands ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marka Yönetimi - Ofis Su Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2 class="mb-4">💧 Marka Yönetimi</h2>
        
        <div class="row">
            <!-- Form -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?= $editBrand ? 'Marka Düzenle' : 'Yeni Marka Ekle' ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <?= showError($error) ?>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <?= showSuccess($success) ?>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="id" value="<?= $editBrand ? $editBrand['id'] : 0 ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Marka Adı</label>
                                <input type="text" 
                                       name="name" 
                                       class="form-control"
                                       value="<?= $editBrand ? e($editBrand['name']) : '' ?>"
                                       required
                                       autofocus>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <?= $editBrand ? 'Güncelle' : 'Ekle' ?>
                                </button>
                                <?php if ($editBrand): ?>
                                    <a href="manage_brands.php" class="btn btn-secondary">İptal</a>
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
                        <h5 class="mb-0">Marka Listesi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Marka Adı</th>
                                        <th>Durum</th>
                                        <th>Kullanım</th>
                                        <th>Eklenme</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($brands as $brand): ?>
                                        <?php
                                        // Bu markayı kullanan kayıt sayısı
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM water_records WHERE brand_id = ?");
                                        $stmt->execute([$brand['id']]);
                                        $usageCount = $stmt->fetch()['count'];
                                        ?>
                                        <tr>
                                            <td><strong><?= e($brand['name']) ?></strong></td>
                                            <td>
                                                <?php if ($brand['active']): ?>
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
                                            <td class="text-muted small"><?= formatDateTime($brand['created_at']) ?></td>
                                            <td>
                                                <a href="?action=edit&id=<?= $brand['id'] ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                                <a href="?action=toggle&id=<?= $brand['id'] ?>" 
                                                   class="btn btn-sm <?= $brand['active'] ? 'btn-secondary' : 'btn-success' ?>">
                                                    <?= $brand['active'] ? 'Pasif Yap' : 'Aktif Yap' ?>
                                                </a>
                                                <?php if ($usageCount == 0): ?>
                                                    <a href="?action=delete&id=<?= $brand['id'] ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Bu markayı tamamen silmek istediğinizden emin misiniz?')">
                                                        Sil
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-danger" 
                                                            disabled 
                                                            title="Bu marka kullanımda olduğu için silinemez">
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

