<!-- Maskot Yardım Widget -->
<div class="mascot-widget">
    <!-- Konuşma Balonu -->
    <div class="speech-bubble" id="speechBubble">
        <div class="speech-bubble-content">
            <strong style="font-size: 1.1rem;">Merhaba! 👋</strong><br>
            <span style="font-size: 0.95rem;">Yardıma ihtiyacın var mı?<br>Bana tıkla!</span>
        </div>
    </div>
    
    <!-- Maskot Görseli -->
    <div class="mascot-container" id="mascotContainer">
        <?php 
        // Admin klasöründe miyiz kontrol et
        $isAdminPage = str_contains($_SERVER['PHP_SELF'], '/admin/');
        $imagePath = $isAdminPage ? '../images/mascot.png' : 'images/mascot.png';
        ?>
        <img src="<?= $imagePath ?>" 
             alt="OfisSU Maskot" 
             class="mascot-image pulse" 
             id="mascotImage"
             draggable="false"
             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22150%22 height=%22200%22%3E%3Cellipse cx=%2275%22 cy=%22100%22 rx=%2260%22 ry=%2280%22 fill=%22%2364B5F6%22/%3E%3Ctext x=%2275%22 y=%22120%22 text-anchor=%22middle%22 font-size=%2260%22 fill=%22white%22%3E💧%3C/text%3E%3C/svg%3E'">
        <span class="help-badge">?</span>
    </div>
    
    <!-- Yardım Paneli -->
    <div class="help-panel" id="helpPanel">
        <div class="help-panel-header">
            <h3>💧 OfisSU Yardım Merkezi</h3>
            <button class="close-help" onclick="toggleHelp()">×</button>
        </div>
        <div class="help-panel-body">
            <!-- FAQ Items -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>🔐 Sisteme nasıl giriş yapabilirim?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p><strong>Cep telefonu numaranız ile giriş yapabilirsiniz:</strong></p>
                    <ul>
                        <li>Login sayfasında telefon numaranızı girin</li>
                        <li>Format: 05XX XXX XX XX</li>
                        <li>Kayıtlı değilseniz admin ile iletişime geçin</li>
                    </ul>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>💰 "Bugün Benim" butonu ne işe yarar?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p><strong>Hızlı kayıt için kullanılır:</strong></p>
                    <ul>
                        <li>Ana sayfadaki yeşil butona tıklayın</li>
                        <li>Bugünün tarihi ve sizin adınız otomatik seçilir</li>
                        <li>Sadece marka, miktar ve fiyat girin</li>
                        <li>Süper pratik! ⚡</li>
                    </ul>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>➕ Yeni kayıt nasıl eklerim?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p><strong>Adım adım kayıt ekleme:</strong></p>
                    <ol>
                        <li><strong>Kayıt Ekle</strong> sayfasına gidin</li>
                        <li><strong>Kim aldı?</strong> - Kullanıcı seçin</li>
                        <li><strong>Tarih</strong> - Bugün butonu ile hızlı seçim</li>
                        <li><strong>Marka</strong> - Su markasını seçin</li>
                        <li><strong>Miktar</strong> - Kaç adet? (örn: 2)</li>
                        <li><strong>Toplam Fiyat</strong> - Ödenen tutar (örn: 40 TL)</li>
                        <li><strong>Birim fiyat</strong> otomatik hesaplanır!</li>
                        <li><strong>Ödeme Yöntemi</strong> - Nakit, Kart vs.</li>
                        <li><strong>Kaydet</strong> butonuna basın</li>
                    </ol>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>✏️ Kayıtları düzenleyebilir miyim?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p><strong>Evet, herkes düzenleyebilir:</strong></p>
                    <ul>
                        <li><strong>Kayıtları Görüntüle</strong> sayfasına gidin</li>
                        <li>Düzenlemek istediğiniz kaydın ✏️ butonuna tıklayın</li>
                        <li>İstediğiniz değişiklikleri yapın</li>
                        <li>Kaydet butonuna basın</li>
                        <li><strong>Not:</strong> Tüm değişiklikler loglanır 📝</li>
                    </ul>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>📊 Raporları nasıl görüntülerim?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p><strong>Detaylı raporlar mevcut:</strong></p>
                    <ul>
                        <li><strong>Raporlar</strong> sayfasına gidin</li>
                        <li>Kullanıcı bazlı harcama raporları</li>
                        <li>Marka bazlı tüketim istatistikleri</li>
                        <li>Aylık tüketim grafikleri</li>
                        <li>Toplam su tüketimi (litre bazında)</li>
                        <li>Kişi başı ortalama tüketim</li>
                    </ul>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>💡 İpuçları ve Püf Noktaları</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p><strong>Sistemi daha verimli kullanın:</strong></p>
                    <ul>
                        <li>🚀 <strong>Hızlı Kayıt:</strong> "Bugün Benim" butonunu kullanın</li>
                        <li>🔍 <strong>Arama:</strong> Kayıtları kullanıcı/ay bazında filtreleyin</li>
                        <li>📊 <strong>Grafik:</strong> Aylık tüketim grafiğinde hover yapın</li>
                        <li>📝 <strong>Log:</strong> Her işlem kaydedilir, şeffaf sistem</li>
                        <li>📱 <strong>Mobil:</strong> Telefonda da sorunsuz çalışır</li>
                        <li>💾 <strong>Otomatik:</strong> Birim fiyat otomatik hesaplanır</li>
                    </ul>
                </div>
            </div>
            
            <div class="alert alert-info mt-3">
                <strong>💧 OfisSU Hakkında</strong><br>
                Ofis su tüketimini takip etmek için geliştirilmiş basit ve kullanışlı bir sistemdir.
            </div>
        </div>
    </div>
</div>

<script>
// Yardım panelini aç/kapat
function toggleHelp() {
    const panel = document.getElementById('helpPanel');
    const bubble = document.getElementById('speechBubble');
    const mascot = document.getElementById('mascotImage');
    
    panel.classList.toggle('active');
    bubble.classList.remove('active');
    
    // Pulse animasyonunu kaldır
    if (panel.classList.contains('active')) {
        mascot.classList.remove('pulse');
    }
}

// FAQ itemlerini aç/kapat
function toggleFaq(element) {
    const faqItem = element.parentElement;
    const allItems = document.querySelectorAll('.faq-item');
    
    // Diğerlerini kapat
    allItems.forEach(item => {
        if (item !== faqItem) {
            item.classList.remove('active');
        }
    });
    
    // Bu itemı toggle et
    faqItem.classList.toggle('active');
}

// Maskotu sürüklenebilir yap
let isDragging = false;
let hasMoved = false;
let currentX;
let currentY;
let initialX;
let initialY;
let xOffset = 0;
let yOffset = 0;
let dragStartTime = 0;
let startX = 0;
let startY = 0;
const DRAG_THRESHOLD = 10; // 10 pixel hareket eşiği
const CLICK_TIME_THRESHOLD = 300; // 300ms - mobil için daha uzun

const mascotWidget = document.querySelector('.mascot-widget');
const mascotContainer = document.getElementById('mascotContainer');
const mascotImage = document.getElementById('mascotImage');

// Mouse olayları
mascotContainer.addEventListener('mousedown', dragStart);
document.addEventListener('mousemove', drag);
document.addEventListener('mouseup', dragEnd);

// Touch olayları
mascotContainer.addEventListener('touchstart', dragStart, { passive: false });
document.addEventListener('touchmove', drag, { passive: false });
document.addEventListener('touchend', dragEnd);

function dragStart(e) {
    // Panel açıksa sürükleme yapma
    if (document.getElementById('helpPanel').classList.contains('active')) {
        return;
    }
    
    dragStartTime = Date.now();
    hasMoved = false;
    
    if (e.type === 'touchstart') {
        initialX = e.touches[0].clientX - xOffset;
        initialY = e.touches[0].clientY - yOffset;
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    } else {
        initialX = e.clientX - xOffset;
        initialY = e.clientY - yOffset;
        startX = e.clientX;
        startY = e.clientY;
    }
    
    if (e.target === mascotContainer || e.target === mascotImage || e.target.classList.contains('help-badge')) {
        isDragging = true;
        mascotWidget.style.cursor = 'grabbing';
        mascotImage.style.cursor = 'grabbing';
    }
}

function drag(e) {
    if (isDragging) {
        let clientX, clientY;
        
        if (e.type === 'touchmove') {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        } else {
            clientX = e.clientX;
            clientY = e.clientY;
        }
        
        // Hareket mesafesini kontrol et
        const deltaX = Math.abs(clientX - startX);
        const deltaY = Math.abs(clientY - startY);
        
        if (deltaX > DRAG_THRESHOLD || deltaY > DRAG_THRESHOLD) {
            hasMoved = true;
            e.preventDefault();
            
            if (e.type === 'touchmove') {
                currentX = e.touches[0].clientX - initialX;
                currentY = e.touches[0].clientY - initialY;
            } else {
                currentX = e.clientX - initialX;
                currentY = e.clientY - initialY;
            }
            
            xOffset = currentX;
            yOffset = currentY;
            
            // Ekran sınırları içinde tut
            const rect = mascotWidget.getBoundingClientRect();
            const maxX = window.innerWidth - rect.width - 20;
            const maxY = window.innerHeight - rect.height - 20;
            
            // Sınırlama hesapları
            xOffset = Math.min(Math.max(-(window.innerWidth - rect.width - 40), xOffset), maxX - 20);
            yOffset = Math.min(Math.max(-(window.innerHeight - rect.height - 40), yOffset), maxY - 20);
            
            setTranslate(xOffset, yOffset, mascotWidget);
        }
    }
}

function dragEnd(e) {
    if (isDragging) {
        isDragging = false;
        mascotWidget.style.cursor = 'grab';
        mascotImage.style.cursor = 'grab';
        
        // Pozisyonu local storage'a kaydet
        if (hasMoved) {
            localStorage.setItem('mascotX', xOffset);
            localStorage.setItem('mascotY', yOffset);
        }
        
        // Eğer hareket etmedi veya çok hızlı tıklandıysa, toggle help çağır
        const dragDuration = Date.now() - dragStartTime;
        if (!hasMoved && dragDuration < CLICK_TIME_THRESHOLD) {
            setTimeout(() => toggleHelp(), 50);
        }
    }
}

function setTranslate(xPos, yPos, el) {
    el.style.transform = `translate(${xPos}px, ${yPos}px)`;
}

// Sayfa yüklendiğinde kaydedilmiş pozisyonu al
window.addEventListener('DOMContentLoaded', function() {
    const savedX = localStorage.getItem('mascotX');
    const savedY = localStorage.getItem('mascotY');
    
    if (savedX !== null && savedY !== null) {
        xOffset = parseInt(savedX);
        yOffset = parseInt(savedY);
        setTranslate(xOffset, yOffset, mascotWidget);
    }
    
    mascotWidget.style.cursor = 'grab';
    mascotImage.style.cursor = 'grab';
    
    // İlk yüklemede konuşma balonunu göster
    setTimeout(function() {
        const bubble = document.getElementById('speechBubble');
        bubble.classList.add('active');
        
        // 6 saniye sonra gizle
        setTimeout(function() {
            bubble.classList.remove('active');
        }, 6000);
    }, 2000);
});

// Panel dışına tıklandığında kapat
document.addEventListener('click', function(event) {
    const widget = document.querySelector('.mascot-widget');
    const panel = document.getElementById('helpPanel');
    
    if (!widget.contains(event.target) && panel.classList.contains('active')) {
        panel.classList.remove('active');
    }
});
</script>

