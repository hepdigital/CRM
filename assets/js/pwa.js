// PWA Functionality for CRM Pro
class PWAManager {
    constructor() {
        this.deferredPrompt = null;
        this.isInstalled = false;
        this.isOnline = navigator.onLine;
        
        this.init();
    }
    
    init() {
        // Service Worker'ı kaydet
        this.registerServiceWorker();
        
        // PWA kurulum olaylarını dinle
        this.setupInstallPrompt();
        
        // Bağlantı durumu değişikliklerini dinle
        this.setupConnectionEvents();
        
        // Kurulum durumunu kontrol et
        this.checkInstallStatus();
        
        // Offline form verilerini yönet
        this.setupOfflineSupport();
        
        // Bildirim izinlerini kontrol et
        this.setupNotifications();
    }
    
    // Service Worker kaydı
    async registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js');
                console.log('Service Worker registered successfully:', registration);
                
                // Güncelleme kontrolü
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            this.showUpdateNotification();
                        }
                    });
                });
                
            } catch (error) {
                console.log('Service Worker registration failed:', error);
            }
        }
    }
    
    // PWA kurulum prompt'unu ayarla
    setupInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallButton();
        });
        
        window.addEventListener('appinstalled', () => {
            console.log('PWA installed successfully');
            this.isInstalled = true;
            this.hideInstallButton();
            this.showInstallSuccessMessage();
        });
    }
    
    // Kurulum butonunu göster
    showInstallButton() {
        const installButton = document.getElementById('pwa-install-btn');
        if (installButton) {
            installButton.style.display = 'block';
            installButton.addEventListener('click', () => this.installPWA());
        } else {
            // Dinamik olarak kurulum butonu oluştur
            this.createInstallButton();
        }
    }
    
    // Dinamik kurulum butonu oluştur
    createInstallButton() {
        const button = document.createElement('button');
        button.id = 'pwa-install-btn';
        button.className = 'btn btn-primary pwa-install-button';
        button.innerHTML = '<i class="fas fa-download"></i> Uygulamayı Yükle';
        button.addEventListener('click', () => this.installPWA());
        
        // Butonu header'a ekle
        const header = document.querySelector('.page-actions');
        if (header) {
            header.appendChild(button);
        }
    }
    
    // PWA kurulumunu başlat
    async installPWA() {
        if (this.deferredPrompt) {
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                console.log('User accepted the install prompt');
            } else {
                console.log('User dismissed the install prompt');
            }
            
            this.deferredPrompt = null;
        }
    }
    
    // Kurulum butonunu gizle
    hideInstallButton() {
        const installButton = document.getElementById('pwa-install-btn');
        if (installButton) {
            installButton.style.display = 'none';
        }
    }
    
    // Kurulum başarı mesajı
    showInstallSuccessMessage() {
        const toast = this.createToast('success', 'Uygulama başarıyla yüklendi!', 'Artık ana ekranınızdan erişebilirsiniz.');
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }
    
    // Güncelleme bildirimi
    showUpdateNotification() {
        const toast = this.createToast('info', 'Yeni güncelleme mevcut!', 'Sayfayı yenileyerek güncellemeleri alabilirsiniz.');
        
        const refreshButton = document.createElement('button');
        refreshButton.className = 'btn btn-sm btn-light mt-2';
        refreshButton.textContent = 'Şimdi Yenile';
        refreshButton.addEventListener('click', () => {
            window.location.reload();
        });
        
        toast.querySelector('.toast-body').appendChild(refreshButton);
        document.body.appendChild(toast);
    }
    
    // Bağlantı olaylarını ayarla
    setupConnectionEvents() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.showConnectionStatus('online');
            this.syncOfflineData();
        });
        
        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.showConnectionStatus('offline');
        });
    }
    
    // Bağlantı durumu göstergesi
    showConnectionStatus(status) {
        const existingStatus = document.querySelector('.connection-status');
        if (existingStatus) {
            existingStatus.remove();
        }
        
        const statusBar = document.createElement('div');
        statusBar.className = `connection-status ${status}`;
        
        if (status === 'online') {
            statusBar.innerHTML = '<i class="fas fa-wifi"></i> Bağlantı geri geldi - Senkronize ediliyor...';
            statusBar.style.background = '#10b981';
        } else {
            statusBar.innerHTML = '<i class="fas fa-wifi-slash"></i> Bağlantı kesildi - Çevrimdışı modda çalışıyor';
            statusBar.style.background = '#ef4444';
        }
        
        statusBar.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            color: white;
            padding: 0.5rem;
            text-align: center;
            font-size: 0.9rem;
            font-weight: 500;
        `;
        
        document.body.appendChild(statusBar);
        
        // 3 saniye sonra kaldır (online durumunda)
        if (status === 'online') {
            setTimeout(() => {
                statusBar.remove();
            }, 3000);
        }
    }
    
    // Offline destek ayarları
    setupOfflineSupport() {
        // Form gönderimlerini yakala
        document.addEventListener('submit', (e) => {
            if (!this.isOnline) {
                e.preventDefault();
                this.handleOfflineForm(e.target);
            }
        });
    }
    
    // Offline form işleme
    handleOfflineForm(form) {
        const formData = new FormData(form);
        const data = {
            url: form.action || window.location.href,
            method: form.method || 'POST',
            data: Object.fromEntries(formData),
            timestamp: Date.now()
        };
        
        // LocalStorage'a kaydet
        this.saveOfflineData(data);
        
        const toast = this.createToast('warning', 'Çevrimdışı Mod', 'Verileriniz kaydedildi. Bağlantı geri geldiğinde otomatik olarak gönderilecek.');
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }
    
    // Offline veri kaydetme
    saveOfflineData(data) {
        const offlineData = JSON.parse(localStorage.getItem('offlineData') || '[]');
        offlineData.push(data);
        localStorage.setItem('offlineData', JSON.stringify(offlineData));
    }
    
    // Offline verileri senkronize et
    async syncOfflineData() {
        const offlineData = JSON.parse(localStorage.getItem('offlineData') || '[]');
        
        if (offlineData.length > 0) {
            console.log('Syncing offline data:', offlineData.length, 'items');
            
            for (let i = 0; i < offlineData.length; i++) {
                const item = offlineData[i];
                
                try {
                    const formData = new FormData();
                    Object.keys(item.data).forEach(key => {
                        formData.append(key, item.data[key]);
                    });
                    
                    const response = await fetch(item.url, {
                        method: item.method,
                        body: formData
                    });
                    
                    if (response.ok) {
                        // Başarılı, listeden kaldır
                        offlineData.splice(i, 1);
                        i--; // Index'i düzelt
                    }
                } catch (error) {
                    console.log('Sync failed for item:', item, error);
                }
            }
            
            // Güncellenmiş listeyi kaydet
            localStorage.setItem('offlineData', JSON.stringify(offlineData));
            
            if (offlineData.length === 0) {
                const toast = this.createToast('success', 'Senkronizasyon Tamamlandı', 'Tüm offline veriler başarıyla gönderildi.');
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.remove();
                }, 3000);
            }
        }
    }
    
    // Bildirim ayarları
    async setupNotifications() {
        if ('Notification' in window) {
            if (Notification.permission === 'default') {
                // Bildirim izni iste (kullanıcı etkileşimi sonrası)
                document.addEventListener('click', this.requestNotificationPermission, { once: true });
            }
        }
    }
    
    // Bildirim izni iste
    async requestNotificationPermission() {
        if (Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            console.log('Notification permission:', permission);
        }
    }
    
    // Kurulum durumunu kontrol et
    checkInstallStatus() {
        // PWA kurulu mu kontrol et
        if (window.matchMedia('(display-mode: standalone)').matches || 
            window.navigator.standalone === true) {
            this.isInstalled = true;
            console.log('PWA is installed');
        }
    }
    
    // Toast bildirimi oluştur
    createToast(type, title, message) {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : type === 'error' ? 'danger' : 'info'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        `;
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}</strong><br>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        
        return toast;
    }
    
    // Mobil optimizasyonları
    setupMobileOptimizations() {
        // Viewport meta tag kontrolü
        if (!document.querySelector('meta[name="viewport"]')) {
            const viewport = document.createElement('meta');
            viewport.name = 'viewport';
            viewport.content = 'width=device-width, initial-scale=1.0, user-scalable=no';
            document.head.appendChild(viewport);
        }
        
        // Touch olayları için optimizasyon
        document.addEventListener('touchstart', () => {}, { passive: true });
        document.addEventListener('touchmove', () => {}, { passive: true });
        
        // iOS Safari için özel ayarlar
        if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
            document.documentElement.style.setProperty('--vh', `${window.innerHeight * 0.01}px`);
            
            window.addEventListener('resize', () => {
                document.documentElement.style.setProperty('--vh', `${window.innerHeight * 0.01}px`);
            });
        }
    }
}

// PWA Manager'ı başlat
document.addEventListener('DOMContentLoaded', () => {
    window.pwaManager = new PWAManager();
});

// Global fonksiyonlar
window.installPWA = () => {
    if (window.pwaManager) {
        window.pwaManager.installPWA();
    }
};

window.checkOfflineData = () => {
    const offlineData = JSON.parse(localStorage.getItem('offlineData') || '[]');
    console.log('Offline data count:', offlineData.length);
    return offlineData;
};