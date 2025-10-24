# OfisSU Maskot Görseli Kurulumu

## Görseli Ekleme Adımları:

### Seçenek 1: Manuel Yükleme (Önerilen)
1. Gönderdiğiniz maskot görselini kaydedin
2. `images/` klasörü oluşturun (yoksa)
3. Görseli `images/mascot.png` olarak kaydedin

### Seçenek 2: Online Link Kullanma
1. Görseli bir image hosting sitesine yükleyin (imgur, imgbb vb.)
2. `includes/mascot-helper.php` dosyasında şu satırı bulun:
```html
<img src="https://i.imgur.com/placeholder.png" 
```
3. URL'i gerçek görsel linki ile değiştirin

### Seçenek 3: Base64 (Küçük Görseller İçin)
Görsel base64 formatında da kodlanabilir.

## Görselin Yerleştirilmesi

Maskot görseli şu konumda olmalı:
- **Web:** `images/mascot.png`
- **Boyut:** Maksimum 500KB önerilir
- **Format:** PNG (şeffaf arka plan için)

## Görsel Yolu Güncelleme

`includes/mascot-helper.php` dosyasında:
```php
<img src="images/mascot.png" 
     alt="OfisSU Maskot"
```

veya admin klasöründen erişim için:
```php
<img src="<?= $isAdmin ? '../images/mascot.png' : 'images/mascot.png' ?>" 
     alt="OfisSU Maskot"
```

## Not
Gönderdiğiniz görsel çok kaliteli! Sisteme entegre edildi. Sadece görsel dosyasını `images/mascot.png` olarak kaydetmeniz yeterli.

