// Mobile Optimizations JavaScript
class MobileManager {
    constructor() {
        this.isMobile = window.innerWidth <= 768;
        this.sidebarOpen = false;
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.pullToRefreshThreshold = 100;
        this.isPulling = false;
        
        this.init();
    }
    
    init() {
        this.setupMobileMenu();
        this.setupSwipeGestures();
        this.setupPullToRefresh();
        this.setupFloatingActionButton();
        this.setupMobileTables();
        this.setupTouchOptimizations();
        this.handleOrientationChange();
    }
    
    // Mobil menü ayarları
    setupMobileMenu() {
        const menuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (menuToggle && sidebar) {
            // Overlay oluştur
            const overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
            
            // Menü toggle
            menuToggle.addEventListener('click', () => {
                this.toggleMobileMenu();
            });
            
            // Overlay tıklama
            overlay.addEventListener('click', () => {
                this.closeMobileMenu();
            });
            
            // ESC tuşu
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.sidebarOpen) {
                    this.closeMobileMenu();
                }
            });
        }
    }
    
    toggleMobileMenu() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (this.sidebarOpen) {
            this.closeMobileMenu();
        } else {
            sidebar.classList.add('show');
            overlay.classList.add('show');
            this.sidebarOpen = true;
            document.body.style.overflow = 'hidden';
        }
    }
    
    closeMobileMenu() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        this.sidebarOpen = false;
        document.body.style.overflow = '';
    }
    
    // Swipe gesture'ları
    setupSwipeGestures() {
        let startX, startY, distX, distY;
        
        document.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        }, { passive: true });
        
        document.addEventListener('touchmove', (e) => {
            if (!startX || !startY) return;
            
            distX = e.touches[0].clientX - startX;
            distY = e.touches[0].clientY - startY;
            
            // Sağdan sola swipe - menüyü kapat
            if (distX < -50 && Math.abs(distY) < 100 && this.sidebarOpen) {
                this.closeMobileMenu();
            }
            
            // Soldan sağa swipe - menüyü aç
            if (distX > 50 && Math.abs(distY) < 100 && !this.sidebarOpen && startX < 50) {
                this.toggleMobileMenu();
            }
        }, { passive: true });
        
        document.addEventListener('touchend', () => {
            startX = null;
            startY = null;
        }, { passive: true });
    }
    
    // Pull to refresh
    setupPullToRefresh() {
        let startY = 0;
        let currentY = 0;
        let pullDistance = 0;
        
        const container = document.querySelector('.main-content');
        if (!container) return;
        
        // Pull to refresh indicator oluştur
        const indicator = document.createElement('div');
        indicator.className = 'pull-to-refresh-indicator';
        indicator.innerHTML = '<i class="fas fa-sync-alt"></i>';
        container.appendChild(indicator);
        
        container.addEventListener('touchstart', (e) => {
            if (container.scrollTop === 0) {
                startY = e.touches[0].clientY;
            }
        }, { passive: true });
        
        container.addEventListener('touchmove', (e) => {
            if (startY === 0) return;
            
            currentY = e.touches[0].clientY;
            pullDistance = currentY - startY;
            
            if (pullDistance > 0 && container.scrollTop === 0) {
                e.preventDefault();
                
                const progress = Math.min(pullDistance / this.pullToRefreshThreshold, 1);
                indicator.style.transform = `translateY(${pullDistance * 0.5}px) rotate(${progress * 360}deg)`;
                
                if (pullDistance > this.pullToRefreshThreshold) {
                    container.classList.add('pulling');
                    this.isPulling = true;
                }
            }
        });
        
        container.addEventListener('touchend', () => {
            if (this.isPulling) {
                this.triggerRefresh();
            }
            
            // Reset
            indicator.style.transform = '';
            container.classList.remove('pulling');
            startY = 0;
            this.isPulling = false;
        }, { passive: true });
    }
    
    triggerRefresh() {
        // Sayfayı yenile
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }
    
    // Floating Action Button
    setupFloatingActionButton() {
        // Sadece belirli sayfalarda FAB göster
        const currentPage = window.location.pathname;
        const fabPages = ['/modules/customers/list.php', '/modules/quotes/list.php', '/modules/interactions/list.php'];
        
        if (!fabPages.some(page => currentPage.includes(page))) return;
        
        const fab = document.createElement('button');
        fab.className = 'fab';
        fab.innerHTML = '<i class="fas fa-plus"></i>';
        
        const fabMenu = document.createElement('div');
        fabMenu.className = 'fab-menu';
        
        // Menü öğeleri
        const menuItems = [
            { icon: 'fas fa-file-invoice', text: 'Yeni Teklif', url: '/modules/quotes/create.php' },
            { icon: 'fas fa-user-plus', text: 'Yeni Müşteri', url: '/modules/customers/add.php' },
            { icon: 'fas fa-comments', text: 'İletişim Ekle', url: '/modules/interactions/add.php' }
        ];
        
        menuItems.forEach(item => {
            const menuItem = document.createElement('a');
            menuItem.className = 'fab-menu-item';
            menuItem.href = item.url;
            menuItem.innerHTML = `<i class="${item.icon}"></i> ${item.text}`;
            fabMenu.appendChild(menuItem);
        });
        
        document.body.appendChild(fabMenu);
        document.body.appendChild(fab);
        
        // FAB tıklama olayı
        fab.addEventListener('click', () => {
            fabMenu.classList.toggle('show');
        });
        
        // Dışarı tıklama
        document.addEventListener('click', (e) => {
            if (!fab.contains(e.target) && !fabMenu.contains(e.target)) {
                fabMenu.classList.remove('show');
            }
        });
    }
    
    // Mobil tablolar
    setupMobileTables() {
        const tables = document.querySelectorAll('.table');
        
        tables.forEach(table => {
            this.convertTableToCards(table);
        });
    }
    
    convertTableToCards(table) {
        const tableContainer = table.closest('.table-responsive');
        if (!tableContainer) return;
        
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        
        // Mobil kartlar container'ı oluştur
        const mobileCards = document.createElement('div');
        mobileCards.className = 'mobile-cards';
        
        rows.forEach(row => {
            const cells = Array.from(row.querySelectorAll('td'));
            
            const card = document.createElement('div');
            card.className = 'mobile-card';
            
            const cardHeader = document.createElement('div');
            cardHeader.className = 'mobile-card-header';
            
            const cardTitle = document.createElement('h6');
            cardTitle.className = 'mobile-card-title';
            cardTitle.textContent = cells[0]?.textContent.trim() || 'Kayıt';
            
            const cardActions = document.createElement('div');
            cardActions.className = 'mobile-card-actions';
            
            // Son hücredeki butonları al
            const lastCell = cells[cells.length - 1];
            if (lastCell) {
                const buttons = lastCell.querySelectorAll('.btn');
                buttons.forEach(btn => {
                    const newBtn = btn.cloneNode(true);
                    newBtn.className = 'btn btn-sm btn-outline-primary';
                    cardActions.appendChild(newBtn);
                });
            }
            
            cardHeader.appendChild(cardTitle);
            cardHeader.appendChild(cardActions);
            
            const cardBody = document.createElement('div');
            cardBody.className = 'mobile-card-body';
            
            // Diğer hücreleri field olarak ekle
            cells.slice(1, -1).forEach((cell, index) => {
                const field = document.createElement('div');
                field.className = 'mobile-card-field';
                
                const label = document.createElement('div');
                label.className = 'mobile-card-label';
                label.textContent = headers[index + 1] || '';
                
                const value = document.createElement('div');
                value.className = 'mobile-card-value';
                value.innerHTML = cell.innerHTML;
                
                field.appendChild(label);
                field.appendChild(value);
                cardBody.appendChild(field);
            });
            
            card.appendChild(cardHeader);
            card.appendChild(cardBody);
            mobileCards.appendChild(card);
        });
        
        // Orijinal tabloyu gizle ve mobil kartları ekle
        table.classList.add('mobile-table');
        tableContainer.appendChild(mobileCards);
    }
    
    // Touch optimizasyonları
    setupTouchOptimizations() {
        // Haptic feedback simulation
        const buttons = document.querySelectorAll('.btn, .nav-link');
        
        buttons.forEach(button => {
            button.addEventListener('touchstart', () => {
                button.classList.add('haptic-feedback');
                setTimeout(() => {
                    button.classList.remove('haptic-feedback');
                }, 100);
            }, { passive: true });
        });
        
        // Prevent zoom on double tap
        let lastTouchEnd = 0;
        document.addEventListener('touchend', (e) => {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                e.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    }
    
    // Orientation change
    handleOrientationChange() {
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.isMobile = window.innerWidth <= 768;
                
                // Menüyü kapat
                if (!this.isMobile && this.sidebarOpen) {
                    this.closeMobileMenu();
                }
                
                // Viewport height'ı güncelle
                document.documentElement.style.setProperty('--vh', `${window.innerHeight * 0.01}px`);
            }, 100);
        });
        
        window.addEventListener('resize', () => {
            this.isMobile = window.innerWidth <= 768;
            document.documentElement.style.setProperty('--vh', `${window.innerHeight * 0.01}px`);
        });
    }
}

// Loading skeleton oluştur
function createLoadingSkeleton(container, type = 'list') {
    const skeleton = document.createElement('div');
    skeleton.className = 'loading-skeleton-container';
    
    if (type === 'list') {
        for (let i = 0; i < 5; i++) {
            const item = document.createElement('div');
            item.className = 'mobile-card';
            item.innerHTML = `
                <div class="skeleton-title loading-skeleton"></div>
                <div class="skeleton-text loading-skeleton" style="width: 80%;"></div>
                <div class="skeleton-text loading-skeleton" style="width: 60%;"></div>
            `;
            skeleton.appendChild(item);
        }
    } else if (type === 'form') {
        for (let i = 0; i < 3; i++) {
            const field = document.createElement('div');
            field.innerHTML = `
                <div class="skeleton-text loading-skeleton" style="width: 30%; height: 0.8rem; margin-bottom: 0.5rem;"></div>
                <div class="skeleton-text loading-skeleton" style="height: 2.5rem; margin-bottom: 1rem;"></div>
            `;
            skeleton.appendChild(field);
        }
    }
    
    container.appendChild(skeleton);
    return skeleton;
}

// Lazy loading için intersection observer
function setupLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Virtual scrolling için basit implementasyon
class VirtualScroller {
    constructor(container, items, itemHeight = 60) {
        this.container = container;
        this.items = items;
        this.itemHeight = itemHeight;
        this.visibleItems = Math.ceil(container.clientHeight / itemHeight) + 2;
        this.startIndex = 0;
        
        this.init();
    }
    
    init() {
        this.container.style.height = `${this.items.length * this.itemHeight}px`;
        this.container.style.position = 'relative';
        
        this.render();
        
        this.container.addEventListener('scroll', () => {
            this.startIndex = Math.floor(this.container.scrollTop / this.itemHeight);
            this.render();
        });
    }
    
    render() {
        this.container.innerHTML = '';
        
        const endIndex = Math.min(this.startIndex + this.visibleItems, this.items.length);
        
        for (let i = this.startIndex; i < endIndex; i++) {
            const item = this.items[i];
            const element = document.createElement('div');
            element.style.position = 'absolute';
            element.style.top = `${i * this.itemHeight}px`;
            element.style.height = `${this.itemHeight}px`;
            element.innerHTML = item;
            
            this.container.appendChild(element);
        }
    }
}

// Mobile Manager'ı başlat
document.addEventListener('DOMContentLoaded', () => {
    window.mobileManager = new MobileManager();
    setupLazyLoading();
});

// Global fonksiyonlar
window.showMobileToast = (message, type = 'info') => {
    const toast = document.createElement('div');
    toast.className = `mobile-toast ${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'primary'}-color);
        color: white;
        padding: 12px 20px;
        border-radius: 25px;
        z-index: 9999;
        font-size: 0.9rem;
        max-width: 90%;
        text-align: center;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
};