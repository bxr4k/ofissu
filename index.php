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
        
        <!-- Sipariş Butonları -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <a href="tel:+905415319074" class="btn btn-success btn-lg w-100 quick-action-btn">
                    📞 Ara & Sipariş Ver
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="https://wa.me/905415319074?text=Merhaba.%20G%C3%B6lc%C3%BCk%20Belediyesi%20Bas%C4%B1n%20Yay%C4%B1n%20M%C3%BCd%C3%BCrl%C3%BC%C4%9F%C3%BC%20i%C3%A7in%20su%20sipari%C5%9Fi%20vermek%20istiyorum." 
                   target="_blank"
                   class="btn btn-whatsapp btn-lg w-100 quick-action-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16" style="margin-right: 5px;">
                        <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                    </svg>
                    WhatsApp Sipariş
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
        
        <!-- İletişim ve Ödeme Bilgileri -->
        <div class="row mt-4">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">📞 İletişim Bilgileri</h5>
                    </div>
                    <div class="card-body">
                        <div class="contact-info">
                            <p class="mb-2"><strong>Su Siparişi İçin:</strong></p>
                            <p class="mb-2 h5">+90 541 531 90 74</p>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="tel:+905415319074" class="btn btn-success">
                                    📞 Ara
                                </a>
                                <a href="https://wa.me/905415319074?text=Merhaba.%20G%C3%B6lc%C3%BCk%20Belediyesi%20Bas%C4%B1n%20Yay%C4%B1n%20M%C3%BCd%C3%BCrl%C3%BC%C4%9F%C3%BC%20i%C3%A7in%20su%20sipari%C5%9Fi%20vermek%20istiyorum." 
                                   target="_blank" 
                                   class="btn btn-whatsapp">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                        <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                                    </svg>
                                    WhatsApp Sipariş
                                </a>
                            </div>
                            <small class="text-muted d-block mt-2">Mobil cihazlardan tıklayarak arayabilir veya WhatsApp ile sipariş verebilirsiniz</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">💳 Ödeme Bilgileri</h5>
                    </div>
                    <div class="card-body">
                        <div class="payment-info">
                            <p class="mb-2"><strong>Gürpınar Su</strong></p>
                            <p class="mb-2">Erol Eren</p>
                            <hr>
                            <p class="mb-1"><small class="text-muted">IBAN:</small></p>
                            <p class="mb-2">
                                <code class="bg-light p-2 d-inline-block" style="font-size: 0.9rem;">
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
    
    <?php include 'includes/mascot-helper.php'; ?>
    
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

