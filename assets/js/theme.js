// Modern Theme Management
class ThemeManager {
    constructor() {
        this.theme = localStorage.getItem('theme') || 'light';
        this.init();
    }

    init() {
        console.log('ThemeManager initialized with theme:', this.theme); // Debug
        this.applyTheme();
        // createThemeToggle'ı DOMContentLoaded sonrası çağır
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.createThemeToggle();
                this.bindEvents();
            });
        } else {
            this.createThemeToggle();
            this.bindEvents();
        }
    }

    applyTheme() {
        console.log('Applying theme:', this.theme); // Debug
        document.documentElement.setAttribute('data-theme', this.theme);
        localStorage.setItem('theme', this.theme);
    }

    createThemeToggle() {
        // Sidebar theme toggle'ı güncelle
        const sidebarToggle = document.getElementById('sidebarThemeToggle');
        console.log('Sidebar toggle element:', sidebarToggle); // Debug
        if (sidebarToggle) {
            const icon = sidebarToggle.querySelector('i');
            const text = sidebarToggle.querySelector('span');

            if (this.theme === 'dark') {
                icon.className = 'fas fa-sun';
                text.textContent = 'Açık Tema';
                sidebarToggle.title = 'Gündüz Moduna Geç';
            } else {
                icon.className = 'fas fa-moon';
                text.textContent = 'Koyu Tema';
                sidebarToggle.title = 'Gece Moduna Geç';
            }
        }
    }

    bindEvents() {
        const sidebarToggle = document.getElementById('sidebarThemeToggle');
        console.log('Binding events to sidebar toggle:', sidebarToggle); // Debug
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                console.log('Sidebar toggle clicked!'); // Debug
                this.toggleTheme();
            });
        }

        // Listen for system theme changes
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addListener((e) => {
                if (!localStorage.getItem('theme')) {
                    this.theme = e.matches ? 'dark' : 'light';
                    this.applyTheme();
                    this.updateToggleIcon();
                }
            });
        }
    }

    toggleTheme() {
        console.log('Toggling theme from', this.theme); // Debug
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        console.log('New theme:', this.theme); // Debug
        this.applyTheme();
        this.updateToggleIcon();
        this.showThemeNotification();
    }

    updateToggleIcon() {
        const sidebarToggle = document.getElementById('sidebarThemeToggle');
        if (sidebarToggle) {
            const icon = sidebarToggle.querySelector('i');
            const text = sidebarToggle.querySelector('span');

            if (this.theme === 'dark') {
                icon.className = 'fas fa-sun';
                text.textContent = 'Açık Tema';
                sidebarToggle.title = 'Gündüz Moduna Geç';
            } else {
                icon.className = 'fas fa-moon';
                text.textContent = 'Koyu Tema';
                sidebarToggle.title = 'Gece Moduna Geç';
            }
        }
    }

    showThemeNotification() {
        // Create notification
        const notification = document.createElement('div');
        notification.className = 'theme-notification';
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.theme === 'dark' ? 'moon' : 'sun'}"></i>
                <span>${this.theme === 'dark' ? 'Gece Modu' : 'Gündüz Modu'} aktif</span>
            </div>
        `;
        
        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1rem 1.5rem;
            box-shadow: var(--shadow-lg);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-primary);
            font-weight: 500;
            animation: slideInRight 0.3s ease-out;
        `;

        document.body.appendChild(notification);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}

// Sidebar Management
class SidebarManager {
    constructor() {
        this.isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        this.init();
    }

    init() {
        this.createSidebarToggle();
        this.bindEvents();
        this.applySidebarState();
    }

    createSidebarToggle() {
        const headerLeft = document.querySelector('.header-left');
        if (!headerLeft) return;

        const sidebarToggle = document.createElement('button');
        sidebarToggle.className = 'sidebar-toggle';
        sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
        sidebarToggle.title = 'Menüyü Aç/Kapat';
        
        headerLeft.insertBefore(sidebarToggle, headerLeft.firstChild);
    }

    bindEvents() {
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => this.toggleSidebar());
        }

        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 1024) {
                const sidebar = document.querySelector('.sidebar');
                const sidebarToggle = document.querySelector('.sidebar-toggle');
                
                if (sidebar && sidebar.classList.contains('show') && 
                    !sidebar.contains(e.target) && 
                    !sidebarToggle.contains(e.target)) {
                    this.collapseSidebar();
                }
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024) {
                this.expandSidebar();
            } else {
                this.collapseSidebar();
            }
        });
    }

    toggleSidebar() {
        if (window.innerWidth <= 1024) {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('show');
            }
        } else {
            this.isCollapsed = !this.isCollapsed;
            this.applySidebarState();
            localStorage.setItem('sidebar-collapsed', this.isCollapsed);
        }
    }

    applySidebarState() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        
        if (sidebar) {
            if (this.isCollapsed) {
                sidebar.classList.add('collapsed');
            } else {
                sidebar.classList.remove('collapsed');
            }
        }

        if (mainContent) {
            if (this.isCollapsed) {
                mainContent.classList.add('expanded');
            } else {
                mainContent.classList.remove('expanded');
            }
        }
    }

    expandSidebar() {
        this.isCollapsed = false;
        this.applySidebarState();
        localStorage.setItem('sidebar-collapsed', false);
    }

    collapseSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.remove('show');
        }
    }
}

// Notification System
class NotificationManager {
    constructor() {
        this.notifications = [];
        this.unreadCount = 0;
        this.init();
    }

    init() {
        this.createNotificationContainer();
        this.createNotificationDropdown();
        this.loadNotifications();
        this.startPolling();
    }

    createNotificationContainer() {
        const container = document.createElement('div');
        container.className = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        `;
        document.body.appendChild(container);
    }

    createNotificationDropdown() {
        // Header'a bildirim dropdown'u ekle
        const headerRight = document.querySelector('.header-right');
        if (!headerRight) return;

        const notificationBtn = document.createElement('div');
        notificationBtn.className = 'notification-dropdown';
        notificationBtn.style.cssText = `
            position: relative;
            cursor: pointer;
        `;
        
        notificationBtn.innerHTML = `
            <button class="theme-toggle" style="position: relative;">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" style="
                    position: absolute;
                    top: -5px;
                    right: -5px;
                    background: var(--danger-color);
                    color: white;
                    border-radius: 50%;
                    width: 18px;
                    height: 18px;
                    font-size: 0.7rem;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                ">0</span>
            </button>
            <div class="notification-panel" style="
                position: absolute;
                top: 100%;
                right: 0;
                width: 350px;
                background: var(--bg-primary);
                border: 1px solid var(--border-color);
                border-radius: var(--radius-lg);
                box-shadow: var(--shadow-xl);
                z-index: 1000;
                display: none;
                max-height: 400px;
                overflow-y: auto;
            ">
                <div class="notification-header" style="
                    padding: 1rem;
                    border-bottom: 1px solid var(--border-light);
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                ">
                    <h6 style="margin: 0; font-weight: 600;">Bildirimler</h6>
                    <button class="btn btn-sm btn-secondary" onclick="notificationManager.markAllAsRead()">
                        Tümünü Okundu İşaretle
                    </button>
                </div>
                <div class="notification-list" style="padding: 0.5rem;">
                    <div class="notification-loading" style="
                        text-align: center;
                        padding: 2rem;
                        color: var(--text-muted);
                    ">
                        <i class="fas fa-spinner fa-spin"></i> Yükleniyor...
                    </div>
                </div>
                <div style="padding: 0.75rem; border-top: 1px solid var(--border-light); text-align: center;">
                    <a href="/modules/notifications/list.php" class="btn btn-sm btn-secondary">Tüm bildirimler</a>
                </div>
            </div>
        `;

        // Click handler
        notificationBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const panel = notificationBtn.querySelector('.notification-panel');
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
            this.loadNotifications();
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!notificationBtn.contains(e.target)) {
                notificationBtn.querySelector('.notification-panel').style.display = 'none';
            }
        });

        const userMenu = headerRight.querySelector('.user-menu');
        if (userMenu) {
            headerRight.insertBefore(notificationBtn, userMenu);
        } else {
            headerRight.insertBefore(notificationBtn, headerRight.firstChild);
        }
    }

    async loadNotifications() {
        try {
            const response = await fetch('/api/notifications.php?limit=20');
            const data = await response.json();
            
            if (data.success) {
                this.unreadCount = data.unread_count;
                this.updateBadge();
                this.renderNotifications(data.notifications);
            }
        } catch (error) {
            console.error('Failed to load notifications:', error);
        }
    }

    renderNotifications(notifications) {
        const container = document.querySelector('.notification-list');
        if (!container) return;

        if (notifications.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                    <i class="fas fa-bell-slash" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                    Henüz bildirim bulunmuyor
                </div>
            `;
            return;
        }

        container.innerHTML = notifications.map(notification => `
            <div class="notification-item" style="
                padding: 1rem;
                border-bottom: 1px solid var(--border-light);
                cursor: pointer;
                transition: background-color var(--transition-fast);
                ${!notification.is_read ? 'background: var(--bg-secondary);' : ''}
            " onclick="notificationManager.openNotification(${notification.id}, '${notification.action_url || ''}')">
                <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                    <div style="
                        width: 8px;
                        height: 8px;
                        border-radius: 50%;
                        background: var(--${notification.type === 'success' ? 'success' : notification.type === 'error' ? 'danger' : notification.type === 'warning' ? 'warning' : 'info'}-color);
                        margin-top: 0.5rem;
                        flex-shrink: 0;
                        ${notification.is_read ? 'display: none;' : ''}
                    "></div>
                    <div style="flex: 1;">
                        <div style="
                            font-weight: 600;
                            color: var(--text-primary);
                            margin-bottom: 0.25rem;
                        ">${notification.title}</div>
                        <div style="
                            color: var(--text-secondary);
                            font-size: 0.9rem;
                            margin-bottom: 0.5rem;
                        ">${notification.message}</div>
                        <div style="
                            color: var(--text-muted);
                            font-size: 0.8rem;
                        ">${this.formatDate(notification.created_at)}</div>
                    </div>
                    <button onclick="event.stopPropagation(); notificationManager.deleteNotification(${notification.id})" 
                            style="
                                background: none;
                                border: none;
                                color: var(--text-muted);
                                cursor: pointer;
                                padding: 0.25rem;
                                border-radius: var(--radius-sm);
                            " title="Sil">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }

    updateBadge() {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            badge.textContent = this.unreadCount;
            badge.style.display = this.unreadCount > 0 ? 'flex' : 'none';
        }
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return 'Az önce';
        if (diff < 3600000) return Math.floor(diff / 60000) + ' dk önce';
        if (diff < 86400000) return Math.floor(diff / 3600000) + ' sa önce';
        return date.toLocaleDateString('tr-TR');
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch('/api/notifications.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'mark_read',
                    notification_id: notificationId
                })
            });
            
            if (response.ok) {
                this.loadNotifications();
            }
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/api/notifications.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'mark_all_read' })
            });
            
            if (response.ok) {
                this.loadNotifications();
            }
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
        }
    }

    async deleteNotification(notificationId) {
        if (!confirm('Bu bildirimi silmek istediğinizden emin misiniz?')) return;
        
        try {
            const response = await fetch('/api/notifications.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'delete',
                    notification_id: notificationId
                })
            });
            
            if (response.ok) {
                this.loadNotifications();
            }
        } catch (error) {
            console.error('Failed to delete notification:', error);
        }
    }

    openNotification(notificationId, actionUrl) {
        this.markAsRead(notificationId);
        if (actionUrl) {
            window.location.href = actionUrl;
        }
    }

    startPolling() {
        // Her 30 saniyede bir bildirimleri kontrol et
        setInterval(() => {
            this.loadNotifications();
        }, 30000);
    }

    // Toast bildirimleri için eski sistem
    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const icon = this.getIcon(type);
        notification.innerHTML = `
            <div class="notification-content">
                <i class="${icon}"></i>
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;

        // Add styles
        notification.style.cssText = `
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1rem 1.5rem;
            box-shadow: var(--shadow-lg);
            color: var(--text-primary);
            font-weight: 500;
            animation: slideInRight 0.3s ease-out;
            max-width: 400px;
            position: relative;
        `;

        // Add type-specific styling
        if (type === 'success') {
            notification.style.borderLeft = '4px solid var(--success-color)';
        } else if (type === 'error') {
            notification.style.borderLeft = '4px solid var(--danger-color)';
        } else if (type === 'warning') {
            notification.style.borderLeft = '4px solid var(--warning-color)';
        } else {
            notification.style.borderLeft = '4px solid var(--info-color)';
        }

        const container = document.querySelector('.notification-container');
        container.appendChild(notification);

        // Close button functionality
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => this.remove(notification));

        // Auto remove
        if (duration > 0) {
            setTimeout(() => this.remove(notification), duration);
        }

        this.notifications.push(notification);
        return notification;
    }

    remove(notification) {
        if (notification && notification.parentNode) {
            notification.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }

    getIcon(type) {
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme manager
    window.themeManager = new ThemeManager();
    
    // Initialize sidebar manager
    window.sidebarManager = new SidebarManager();
    
    // Initialize notification manager
    window.notificationManager = new NotificationManager();
    
    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .notification-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .notification-close {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--text-muted);
            cursor: pointer;
            margin-left: auto;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .notification-close:hover {
            color: var(--text-primary);
        }
    `;
    document.head.appendChild(style);

    // Skeleton göster/gizle: sayfa hazır olana kadar iskeletleri göster
    const swapSkeletonWithContent = () => {
        document.querySelectorAll('.show-on-loading').forEach(el => {
            el.style.display = 'none';
        });
        document.querySelectorAll('.hidden-until-ready').forEach(el => {
            el.classList.remove('hidden-until-ready');
            el.style.removeProperty('display');
        });
    };

    const skeletonHandlers = () => {
        // DOM hazır olur olmaz gerçek içeriği göster, skeletonları gizle
        swapSkeletonWithContent();

        // Güvenlik için: tüm kaynaklar yüklendiğinde de tekrar dene
        window.addEventListener('load', swapSkeletonWithContent);

        // Ek güvenlik: 2 saniye sonra tekrar dene (olası yarış durumları için)
        setTimeout(swapSkeletonWithContent, 2000);
    };
    skeletonHandlers();
});

// Export for global access
window.ThemeManager = ThemeManager;
window.SidebarManager = SidebarManager;
window.NotificationManager = NotificationManager;
