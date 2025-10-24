<?php
/**
 * Su Kayıtlarını Görüntüleme ve Düzenleme
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$currentUser = getCurrentUser();
$error = '';
$success = '';

// Kayıt silme
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $recordId = intval($_GET['id']);
    
    try {
        // Önce kayıt bilgilerini al (log için)
        $stmt = $pdo->prepare("
            SELECT wr.*, u.name as user_name 
            FROM water_records wr 
            JOIN users u ON wr.user_id = u.id 
            WHERE wr.id = ?
        ");
        $stmt->execute([$recordId]);
        $record = $stmt->fetch();
        
        if ($record) {
            $stmt = $pdo->prepare("DELETE FROM water_records WHERE id = ?");
            $stmt->execute([$recordId]);
            
            addLog("Kayıt silindi: {$record['user_name']} - {$record['date']} - {$record['total_price']} TL");
            $success = 'Kayıt başarıyla silindi';
        }
    } catch (PDOException $e) {
        $error = 'Silme hatası: ' . $e->getMessage();
    }
}

// Sayfalama
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filtreleme
$filterUser = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$filterMonth = isset($_GET['month']) ? trim($_GET['month']) : '';

$where = [];
$params = [];

if ($filterUser > 0) {
    $where[] = "wr.user_id = ?";
    $params[] = $filterUser;
}

if (!empty($filterMonth)) {
    $where[] = "DATE_FORMAT(wr.date, '%Y-%m') = ?";
    $params[] = $filterMonth;
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Toplam kayıt sayısı
$countQuery = "SELECT COUNT(*) as total FROM water_records wr $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalRecords = $stmt->fetch()['total'];
$totalPages = ceil($totalRecords / $perPage);

// Kayıtları getir
$query = "
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
    $whereClause
    ORDER BY wr.date DESC, wr.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll();

// Kullanıcı listesi (filtre için)
$users = getAllUsers();

// Son 12 ayın listesi (filtre için)
$months = [];
for ($i = 0; $i < 12; $i++) {
    $date = date('Y-m', strtotime("-$i months"));
    $months[] = $date;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Su Kayıtları - Ofis Su Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📋 Su Kayıtları</h2>
            <a href="add_record.php" class="btn btn-primary">➕ Yeni Kayıt</a>
        </div>
        
        <!-- Filtreleme -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kullanıcı</label>
                        <select name="user_id" class="form-select">
                            <option value="0">Tümü</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= $filterUser == $user['id'] ? 'selected' : '' ?>>
                                    <?= e($user['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ay</label>
                        <select name="month" class="form-select">
                            <option value="">Tümü</option>
                            <?php foreach ($months as $month): ?>
                                <option value="<?= $month ?>" <?= $filterMonth == $month ? 'selected' : '' ?>>
                                    <?= date('F Y', strtotime($month . '-01')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filtrele</button>
                        <a href="view_records.php" class="btn btn-secondary">Temizle</a>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($error): ?>
            <?= showError($error) ?>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <?= showSuccess($success) ?>
        <?php endif; ?>
        
        <!-- Kayıt Listesi -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Kayıtlar</h5>
                <span class="badge bg-info">Toplam: <?= $totalRecords ?> kayıt</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($records)): ?>
                    <p class="text-center text-muted py-4">Kayıt bulunamadı</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>Kim Aldı</th>
                                    <th>Marka</th>
                                    <th>Miktar</th>
                                    <th>Birim Fiyat</th>
                                    <th>Toplam</th>
                                    <th>Ödeme</th>
                                    <th>Ekleyen</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?= formatDate($record['date']) ?></td>
                                        <td><strong><?= e($record['user_name']) ?></strong></td>
                                        <td><?= e($record['brand_name']) ?></td>
                                        <td><span class="badge bg-primary"><?= $record['quantity'] ?> adet</span></td>
                                        <td><?= number_format($record['unit_price'], 2) ?> ₺</td>
                                        <td><strong><?= number_format($record['total_price'], 2) ?> ₺</strong></td>
                                        <td><?= e($record['payment_method_name']) ?></td>
                                        <td class="text-muted small"><?= e($record['created_by_name']) ?></td>
                                        <td>
                                            <a href="edit_record.php?id=<?= $record['id'] ?>" 
                                               class="btn btn-sm btn-warning"
                                               title="Düzenle">
                                                ✏️
                                            </a>
                                            <a href="?action=delete&id=<?= $record['id'] ?>&page=<?= $page ?><?= $filterUser ? "&user_id=$filterUser" : '' ?><?= $filterMonth ? "&month=$filterMonth" : '' ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Bu kaydı silmek istediğinizden emin misiniz?')"
                                               title="Sil">
                                                🗑️
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Sayfalama -->
                    <?php if ($totalPages > 1): ?>
                        <div class="card-footer">
                            <nav>
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page - 1 ?><?= $filterUser ? "&user_id=$filterUser" : '' ?><?= $filterMonth ? "&month=$filterMonth" : '' ?>">
                                                Önceki
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?><?= $filterUser ? "&user_id=$filterUser" : '' ?><?= $filterMonth ? "&month=$filterMonth" : '' ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?><?= $filterUser ? "&user_id=$filterUser" : '' ?><?= $filterMonth ? "&month=$filterMonth" : '' ?>">
                                                Sonraki
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/mascot-helper.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

