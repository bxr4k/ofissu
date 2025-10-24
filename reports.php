<?php
/**
 * Raporlama Sayfası
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

// Kullanıcı bazlı istatistikler
$userStats = $pdo->query("
    SELECT 
        u.name,
        COUNT(wr.id) as purchase_count,
        SUM(wr.quantity) as total_quantity,
        SUM(wr.total_price) as total_spent
    FROM users u
    LEFT JOIN water_records wr ON u.id = wr.user_id
    GROUP BY u.id, u.name
    ORDER BY purchase_count DESC, u.name
")->fetchAll();

// Marka bazlı istatistikler
$brandStats = $pdo->query("
    SELECT 
        b.name,
        COUNT(wr.id) as purchase_count,
        SUM(wr.quantity) as total_quantity
    FROM brands b
    LEFT JOIN water_records wr ON b.id = wr.brand_id
    WHERE b.active = 1
    GROUP BY b.id, b.name
    ORDER BY total_quantity DESC
")->fetchAll();

// Ödeme yöntemi bazlı istatistikler
$paymentStats = $pdo->query("
    SELECT 
        pm.name,
        COUNT(wr.id) as purchase_count,
        SUM(wr.quantity) as total_quantity
    FROM payment_methods pm
    LEFT JOIN water_records wr ON pm.id = wr.payment_method_id
    WHERE pm.active = 1
    GROUP BY pm.id, pm.name
    ORDER BY purchase_count DESC
")->fetchAll();

// Aylık tüketim eğilimleri (son 6 ay)
$monthlyTrends = $pdo->query("
    SELECT 
        DATE_FORMAT(date, '%Y-%m') as month,
        COUNT(*) as purchase_count,
        SUM(quantity) as total_quantity
    FROM water_records
    WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(date, '%Y-%m')
    ORDER BY month DESC
")->fetchAll();

// Genel istatistikler
$generalStats = $pdo->query("
    SELECT 
        COUNT(*) as total_purchases,
        SUM(quantity) as total_quantity,
        SUM(total_price) as total_spent,
        AVG(quantity) as avg_quantity_per_purchase,
        AVG(unit_price) as avg_unit_price,
        MIN(date) as first_purchase,
        MAX(date) as last_purchase,
        DATEDIFF(MAX(date), MIN(date)) as days_span
    FROM water_records
")->fetch();

// Ortalama tüketim hızı hesapla (litre bazında)
$avgConsumptionRate = 0;
$avgConsumptionRateLiters = 0;
if ($generalStats && $generalStats['days_span'] > 0) {
    $avgConsumptionRate = $generalStats['total_quantity'] / $generalStats['days_span'];
    $avgConsumptionRateLiters = ($generalStats['total_quantity'] * 19) / $generalStats['days_span'];
}

// Son 6 ayın haftalık tüketimi
$weeklyTrends = $pdo->query("
    SELECT 
        YEARWEEK(date, 1) as week,
        DATE(MIN(date)) as week_start,
        DATE(MAX(date)) as week_end,
        SUM(quantity) as total_quantity
    FROM water_records
    WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY YEARWEEK(date, 1)
    ORDER BY week ASC
")->fetchAll();

// Maksimum değerleri bul (grafik için)
$maxUserCount = !empty($userStats) ? max(array_column($userStats, 'purchase_count')) : 1;
$maxBrandQuantity = !empty($brandStats) ? max(array_column($brandStats, 'total_quantity')) : 1;
$maxMonthlyQuantity = !empty($monthlyTrends) ? max(array_column($monthlyTrends, 'total_quantity')) : 1;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raporlar - Ofis Su Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2 class="mb-4">📊 Raporlar ve İstatistikler</h2>
        
        <!-- Genel İstatistikler -->
        <div class="row mb-4">
            <div class="col-md-2 mb-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3 class="stat-number"><?= $generalStats['total_purchases'] ?? 0 ?></h3>
                        <p class="stat-label mb-0">Toplam Alım</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="stat-number"><?= $generalStats['total_quantity'] ?? 0 ?></h3>
                        <p class="stat-label mb-0">Toplam Adet</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="card stat-card bg-danger text-white">
                    <div class="card-body text-center">
                        <h3 class="stat-number"><?= number_format($generalStats['total_spent'] ?? 0, 0) ?> ₺</h3>
                        <p class="stat-label mb-0">Toplam Harcama</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body text-center">
                        <h3 class="stat-number"><?= number_format($generalStats['avg_quantity_per_purchase'] ?? 0, 1) ?></h3>
                        <p class="stat-label mb-0">Ort. Adet/Alım</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="card stat-card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h3 class="stat-number"><?= number_format($generalStats['avg_unit_price'] ?? 0, 2) ?> ₺</h3>
                        <p class="stat-label mb-0">Ort. Damacana Fiyatı</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="card stat-card bg-secondary text-white">
                    <div class="card-body text-center">
                        <h3 class="stat-number"><?= number_format($avgConsumptionRateLiters, 1) ?></h3>
                        <p class="stat-label mb-0">Litre/Gün</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Kullanıcı Bazlı İstatistikler -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">👥 Kullanıcı Bazlı Alımlar</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kullanıcı</th>
                                        <th>Alım Sayısı</th>
                                        <th>Toplam Adet</th>
                                        <th>Toplam Harcama</th>
                                        <th>Grafik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userStats as $stat): ?>
                                        <tr>
                                            <td><strong><?= e($stat['name']) ?></strong></td>
                                            <td><?= $stat['purchase_count'] ?></td>
                                            <td><span class="badge bg-primary"><?= $stat['total_quantity'] ?? 0 ?> adet</span></td>
                                            <td><strong><?= number_format($stat['total_spent'] ?? 0, 2) ?> ₺</strong></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" 
                                                         style="width: <?= ($stat['purchase_count'] / $maxUserCount * 100) ?>%">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Marka Bazlı İstatistikler -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">💧 Marka Bazlı Tüketim</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Marka</th>
                                        <th>Alım Sayısı</th>
                                        <th>Toplam Adet</th>
                                        <th>Grafik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($brandStats as $stat): ?>
                                        <?php if ($stat['purchase_count'] > 0): ?>
                                            <tr>
                                                <td><strong><?= e($stat['name']) ?></strong></td>
                                                <td><?= $stat['purchase_count'] ?></td>
                                                <td><span class="badge bg-success"><?= $stat['total_quantity'] ?> adet</span></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-success" 
                                                             style="width: <?= ($stat['total_quantity'] / $maxBrandQuantity * 100) ?>%">
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ödeme Yöntemi İstatistikleri -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">💳 Ödeme Yöntemi Dağılımı</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($paymentStats as $stat): ?>
                                <?php if ($stat['purchase_count'] > 0): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4><?= e($stat['name']) ?></h4>
                                                <p class="mb-1"><strong><?= $stat['purchase_count'] ?></strong> alım</p>
                                                <p class="mb-0 text-muted"><?= $stat['total_quantity'] ?> adet</p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Haftalık Tüketim Trendi -->
        <?php if (!empty($weeklyTrends)): ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">📊 Haftalık Tüketim Trendi (Son 6 Ay)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="weeklyChart" style="max-height: 350px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Aylık Tüketim Eğilimleri -->
        <?php if (!empty($monthlyTrends)): ?>
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">📈 Aylık Su Tüketimi (Son 6 Ay)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyChart" style="max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Muhtelif İstatistikler -->
        <?php if ($generalStats && $generalStats['total_purchases'] > 0): ?>
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-header">
                            <h5 class="mb-0">📊 Muhtelif İstatistikler</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3 mb-3">
                                    <h6>İlk Alım</h6>
                                    <p class="h5"><?= formatDate($generalStats['first_purchase']) ?></p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <h6>Son Alım</h6>
                                    <p class="h5"><?= formatDate($generalStats['last_purchase']) ?></p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <h6>Toplam Gün</h6>
                                    <p class="h5"><?= $generalStats['days_span'] ?> gün</p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <h6>Günlük Ort. Tüketim</h6>
                                    <p class="h5"><?= number_format($avgConsumptionRateLiters, 1) ?> litre</p>
                                    <small class="text-muted">(<?= number_format($avgConsumptionRate, 2) ?> adet/gün)</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-md-4 mb-3">
                                    <h6>Toplam Su Tüketimi</h6>
                                    <p class="h5 text-primary"><?= number_format($generalStats['total_quantity'] * 19, 0) ?> litre</p>
                                    <small class="text-muted">(19 lt damacana bazında)</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <h6>Kişi Başı Ort. Tüketim</h6>
                                    <?php 
                                    $activeUsers = count(array_filter($userStats, fn($u) => $u['purchase_count'] > 0));
                                    $perPerson = $activeUsers > 0 ? ($generalStats['total_quantity'] * 19) / $activeUsers : 0;
                                    ?>
                                    <p class="h5 text-success"><?= number_format($perPerson, 1) ?> lt</p>
                                    <small class="text-muted">(Aktif <?= $activeUsers ?> kişi)</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <h6>En Aktif Kullanıcı</h6>
                                    <p class="h5 text-warning"><?= e($userStats[0]['name']) ?></p>
                                    <small class="text-muted">(<?= $userStats[0]['purchase_count'] ?> alım)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/mascot-helper.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Ortak Chart.js ayarları
        Chart.defaults.font.family = 'system-ui, -apple-system, sans-serif';
        Chart.defaults.font.size = 12;
        
        // Haftalık tüketim grafiği (Çizgi Grafik)
        <?php if (!empty($weeklyTrends)): ?>
        const ctxWeekly = document.getElementById('weeklyChart');
        const weeklyData = <?= json_encode($weeklyTrends) ?>;
        const weeklyLabels = weeklyData.map((item, index) => {
            const start = new Date(item.week_start);
            const end = new Date(item.week_end);
            return start.toLocaleDateString('tr-TR', { day: 'numeric', month: 'short' }) + 
                   ' - ' + end.toLocaleDateString('tr-TR', { day: 'numeric', month: 'short' });
        });
        const weeklyQuantities = weeklyData.map(item => item.total_quantity * 19);
        
        new Chart(ctxWeekly, {
            type: 'line',
            data: {
                labels: weeklyLabels,
                datasets: [{
                    label: 'Haftalık Tüketim (Litre)',
                    data: weeklyQuantities,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 6,
                    pointHoverRadius: 9,
                    pointBackgroundColor: 'rgb(54, 162, 235)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    borderWidth: 3,
                    spanGaps: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            padding: 15,
                            font: { size: 13, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 15,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y.toLocaleString('tr-TR') + ' litre';
                            },
                            afterLabel: function(context) {
                                const adet = (context.parsed.y / 19).toFixed(1);
                                return '(' + adet + ' damacana)';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('tr-TR') + ' lt';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
        
        // Aylık tüketim grafiği (Line Chart + Bar Chart Combined)
        <?php if (!empty($monthlyTrends)): ?>
        const ctx = document.getElementById('monthlyChart');
        
        const monthlyData = <?= json_encode(array_reverse($monthlyTrends)) ?>;
        const labels = monthlyData.map(item => {
            const date = new Date(item.month + '-01');
            return date.toLocaleDateString('tr-TR', { month: 'long', year: 'numeric' });
        });
        const quantities = monthlyData.map(item => item.total_quantity * 19);
        
        // Renk gradyanı oluştur (mor tonları)
        const monthlyColors = quantities.map((value, index) => {
            const max = Math.max(...quantities);
            const intensity = value / max;
            return `rgba(153, 102, 255, ${0.4 + (intensity * 0.6)})`;
        });
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Trend Çizgisi',
                        data: quantities,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.4,
                        fill: false,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointBackgroundColor: 'rgb(255, 99, 132)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        borderWidth: 3
                    },
                    {
                        type: 'bar',
                        label: 'Aylık Tüketim (Litre)',
                        data: quantities,
                        backgroundColor: monthlyColors,
                        borderColor: 'rgb(153, 102, 255)',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            padding: 15,
                            font: { size: 13, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 15,
                        titleFont: { size: 15, weight: 'bold' },
                        bodyFont: { size: 14 },
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.type === 'line') {
                                    return 'Trend: ' + context.parsed.y.toLocaleString('tr-TR') + ' litre';
                                }
                                return 'Tüketim: ' + context.parsed.y.toLocaleString('tr-TR') + ' litre';
                            },
                            afterLabel: function(context) {
                                if (context.dataset.type === 'bar') {
                                    const adet = (context.parsed.y / 19).toFixed(1);
                                    return '(' + adet + ' damacana)';
                                }
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('tr-TR') + ' lt';
                            },
                            font: { size: 12 }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>

