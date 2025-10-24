<?php
/**
 * Sistem Logları
 */

require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

// Filtreleme
$filterUser = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$filterAction = isset($_GET['action']) ? trim($_GET['action']) : '';

// Sorgu hazırla
$where = [];
$params = [];

if ($filterUser > 0) {
    $where[] = "l.user_id = ?";
    $params[] = $filterUser;
}

if (!empty($filterAction)) {
    $where[] = "l.action LIKE ?";
    $params[] = "%$filterAction%";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Sayfalama
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Toplam kayıt sayısı
$countQuery = "SELECT COUNT(*) as total FROM logs l $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalLogs = $stmt->fetch()['total'];
$totalPages = ceil($totalLogs / $perPage);

// Logları getir
$query = "
    SELECT 
        l.*,
        u.name as user_name
    FROM logs l
    LEFT JOIN users u ON l.user_id = u.id
    $whereClause
    ORDER BY l.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Kullanıcı listesi (filtre için)
$users = getAllUsers();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Logları - Ofis Su Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2 class="mb-4">📋 Sistem Logları</h2>
        
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
                        <label class="form-label">İşlem</label>
                        <input type="text" 
                               name="action" 
                               class="form-control" 
                               value="<?= e($filterAction) ?>"
                               placeholder="Aranacak kelime...">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filtrele</button>
                        <a href="logs.php" class="btn btn-secondary">Temizle</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Log Listesi -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Log Kayıtları</h5>
                <span class="badge bg-info">Toplam: <?= $totalLogs ?> kayıt</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($logs)): ?>
                    <p class="text-center text-muted py-4">Kayıt bulunamadı</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 180px;">Tarih/Saat</th>
                                    <th style="width: 150px;">Kullanıcı</th>
                                    <th>İşlem</th>
                                    <th>Detaylar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td class="small"><?= formatDateTime($log['created_at']) ?></td>
                                        <td>
                                            <?php if ($log['user_name']): ?>
                                                <strong><?= e($log['user_name']) ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e($log['action']) ?></td>
                                        <td>
                                            <?php if ($log['details']): ?>
                                                <small class="text-muted">
                                                    <?= e(substr($log['details'], 0, 100)) ?>
                                                    <?= strlen($log['details']) > 100 ? '...' : '' ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
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
                                            <a class="page-link" href="?page=<?= $page - 1 ?><?= $filterUser ? "&user_id=$filterUser" : '' ?><?= $filterAction ? "&action=$filterAction" : '' ?>">
                                                Önceki
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?><?= $filterUser ? "&user_id=$filterUser" : '' ?><?= $filterAction ? "&action=$filterAction" : '' ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?><?= $filterUser ? "&user_id=$filterUser" : '' ?><?= $filterAction ? "&action=$filterAction" : '' ?>">
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

