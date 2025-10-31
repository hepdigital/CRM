<?php
// modules/interactions/edit.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

checkAuth();

$interaction_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];
$success = '';

if (!$interaction_id) {
    header('Location: list.php');
    exit;
}

// İletişim kaydını getir
$stmt = $db->prepare("
    SELECT ci.*, c.company_name 
    FROM customer_interactions ci
    JOIN customers c ON ci.customer_id = c.id
    WHERE ci.id = ?
");
$stmt->execute([$interaction_id]);
$interaction = $stmt->fetch();

if (!$interaction) {
    header('Location: list.php');
    exit;
}

// Sadece kendi kaydını veya admin düzenleyebilir
if ($interaction['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
    header('Location: list.php');
    exit;
}

// Müşterileri getir
$customers = $db->query("SELECT id, company_name FROM customers ORDER BY company_name")->fetchAll();

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)$_POST['customer_id'];
    $type = $_POST['type'];
    $subject = trim($_POST['subject']);
    $description = trim($_POST['description']);
    $contact_person = trim($_POST['contact_person']);
    $duration = !empty($_POST['duration']) ? (int)$_POST['duration'] : null;
    $outcome = $_POST['outcome'];
    $next_action = trim($_POST['next_action']);
    $next_action_date = !empty($_POST['next_action_date']) ? $_POST['next_action_date'] : null;
    $is_important = isset($_POST['is_important']) ? 1 : 0;

    // Validasyon
    if (empty($customer_id)) {
        $errors[] = 'Müşteri seçimi zorunludur.';
    }
    if (empty($subject)) {
        $errors[] = 'Konu alanı zorunludur.';
    }
    if (empty($type)) {
        $errors[] = 'İletişim türü seçimi zorunludur.';
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                UPDATE customer_interactions SET
                customer_id = ?, type = ?, subject = ?, description = ?, 
                contact_person = ?, duration = ?, outcome = ?, next_action = ?, 
                next_action_date = ?, is_important = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $customer_id, $type, $subject, $description,
                $contact_person, $duration, $outcome, $next_action,
                $next_action_date, $is_important, $interaction_id
            ]);

            $success = 'İletişim kaydı başarıyla güncellendi.';
            
            // Güncel veriyi al
            $stmt = $db->prepare("
                SELECT ci.*, c.company_name 
                FROM customer_interactions ci
                JOIN customers c ON ci.customer_id = c.id
                WHERE ci.id = ?
            ");
            $stmt->execute([$interaction_id]);
            $interaction = $stmt->fetch();
            
        } catch (Exception $e) {
            $errors[] = 'Kayıt güncellenirken bir hata oluştu: ' . $e->getMessage();
        }
    }
}

require_once '../../includes/header.php';
?>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">İletişim Kaydını Düzenle</h1>
        <p style="color: var(--text-muted); margin: 0.5rem 0 0;"><?php echo htmlspecialchars($interaction['company_name']); ?> - <?php echo htmlspecialchars($interaction['subject']); ?></p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="list.php?customer_id=<?php echo $interaction['customer_id']; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Geri Dön
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Müşteri *</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Müşteri seçin...</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['id']; ?>" 
                                        <?php echo $customer['id'] == $interaction['customer_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($customer['company_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">İletişim Türü *</label>
                        <select name="type" class="form-select" required>
                            <option value="">Tür seçin...</option>
                            <option value="call" <?php echo $interaction['type'] === 'call' ? 'selected' : ''; ?>>
                                📞 Telefon Görüşmesi
                            </option>
                            <option value="email" <?php echo $interaction['type'] === 'email' ? 'selected' : ''; ?>>
                                📧 E-posta
                            </option>
                            <option value="meeting" <?php echo $interaction['type'] === 'meeting' ? 'selected' : ''; ?>>
                                👥 Toplantı
                            </option>
                            <option value="whatsapp" <?php echo $interaction['type'] === 'whatsapp' ? 'selected' : ''; ?>>
                                💬 WhatsApp
                            </option>
                            <option value="sms" <?php echo $interaction['type'] === 'sms' ? 'selected' : ''; ?>>
                                📱 SMS
                            </option>
                            <option value="note" <?php echo $interaction['type'] === 'note' ? 'selected' : ''; ?>>
                                📝 Not
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Konu *</label>
                <input type="text" name="subject" class="form-control" 
                       value="<?php echo htmlspecialchars($interaction['subject']); ?>" 
                       placeholder="İletişimin konusunu kısaca yazın" required>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">İletişim Kurulan Kişi</label>
                        <input type="text" name="contact_person" class="form-control" 
                               value="<?php echo htmlspecialchars($interaction['contact_person']); ?>" 
                               placeholder="Görüştüğünüz kişinin adı">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Süre (Dakika)</label>
                        <input type="number" name="duration" class="form-control" 
                               value="<?php echo $interaction['duration']; ?>" 
                               placeholder="0" min="0">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Sonuç</label>
                        <select name="outcome" class="form-select">
                            <option value="neutral" <?php echo $interaction['outcome'] === 'neutral' ? 'selected' : ''; ?>>
                                😐 Nötr
                            </option>
                            <option value="positive" <?php echo $interaction['outcome'] === 'positive' ? 'selected' : ''; ?>>
                                😊 Olumlu
                            </option>
                            <option value="negative" <?php echo $interaction['outcome'] === 'negative' ? 'selected' : ''; ?>>
                                😞 Olumsuz
                            </option>
                            <option value="follow_up_needed" <?php echo $interaction['outcome'] === 'follow_up_needed' ? 'selected' : ''; ?>>
                                🔄 Takip Gerekli
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Açıklama</label>
                <textarea name="description" class="form-control" rows="4" 
                          placeholder="İletişimin detaylarını, konuşulan konuları ve önemli noktaları yazın..."><?php echo htmlspecialchars($interaction['description']); ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Sonraki Aksiyon</label>
                        <input type="text" name="next_action" class="form-control" 
                               value="<?php echo htmlspecialchars($interaction['next_action']); ?>" 
                               placeholder="Yapılması gereken sonraki adım">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Sonraki Aksiyon Tarihi</label>
                        <input type="datetime-local" name="next_action_date" class="form-control" 
                               value="<?php echo $interaction['next_action_date'] ? date('Y-m-d\TH:i', strtotime($interaction['next_action_date'])) : ''; ?>">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input type="checkbox" name="is_important" class="form-check-input" id="is_important" 
                           <?php echo $interaction['is_important'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_important">
                        <i class="fas fa-star text-warning"></i> Bu kayıt önemli olarak işaretle
                    </label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Güncelle
                </button>
                <a href="list.php?customer_id=<?php echo $interaction['customer_id']; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> İptal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
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

<?php require_once '../../includes/footer.php'; ?>