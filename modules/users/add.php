<?php
ob_start(); // Çıktı tamponlamasını başlat
require_once '../../includes/header.php';

// Sadece admin erişebilir
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Form verilerini al
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $role = $_POST['role'];

        // Validation
        $errors = [];
        
        // Kullanıcı adı kontrolü
        if (empty($username)) {
            $errors[] = 'Kullanıcı adı zorunludur.';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Kullanıcı adı en az 3 karakter olmalıdır.';
        }

        // Kullanıcı adı benzersiz mi?
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Bu kullanıcı adı zaten kullanılıyor.';
        }

        // Email kontrolü
        if (empty($email)) {
            $errors[] = 'E-posta adresi zorunludur.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçerli bir e-posta adresi giriniz.';
        }

        // Email benzersiz mi?
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Bu e-posta adresi zaten kullanılıyor.';
        }

        // Şifre kontrolü
        if (empty($password)) {
            $errors[] = 'Şifre zorunludur.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Şifre en az 6 karakter olmalıdır.';
        }

        // Rol kontrolü
        if (!in_array($role, ['admin', 'user'])) {
            $errors[] = 'Geçersiz rol.';
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Kullanıcıyı ekle
            $stmt = $db->prepare("
                INSERT INTO users (username, password, email, full_name, role)
                VALUES (:username, :password, :email, :full_name, :role)
            ");

            $stmt->execute([
                ':username' => $username,
                ':password' => $hashedPassword,
                ':email' => $email,
                ':full_name' => $full_name,
                ':role' => $role
            ]);

            // Yeni kullanıcı bildirimi (sadece adminlere)
            $adminStmt = $db->query("SELECT id FROM users WHERE role = 'admin'");
            $admins = $adminStmt->fetchAll();

            foreach ($admins as $admin) {
                $notificationManager->create(
                    $admin['id'],
                    'info',
                    'Yeni Kullanıcı Eklendi',
                    "Yeni kullanıcı kaydedildi: {$username}" . ($full_name ? " ({$full_name})" : '') . " - Rol: " . ($role === 'admin' ? 'Yönetici' : 'Kullanıcı'),
                    '/modules/users/list.php'
                );
            }

            // Tamponu temizle ve yönlendir
            ob_end_clean();
            header("Location: list.php?success=1");
            exit();
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

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Yeni Kullanıcı Ekle</h5>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Kullanıcı Adı *</label>
                        <input type="text" name="username" class="form-control" required
                               minlength="3" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        <div class="form-text">En az 3 karakter olmalıdır.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">E-posta *</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ad Soyad</label>
                        <input type="text" name="full_name" class="form-control"
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Şifre *</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                        <div class="form-text">En az 6 karakter olmalıdır.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rol *</label>
                        <select name="role" class="form-select" required>
                            <option value="user" <?php echo (isset($_POST['role']) && $_POST['role'] === 'user') ? 'selected' : ''; ?>>
                                Kullanıcı
                            </option>
                            <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>
                                Yönetici
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Kaydet
                </button>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri
                </a>
            </div>
        </form>
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
})();
</script>

<?php require_once '../../includes/footer.php'; ?>