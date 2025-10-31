<?php
// modules/customers/edit.php
require_once '../../includes/header.php';

$message = '';
$messageType = '';

// ID kontrolü
if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}

$id = (int)$_GET['id'];

// Mevcut müşteri bilgilerini getir
try {
    $stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$id]);
    $customer = $stmt->fetch();

    if (!$customer) {
        header('Location: list.php');
        exit;
    }
} catch (Exception $e) {
    die("Müşteri bilgileri alınamadı: " . $e->getMessage());
}

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Form verilerini al
        $company_name = trim($_POST['company_name']);
        $contact_person = trim($_POST['contact_person']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);

        // Zorunlu alan kontrolü
        if (empty($company_name)) {
            throw new Exception('Firma adı zorunludur.');
        }

        // Email format kontrolü
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Geçersiz e-posta adresi.');
        }

        // Veritabanını güncelle
        $stmt = $db->prepare("
            UPDATE customers 
            SET company_name = :company_name,
                contact_person = :contact_person,
                email = :email,
                phone = :phone,
                address = :address
            WHERE id = :id
        ");

        $stmt->execute([
            ':company_name' => $company_name,
            ':contact_person' => $contact_person,
            ':email' => $email,
            ':phone' => $phone,
            ':address' => $address,
            ':id' => $id
        ]);

        $message = 'Müşteri bilgileri başarıyla güncellendi.';
        $messageType = 'success';

        // Güncel bilgileri tekrar getir
        $stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        $customer = $stmt->fetch();

    } catch (Exception $e) {
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Müşterinin teklif istatistiklerini getir
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_quotes,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_quotes,
        SUM(total_amount) as total_amount
    FROM quotes 
    WHERE customer_id = ?
");
$stmt->execute([$id]);
$stats = $stmt->fetch();
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Müşteri Düzenle</h5>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Müşteri İstatistikleri -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Toplam Teklif</h6>
                        <h3 class="card-text"><?php echo $stats['total_quotes']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Kabul Edilen Teklif</h6>
                        <h3 class="card-text"><?php echo $stats['accepted_quotes']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Toplam Tutar</h6>
                        <h3 class="card-text">
                            <?php echo number_format($stats['total_amount'], 2, ',', '.'); ?> TL
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Firma Adı *</label>
                        <input type="text" name="company_name" class="form-control" 
                               value="<?php echo htmlspecialchars($customer['company_name']); ?>" required>
                        <div class="invalid-feedback">
                            Firma adı zorunludur.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">İletişim Kişisi</label>
                        <input type="text" name="contact_person" class="form-control"
                               value="<?php echo htmlspecialchars($customer['contact_person']); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">E-posta</label>
                        <input type="email" name="email" class="form-control"
                               value="<?php echo htmlspecialchars($customer['email']); ?>">
                        <div class="invalid-feedback">
                            Geçerli bir e-posta adresi giriniz.
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Telefon</label>
                        <input type="tel" name="phone" class="form-control"
                               value="<?php echo htmlspecialchars($customer['phone']); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adres</label>
                        <textarea name="address" class="form-control" rows="4"><?php echo htmlspecialchars($customer['address']); ?></textarea>
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
                <a href="../quotes/create.php?customer_id=<?php echo $id; ?>" class="btn btn-success">
                    <i class="fas fa-file-invoice-dollar"></i> Yeni Teklif Oluştur
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