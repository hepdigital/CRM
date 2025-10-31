<?php
// modules/interactions/add.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

checkAuth();

$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;
$errors = [];
$success = '';

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
                INSERT INTO customer_interactions 
                (customer_id, user_id, type, subject, description, contact_person, duration, outcome, next_action, next_action_date, is_important)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $customer_id,
                $_SESSION['user_id'],
                $type,
                $subject,
                $description,
                $contact_person,
                $duration,
                $outcome,
                $next_action,
                $next_action_date,
                $is_important
            ]);

            $success = 'İletişim kaydı başarıyla oluşturuldu.';
            
            // Formu temizle
            $_POST = [];
            
            // 2 saniye sonra yönlendir
            header("refresh:2;url=list.php?customer_id=$customer_id");
            
        } catch (Exception $e) {
            $errors[] = 'Kayıt oluşturulurken bir hata oluştu: ' . $e->getMessage();
        }
    }
}

require_once '../../includes/header.php';
?>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">Yeni İletişim Kaydı</h1>
        <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Müşteri ile yapılan iletişimi kaydedin</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="list.php<?php echo $customer_id ? '?customer_id=' . $customer_id : ''; ?>" class="btn btn-secondary">
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
                                        <?php echo (isset($_POST['customer_id']) && $_POST['customer_id'] == $customer['id']) || $customer_id == $customer['id'] ? 'selected' : ''; ?>>
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
                            <option value="call" <?php echo (isset($_POST['type']) && $_POST['type'] === 'call') ? 'selected' : ''; ?>>
                                📞 Telefon Görüşmesi
                            </option>
                            <option value="email" <?php echo (isset($_POST['type']) && $_POST['type'] === 'email') ? 'selected' : ''; ?>>
                                📧 E-posta
                            </option>
                            <option value="meeting" <?php echo (isset($_POST['type']) && $_POST['type'] === 'meeting') ? 'selected' : ''; ?>>
                                👥 Toplantı
                            </option>
                            <option value="whatsapp" <?php echo (isset($_POST['type']) && $_POST['type'] === 'whatsapp') ? 'selected' : ''; ?>>
                                💬 WhatsApp
                            </option>
                            <option value="sms" <?php echo (isset($_POST['type']) && $_POST['type'] === 'sms') ? 'selected' : ''; ?>>
                                📱 SMS
                            </option>
                            <option value="note" <?php echo (isset($_POST['type']) && $_POST['type'] === 'note') ? 'selected' : ''; ?>>
                                📝 Not
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Konu *</label>
                <input type="text" name="subject" class="form-control" 
                       value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" 
                       placeholder="İletişimin konusunu kısaca yazın" required>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">İletişim Kurulan Kişi</label>
                        <input type="text" name="contact_person" class="form-control" 
                               value="<?php echo isset($_POST['contact_person']) ? htmlspecialchars($_POST['contact_person']) : ''; ?>" 
                               placeholder="Görüştüğünüz kişinin adı">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Süre (Dakika)</label>
                        <input type="number" name="duration" class="form-control" 
                               value="<?php echo isset($_POST['duration']) ? $_POST['duration'] : ''; ?>" 
                               placeholder="0" min="0">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Sonuç</label>
                        <select name="outcome" class="form-select">
                            <option value="neutral" <?php echo (isset($_POST['outcome']) && $_POST['outcome'] === 'neutral') ? 'selected' : ''; ?>>
                                😐 Nötr
                            </option>
                            <option value="positive" <?php echo (isset($_POST['outcome']) && $_POST['outcome'] === 'positive') ? 'selected' : ''; ?>>
                                😊 Olumlu
                            </option>
                            <option value="negative" <?php echo (isset($_POST['outcome']) && $_POST['outcome'] === 'negative') ? 'selected' : ''; ?>>
                                😞 Olumsuz
                            </option>
                            <option value="follow_up_needed" <?php echo (isset($_POST['outcome']) && $_POST['outcome'] === 'follow_up_needed') ? 'selected' : ''; ?>>
                                🔄 Takip Gerekli
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Açıklama</label>
                <textarea name="description" class="form-control" rows="4" 
                          placeholder="İletişimin detaylarını, konuşulan konuları ve önemli noktaları yazın..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Sonraki Aksiyon</label>
                        <input type="text" name="next_action" class="form-control" 
                               value="<?php echo isset($_POST['next_action']) ? htmlspecialchars($_POST['next_action']) : ''; ?>" 
                               placeholder="Yapılması gereken sonraki adım">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Sonraki Aksiyon Tarihi</label>
                        <input type="datetime-local" name="next_action_date" class="form-control" 
                               value="<?php echo isset($_POST['next_action_date']) ? $_POST['next_action_date'] : ''; ?>">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input type="checkbox" name="is_important" class="form-check-input" id="is_important" 
                           <?php echo (isset($_POST['is_important']) && $_POST['is_important']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_important">
                        <i class="fas fa-star text-warning"></i> Bu kayıt önemli olarak işaretle
                    </label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Kaydet
                </button>
                <a href="list.php<?php echo $customer_id ? '?customer_id=' . $customer_id : ''; ?>" class="btn btn-secondary">
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

// Hızlı konu önerileri
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.querySelector('select[name="type"]');
    const subjectInput = document.querySelector('input[name="subject"]');
    
    const suggestions = {
        'call': ['Fiyat teklifi görüşmesi', 'Ürün bilgi talebi', 'Sipariş takibi', 'Şikayet görüşmesi'],
        'email': ['Teklif gönderimi', 'Ürün kataloğu paylaşımı', 'Fatura gönderimi', 'Bilgilendirme'],
        'meeting': ['Ürün tanıtımı', 'Sözleşme görüşmesi', 'İş geliştirme toplantısı', 'Proje değerlendirmesi'],
        'whatsapp': ['Hızlı bilgi paylaşımı', 'Ürün fotoğrafı gönderimi', 'Sipariş onayı', 'Teslimat koordinasyonu'],
        'sms': ['Randevu hatırlatması', 'Sipariş durumu', 'Ödeme hatırlatması', 'Kampanya duyurusu'],
        'note': ['Genel not', 'Müşteri tercihi', 'Özel durum', 'Hatırlatma notu']
    };
    
    typeSelect.addEventListener('change', function() {
        const type = this.value;
        if (type && suggestions[type] && !subjectInput.value) {
            // İlk öneriyi placeholder olarak göster
            subjectInput.placeholder = suggestions[type][0];
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>