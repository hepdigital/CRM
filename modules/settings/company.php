<?php
// modules/settings/company.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

checkAuth();
checkAdmin(); // Sadece admin erişebilir

$errors = [];
$success = '';

// Mevcut ayarları getir
$stmt = $db->query("SELECT * FROM company_settings ORDER BY id DESC LIMIT 1");
$settings = $stmt->fetch();

// Eğer ayar yoksa varsayılan değerler
if (!$settings) {
    $settings = [
        'company_name' => '',
        'company_title' => '',
        'address' => '',
        'phone' => '',
        'phone_2' => '',
        'email' => '',
        'website' => '',
        'tax_office' => '',
        'tax_number' => '',
        'logo_url' => '',
        'signature_url' => '',
        'bank_name' => '',
        'bank_branch' => '',
        'account_holder' => '',
        'iban' => '',
        'swift_code' => '',
        'pdf_header_color' => '#e94e1a',
        'pdf_footer_text' => '',
        'pdf_notes' => '',
        'email_signature' => '',
        'email_footer' => '',
        'facebook_url' => '',
        'instagram_url' => '',
        'linkedin_url' => '',
        'twitter_url' => '',
        'currency' => 'TL',
        'timezone' => 'Europe/Istanbul',
        'date_format' => 'd.m.Y',
        'decimal_places' => 2
    ];
}

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'company_name' => trim($_POST['company_name']),
        'company_title' => trim($_POST['company_title']),
        'address' => trim($_POST['address']),
        'phone' => trim($_POST['phone']),
        'phone_2' => trim($_POST['phone_2']),
        'email' => trim($_POST['email']),
        'website' => trim($_POST['website']),
        'tax_office' => trim($_POST['tax_office']),
        'tax_number' => trim($_POST['tax_number']),
        'logo_url' => trim($_POST['logo_url']),
        'signature_url' => trim($_POST['signature_url']),
        'bank_name' => trim($_POST['bank_name']),
        'bank_branch' => trim($_POST['bank_branch']),
        'account_holder' => trim($_POST['account_holder']),
        'iban' => trim($_POST['iban']),
        'swift_code' => trim($_POST['swift_code']),
        'pdf_header_color' => trim($_POST['pdf_header_color']),
        'pdf_footer_text' => trim($_POST['pdf_footer_text']),
        'pdf_notes' => trim($_POST['pdf_notes']),
        'email_signature' => trim($_POST['email_signature']),
        'email_footer' => trim($_POST['email_footer']),
        'facebook_url' => trim($_POST['facebook_url']),
        'instagram_url' => trim($_POST['instagram_url']),
        'linkedin_url' => trim($_POST['linkedin_url']),
        'twitter_url' => trim($_POST['twitter_url']),
        'currency' => trim($_POST['currency']),
        'timezone' => trim($_POST['timezone']),
        'date_format' => trim($_POST['date_format']),
        'decimal_places' => (int)$_POST['decimal_places']
    ];

    // Validasyon
    if (empty($data['company_name'])) {
        $errors[] = 'Şirket adı zorunludur.';
    }
    
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta adresi girin.';
    }
    
    if (!empty($data['website']) && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Geçerli bir website adresi girin.';
    }

    if (empty($errors)) {
        try {
            // Mevcut kayıt var mı kontrol et
            $stmt = $db->query("SELECT COUNT(*) as count FROM company_settings");
            $count = $stmt->fetch()['count'];
            
            if ($count > 0) {
                // Güncelle
                $sql = "UPDATE company_settings SET 
                    company_name = ?, company_title = ?, address = ?, phone = ?, phone_2 = ?,
                    email = ?, website = ?, tax_office = ?, tax_number = ?, logo_url = ?,
                    signature_url = ?, bank_name = ?, bank_branch = ?, account_holder = ?,
                    iban = ?, swift_code = ?, pdf_header_color = ?, pdf_footer_text = ?,
                    pdf_notes = ?, email_signature = ?, email_footer = ?, facebook_url = ?,
                    instagram_url = ?, linkedin_url = ?, twitter_url = ?, currency = ?,
                    timezone = ?, date_format = ?, decimal_places = ?
                    WHERE id = (SELECT id FROM (SELECT id FROM company_settings ORDER BY id DESC LIMIT 1) as temp)";
                
                $stmt = $db->prepare($sql);
                $stmt->execute(array_values($data));
            } else {
                // Yeni kayıt
                $sql = "INSERT INTO company_settings (
                    company_name, company_title, address, phone, phone_2, email, website,
                    tax_office, tax_number, logo_url, signature_url, bank_name, bank_branch,
                    account_holder, iban, swift_code, pdf_header_color, pdf_footer_text,
                    pdf_notes, email_signature, email_footer, facebook_url, instagram_url,
                    linkedin_url, twitter_url, currency, timezone, date_format, decimal_places
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $db->prepare($sql);
                $stmt->execute(array_values($data));
            }

            $success = 'Şirket ayarları başarıyla kaydedildi.';
            
            // Güncel ayarları tekrar getir
            $stmt = $db->query("SELECT * FROM company_settings ORDER BY id DESC LIMIT 1");
            $settings = $stmt->fetch();
            
        } catch (Exception $e) {
            $errors[] = 'Kayıt sırasında bir hata oluştu: ' . $e->getMessage();
        }
    }
}

require_once '../../includes/header.php';
?>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">Şirket Ayarları</h1>
        <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Şirket bilgileri ve PDF ayarlarını yönetin</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="index.php" class="btn btn-secondary">
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

<form method="POST" class="needs-validation" novalidate>
    <div class="row">
        <!-- Sol Kolon -->
        <div class="col-md-8">
            <!-- Logo Bölümü -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-image"></i> Şirket Logosu
                    </h5>
                </div>
                <div class="card-body">
                    <div class="logo-upload-container">
                        <input type="hidden" name="logo_url" id="logo_url" value="<?php echo htmlspecialchars($settings['logo_url']); ?>">
                        
                        <div class="current-logo mb-3" id="current-logo" <?php echo !$settings['logo_url'] ? 'style="display: none;"' : ''; ?>>
                            <img src="<?php echo htmlspecialchars($settings['logo_url']); ?>" 
                                 alt="Logo" class="img-thumbnail" style="max-height: 120px;" id="logo-preview">
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeLogo()">
                                    <i class="fas fa-trash"></i> Logoyu Kaldır
                                </button>
                            </div>
                        </div>
                        
                        <div class="upload-area" id="upload-area">
                            <input type="file" id="logo-file" accept="image/*" style="display: none;" onchange="uploadLogo()">
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('logo-file').click()">
                                <i class="fas fa-upload"></i> Logo Yükle
                            </button>
                            <div class="mt-2">
                                <small class="text-muted">JPG, PNG, GIF veya WebP formatında, maksimum 5MB</small>
                            </div>
                        </div>
                        
                        <div class="upload-progress" id="upload-progress" style="display: none;">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted">Logo yükleniyor...</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Temel Bilgiler -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle"></i> Temel Bilgiler
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Şirket Adı *</label>
                                <input type="text" name="company_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['company_name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Website</label>
                                <input type="url" name="website" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['website']); ?>" 
                                       placeholder="https://example.com">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Uzun Şirket Unvanı</label>
                        <textarea name="company_title" class="form-control" rows="2" 
                                  placeholder="Resmi şirket unvanınızı girin"><?php echo htmlspecialchars($settings['company_title']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Adres</label>
                        <textarea name="address" class="form-control" rows="3" 
                                  placeholder="Şirket adresinizi girin"><?php echo htmlspecialchars($settings['address']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Telefon</label>
                                <input type="text" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['phone']); ?>" 
                                       placeholder="+90 XXX XXX XX XX">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">İkinci Telefon</label>
                                <input type="text" name="phone_2" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['phone_2']); ?>" 
                                       placeholder="+90 XXX XXX XX XX">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">E-posta</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['email']); ?>" 
                                       placeholder="info@example.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Para Birimi</label>
                                <select name="currency" class="form-select">
                                    <option value="TL" <?php echo $settings['currency'] === 'TL' ? 'selected' : ''; ?>>Türk Lirası (TL)</option>
                                    <option value="USD" <?php echo $settings['currency'] === 'USD' ? 'selected' : ''; ?>>Dolar (USD)</option>
                                    <option value="EUR" <?php echo $settings['currency'] === 'EUR' ? 'selected' : ''; ?>>Euro (EUR)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vergi Bilgileri -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-receipt"></i> Vergi Bilgileri
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Vergi Dairesi</label>
                                <input type="text" name="tax_office" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['tax_office']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Vergi Numarası</label>
                                <input type="text" name="tax_number" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['tax_number']); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banka Bilgileri -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-university"></i> Banka Bilgileri
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Banka Adı</label>
                                <input type="text" name="bank_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['bank_name']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Şube</label>
                                <input type="text" name="bank_branch" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['bank_branch']); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Hesap Sahibi</label>
                        <input type="text" name="account_holder" class="form-control" 
                               value="<?php echo htmlspecialchars($settings['account_holder']); ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">IBAN</label>
                                <input type="text" name="iban" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['iban']); ?>" 
                                       placeholder="TR00 0000 0000 0000 0000 0000 00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">SWIFT Kodu</label>
                                <input type="text" name="swift_code" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['swift_code']); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PDF Ayarları -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-file-pdf"></i> PDF Ayarları
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">PDF Başlık Rengi</label>
                                <input type="color" name="pdf_header_color" class="form-control form-control-color" 
                                       value="<?php echo htmlspecialchars($settings['pdf_header_color']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ondalık Basamak Sayısı</label>
                                <select name="decimal_places" class="form-select">
                                    <option value="0" <?php echo $settings['decimal_places'] == 0 ? 'selected' : ''; ?>>0</option>
                                    <option value="1" <?php echo $settings['decimal_places'] == 1 ? 'selected' : ''; ?>>1</option>
                                    <option value="2" <?php echo $settings['decimal_places'] == 2 ? 'selected' : ''; ?>>2</option>
                                    <option value="3" <?php echo $settings['decimal_places'] == 3 ? 'selected' : ''; ?>>3</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">PDF Alt Bilgi Metni</label>
                        <textarea name="pdf_footer_text" class="form-control" rows="3" 
                                  placeholder="PDF'lerin alt kısmında görünecek metin"><?php echo htmlspecialchars($settings['pdf_footer_text']); ?></textarea>
                        <div class="form-text">Her satır için \n kullanın</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Varsayılan Teklif Notları</label>
                        <textarea name="pdf_notes" class="form-control" rows="4" 
                                  placeholder="Tekliflerde varsayılan olarak görünecek notlar"><?php echo htmlspecialchars($settings['pdf_notes']); ?></textarea>
                        <div class="form-text">Her satır için \n kullanın</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sağ Kolon -->
        <div class="col-md-4">
            <!-- İmza Görseli -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-signature"></i> İmza Görseli
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">İmza Görseli URL</label>
                        <input type="url" name="signature_url" class="form-control" 
                               value="<?php echo htmlspecialchars($settings['signature_url']); ?>" 
                               placeholder="https://example.com/signature.png">
                        <?php if ($settings['signature_url']): ?>
                            <div class="mt-2">
                                <img src="<?php echo htmlspecialchars($settings['signature_url']); ?>" 
                                     alt="İmza" class="img-thumbnail" style="max-height: 80px;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sosyal Medya -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-share-alt"></i> Sosyal Medya
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Facebook</label>
                        <input type="url" name="facebook_url" class="form-control" 
                               value="<?php echo htmlspecialchars($settings['facebook_url']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Instagram</label>
                        <input type="url" name="instagram_url" class="form-control" 
                               value="<?php echo htmlspecialchars($settings['instagram_url']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">LinkedIn</label>
                        <input type="url" name="linkedin_url" class="form-control" 
                               value="<?php echo htmlspecialchars($settings['linkedin_url']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Twitter</label>
                        <input type="url" name="twitter_url" class="form-control" 
                               value="<?php echo htmlspecialchars($settings['twitter_url']); ?>">
                    </div>
                </div>
            </div>

            <!-- E-posta Ayarları -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-envelope"></i> E-posta Ayarları
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">E-posta İmzası</label>
                        <textarea name="email_signature" class="form-control" rows="3" 
                                  placeholder="E-postalarda kullanılacak imza"><?php echo htmlspecialchars($settings['email_signature']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">E-posta Alt Bilgi</label>
                        <textarea name="email_footer" class="form-control" rows="2" 
                                  placeholder="E-posta alt bilgisi"><?php echo htmlspecialchars($settings['email_footer']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Sistem Ayarları -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-cogs"></i> Sistem Ayarları
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Zaman Dilimi</label>
                        <select name="timezone" class="form-select">
                            <option value="Europe/Istanbul" <?php echo $settings['timezone'] === 'Europe/Istanbul' ? 'selected' : ''; ?>>İstanbul</option>
                            <option value="UTC" <?php echo $settings['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tarih Formatı</label>
                        <select name="date_format" class="form-select">
                            <option value="d.m.Y" <?php echo $settings['date_format'] === 'd.m.Y' ? 'selected' : ''; ?>>31.12.2023</option>
                            <option value="d/m/Y" <?php echo $settings['date_format'] === 'd/m/Y' ? 'selected' : ''; ?>>31/12/2023</option>
                            <option value="Y-m-d" <?php echo $settings['date_format'] === 'Y-m-d' ? 'selected' : ''; ?>>2023-12-31</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Ayarları Kaydet
        </button>
        <a href="../quotes/generate_pdf.php?id=1&preview=1" class="btn btn-outline-info" target="_blank">
            <i class="fas fa-eye"></i> PDF Önizleme
        </a>
    </div>
</form>

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

// IBAN formatı
document.querySelector('input[name="iban"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '').toUpperCase();
    let formatted = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formatted;
});

// Logo yükleme fonksiyonları
function uploadLogo() {
    const fileInput = document.getElementById('logo-file');
    const file = fileInput.files[0];
    
    if (!file) return;
    
    // Dosya türü kontrolü
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        alert('Sadece JPG, PNG, GIF ve WebP dosyaları yüklenebilir');
        return;
    }
    
    // Dosya boyutu kontrolü (5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('Dosya boyutu 5MB\'dan büyük olamaz');
        return;
    }
    
    const formData = new FormData();
    formData.append('logo', file);
    
    // Progress göster
    document.getElementById('upload-area').style.display = 'none';
    document.getElementById('upload-progress').style.display = 'block';
    
    fetch('upload_logo.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('upload-progress').style.display = 'none';
        document.getElementById('upload-area').style.display = 'block';
        
        if (data.success) {
            // Logo önizlemesini güncelle
            document.getElementById('logo_url').value = data.url;
            document.getElementById('logo-preview').src = data.url;
            document.getElementById('current-logo').style.display = 'block';
            
            // Başarı mesajı
            showToast('Logo başarıyla yüklendi!', 'success');
        } else {
            alert('Hata: ' + data.message);
        }
    })
    .catch(error => {
        document.getElementById('upload-progress').style.display = 'none';
        document.getElementById('upload-area').style.display = 'block';
        alert('Logo yüklenirken bir hata oluştu');
        console.error('Error:', error);
    });
}

function removeLogo() {
    if (confirm('Logoyu kaldırmak istediğinizden emin misiniz?')) {
        document.getElementById('logo_url').value = '';
        document.getElementById('current-logo').style.display = 'none';
        showToast('Logo kaldırıldı', 'info');
    }
}

function showToast(message, type) {
    // Basit toast bildirimi
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}
</script>

<?php require_once '../../includes/footer.php'; ?>