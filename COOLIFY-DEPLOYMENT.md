# OfisSU - Coolify Deployment Rehberi 🚀

Bu döküman, OfisSU uygulamasını Coolify üzerinde nasıl deploy edeceğinizi adım adım açıklar.

## 📋 Gereksinimler

- Coolify hesabı ve sunucu
- MySQL/MariaDB veritabanı servisi
- Git repository (GitHub, GitLab, vb.)

## 🔧 Deployment Adımları

### 1. Veritabanı Oluşturma

Coolify'da MySQL servis ekleyin:
- **Service Type**: MySQL 8.0 (veya MariaDB)
- **Database Name**: `ofissu`
- **Username**: `ofissu_user`
- **Password**: Güçlü bir şifre belirleyin
- Veritabanı bilgilerini bir yere not alın

### 2. Uygulama Oluşturma

1. Coolify'da **New Resource** → **Application** seçin
2. Git repository'nizi bağlayın
3. **Build Pack**: PHP
4. **PHP Version**: 8.1 veya üzeri

### 3. Environment Variables Ayarlama

Coolify uygulamanızın **Environment Variables** bölümüne şu değişkenleri ekleyin:

```bash
# Uygulama Ayarları
APP_NAME=OfisSU
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ofissu.yourdomain.com

# Veritabanı Ayarları (Kendi bilgilerinizi girin)
DB_HOST=mysql
DB_PORT=3306
DB_NAME=ofissu
DB_USER=ofissu_user
DB_PASS=sizin_veritabani_sifreniz
DB_CHARSET=utf8mb4

# Opsiyonel Ayarlar
SESSION_LIFETIME=1440
SESSION_NAME=OFISSU_SESSION
TIMEZONE=Europe/Istanbul
BOTTLE_SIZE=19
```

**Önemli:** 
- `DB_HOST` genellikle MySQL servisinizin internal hostname'idir (Coolify tarafından sağlanır)
- `DB_PASS` kısmına MySQL için belirlediğiniz şifreyi yazın

### 4. Domain Ayarlama

1. **Domains** bölümünden domain ekleyin
2. SSL sertifikası için "Generate SSL" seçeneğini aktifleştirin
3. Coolify otomatik olarak Let's Encrypt sertifikası oluşturacaktır

### 5. Deploy

1. **Deploy** butonuna tıklayın
2. İlk deploy tamamlandıktan sonra uygulamanıza gidin
3. Otomatik olarak `install.php` sayfasına yönlendirileceksiniz

### 6. Kurulum

İki seçenek var:

#### Seçenek A: Otomatik Kurulum (Önerilen)
Environment variables doğru ayarlandıysa, `install.php` sayfası şunu gösterecektir:
> "Sistem environment variables kullanılarak yapılandırılmış!"

Bu durumda sadece veritabanı tablolarını oluşturmak için kurulum formunu doldurun.

#### Seçenek B: Manuel Kurulum
Eğer environment variables tanınmıyorsa, formu doldurun:
- **Veritabanı Sunucusu**: MySQL internal hostname
- **Veritabanı Adı**: ofissu
- **Kullanıcı Adı**: ofissu_user
- **Şifre**: Veritabanı şifreniz

### 7. İlk Giriş

Kurulum tamamlandıktan sonra:
- **Telefon**: `05555555555`
- Bu root admin hesabıdır
- Giriş yaptıktan sonra şifresiz sisteme gireceksiniz

## 🔐 Güvenlik Önerileri

1. **Root Admin'i Değiştirin**
   - Admin panelinden root kullanıcının telefon numarasını değiştirin
   - Gerçek bir telefon numarası kullanın

2. **install.php'yi Silin** (Opsiyonel ama Önerilir)
   - Kurulum sonrası `install.php` dosyasını silebilirsiniz
   - Veya build sırasında exclude edebilirsiniz

3. **Environment Variables**
   - `.env` dosyasını asla commit etmeyin
   - Tüm hassas bilgileri Coolify environment variables'da tutun

## 📊 Veritabanı Bağlantısı

MySQL servisiniz Coolify tarafından yönetiliyorsa:
- Internal network üzerinden bağlanır
- `DB_HOST` değeri genellikle servis adıdır (örn: `mysql`, `mariadb`)
- Port varsayılan olarak `3306`'dır

## 🔄 Güncelleme

Yeni bir versiyon deploy etmek için:
1. Git repository'nizi güncelleyin
2. Coolify otomatik olarak yeni commit'leri tespit edecektir
3. **Redeploy** butonuna tıklayın
4. Environment variables değişmez, korunur

## ⚡ Performans İpuçları

1. **PHP Optimizasyonu**
   - OPcache aktif olduğundan emin olun
   - `php.ini` ayarlarını Coolify'da optimize edin

2. **Veritabanı**
   - MySQL için yeterli memory ayırın
   - InnoDB buffer pool size'ı artırın

3. **SSL**
   - Coolify'ın otomatik SSL yenileme özelliği aktiftir
   - Sertifika 90 gün önceden yenilenir

## 🐛 Sorun Giderme

### "Permission denied" Hatası
✅ **Çözüldü!** Uygulama artık environment variables kullanıyor, dosya yazma gerektirmiyor.

### Veritabanı Bağlanamıyor
1. Environment variables'ları kontrol edin
2. MySQL servisinizin çalıştığından emin olun
3. DB_HOST'un doğru olduğunu kontrol edin (Coolify internal hostname)

### Sayfa Açılmıyor
1. Deployment loglarını kontrol edin
2. PHP version'ın 8.1+ olduğundan emin olun
3. Domain ayarlarını ve SSL'i kontrol edin

## 📝 Notlar

- Uygulama PHP 8.1+ gerektirir
- MySQL 5.7+ veya MariaDB 10.3+ önerilir
- 19 litrelik damacana standardı varsayılan olarak kullanılır
- Tüm fiyatlar TL cinsindendir

## 🎉 Kurulum Sonrası

Kurulum başarılı olduktan sonra:
1. Root admin ile giriş yapın
2. Yeni kullanıcılar ekleyin
3. Markalar ve ödeme yöntemlerini özelleştirin
4. Su tüketimlerini kaydetmeye başlayın!

## 🆘 Destek

Sorun yaşarsanız:
- GitHub Issues
- Coolify community
- Deployment loglarını kontrol edin

---

**Kolay gelsin! 💧**

