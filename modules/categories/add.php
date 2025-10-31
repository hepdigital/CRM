<?php
// modules/categories/add.php
require_once '../../includes/header.php';

// Sadece admin erişebilir
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $message = 'Kategori adı zorunludur.';
        $messageType = 'danger';
    } else {
        try {
            // Aynı isimde kategori var mı kontrol et
            $stmt = $db->prepare("SELECT COUNT(*) FROM product_categories WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetchColumn() > 0) {
                $message = 'Bu kategori zaten mevcut.';
                $messageType = 'danger';
            } else {
                // Yeni kategoriyi ekle
                $stmt = $db->prepare("INSERT INTO product_categories (name) VALUES (?)");
                $stmt->execute([$name]);
                header("Location: list.php?success=1");
                exit;
            }
        } catch (Exception $e) {
            $message = 'Bir hata oluştu: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Yeni Kategori Ekle</h5>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label">Kategori Adı *</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                <div class="invalid-feedback">
                    Lütfen bir kategori adı girin.
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