<?php
// modules/profile/index.php
require_once '../../includes/header.php';

// Kullanıcı bilgilerini al
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Form verilerini al
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        
        // Validation
        $errors = [];

        // Email kontrolü
        if (empty($email)) {
            $errors[] = 'E-posta adresi zorunludur.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçerli bir e-posta adresi giriniz.';
        }

        // Email benzersiz mi?
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Bu e-posta adresi zaten kullanılıyor.';
        }

        if (empty($errors)) {
            // Bilgileri güncelle
            $stmt = $db->prepare("
                UPDATE users 
                SET full_name = ?, email = ?
                WHERE id = ?
            ");
            
            $stmt->execute([$full_name, $email, $_SESSION['user_id']]);

            $message = 'Profil bilgileriniz güncellendi.';
            $messageType = 'success';

            // Session'daki kullanıcı adını güncelle
            $_SESSION['full_name'] = $full_name;

            // Güncel bilgileri al
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
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

// Son aktiviteleri getir
$stmt = $db->prepare("
    SELECT * FROM activity_logs 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$activities = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-md-4">
        <!-- Profil Bilgileri -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Profil Bilgileri</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <img src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($user['email']))); ?>?s=200" 
                         alt="<?php echo htmlspecialchars($user['full_name']); ?>"
                         class="rounded-circle img-thumbnail">
                    <h5 class="mt-3 mb-0"><?php echo htmlspecialchars($user['full_name']); ?></h5>
                    <small class="text-muted"><?php echo $user['role'] === 'admin' ? 'Yönetici' : 'Kullanıcı'; ?></small>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Kullanıcı Adı</label>
                    <div class="form-control-plaintext"><?php echo htmlspecialchars($user['username']); ?></div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Son Giriş</label>
                    <div class="form-control-plaintext">
                        <?php echo $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : '-'; ?>
                    </div>
                </div>

                <a href="password.php" class="btn btn-warning w-100">
                    <i class="fas fa-key"></i> Şifre Değiştir
                </a>
            </div>
        </div>

        <!-- Son Aktiviteler -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Son Aktiviteler</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($activities as $activity): ?>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1"><?php echo htmlspecialchars($activity['action']); ?></h6>
                            <small class="text-muted">
                                <?php echo htmlspecialchars($activity['description']); ?>
                            </small>
                        </div>
                        <small class="text-muted">
                            <?php echo date('d.m.Y H:i', strtotime($activity['created_at'])); ?>
                        </small>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($activities)): ?>
                <div class="list-group-item text-center text-muted">
                    Henüz aktivite bulunmuyor.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Profil Düzenleme -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Profil Düzenle</h5>
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
                        <label class="form-label">Ad Soyad</label>
                        <input type="text" name="full_name" class="form-control"
                               value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">E-posta *</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?php echo htmlspecialchars($user['email']); ?>">
                        <div class="form-text">
                            E-posta adresiniz profil fotoğrafı için Gravatar ile ilişkilendirilecektir.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Kaydet
                    </button>
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
})();
</script>

<?php require_once '../../includes/footer.php'; ?>