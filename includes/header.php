<?php
// includes/header.php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/notification.php';
require_once __DIR__ . '/company_settings.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: /login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($companySettings->getCompanyName()); ?> - CRM</title>
    
    <!-- PWA Meta Tags -->
    <meta name="description" content="Basit ve etkili müşteri ilişkileri yönetim sistemi">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="CRM Pro">
    <meta name="msapplication-TileColor" content="#6366f1">
    <meta name="msapplication-config" content="/browserconfig.xml">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/icons/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/apple-touch-icon.png">
    <link rel="mask-icon" href="/assets/icons/safari-pinned-tab.svg" color="#6366f1">
    
    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/modern-theme.css" rel="stylesheet">
    <link href="/assets/css/mobile.css" rel="stylesheet">
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/assets/js/pwa.js" defer></script>
</head>
<body>
    <?php if(isset($_SESSION['user_id'])): ?>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <?php 
                    $logoUrl = $companySettings->get('logo_url');
                    if ($logoUrl): 
                    ?>
                        <img src="<?php echo htmlspecialchars($logoUrl); ?>" 
                             alt="<?php echo htmlspecialchars($companySettings->getCompanyName()); ?>" 
                             style="max-height: 40px; max-width: 40px; object-fit: contain;">
                    <?php else: ?>
                        <i class="fas fa-chart-line"></i>
                    <?php endif; ?>
                </div>
                <div class="sidebar-brand"><?php echo htmlspecialchars($companySettings->getCompanyName()); ?></div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Ana Menü</div>
                    <div class="nav-item">
                        <a href="/" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">İşlemler</div>
                    <div class="nav-item">
                        <a href="/modules/products/list.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'products') !== false ? 'active' : ''; ?>">
                            <i class="fas fa-box"></i>
                            <span>Ürünler</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/modules/customers/list.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'customers') !== false ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i>
                            <span>Müşteriler</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/modules/quotes/list.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'quotes') !== false ? 'active' : ''; ?>">
                            <i class="fas fa-file-invoice"></i>
                            <span>Teklifler</span>
                        </a>
                    </div>

                    <?php if($_SESSION['role'] === 'admin'): ?>
                    <div class="nav-item">
                        <a href="/modules/users/list.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : ''; ?>">
                            <i class="fas fa-user-cog"></i>
                            <span>Kullanıcılar</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/modules/settings/index.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'settings') !== false ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i>
                            <span>Ayarlar</span>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Hızlı Erişim</div>
                    <div class="nav-item">
                        <a href="/modules/quotes/create.php" class="nav-link">
                            <i class="fas fa-plus-circle"></i>
                            <span>Yeni Teklif</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/modules/customers/add.php" class="nav-link">
                            <i class="fas fa-user-plus"></i>
                            <span>Yeni Müşteri</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/modules/products/add.php" class="nav-link">
                            <i class="fas fa-box-open"></i>
                            <span>Yeni Ürün</span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Theme Toggle -->
            <div class="sidebar-footer">
                <button class="sidebar-theme-toggle" id="sidebarThemeToggle" title="Tema Değiştir">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <button class="mobile-menu-toggle d-md-none" id="mobileMenuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Dashboard</h1>
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Ara...">
                    </div>
                    
                    <!-- Bildirimler -->
                    <div class="notification-menu">
                        <div class="notification-icon" id="notificationIcon">
                            <i class="fas fa-bell"></i>
                            <?php 
                            $unreadCount = $notificationManager->getUnreadCount($_SESSION['user_id']);
                            if ($unreadCount > 0): 
                            ?>
                            <span class="notification-badge"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <h4>Bildirimler</h4>
                                <?php if ($unreadCount > 0): ?>
                                <button class="mark-all-read" onclick="markAllAsRead()">Tümünü Okundu İşaretle</button>
                                <?php endif; ?>
                            </div>
                            <div class="notification-list">
                                <?php 
                                $notifications = $notificationManager->getUserNotifications($_SESSION['user_id'], 5);
                                if (empty($notifications)): 
                                ?>
                                <div class="no-notifications">
                                    <i class="fas fa-bell-slash"></i>
                                    <p>Henüz bildirim yok</p>
                                </div>
                                <?php else: ?>
                                    <?php foreach ($notifications as $notification): ?>
                                    <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>" 
                                         onclick="markAsRead(<?php echo $notification['id']; ?>)">
                                        <div class="notification-icon-small">
                                            <?php
                                            $iconClass = match($notification['type']) {
                                                'success' => 'fas fa-check-circle',
                                                'warning' => 'fas fa-exclamation-triangle',
                                                'error' => 'fas fa-times-circle',
                                                'info' => 'fas fa-info-circle',
                                                default => 'fas fa-bell'
                                            };
                                            ?>
                                            <i class="<?php echo $iconClass; ?>"></i>
                                        </div>
                                        <div class="notification-content">
                                            <h5><?php echo htmlspecialchars($notification['title']); ?></h5>
                                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                            <small><?php echo date('d.m.Y H:i', strtotime($notification['created_at'])); ?></small>
                                        </div>
                                        <?php if (!$notification['is_read']): ?>
                                        <div class="unread-indicator"></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="notification-footer">
                                <a href="/modules/notifications/list.php">Tüm Bildirimleri Görüntüle</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kullanıcı Menüsü -->
                    <div class="user-menu">
                        <div class="user-avatar" id="userAvatar">
                            <?php echo strtoupper(substr($_SESSION['full_name'] ?: $_SESSION['username'], 0, 1)); ?>
                        </div>
                        
                        <div class="user-dropdown" id="userDropdown">
                            <div class="user-info">
                                <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?: $_SESSION['username']); ?></div>
                                <div class="user-role"><?php echo ucfirst($_SESSION['role']); ?></div>
                            </div>
                            <div class="user-menu-items">
                                <a href="/modules/profile/index.php" class="user-menu-item">
                                    <i class="fas fa-user"></i>
                                    <span>Profil</span>
                                </a>
                                <a href="/modules/profile/password.php" class="user-menu-item">
                                    <i class="fas fa-key"></i>
                                    <span>Şifre Değiştir</span>
                                </a>
                                <div class="user-menu-divider"></div>
                                <a href="/logout.php" class="user-menu-item logout">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Çıkış Yap</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <div class="content">
    <?php else: ?>
    <div class="container my-4">
    <?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Notification dropdown toggle
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (notificationIcon && notificationDropdown) {
        notificationIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
            
            // Close user dropdown if open
            const userDropdown = document.getElementById('userDropdown');
            if (userDropdown) {
                userDropdown.classList.remove('show');
            }
        });
    }
    
    // User dropdown toggle
    const userAvatar = document.getElementById('userAvatar');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userAvatar && userDropdown) {
        userAvatar.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
            
            // Close notification dropdown if open
            if (notificationDropdown) {
                notificationDropdown.classList.remove('show');
            }
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (notificationDropdown && !notificationIcon.contains(e.target) && !notificationDropdown.contains(e.target)) {
            notificationDropdown.classList.remove('show');
        }
        
        if (userDropdown && !userAvatar.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
    });
    
    // Notification functions
    window.markAsRead = function(notificationId) {
        fetch('/api/notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'mark_as_read',
                notification_id: notificationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove unread indicator
                const notificationItem = document.querySelector(`[onclick="markAsRead(${notificationId})"]`);
                if (notificationItem) {
                    notificationItem.classList.remove('unread');
                    notificationItem.classList.add('read');
                    const unreadIndicator = notificationItem.querySelector('.unread-indicator');
                    if (unreadIndicator) {
                        unreadIndicator.remove();
                    }
                }
            }
        })
        .catch(error => console.error('Error:', error));
    };
    
    window.markAllAsRead = function() {
        fetch('/api/notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'mark_all_read'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove all unread indicators
                const unreadItems = document.querySelectorAll('.notification-item.unread');
                unreadItems.forEach(item => {
                    item.classList.remove('unread');
                    item.classList.add('read');
                    const unreadIndicator = item.querySelector('.unread-indicator');
                    if (unreadIndicator) {
                        unreadIndicator.remove();
                    }
                });
                
                // Hide mark all button
                const markAllButton = document.querySelector('.mark-all-read');
                if (markAllButton) {
                    markAllButton.style.display = 'none';
                }
                
                // Update notification badge
                const notificationBadge = document.querySelector('.notification-badge');
                if (notificationBadge) {
                    notificationBadge.style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Error:', error));
    };
    
    // Theme Toggle Functionality
    const themeToggle = document.getElementById('sidebarThemeToggle');
    
    if (themeToggle) {
        const themeIcon = themeToggle.querySelector('i');
        
        // Load saved theme or default to light
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeUI(savedTheme);
        
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            // Update theme
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update UI
            updateThemeUI(newTheme);
        });
        
        function updateThemeUI(theme) {
            if (theme === 'dark') {
                themeIcon.className = 'fas fa-sun';
            } else {
                themeIcon.className = 'fas fa-moon';
            }
        }
    }
});
</script>