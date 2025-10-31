<?php
// Test sayfası - Şirket ayarları
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/company_settings.php';

checkAuth();
require_once 'includes/header.php';
?>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">Şirket Ayarları Test</h1>
        <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Şirket ayarlarının nasıl kullanıldığını test edin</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="modules/settings/company.php" class="btn btn-primary">
            <i class="fas fa-cog"></i> Ayarları Düzenle
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-info-circle"></i> Temel Bilgiler
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Şirket Adı:</strong></td>
                        <td><?php echo htmlspecialchars($companySettings->getCompanyName()); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Şirket Unvanı:</strong></td>
                        <td><?php echo htmlspecialchars($companySettings->getCompanyTitle()); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Adres:</strong></td>
                        <td><?php echo nl2br(htmlspecialchars($companySettings->get('address'))); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Telefon:</strong></td>
                        <td><?php echo htmlspecialchars($companySettings->get('phone')); ?></td>
                    </tr>
                    <tr>
                        <td><strong>E-posta:</strong></td>
                        <td><?php echo htmlspecialchars($companySettings->get('email')); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Para Birimi:</strong></td>
                        <td><?php echo htmlspecialchars($companySettings->get('currency')); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-university"></i> Banka Bilgileri
                </h5>
            </div>
            <div class="card-body">
                <?php $bankInfo = $companySettings->getBankInfo(); ?>
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Banka:</strong></td>
                        <td><?php echo htmlspecialchars($bankInfo['bank_name']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Hesap Sahibi:</strong></td>
                        <td><?php echo htmlspecialchars($bankInfo['account_holder']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>IBAN:</strong></td>
                        <td><?php echo htmlspecialchars($bankInfo['iban']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-palette"></i> PDF Ayarları
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label><strong>PDF Başlık Rengi:</strong></label>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 30px; height: 30px; background-color: <?php echo $companySettings->get('pdf_header_color'); ?>; border: 1px solid #ddd; border-radius: 4px;"></div>
                        <span><?php echo $companySettings->get('pdf_header_color'); ?></span>
                        <span class="text-muted">RGB: <?php echo implode(', ', $companySettings->getPdfHeaderColorRgb()); ?></span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label><strong>Ondalık Basamak:</strong></label>
                    <span><?php echo $companySettings->get('decimal_places'); ?></span>
                </div>
                
                <div class="mb-3">
                    <label><strong>Para Formatı Örneği:</strong></label>
                    <div>
                        <?php echo $companySettings->formatMoney(1234.56); ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label><strong>Tarih Formatı Örneği:</strong></label>
                    <div>
                        <?php echo $companySettings->formatDate(time()); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-file-pdf"></i> PDF Metinleri
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label><strong>PDF Footer:</strong></label>
                    <div class="border p-2 bg-light small">
                        <?php echo nl2br(htmlspecialchars($companySettings->getPdfFooter())); ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label><strong>Varsayılan Teklif Notları:</strong></label>
                    <div class="border p-2 bg-light small">
                        <?php echo nl2br(htmlspecialchars($companySettings->getPdfNotes())); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-envelope"></i> E-posta İmzası
                </h5>
            </div>
            <div class="card-body">
                <div class="border p-3 bg-light">
                    <pre style="margin: 0; font-family: inherit;"><?php echo htmlspecialchars($companySettings->getEmailSignature()); ?></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-share-alt"></i> Sosyal Medya
                </h5>
            </div>
            <div class="card-body">
                <?php $socialMedia = $companySettings->getSocialMedia(); ?>
                <div class="d-flex gap-3">
                    <?php if ($socialMedia['facebook']): ?>
                        <a href="<?php echo htmlspecialchars($socialMedia['facebook']); ?>" target="_blank" class="btn btn-outline-primary">
                            <i class="fab fa-facebook"></i> Facebook
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($socialMedia['instagram']): ?>
                        <a href="<?php echo htmlspecialchars($socialMedia['instagram']); ?>" target="_blank" class="btn btn-outline-danger">
                            <i class="fab fa-instagram"></i> Instagram
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($socialMedia['linkedin']): ?>
                        <a href="<?php echo htmlspecialchars($socialMedia['linkedin']); ?>" target="_blank" class="btn btn-outline-info">
                            <i class="fab fa-linkedin"></i> LinkedIn
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($socialMedia['twitter']): ?>
                        <a href="<?php echo htmlspecialchars($socialMedia['twitter']); ?>" target="_blank" class="btn btn-outline-secondary">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                    <?php endif; ?>
                </div>
                
                <?php if (!array_filter($socialMedia)): ?>
                    <p class="text-muted">Henüz sosyal medya hesabı eklenmemiş.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <div class="alert alert-info">
        <h6><i class="fas fa-info-circle"></i> Test İpuçları:</h6>
        <ul class="mb-0">
            <li>Şirket ayarlarını değiştirdikten sonra bu sayfayı yenileyerek değişiklikleri görebilirsiniz</li>
            <li>PDF önizlemesi için herhangi bir teklifi PDF olarak indirin</li>
            <li>E-posta imzası, sistem genelinde e-posta gönderimlerinde kullanılacaktır</li>
            <li>Para ve tarih formatları tüm sistemde otomatik olarak uygulanacaktır</li>
        </ul>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>