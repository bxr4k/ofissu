<?php
/**
 * Ofis Su Takip Sistemi - Kurulum Dosyası (Güncel Versiyon)
 * Tüm güncel özelliklerle hazırlanmıştır
 */

$errors = [];
$success = false;

// Kurulum zaten yapılmış mı kontrol et
if (file_exists('config.php')) {
    require_once 'config.php';
    if (defined('DB_HOST')) {
        $success = true;
        $message = "Sistem zaten kurulmuş! <a href='login.php'>Giriş yapmak için tıklayın</a><br><small>Eğer yeniden kurulum yapmak istiyorsanız, config.php dosyasını silin.</small>";
    }
}

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$success) {
    $host = trim($_POST['host'] ?? '');
    $dbname = trim($_POST['dbname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Basit validasyon
    if (empty($host)) $errors[] = "Veritabanı sunucusu boş olamaz";
    if (empty($dbname)) $errors[] = "Veritabanı adı boş olamaz";
    if (empty($username)) $errors[] = "Kullanıcı adı boş olamaz";
    
    if (empty($errors)) {
        try {
            // Veritabanına bağlan
            $dsn = "mysql:host=$host;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Veritabanını oluştur
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbname`");
            
            // Tabloları oluştur
            
            // 1. users tablosu (phone_number ile)
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    phone_number VARCHAR(11) UNIQUE NOT NULL COMMENT 'Cep telefonu numarası (05XXXXXXXXX)',
                    name VARCHAR(100) NOT NULL COMMENT 'İsim soyisim',
                    is_admin TINYINT(1) DEFAULT 0 COMMENT '1=Admin, 0=Normal kullanıcı',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_phone (phone_number)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // 2. brands tablosu
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS brands (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL COMMENT 'Marka adı',
                    active TINYINT(1) DEFAULT 1 COMMENT '1=Aktif, 0=Pasif',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // 3. payment_methods tablosu
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS payment_methods (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL COMMENT 'Ödeme yöntemi adı',
                    active TINYINT(1) DEFAULT 1 COMMENT '1=Aktif, 0=Pasif',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // 4. water_records tablosu (fiyat bilgileriyle)
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS water_records (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL COMMENT 'Suyu alan kullanıcı',
                    date DATE NOT NULL COMMENT 'Alım tarihi',
                    brand_id INT NOT NULL COMMENT 'Su markası',
                    quantity INT NOT NULL COMMENT 'Miktar (adet)',
                    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Birim fiyat (TL)',
                    total_price DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Toplam fiyat (TL)',
                    payment_method_id INT NOT NULL COMMENT 'Ödeme yöntemi',
                    created_by INT NOT NULL COMMENT 'Kaydı ekleyen kullanıcı',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
                    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE CASCADE,
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_user (user_id),
                    INDEX idx_date (date),
                    INDEX idx_created_by (created_by)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // 5. logs tablosu
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT COMMENT 'İşlemi yapan kullanıcı',
                    action VARCHAR(255) NOT NULL COMMENT 'İşlem açıklaması',
                    details TEXT COMMENT 'Detaylı bilgi (JSON)',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                    INDEX idx_user (user_id),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Default verileri ekle
            
            // Root admin kullanıcısı
            $stmt = $pdo->prepare("INSERT IGNORE INTO users (phone_number, name, is_admin) VALUES (?, ?, 1)");
            $stmt->execute(['05555555555', 'Root Admin']);
            
            // Default markalar
            $brands = ['Erikli', 'Hamidiye', 'Sırma', 'Beypazarı'];
            $stmt = $pdo->prepare("INSERT IGNORE INTO brands (name) VALUES (?)");
            foreach ($brands as $brand) {
                $stmt->execute([$brand]);
            }
            
            // Default ödeme yöntemleri
            $methods = ['Nakit', 'Kredi Kartı', 'Havale', 'Banka Kartı'];
            $stmt = $pdo->prepare("INSERT IGNORE INTO payment_methods (name) VALUES (?)");
            foreach ($methods as $method) {
                $stmt->execute([$method]);
            }
            
            // config.php dosyasını oluştur
            $config_content = "<?php\n";
            $config_content .= "/**\n";
            $config_content .= " * Veritabanı Yapılandırması\n";
            $config_content .= " * Otomatik oluşturuldu: " . date('Y-m-d H:i:s') . "\n";
            $config_content .= " */\n\n";
            $config_content .= "define('DB_HOST', '$host');\n";
            $config_content .= "define('DB_NAME', '$dbname');\n";
            $config_content .= "define('DB_USER', '$username');\n";
            $config_content .= "define('DB_PASS', '" . addslashes($password) . "');\n";
            $config_content .= "define('DB_CHARSET', 'utf8mb4');\n";
            
            file_put_contents('config.php', $config_content);
            
            $success = true;
            $message = "<strong>🎉 Kurulum başarıyla tamamlandı!</strong><br><br>";
            $message .= "✅ Veritabanı oluşturuldu<br>";
            $message .= "✅ Tüm tablolar oluşturuldu<br>";
            $message .= "✅ Fiyat sistemi aktif<br>";
            $message .= "✅ Telefon numarası sistemi aktif<br><br>";
            $message .= "<div class='alert alert-success'>";
            $message .= "<strong>Root Kullanıcı Bilgileri:</strong><br>";
            $message .= "📱 Telefon: <strong>05555555555</strong><br>";
            $message .= "👤 İsim: Root Admin<br>";
            $message .= "🔑 Yetki: Admin";
            $message .= "</div>";
            $message .= "<a href='login.php' class='btn btn-primary btn-lg mt-3'>Giriş Yap</a>";
            
        } catch (PDOException $e) {
            $errors[] = "Veritabanı hatası: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ofis Su Takip - Kurulum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .install-container {
            max-width: 600px;
            width: 100%;
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .feature-list {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .feature-list li {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <h2 class="text-center mb-2">💧 Ofis Su Takip Sistemi</h2>
        <h5 class="text-center mb-4 text-muted">Kurulum Sihirbazı</h5>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= $message ?>
            </div>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>❌ Hatalar:</strong>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="alert alert-info">
                <strong>📋 Kurulum Bilgileri:</strong>
                <ul class="mb-0 mt-2">
                    <li>✅ Telefon numarası ile giriş sistemi</li>
                    <li>✅ Fiyat takip sistemi (birim + toplam)</li>
                    <li>✅ Marka ve ödeme yöntemi yönetimi</li>
                    <li>✅ Detaylı raporlama</li>
                    <li>✅ Sistem logları</li>
                </ul>
            </div>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Veritabanı Sunucusu</label>
                    <input type="text" name="host" class="form-control" value="localhost" required>
                    <small class="form-text text-muted">Genellikle "localhost"</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Veritabanı Adı</label>
                    <input type="text" name="dbname" class="form-control" placeholder="ofis_su_takip" required>
                    <small class="form-text text-muted">Yoksa otomatik oluşturulacak</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Kullanıcı Adı</label>
                    <input type="text" name="username" class="form-control" value="root" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Şifre</label>
                    <input type="password" name="password" class="form-control" placeholder="Boş bırakılabilir">
                </div>
                
                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    🚀 Kurulumu Başlat
                </button>
            </form>
            
            <div class="alert alert-warning mt-3">
                <strong>⚠️ Önemli:</strong> Kurulum tamamlandıktan sonra root kullanıcı ile giriş yapabilirsiniz:
                <br><strong>📱 Telefon: 05555555555</strong>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

