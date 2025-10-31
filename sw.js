// Service Worker for CRM Pro PWA
const CACHE_NAME = 'crm-pro-v1.0.0';
const OFFLINE_URL = '/offline.html';

// Önbelleğe alınacak dosyalar
const CACHE_URLS = [
  '/',
  '/offline.html',
  '/assets/css/modern-theme.css',
  '/assets/js/theme.js',
  '/login.php',
  '/modules/customers/list.php',
  '/modules/quotes/list.php',
  '/modules/interactions/list.php',
  '/modules/notes/list.php',
  // Bootstrap ve Font Awesome CDN'leri
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
  'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
  'https://cdn.jsdelivr.net/npm/chart.js'
];

// Service Worker kurulumu
self.addEventListener('install', event => {
  console.log('Service Worker: Installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Service Worker: Caching files');
        return cache.addAll(CACHE_URLS);
      })
      .then(() => {
        console.log('Service Worker: Cached all files successfully');
        return self.skipWaiting();
      })
      .catch(err => {
        console.log('Service Worker: Cache failed', err);
      })
  );
});

// Service Worker aktivasyonu
self.addEventListener('activate', event => {
  console.log('Service Worker: Activating...');
  
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cache => {
          if (cache !== CACHE_NAME) {
            console.log('Service Worker: Deleting old cache', cache);
            return caches.delete(cache);
          }
        })
      );
    }).then(() => {
      console.log('Service Worker: Activated');
      return self.clients.claim();
    })
  );
});

// Fetch olayları - Network First stratejisi
self.addEventListener('fetch', event => {
  // Sadece GET isteklerini önbelleğe al
  if (event.request.method !== 'GET') {
    return;
  }

  // API istekleri için Network First
  if (event.request.url.includes('/api/') || 
      event.request.url.includes('.php') ||
      event.request.url.includes('modules/')) {
    
    event.respondWith(
      fetch(event.request)
        .then(response => {
          // Başarılı yanıtı önbelleğe kaydet
          if (response.status === 200) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then(cache => {
              cache.put(event.request, responseClone);
            });
          }
          return response;
        })
        .catch(() => {
          // Network başarısız, önbellekten dön
          return caches.match(event.request)
            .then(cachedResponse => {
              if (cachedResponse) {
                return cachedResponse;
              }
              // Eğer sayfa önbellekte yoksa offline sayfasını göster
              if (event.request.mode === 'navigate') {
                return caches.match(OFFLINE_URL);
              }
            });
        })
    );
  } 
  // Statik dosyalar için Cache First
  else {
    event.respondWith(
      caches.match(event.request)
        .then(cachedResponse => {
          if (cachedResponse) {
            return cachedResponse;
          }
          return fetch(event.request)
            .then(response => {
              // Başarılı yanıtı önbelleğe kaydet
              if (response.status === 200) {
                const responseClone = response.clone();
                caches.open(CACHE_NAME).then(cache => {
                  cache.put(event.request, responseClone);
                });
              }
              return response;
            });
        })
    );
  }
});

// Background Sync - Offline işlemler için
self.addEventListener('sync', event => {
  console.log('Service Worker: Background sync', event.tag);
  
  if (event.tag === 'background-sync') {
    event.waitUntil(
      // Offline sırasında kaydedilen verileri senkronize et
      syncOfflineData()
    );
  }
});

// Push bildirimleri
self.addEventListener('push', event => {
  console.log('Service Worker: Push notification received');
  
  const options = {
    body: event.data ? event.data.text() : 'Yeni bildirim',
    icon: '/assets/icons/icon-192x192.png',
    badge: '/assets/icons/badge-72x72.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'Görüntüle',
        icon: '/assets/icons/checkmark.png'
      },
      {
        action: 'close',
        title: 'Kapat',
        icon: '/assets/icons/xmark.png'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('CRM Pro', options)
  );
});

// Bildirim tıklama olayları
self.addEventListener('notificationclick', event => {
  console.log('Service Worker: Notification click received');
  
  event.notification.close();

  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});

// Offline veri senkronizasyonu
async function syncOfflineData() {
  try {
    // IndexedDB'den offline kaydedilen verileri al
    const offlineData = await getOfflineData();
    
    if (offlineData.length > 0) {
      // Her bir offline kaydı sunucuya gönder
      for (const data of offlineData) {
        try {
          const response = await fetch(data.url, {
            method: data.method,
            headers: data.headers,
            body: data.body
          });
          
          if (response.ok) {
            // Başarılı gönderim sonrası offline kaydı sil
            await removeOfflineData(data.id);
          }
        } catch (error) {
          console.log('Sync failed for:', data.url, error);
        }
      }
    }
  } catch (error) {
    console.log('Background sync failed:', error);
  }
}

// IndexedDB işlemleri (basit implementasyon)
async function getOfflineData() {
  // Bu fonksiyon IndexedDB'den offline verileri getirecek
  // Şimdilik boş array döndürüyoruz
  return [];
}

async function removeOfflineData(id) {
  // Bu fonksiyon IndexedDB'den belirtilen kaydı silecek
  console.log('Removing offline data:', id);
}