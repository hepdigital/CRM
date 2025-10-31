<?php
// PWA Test Sayfası
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

checkAuth();
require_once 'includes/header.php';
?>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">PWA Test Sayfası</h1>
        <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Progressive Web App özelliklerini test edin</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <button id="pwa-install-test" class="btn btn-primary" onclick="installPWA()">
            <i class="fas fa-download"></i> Uygulamayı Yükle
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-info-circle"></i> PWA Durumu
                </h5>
            </div>
            <div class="card-body">
                <div class="pwa-status">
                    <div class="status-item">
                        <label>Service Worker:</label>
                        <span id="sw-status" class="badge bg-secondary">Kontrol ediliyor...</span>
                    </div>
                    
                    <div class="status-item">
                        <label>Manifest:</label>
                        <span id="manifest-status" class="badge bg-secondary">Kontrol ediliyor...</span>
                    </div>
                    
                    <div class="status-item">
                        <label>Kurulum Durumu:</label>
                        <span id="install-status" class="badge bg-secondary">Kontrol ediliyor...</span>
                    </div>
                    
                    <div class="status-item">
                        <label>Bağlantı Durumu:</label>
                        <span id="connection-status" class="badge bg-success">Çevrimiçi</span>
                    </div>
                    
                    <div class="status-item">
                        <label>Bildirim İzni:</label>
                        <span id="notification-status" class="badge bg-secondary">Kontrol ediliyor...</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-cog"></i> PWA Ayarları
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="requestNotificationPermission()">
                        <i class="fas fa-bell"></i> Bildirim İzni İste
                    </button>
                    
                    <button class="btn btn-outline-info" onclick="testNotification()">
                        <i class="fas fa-paper-plane"></i> Test Bildirimi Gönder
                    </button>
                    
                    <button class="btn btn-outline-warning" onclick="clearCache()">
                        <i class="fas fa-trash"></i> Önbelleği Temizle
                    </button>
                    
                    <button class="btn btn-outline-success" onclick="updateServiceWorker()">
                        <i class="fas fa-sync"></i> Service Worker Güncelle
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-database"></i> Offline Veriler
                </h5>
            </div>
            <div class="card-body">
                <div id="offline-data-list">
                    <p class="text-muted">Offline veriler yükleniyor...</p>
                </div>
                
                <div class="mt-3">
                    <button class="btn btn-outline-primary" onclick="addTestOfflineData()">
                        <i class="fas fa-plus"></i> Test Verisi Ekle
                    </button>
                    <button class="btn btn-outline-danger" onclick="clearOfflineData()">
                        <i class="fas fa-trash"></i> Offline Verileri Temizle
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-mobile-alt"></i> Mobil Özellikler
                </h5>
            </div>
            <div class="card-body">
                <div class="mobile-features">
                    <div class="feature-item">
                        <label>Cihaz Türü:</label>
                        <span id="device-type">Bilinmiyor</span>
                    </div>
                    
                    <div class="feature-item">
                        <label>Ekran Boyutu:</label>
                        <span id="screen-size">-</span>
                    </div>
                    
                    <div class="feature-item">
                        <label>Orientation:</label>
                        <span id="orientation">-</span>
                    </div>
                    
                    <div class="feature-item">
                        <label>Touch Support:</label>
                        <span id="touch-support">-</span>
                    </div>
                    
                    <div class="feature-item">
                        <label>Standalone Mode:</label>
                        <span id="standalone-mode">-</span>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button class="btn btn-outline-info" onclick="testHapticFeedback()">
                        <i class="fas fa-hand-paper"></i> Haptic Feedback Test
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pwa-status .status-item,
.mobile-features .feature-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-light);
}

.pwa-status .status-item:last-child,
.mobile-features .feature-item:last-child {
    border-bottom: none;
}

.pwa-status label,
.mobile-features label {
    font-weight: 500;
    margin: 0;
}

.offline-data-item {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.offline-data-item .timestamp {
    color: var(--text-muted);
    font-size: 0.8rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    checkPWAStatus();
    updateDeviceInfo();
    loadOfflineData();
    
    // Bağlantı durumu değişikliklerini dinle
    window.addEventListener('online', updateConnectionStatus);
    window.addEventListener('offline', updateConnectionStatus);
});

function checkPWAStatus() {
    // Service Worker durumu
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistration().then(registration => {
            const swStatus = document.getElementById('sw-status');
            if (registration) {
                swStatus.textContent = 'Aktif';
                swStatus.className = 'badge bg-success';
            } else {
                swStatus.textContent = 'Kayıtlı Değil';
                swStatus.className = 'badge bg-danger';
            }
        });
    } else {
        document.getElementById('sw-status').textContent = 'Desteklenmiyor';
        document.getElementById('sw-status').className = 'badge bg-danger';
    }
    
    // Manifest durumu
    const manifestLink = document.querySelector('link[rel="manifest"]');
    const manifestStatus = document.getElementById('manifest-status');
    if (manifestLink) {
        fetch(manifestLink.href)
            .then(response => {
                if (response.ok) {
                    manifestStatus.textContent = 'Yüklendi';
                    manifestStatus.className = 'badge bg-success';
                } else {
                    manifestStatus.textContent = 'Hata';
                    manifestStatus.className = 'badge bg-danger';
                }
            })
            .catch(() => {
                manifestStatus.textContent = 'Bulunamadı';
                manifestStatus.className = 'badge bg-danger';
            });
    } else {
        manifestStatus.textContent = 'Bulunamadı';
        manifestStatus.className = 'badge bg-danger';
    }
    
    // Kurulum durumu
    const installStatus = document.getElementById('install-status');
    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
        installStatus.textContent = 'Kurulu';
        installStatus.className = 'badge bg-success';
    } else {
        installStatus.textContent = 'Kurulu Değil';
        installStatus.className = 'badge bg-warning';
    }
    
    // Bildirim durumu
    const notificationStatus = document.getElementById('notification-status');
    if ('Notification' in window) {
        const permission = Notification.permission;
        notificationStatus.textContent = permission === 'granted' ? 'İzin Verildi' : 
                                       permission === 'denied' ? 'Reddedildi' : 'Bekliyor';
        notificationStatus.className = permission === 'granted' ? 'badge bg-success' : 
                                     permission === 'denied' ? 'badge bg-danger' : 'badge bg-warning';
    } else {
        notificationStatus.textContent = 'Desteklenmiyor';
        notificationStatus.className = 'badge bg-danger';
    }
}

function updateConnectionStatus() {
    const connectionStatus = document.getElementById('connection-status');
    if (navigator.onLine) {
        connectionStatus.textContent = 'Çevrimiçi';
        connectionStatus.className = 'badge bg-success';
    } else {
        connectionStatus.textContent = 'Çevrimdışı';
        connectionStatus.className = 'badge bg-danger';
    }
}

function updateDeviceInfo() {
    // Cihaz türü
    const deviceType = /Mobi|Android/i.test(navigator.userAgent) ? 'Mobil' : 'Masaüstü';
    document.getElementById('device-type').textContent = deviceType;
    
    // Ekran boyutu
    document.getElementById('screen-size').textContent = `${window.innerWidth}x${window.innerHeight}`;
    
    // Orientation
    const orientation = window.innerWidth > window.innerHeight ? 'Yatay' : 'Dikey';
    document.getElementById('orientation').textContent = orientation;
    
    // Touch support
    const touchSupport = 'ontouchstart' in window ? 'Var' : 'Yok';
    document.getElementById('touch-support').textContent = touchSupport;
    
    // Standalone mode
    const standaloneMode = window.matchMedia('(display-mode: standalone)').matches ? 'Aktif' : 'Pasif';
    document.getElementById('standalone-mode').textContent = standaloneMode;
}

function loadOfflineData() {
    const offlineData = JSON.parse(localStorage.getItem('offlineData') || '[]');
    const container = document.getElementById('offline-data-list');
    
    if (offlineData.length === 0) {
        container.innerHTML = '<p class="text-muted">Offline veri bulunmuyor.</p>';
        return;
    }
    
    container.innerHTML = '';
    offlineData.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = 'offline-data-item';
        div.innerHTML = `
            <strong>${item.url}</strong> (${item.method})<br>
            <div class="timestamp">${new Date(item.timestamp).toLocaleString('tr-TR')}</div>
        `;
        container.appendChild(div);
    });
}

async function requestNotificationPermission() {
    if ('Notification' in window) {
        const permission = await Notification.requestPermission();
        checkPWAStatus();
        
        if (permission === 'granted') {
            showMobileToast('Bildirim izni verildi!', 'success');
        } else {
            showMobileToast('Bildirim izni reddedildi.', 'error');
        }
    }
}

function testNotification() {
    if (Notification.permission === 'granted') {
        new Notification('CRM Pro Test', {
            body: 'Bu bir test bildirimidir.',
            icon: '/assets/icons/icon-192x192.png',
            badge: '/assets/icons/badge-72x72.png'
        });
    } else {
        showMobileToast('Önce bildirim izni verin.', 'error');
    }
}

async function clearCache() {
    if ('caches' in window) {
        const cacheNames = await caches.keys();
        await Promise.all(cacheNames.map(name => caches.delete(name)));
        showMobileToast('Önbellek temizlendi!', 'success');
    }
}

async function updateServiceWorker() {
    if ('serviceWorker' in navigator) {
        const registration = await navigator.serviceWorker.getRegistration();
        if (registration) {
            await registration.update();
            showMobileToast('Service Worker güncellendi!', 'success');
        }
    }
}

function addTestOfflineData() {
    const testData = {
        url: '/test-endpoint',
        method: 'POST',
        data: { test: 'data', timestamp: Date.now() },
        timestamp: Date.now()
    };
    
    const offlineData = JSON.parse(localStorage.getItem('offlineData') || '[]');
    offlineData.push(testData);
    localStorage.setItem('offlineData', JSON.stringify(offlineData));
    
    loadOfflineData();
    showMobileToast('Test verisi eklendi!', 'success');
}

function clearOfflineData() {
    localStorage.removeItem('offlineData');
    loadOfflineData();
    showMobileToast('Offline veriler temizlendi!', 'success');
}

function testHapticFeedback() {
    // Vibration API test
    if ('vibrate' in navigator) {
        navigator.vibrate([100, 50, 100]);
        showMobileToast('Haptic feedback test edildi!', 'success');
    } else {
        showMobileToast('Haptic feedback desteklenmiyor.', 'error');
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>