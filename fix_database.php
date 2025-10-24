<?php
/**
 * KAPSAMLI VERİTABANI GÜNCELLEMESİ
 * 
 * Bu dosya mevcut veritabanınızı en güncel haline getirir:
 * - TC'den telefon numarasına geçiş
 * - Fiyat alanları ekleme
 * - Eksik sütunları tamamlama
 * 
 * KULLANIM: Tarayıcıda bu dosyayı açın, ardından SİLİN!
 */

require_once 'includes/db.php';

echo "<html><head><meta charset='UTF-8'><title>Veritabanı Güncelleme</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<style>body { padding: 2rem; background: #f8f9fa; }</style></head><body>";
echo "<div class='container'><div class='card shadow'><div class='card-body'>";
echo "<h2 class='text-center mb-4'>🔧 Veritabanı Güncelleme Aracı</h2>";

$updates = [];
$errors = [];

try {
    // 1. USERS TABLOSU - TC'den Telefon Numarasına
    echo "<h5>1️⃣ Users Tablosu Kontrolü</h5>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone_number'");
    $hasPhoneNumber = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'tc_no'");
    $hasTcNo = $stmt->rowCount() > 0;
    
    if ($hasTcNo && !$hasPhoneNumber) {
        // tc_no'yu phone_number'a çevir
        $pdo->exec("ALTER TABLE users CHANGE tc_no phone_number VARCHAR(11) NOT NULL COMMENT 'Cep telefonu numarası'");
        $updates[] = "✅ tc_no alanı phone_number olarak değiştirildi";
        
        // Root kullanıcıyı güncelle
        $pdo->exec("UPDATE users SET phone_number = '05555555555' WHERE phone_number = '12345678901'");
        $updates[] = "✅ Root kullanıcı telefon numarası güncellendi";
        
    } elseif ($hasPhoneNumber && $hasTcNo) {
        // Her ikisi de var, tc_no'yu sil
        $pdo->exec("ALTER TABLE users DROP COLUMN tc_no");
        $updates[] = "✅ Gereksiz tc_no sütunu kaldırıldı";
        
    } elseif (!$hasPhoneNumber && !$hasTcNo) {
        // Hiçbiri yok, phone_number ekle
        $pdo->exec("ALTER TABLE users ADD COLUMN phone_number VARCHAR(11) UNIQUE NOT NULL COMMENT 'Cep telefonu numarası' AFTER id");
        $updates[] = "✅ phone_number sütunu eklendi";
    } else {
        $updates[] = "ℹ️ phone_number sütunu zaten mevcut";
    }
    
    // 2. WATER_RECORDS TABLOSU - Fiyat Alanları
    echo "<h5 class='mt-4'>2️⃣ Water Records Tablosu Kontrolü</h5>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM water_records LIKE 'unit_price'");
    $hasUnitPrice = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM water_records LIKE 'total_price'");
    $hasTotalPrice = $stmt->rowCount() > 0;
    
    if (!$hasUnitPrice) {
        $pdo->exec("ALTER TABLE water_records ADD COLUMN unit_price DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Birim fiyat (TL)' AFTER quantity");
        $updates[] = "✅ unit_price sütunu eklendi";
    } else {
        $updates[] = "ℹ️ unit_price sütunu zaten mevcut";
    }
    
    if (!$hasTotalPrice) {
        $pdo->exec("ALTER TABLE water_records ADD COLUMN total_price DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Toplam fiyat (TL)' AFTER unit_price");
        $updates[] = "✅ total_price sütunu eklendi";
    } else {
        $updates[] = "ℹ️ total_price sütunu zaten mevcut";
    }
    
    // 3. İNDEXLER - Performans İyileştirme
    echo "<h5 class='mt-4'>3️⃣ Index Kontrolü</h5>";
    
    try {
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_phone ON users(phone_number)");
        $updates[] = "✅ users.phone_number index eklendi";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key') === false) {
            $updates[] = "ℹ️ users.phone_number index zaten mevcut";
        }
    }
    
    try {
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_date ON water_records(date)");
        $updates[] = "✅ water_records.date index eklendi";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key') === false) {
            $updates[] = "ℹ️ water_records.date index zaten mevcut";
        }
    }
    
    // 4. YORUM EKLEMELERİ - Dokümantasyon
    echo "<h5 class='mt-4'>4️⃣ Sütun Yorumları Ekleniyor</h5>";
    
    try {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN name VARCHAR(100) NOT NULL COMMENT 'İsim soyisim'");
        $pdo->exec("ALTER TABLE users MODIFY COLUMN is_admin TINYINT(1) DEFAULT 0 COMMENT '1=Admin, 0=Normal kullanıcı'");
        $updates[] = "✅ users tablosu yorumları eklendi";
    } catch (PDOException $e) {
        // Hata olsa da devam et
    }
    
    try {
        $pdo->exec("ALTER TABLE brands MODIFY COLUMN name VARCHAR(100) NOT NULL COMMENT 'Marka adı'");
        $pdo->exec("ALTER TABLE brands MODIFY COLUMN active TINYINT(1) DEFAULT 1 COMMENT '1=Aktif, 0=Pasif'");
        $updates[] = "✅ brands tablosu yorumları eklendi";
    } catch (PDOException $e) {
        // Hata olsa da devam et
    }
    
    // 5. SONUÇ RAPORU
    echo "<hr><h4 class='text-success mt-4'>✅ Güncelleme Tamamlandı!</h4>";
    echo "<div class='alert alert-success'>";
    echo "<strong>Yapılan İşlemler:</strong><ul class='mb-0 mt-2'>";
    foreach ($updates as $update) {
        echo "<li>$update</li>";
    }
    echo "</ul></div>";
    
    if (!empty($errors)) {
        echo "<div class='alert alert-warning'>";
        echo "<strong>Uyarılar:</strong><ul class='mb-0 mt-2'>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul></div>";
    }
    
    echo "<div class='alert alert-info'>";
    echo "<strong>📱 Root Kullanıcı Bilgileri:</strong><br>";
    echo "Telefon: <strong>05555555555</strong><br>";
    echo "İsim: Root Admin<br>";
    echo "</div>";
    
    echo "<div class='alert alert-warning'>";
    echo "<strong>⚠️ ÖNEMLİ:</strong><br>";
    echo "1. Çıkış yapıp yeniden giriş yapın<br>";
    echo "2. Bu dosyayı (fix_database.php) SİLİN!<br>";
    echo "3. Sistemi test edin";
    echo "</div>";
    
    echo "<div class='text-center'>";
    echo "<a href='logout.php' class='btn btn-warning me-2'>Çıkış Yap</a>";
    echo "<a href='login.php' class='btn btn-primary'>Giriş Sayfası</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>";
    echo "<strong>❌ HATA:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "</div></div></div></body></html>";

