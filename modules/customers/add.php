<?php
// modules/customers/add.php
require_once '../../includes/header.php';

$message = '';
$messageType = '';

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

        // Veritabanına kaydet
        $stmt = $db->prepare("
            INSERT INTO customers (company_name, contact_person, email, phone, address)
            VALUES (:company_name, :contact_person, :email, :phone, :address)
        ");

        $stmt->execute([
            ':company_name' => $company_name,
            ':contact_person' => $contact_person,
            ':email' => $email,
            ':phone' => $phone,
            ':address' => $address
        ]);

        // Yeni müşteri bildirimi
        $notificationManager->createSystemNotification(
            'success',
            'Yeni Müşteri Eklendi',
            "Yeni müşteri kaydedildi: {$company_name}" . ($contact_person ? " ({$contact_person})" : ''),
            '/modules/customers/list.php'
        );

        $message = 'Müşteri başarıyla eklendi.';
        $messageType = 'success';

        // Teklif oluşturmaya yönlendir seçeneği
        if (isset($_POST['create_quote']) && $_POST['create_quote'] == '1') {
            $customerId = $db->lastInsertId();
            header("Location: ../quotes/create.php?customer_id=" . $customerId);
            exit;
        }

    } catch (Exception $e) {
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Yeni Müşteri Ekle</h5>
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
                        <label class="form-label">Firma Adı *</label>
                        <input type="text" name="company_name" class="form-control" required>
                        <div class="invalid-feedback">
                            Firma adı zorunludur.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">İletişim Kişisi</label>
                        <input type="text" name="contact_person" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">E-posta</label>
                        <input type="email" name="email" class="form-control">
                        <div class="invalid-feedback">
                            Geçerli bir e-posta adresi giriniz.
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Telefon</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adres</label>
                        <textarea name="address" class="form-control" rows="4"></textarea>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" name="create_quote" value="1" class="form-check-input" id="createQuote">
                    <label class="form-check-label" for="createQuote">
                        Müşteri kaydından sonra hemen teklif oluştur
                    </label>
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