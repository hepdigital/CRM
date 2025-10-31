<?php
// modules/users/edit.php
require_once '../../includes/header.php';

// Sadece admin erişebilir
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

// ID kontrolü
if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}

$id = (int)$_GET['id'];

// Kullanıcı bilgilerini getir
try {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: list.php');
        exit;
    }
} catch (Exception $e) {
    die("Kullanıcı bilgileri alınamadı: " . $e->getMessage());
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Form verilerini al
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $role = $_POST['role'];
        $password = trim($_POST['password']); // Opsiyonel

        // Validation
        $errors = [];
        
        // Kullanıcı adı kontrolü
        if (empty($username)) {
            $errors[] = 'Kullanıcı adı zorunludur.';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Kullanıcı adı en az 3 karakter olmalıdır.';
        }

        // Kullanıcı adı benzersiz mi? (kendisi hariç)
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Bu kullanıcı adı zaten kullanılıyor.';
        }

        // Email kontrolü
        if (empty($email)) {
            $errors[] = 'E-posta adresi zorunludur.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçerli bir e-posta adresi giriniz.';
        }

        // Email benzersiz mi? (kendisi hariç)
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Bu e-posta adresi zaten kullanılıyor.';
        }

        // Rol kontrolü
        if (!in_array($role, ['admin', 'user'])) {
            $errors[] = 'Geçersiz rol.';
        }

        // Şifre kontrolü (opsiyonel)
        if (!empty($password) && strlen($password) < 6) {
            $errors[] = 'Şifre en az 6 karakter olmalıdır.';
        }

        if (empty($errors)) {
            // Güncelleme sorgusunu hazırla
            $sql = "UPDATE users SET 
                    username = :username,
                    email = :email,
                    full_name = :full_name,
                    role = :role";
            
            $params = [
                ':username' => $username,
                ':email' => $email,
                ':full_name' => $full_name,
                ':role' => $role,
                ':id' => $id
            ];

            // Şifre değiştirilecekse ekle
            if (!empty($password)) {
                $sql .= ", password = :password";
                $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = :id";
            
            // Güncelleme yap
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            $message = 'Kullanıcı bilgileri başarıyla güncellendi.';
            $messageType = 'success';

            // Güncel bilgileri tekrar çek
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();

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
        <h5 class="mb-0">Kullanıcı Düzenle</h5>
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
                               minlength="3" value="<?php echo htmlspecialchars($user['username']); ?>">
                        <div class="form-text">En az 3 karakter olmalıdır.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">E-posta *</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ad Soyad</label>
                        <input type="text" name="full_name" class="form-control"
                               value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Yeni Şifre</label>
                        <input type="password" name="password" class="form-control" minlength="6">
                        <div class="form-text">Boş bırakırsanız şifre değişmeyecektir.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rol *</label>
                        <select name="role" class="form-select" required 
                                <?php echo $user['id'] === $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>
                                Kullanıcı
                            </option>
                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>
                                Yönetici
                            </option>
                        </select>
                        <?php if ($user['id'] === $_SESSION['user_id']): ?>
                        <div class="form-text">Kendi rolünüzü değiştiremezsiniz.</div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Son Giriş</label>
                        <input type="text" class="form-control" readonly
                               value="<?php echo $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : '-'; ?>">
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