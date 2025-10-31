<?php
// modules/profile/password.php
require_once '../../includes/header.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validation
        $errors = [];

        // Mevcut şifreyi kontrol et
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!password_verify($current_password, $user['password'])) {
            $errors[] = 'Mevcut şifre hatalı.';
        }

        // Yeni şifre kontrolü
        if (empty($new_password)) {
            $errors[] = 'Yeni şifre zorunludur.';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'Yeni şifre en az 6 karakter olmalıdır.';
        }

        // Şifre eşleşme kontrolü
        if ($new_password !== $confirm_password) {
            $errors[] = 'Yeni şifreler eşleşmiyor.';
        }

        if (empty($errors)) {
            // Şifreyi güncelle
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $_SESSION['user_id']]);

            // Aktivite logu
            $stmt = $db->prepare("
                INSERT INTO activity_logs (user_id, action, description, ip_address)
                VALUES (?, 'password_change', 'Şifre değiştirildi', ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR']]);

            $message = 'Şifreniz başarıyla güncellendi.';
            $messageType = 'success';
            
            // Form verilerini temizle
            $_POST = array();
        } else {
            $message = implode('<br>', $errors);
            $messageType = 'danger';
        }
    } catch (Exception $e) {
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Şifre Değiştir</h5>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Mevcut Şifre *</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Yeni Şifre *</label>
                        <input type="password" name="new_password" class="form-control" 
                               required minlength="6">
                        <div class="form-text">En az 6 karakter olmalıdır.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Yeni Şifre (Tekrar) *</label>
                        <input type="password" name="confirm_password" class="form-control" 
                               required minlength="6">
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Şifreyi Güncelle
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Geri
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Form doğrulama
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

    // Şifre eşleşme kontrolü
    var password = document.querySelector('input[name="new_password"]');
    var confirm = document.querySelector('input[name="confirm_password"]');
    
    function validatePassword() {
        if (password.value !== confirm.value) {
            confirm.setCustomValidity('Şifreler eşleşmiyor.');
        } else {
            confirm.setCustomValidity('');
        }
    }

    password.addEventListener('change', validatePassword);
    confirm.addEventListener('keyup', validatePassword);
})();
</script>

<?php require_once '../../includes/footer.php'; ?>