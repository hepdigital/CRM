<?php
// includes/auth.php

// Oturum kontrolü
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

// Admin yetkisi kontrolü
function checkAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: /index.php');
        exit;
    }
}

// Kullanıcı girişi
function loginUser($username, $password) {
    global $db;

    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Giriş başarılı
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];

            // Son giriş zamanını güncelle
            $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Aktivite logu
            logActivity($user['id'], 'login', 'Kullanıcı girişi yapıldı');

            return true;
        }

        return false;

    } catch (Exception $e) {
        error_log('Login error: ' . $e->getMessage());
        return false;
    }
}

// Kullanıcı çıkışı
function logoutUser() {
    // Aktivite logu
    if (isset($_SESSION['user_id'])) {
        logActivity($_SESSION['user_id'], 'logout', 'Kullanıcı çıkışı yapıldı');
    }

    // Session'ı temizle
    session_destroy();
}

// Aktivite logu
function logActivity($userId, $action, $description) {
    global $db;

    try {
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, description, ip_address)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR']
        ]);

    } catch (Exception $e) {
        error_log('Activity log error: ' . $e->getMessage());
    }
}

// Şifre sıfırlama token'ı oluşturma
function generatePasswordResetToken($userId) {
    global $db;

    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    try {
        $stmt = $db->prepare("
            INSERT INTO password_resets (user_id, token, expiry)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([$userId, $token, $expiry]);
        return $token;

    } catch (Exception $e) {
        error_log('Password reset token error: ' . $e->getMessage());
        return false;
    }
}

// Şifre sıfırlama token'ı doğrulama
function validatePasswordResetToken($token) {
    global $db;

    try {
        $stmt = $db->prepare("
            SELECT user_id 
            FROM password_resets 
            WHERE token = ? AND expiry > NOW() AND used = 0
        ");

        $stmt->execute([$token]);
        return $stmt->fetchColumn();

    } catch (Exception $e) {
        error_log('Password reset validation error: ' . $e->getMessage());
        return false;
    }
}

// Şifre güncelleme
function updatePassword($userId, $newPassword) {
    global $db;

    try {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $userId]);

        // Aktivite logu
        logActivity($userId, 'password_change', 'Şifre güncellendi');

        return true;

    } catch (Exception $e) {
        error_log('Password update error: ' . $e->getMessage());
        return false;
    }
}

// Oturum süresini kontrol et ve güncelle
function checkSessionTimeout() {
    $timeout = 30 * 60; // 30 dakika

    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        logoutUser();
        header('Location: /login.php?timeout=1');
        exit;
    }

    $_SESSION['last_activity'] = time();
}

// IP bazlı giriş denemesi kontrolü
function checkLoginAttempts($username) {
    global $db;
    $maxAttempts = 5; // Maximum deneme sayısı
    $lockoutTime = 15 * 60; // 15 dakika
    $ip = $_SERVER['REMOTE_ADDR'];

    try {
        // Eski kayıtları temizle
        $stmt = $db->prepare("
            DELETE FROM login_attempts 
            WHERE attempt_time < DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$lockoutTime]);

        // Deneme sayısını kontrol et
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM login_attempts 
            WHERE ip_address = ? AND username = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$ip, $username, $lockoutTime]);
        $attempts = $stmt->fetchColumn();

        if ($attempts >= $maxAttempts) {
            return false;
        }

        // Yeni denemeyi kaydet
        $stmt = $db->prepare("
            INSERT INTO login_attempts (ip_address, username, attempt_time)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$ip, $username]);

        return true;

    } catch (Exception $e) {
        error_log('Login attempts check error: ' . $e->getMessage());
        return true; // Hata durumunda giriş denemesine izin ver
    }
}

// Kullanıcı yetki kontrolü
function hasPermission($permission) {
    // Admin tüm yetkilere sahip
    if ($_SESSION['role'] === 'admin') {
        return true;
    }

    // Yetki tablosundan kontrol et
    global $db;
    try {
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM user_permissions 
            WHERE user_id = ? AND permission = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $permission]);
        return $stmt->fetchColumn() > 0;

    } catch (Exception $e) {
        error_log('Permission check error: ' . $e->getMessage());
        return false;
    }
}

// İki faktörlü doğrulama kodu oluşturma
function generate2FACode($userId) {
    global $db;
    
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    try {
        $stmt = $db->prepare("
            INSERT INTO two_factor_codes (user_id, code, expiry)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $code, $expiry]);
        
        return $code;

    } catch (Exception $e) {
        error_log('2FA code generation error: ' . $e->getMessage());
        return false;
    }
}

// İki faktörlü doğrulama kodu kontrolü
function verify2FACode($userId, $code) {
    global $db;

    try {
        $stmt = $db->prepare("
            SELECT id 
            FROM two_factor_codes 
            WHERE user_id = ? AND code = ? AND expiry > NOW() AND used = 0
        ");
        $stmt->execute([$userId, $code]);
        
        if ($stmt->fetchColumn()) {
            // Kodu kullanıldı olarak işaretle
            $stmt = $db->prepare("
                UPDATE two_factor_codes 
                SET used = 1 
                WHERE user_id = ? AND code = ?
            ");
            $stmt->execute([$userId, $code]);
            
            return true;
        }

        return false;

    } catch (Exception $e) {
        error_log('2FA verification error: ' . $e->getMessage());
        return false;
    }
}

// Kullanıcı oturum bilgilerini güncelle
function refreshUserSession($userId) {
    global $db;

    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            return true;
        }

        return false;

    } catch (Exception $e) {
        error_log('Session refresh error: ' . $e->getMessage());
        return false;
    }
}