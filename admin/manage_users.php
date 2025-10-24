<?php
/**
 * Kullanıcı Yönetimi
 */

require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

$error = '';
$success = '';

// Kullanıcı silme
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    
    // Root kullanıcıyı silmeye izin verme
    $stmt = $pdo->prepare("SELECT phone_number, tc_no FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    $userPhone = $user['phone_number'] ?? $user['tc_no'] ?? '';
    
    if ($user && ($userPhone === '05555555555' || $userPhone === '12345678901')) {
        $error = 'Root kullanıcı silinemez!';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            addLog("Kullanıcı silindi: ID=$userId");
            $success = 'Kullanıcı başarıyla silindi';
        } catch (PDOException $e) {
            $error = 'Silme hatası: ' . $e->getMessage();
        }
    }
}

// Kullanıcı ekleme/düzenleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $phone_number = trim($_POST['phone_number'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    // Telefon numarasını temizle ve formatla
    $phone_number = cleanPhoneNumber($phone_number);
    
    if (empty($phone_number)) {
        $error = 'Cep telefonu numarası boş olamaz';
    } elseif (!validatePhone($phone_number)) {
        $error = 'Geçersiz telefon numarası';
    } elseif (empty($name)) {
        $error = 'İsim boş olamaz';
    } else {
        try {
            if ($id > 0) {
                // Güncelleme
                $stmt = $pdo->prepare("UPDATE users SET phone_number = ?, name = ?, is_admin = ? WHERE id = ?");
                $stmt->execute([$phone_number, $name, $is_admin, $id]);
                addLog("Kullanıcı güncellendi: $name ($phone_number)");
                $success = 'Kullanıcı başarıyla güncellendi';
            } else {
                // Yeni ekleme
                $stmt = $pdo->prepare("INSERT INTO users (phone_number, name, is_admin) VALUES (?, ?, ?)");
                $stmt->execute([$phone_number, $name, $is_admin]);
                addLog("Yeni kullanıcı eklendi: $name ($phone_number)");
                $success = 'Kullanıcı başarıyla eklendi';
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $error = 'Bu telefon numarası zaten kayıtlı';
            } else {
                $error = 'Kayıt hatası: ' . $e->getMessage();
            }
        }
    }
}

// Düzenleme için kullanıcı getir
$editUser = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([intval($_GET['id'])]);
    $editUser = $stmt->fetch();
}

// Tüm kullanıcıları getir
$users = getAllUsers();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi - Ofis Su Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2 class="mb-4">👥 Kullanıcı Yönetimi</h2>
        
        <div class="row">
            <!-- Form -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?= $editUser ? 'Kullanıcı Düzenle' : 'Yeni Kullanıcı Ekle' ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <?= showError($error) ?>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <?= showSuccess($success) ?>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="id" value="<?= $editUser ? $editUser['id'] : 0 ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Cep Telefonu</label>
                                <input type="text" 
                                       name="phone_number" 
                                       class="form-control" 
                                       placeholder="05XX XXX XX XX, +90 5XX veya 5XX"
                                       value="<?= $editUser ? e($editUser['phone_number'] ?? $editUser['tc_no'] ?? '') : '' ?>"
                                       required>
                                <small class="form-text text-muted">Herhangi bir formatta girin, otomatik düzeltilir</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">İsim Soyisim</label>
                                <input type="text" 
                                       name="name" 
                                       class="form-control"
                                       value="<?= $editUser ? e($editUser['name']) : '' ?>"
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="is_admin" 
                                           name="is_admin"
                                           <?= ($editUser && $editUser['is_admin']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_admin">
                                        Admin Yetkisi
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <?= $editUser ? 'Güncelle' : 'Ekle' ?>
                                </button>
                                <?php if ($editUser): ?>
                                    <a href="manage_users.php" class="btn btn-secondary">İptal</a>
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
                        <h5 class="mb-0">Kullanıcı Listesi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Telefon</th>
                                        <th>İsim</th>
                                        <th>Yetki</th>
                                        <th>Eklenme</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= e($user['phone_number'] ?? $user['tc_no'] ?? 'Bilinmiyor') ?></td>
                                            <td><strong><?= e($user['name']) ?></strong></td>
                                            <td>
                                                <?php if ($user['is_admin']): ?>
                                                    <span class="badge bg-danger">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Kullanıcı</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted small"><?= formatDateTime($user['created_at']) ?></td>
                                            <td>
                                                <a href="?action=edit&id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                                <?php 
                                                $userPhone = $user['phone_number'] ?? $user['tc_no'] ?? '';
                                                if ($userPhone !== '05555555555' && $userPhone !== '12345678901'): 
                                                ?>
                                                    <a href="?action=delete&id=<?= $user['id'] ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')">
                                                        Sil
                                                    </a>
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

