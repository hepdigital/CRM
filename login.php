<?php
session_start();
require_once 'config/database.php';
require_once 'includes/company_settings.php';

// Zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Lütfen tüm alanları doldurun.';
    } else {
        try {
            // Kullanıcıyı veritabanında ara
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Kullanıcı varsa ve şifre doğruysa
            if ($user && password_verify($password, $user['password'])) {
                // Giriş başarılı
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];

                // Son giriş zamanını güncelle
                $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);

                header('Location: index.php');
                exit;
            } else {
                // Kullanıcı yoksa veya şifre yanlışsa
                $error = 'Geçersiz kullanıcı adı veya şifre.';
            }
        } catch (Exception $e) {
            $error = 'Bir hata oluştu: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - <?php echo htmlspecialchars($companySettings->getCompanyName()); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/modern-theme.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--info-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 2rem;
        }
        
        .login-card {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--info-color));
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-logo {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }
        
        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }
        
        .login-subtitle {
            opacity: 0.9;
            margin: 0.5rem 0 0;
            font-size: 0.9rem;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            height: 48px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 0 1rem;
            font-size: 0.9rem;
            transition: all var(--transition-fast);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .btn-login {
            height: 48px;
            background: linear-gradient(135deg, var(--primary-color), var(--info-color));
            border: none;
            border-radius: var(--radius-md);
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all var(--transition-fast);
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-login:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left var(--transition-normal);
        }
        
        .btn-login:hover:before {
            left: 100%;
        }
        
        .alert {
            border-radius: var(--radius-md);
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }
        
        .login-footer {
            text-align: center;
            padding: 1.5rem 2rem;
            background: var(--bg-secondary);
            color: var(--text-muted);
            font-size: 0.8rem;
        }
        
        .theme-toggle-login {
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all var(--transition-fast);
            backdrop-filter: blur(10px);
        }
        
        .theme-toggle-login:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 1rem;
            }
            
            .login-header,
            .login-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <button class="theme-toggle-login" onclick="toggleTheme()" title="Tema Değiştir">
        <i class="fas fa-moon"></i>
    </button>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <?php 
                    $logoUrl = $companySettings->get('logo_url');
                    if ($logoUrl): 
                    ?>
                        <img src="<?php echo htmlspecialchars($logoUrl); ?>" 
                             alt="<?php echo htmlspecialchars($companySettings->getCompanyName()); ?>" 
                             style="max-height: 50px; max-width: 50px; object-fit: contain; border-radius: 8px;">
                    <?php else: ?>
                        <i class="fas fa-chart-line"></i>
                    <?php endif; ?>
                </div>
                <h1 class="login-title"><?php echo htmlspecialchars($companySettings->getCompanyName()); ?></h1>
                <p class="login-subtitle">Sisteminize hoş geldiniz</p>
            </div>
            
            <div class="login-body">
                <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user me-2"></i>Kullanıcı Adı
                        </label>
                        <input type="text" name="username" class="form-control" required
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               placeholder="Kullanıcı adınızı girin">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock me-2"></i>Şifre
                        </label>
                        <input type="password" name="password" class="form-control" required
                               placeholder="Şifrenizi girin">
                    </div>

                    <button type="submit" class="btn btn-login w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Giriş Yap
                    </button>
                </form>
            </div>
            
            <div class="login-footer">
                <i class="fas fa-copyright me-1"></i>
                <?php echo date('Y'); ?> <?php echo htmlspecialchars($companySettings->getCompanyName()); ?>. Tüm hakları saklıdır.
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/theme.js"></script>
    <script>
    // Theme toggle for login page
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Update icon
        const icon = document.querySelector('.theme-toggle-login i');
        icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
    
    // Initialize theme
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        const icon = document.querySelector('.theme-toggle-login i');
        icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    });
    
    // Form validation
    (function() {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
    </script>
</body>
</html>