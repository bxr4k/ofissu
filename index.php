<?php
/**
 * Ana Dashboard
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$currentUser = getCurrentUser();

// "Bugün Benim" butonu tıklandıysa
if (isset($_GET['action']) && $_GET['action'] === 'bugun_benim') {
    header('Location: add_record.php?quick=1');
    exit;
}

// Son 10 kaydı getir
$stmt = $pdo->query("
    SELECT 
        wr.*,
        u.name as user_name,
        b.name as brand_name,
        pm.name as payment_method_name,
        creator.name as created_by_name
    FROM water_records wr
    JOIN users u ON wr.user_id = u.id
    JOIN brands b ON wr.brand_id = b.id
    JOIN payment_methods pm ON wr.payment_method_id = pm.id
    JOIN users creator ON wr.created_by = creator.id
    ORDER BY wr.created_at DESC
    LIMIT 10
");
$recentRecords = $stmt->fetchAll();

// Hızlı istatistikler
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_records,
        SUM(quantity) as total_quantity,
        SUM(total_price) as total_spent,
        COUNT(DISTINCT user_id) as total_users
    FROM water_records
")->fetch();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ana Sayfa - Ofis Su Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="welcome-section mb-4">
                    <h2>Hoş Geldin, <?= e($currentUser['name']) ?>! 👋</h2>
                    <p class="text-muted">Su alım takip sistemine hoş geldiniz</p>
                </div>
            </div>
        </div>
        
        <!-- Hızlı İşlemler -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <a href="?action=bugun_benim" class="btn btn-success btn-lg w-100 quick-action-btn">
                    💰 Bugün Benim
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="add_record.php" class="btn btn-primary btn-lg w-100 quick-action-btn">
                    ➕ Yeni Kayıt Ekle
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="reports.php" class="btn btn-info btn-lg w-100 quick-action-btn">
                    📊 Raporlar
                </a>
            </div>
        </div>
        
        <!-- Hızlı İstatistikler -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <h3 class="stat-number"><?= $stats['total_records'] ?></h3>
                        <p class="stat-label">Toplam Alım</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <h3 class="stat-number"><?= $stats['total_quantity'] ?></h3>
                        <p class="stat-label">Toplam Adet</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="stat-number"><?= number_format($stats['total_spent'] ?? 0, 2) ?> ₺</h3>
                        <p class="stat-label">Toplam Harcama</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <h3 class="stat-number"><?= $stats['total_users'] ?></h3>
                        <p class="stat-label">Katılımcı</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Son Kayıtlar -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Son Kayıtlar</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentRecords)): ?>
                            <p class="text-muted text-center py-4">Henüz kayıt bulunmuyor. Hemen ilk kaydı ekleyin!</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tarih</th>
                                            <th>Kim Aldı</th>
                                            <th>Marka</th>
                                            <th>Miktar</th>
                                            <th>Fiyat</th>
                                            <th>Ödeme</th>
                                            <?php if ($currentUser['is_admin']): ?>
                                            <th>Ekleyen</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentRecords as $record): ?>
                                            <tr>
                                                <td><?= formatDate($record['date']) ?></td>
                                                <td><strong><?= e($record['user_name']) ?></strong></td>
                                                <td><?= e($record['brand_name']) ?></td>
                                                <td><span class="badge bg-primary"><?= $record['quantity'] ?> adet</span></td>
                                                <td><strong><?= number_format($record['total_price'], 2) ?> ₺</strong></td>
                                                <td><?= e($record['payment_method_name']) ?></td>
                                                <?php if ($currentUser['is_admin']): ?>
                                                <td class="text-muted small"><?= e($record['created_by_name']) ?></td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/mascot-helper.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

