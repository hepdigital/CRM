<?php
// modules/settings/index.php
require_once '../../includes/header.php';

// Sadece admin erişebilir
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

// Ayarları getir
$settings = [];
$stmt = $db->query("SELECT * FROM system_settings ORDER BY setting_key");
$settingsData = $stmt->fetchAll();

foreach ($settingsData as $setting) {
    $settings[$setting['setting_key']] = $setting;
}

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = true;
    $errors = [];
    
    try {
        $db->beginTransaction();
        
        foreach ($_POST['settings'] as $key => $value) {
            // Şifre alanları boşsa güncelleme
            if ($settings[$key]['setting_type'] === 'password' && empty($value)) {
                continue;
            }
            
            $stmt = $db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
        
        $db->commit();
        
        // Başarı bildirimi
        $_SESSION['success_message'] = 'Ayarlar başarıyla güncellendi.';
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        $db->rollBack();
        $errors[] = 'Ayarlar güncellenirken bir hata oluştu: ' . $e->getMessage();
        $success = false;
    }
}

// Başarı mesajı
$successMessage = '';
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">Sistem Ayarları</h1>
        <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Sistem ayarlarını yönetin ve yapılandırın</p>
    </div>
</div>

<!-- Ayar Kategorileri -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-building fa-3x text-primary"></i>
                </div>
                <h5 class="card-title">Şirket Ayarları</h5>
                <p class="card-text">Şirket bilgileri, logo, banka bilgileri ve PDF ayarları</p>
                <a href="company.php" class="btn btn-primary">
                    <i class="fas fa-cog"></i> Yönet
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-server fa-3x text-info"></i>
                </div>
                <h5 class="card-title">Sistem Ayarları</h5>
                <p class="card-text">E-posta, veritabanı ve genel sistem ayarları</p>
                <button type="button" class="btn btn-info" onclick="toggleSystemSettings()">
                    <i class="fas fa-cog"></i> Yönet
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Sistem Ayarları (Gizli) -->
<div id="systemSettingsPanel" style="display: none;">

<?php if ($successMessage): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 2rem;">
    <i class="fas fa-check-circle me-2"></i>
    <?php echo $successMessage; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-bottom: 2rem;">
    <i class="fas fa-exclamation-circle me-2"></i>
    <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
        <li><?php echo $error; ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Settings Form -->
<form method="POST" id="settingsForm">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        
        <!-- SMTP Ayarları -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-envelope"></i> E-posta Ayarları (SMTP)
                </h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">SMTP Sunucu</label>
                    <input type="text" name="settings[smtp_host]" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['smtp_host']['setting_value'] ?? ''); ?>"
                           placeholder="smtp.gmail.com">
                </div>
                
                <div class="form-group">
                    <label class="form-label">SMTP Port</label>
                    <input type="number" name="settings[smtp_port]" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['smtp_port']['setting_value'] ?? '587'); ?>"
                           placeholder="587">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Kullanıcı Adı</label>
                    <input type="email" name="settings[smtp_username]" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['smtp_username']['setting_value'] ?? ''); ?>"
                           placeholder="your-email@gmail.com">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Şifre</label>
                    <input type="password" name="settings[smtp_password]" class="form-control" 
                           placeholder="Şifre değiştirmek için yeni şifre girin">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Şifreleme</label>
                    <select name="settings[smtp_encryption]" class="form-control">
                        <option value="tls" <?php echo ($settings['smtp_encryption']['setting_value'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                        <option value="ssl" <?php echo ($settings['smtp_encryption']['setting_value'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                        <option value="" <?php echo empty($settings['smtp_encryption']['setting_value'] ?? '') ? 'selected' : ''; ?>>Yok</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Gönderen E-posta</label>
                    <input type="email" name="settings[smtp_from_email]" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['smtp_from_email']['setting_value'] ?? ''); ?>"
                           placeholder="noreply@yourcompany.com">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Gönderen Adı</label>
                    <input type="text" name="settings[smtp_from_name]" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['smtp_from_name']['setting_value'] ?? 'CRM Pro'); ?>"
                           placeholder="CRM Pro">
                </div>
            </div>
        </div>
        
        <!-- Bildirim Ayarları -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-bell"></i> Bildirim Ayarları
                </h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <input type="checkbox" name="settings[notification_email_enabled]" value="1" 
                               <?php echo ($settings['notification_email_enabled']['setting_value'] ?? '0') === '1' ? 'checked' : ''; ?>
                               class="form-check-input" style="width: 20px; height: 20px;">
                        <label class="form-label" style="margin: 0;">E-posta Bildirimleri Aktif</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <input type="checkbox" name="settings[notification_system_enabled]" value="1" 
                               <?php echo ($settings['notification_system_enabled']['setting_value'] ?? '1') === '1' ? 'checked' : ''; ?>
                               class="form-check-input" style="width: 20px; height: 20px;">
                        <label class="form-label" style="margin: 0;">Sistem Bildirimleri Aktif</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Düşük Stok Uyarı Eşiği</label>
                    <input type="number" name="settings[low_stock_threshold]" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['low_stock_threshold']['setting_value'] ?? '10'); ?>"
                           min="1" max="1000">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Teklif Geçerlilik Süresi (Gün)</label>
                    <input type="number" name="settings[quote_expiry_days]" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['quote_expiry_days']['setting_value'] ?? '30'); ?>"
                           min="1" max="365">
                </div>
            </div>
        </div>
        
        <!-- Sistem Ayarları -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-cog"></i> Sistem Ayarları
                </h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Saat Dilimi</label>
                    <select name="settings[system_timezone]" class="form-control">
                        <option value="Europe/Istanbul" <?php echo ($settings['system_timezone']['setting_value'] ?? 'Europe/Istanbul') === 'Europe/Istanbul' ? 'selected' : ''; ?>>Europe/Istanbul</option>
                        <option value="UTC" <?php echo ($settings['system_timezone']['setting_value'] ?? '') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                        <option value="America/New_York" <?php echo ($settings['system_timezone']['setting_value'] ?? '') === 'America/New_York' ? 'selected' : ''; ?>>America/New_York</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tarih Formatı</label>
                    <select name="settings[date_format]" class="form-control">
                        <option value="d.m.Y" <?php echo ($settings['date_format']['setting_value'] ?? 'd.m.Y') === 'd.m.Y' ? 'selected' : ''; ?>>dd.mm.yyyy</option>
                        <option value="Y-m-d" <?php echo ($settings['date_format']['setting_value'] ?? '') === 'Y-m-d' ? 'selected' : ''; ?>>yyyy-mm-dd</option>
                        <option value="m/d/Y" <?php echo ($settings['date_format']['setting_value'] ?? '') === 'm/d/Y' ? 'selected' : ''; ?>>mm/dd/yyyy</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Para Birimi Sembolü</label>
                    <input type="text" name="settings[currency_symbol]" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['currency_symbol']['setting_value'] ?? '₺'); ?>"
                           placeholder="₺" maxlength="5">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Sayfa Başına Kayıt Sayısı</label>
                    <input type="number" name="settings[items_per_page]" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['items_per_page']['setting_value'] ?? '10'); ?>"
                           min="5" max="100">
                </div>
            </div>
        </div>
        
        <!-- Yedekleme Ayarları -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-database"></i> Yedekleme Ayarları
                </h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <input type="checkbox" name="settings[backup_enabled]" value="1" 
                               <?php echo ($settings['backup_enabled']['setting_value'] ?? '0') === '1' ? 'checked' : ''; ?>
                               class="form-check-input" style="width: 20px; height: 20px;">
                        <label class="form-label" style="margin: 0;">Otomatik Yedekleme Aktif</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Yedekleme Sıklığı</label>
                    <select name="settings[backup_frequency]" class="form-control">
                        <option value="daily" <?php echo ($settings['backup_frequency']['setting_value'] ?? 'daily') === 'daily' ? 'selected' : ''; ?>>Günlük</option>
                        <option value="weekly" <?php echo ($settings['backup_frequency']['setting_value'] ?? '') === 'weekly' ? 'selected' : ''; ?>>Haftalık</option>
                        <option value="monthly" <?php echo ($settings['backup_frequency']['setting_value'] ?? '') === 'monthly' ? 'selected' : ''; ?>>Aylık</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Save Button -->
    <div style="display: flex; justify-content: center; margin-top: 2rem;">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save"></i> Ayarları Kaydet
        </button>
    </div>
</form>

<script>
// Form validation
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    const requiredFields = this.querySelectorAll('input[required], select[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Lütfen tüm gerekli alanları doldurun.');
    }
});

// Checkbox handling
document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        if (!this.checked) {
            // Hidden input ekle
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = this.name;
            hiddenInput.value = '0';
            this.parentNode.appendChild(hiddenInput);
        } else {
            // Hidden input'u kaldır
            const hiddenInput = this.parentNode.querySelector('input[type="hidden"][name="' + this.name + '"]');
            if (hiddenInput) {
                hiddenInput.remove();
            }
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?></div
> <!-- systemSettingsPanel sonu -->

<script>
function toggleSystemSettings() {
    const panel = document.getElementById('systemSettingsPanel');
    if (panel.style.display === 'none') {
        panel.style.display = 'block';
    } else {
        panel.style.display = 'none';
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>