# Ofis Su Takip Sistemi

Ofiste su alımlarını ve harcamalarını takip etmek için geliştirilmiş basit ve kullanışlı bir PHP uygulaması.

## Özellikler

- ✅ Cep telefonu numarası ile şifresiz giriş
- ✅ "Bugün Benim" hızlı kayıt özelliği
- ✅ Herkes herkesin yerine kayıt ekleyebilir
- ✅ Birim fiyat ve toplam fiyat hesaplama
- ✅ Detaylı raporlama ve istatistikler
- ✅ Kullanıcı bazlı alım ve harcama takibi
- ✅ Marka bazlı tüketim analizi
- ✅ Ödeme yöntemi takibi
- ✅ Aylık tüketim eğilimleri
- ✅ Tüm işlemler loglanır
- ✅ Admin yönetim paneli
- ✅ Modern ve responsive arayüz

## Kurulum

### Gereksinimler

- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Web sunucusu (Apache/Nginx)

### Yeni Kurulum

1. Projeyi sunucunuza yükleyin
2. Tarayıcınızda `install.php` dosyasını açın
3. Veritabanı bilgilerinizi girin:
   - Veritabanı Sunucusu: `localhost`
   - Veritabanı Adı: `ofis_su_takip` (veya istediğiniz bir isim)
   - Kullanıcı Adı: MySQL kullanıcı adınız
   - Şifre: MySQL şifreniz
4. "Kurulumu Başlat" butonuna tıklayın
5. Kurulum tamamlandıktan sonra giriş yapabilirsiniz

### Mevcut Veritabanını Güncelleme

**Fiyat Alanları İçin:**
Eğer daha önce kurulum yaptıysanız ve fiyat alanlarını eklemek istiyorsanız:
1. Tarayıcınızda `update_database.php` dosyasını açın
2. Güncelleme otomatik olarak yapılacaktır
3. Güncelleme tamamlandıktan sonra `update_database.php` dosyasını silin

**TC'den Telefon Numarasına Geçiş İçin:**
Eğer TC kimlik numarası kullanıyorsanız ve telefon numarasına geçmek istiyorsanız:
1. Tarayıcınızda `update_tc_to_phone.php` dosyasını açın
2. Güncelleme otomatik olarak yapılacaktır
3. Root kullanıcı telefon numarası: **05555555555**
4. Güncelleme tamamlandıktan sonra `update_tc_to_phone.php` dosyasını silin

### İlk Giriş

Kurulum sonrası default root kullanıcı ile giriş yapabilirsiniz:
- **Cep Telefonu:** 05555555555
- **İsim:** Root Admin
- **Yetki:** Admin

⚠️ **Önemli:** İlk girişten sonra admin panelinden bu bilgileri değiştirmeniz önerilir.

## Kullanım

### Kullanıcı İşlemleri

1. **Giriş Yapma:** Cep telefonu numaranızı (05XX XXX XX XX) girerek sisteme giriş yapın
2. **Hızlı Kayıt:** Ana sayfadaki "Bugün Benim" butonuyla bugün tarihi ve kendiniz için otomatik kayıt ekleyin
3. **Manuel Kayıt:** "Kayıt Ekle" sayfasından herhangi bir kullanıcı adına kayıt oluşturun
   - Tarih seçin (Bugün butonu ile hızlı seçim)
   - Kullanıcı seçin
   - Marka seçin
   - Miktar girin (adet)
   - Toplam fiyat girin (TL)
   - Birim fiyat otomatik hesaplanır
   - Ödeme yöntemini seçin
4. **Raporlar:** "Raporlar" sayfasından detaylı istatistikleri görüntüleyin

### Admin İşlemleri

Admin kullanıcılar ek olarak şunları yapabilir:

- **Kullanıcı Yönetimi:** Yeni kullanıcı ekleme, düzenleme, silme
- **Marka Yönetimi:** Su markası ekleme, düzenleme, aktif/pasif yapma
- **Ödeme Yöntemi Yönetimi:** Ödeme yöntemi ekleme, düzenleme
- **Log Görüntüleme:** Tüm sistem işlemlerini görüntüleme ve filtreleme

## Yapı

```
ofissu/
├── admin/
│   ├── manage_users.php          # Kullanıcı yönetimi
│   ├── manage_brands.php          # Marka yönetimi
│   ├── manage_payment_methods.php # Ödeme yöntemi yönetimi
│   └── logs.php                   # Sistem logları
├── css/
│   └── style.css                  # Özel stiller
├── includes/
│   ├── db.php                     # Veritabanı bağlantısı
│   ├── functions.php              # Yardımcı fonksiyonlar
│   └── header.php                 # Navigasyon menüsü
├── config.php                     # Veritabanı yapılandırması (kurulum sonrası oluşur)
├── install.php                    # Kurulum dosyası
├── login.php                      # Giriş sayfası
├── logout.php                     # Çıkış işlemi
├── index.php                      # Ana sayfa (dashboard)
├── add_record.php                 # Kayıt ekleme sayfası
└── reports.php                    # Raporlama sayfası
```

## Veritabanı Yapısı

### Tablolar

- **users:** Kullanıcı bilgileri (cep telefonu, isim, admin yetkisi)
- **brands:** Su markaları
- **payment_methods:** Ödeme yöntemleri
- **water_records:** Su alım kayıtları (miktar, birim fiyat, toplam fiyat)
- **logs:** Sistem işlem logları

## Güvenlik

- PDO prepared statements ile SQL injection koruması
- XSS koruması (htmlspecialchars)
- Session bazlı yetkilendirme
- Admin sayfaları için yetki kontrolü

## Geliştirme

### Yeni Özellik Ekleme

Sistem modüler yapıda tasarlanmıştır. Yeni özellikler eklemek için:

1. Gerekli veritabanı tablolarını oluşturun
2. `includes/functions.php` dosyasına yardımcı fonksiyonlar ekleyin
3. Yeni sayfa oluşturun ve `includes/header.php` dosyasına menü ekleyin
4. Önemli işlemleri `addLog()` fonksiyonu ile loglayın

## Lisans

Bu proje özel kullanım içindir.

## Destek

Sorularınız için proje sahibi ile iletişime geçin.

---

Made with 💧 for office water management

