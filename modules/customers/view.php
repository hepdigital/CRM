<?php
// modules/customers/view.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

checkAuth();

$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$customer_id) {
    header('Location: list.php');
    exit;
}

// Müşteri bilgilerini getir
$stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

if (!$customer) {
    header('Location: list.php');
    exit;
}

// Son iletişim kayıtları (10 adet)
$stmt = $db->prepare("
    SELECT 
        ci.*,
        u.full_name as user_name
    FROM customer_interactions ci
    JOIN users u ON ci.user_id = u.id
    WHERE ci.customer_id = ?
    ORDER BY ci.created_at DESC
    LIMIT 10
");
$stmt->execute([$customer_id]);
$recent_interactions = $stmt->fetchAll();

// Son notlar (10 adet)
$stmt = $db->prepare("
    SELECT 
        cn.*,
        u.full_name as user_name
    FROM customer_notes cn
    JOIN users u ON cn.user_id = u.id
    WHERE cn.customer_id = ? AND (cn.is_private = 0 OR cn.user_id = ?)
    ORDER BY cn.created_at DESC
    LIMIT 10
");
$stmt->execute([$customer_id, $_SESSION['user_id']]);
$recent_notes = $stmt->fetchAll();

// Teklifler
$stmt = $db->prepare("
    SELECT 
        q.*,
        (
            SELECT SUM(
                (qi.unit_price * qi.quantity) + 
                (qi.unit_price * qi.quantity * qi.vat_rate / 100)
            )
            FROM quote_items qi 
            WHERE qi.quote_id = q.id
        ) as calculated_total
    FROM quotes q
    WHERE q.customer_id = ?
    ORDER BY q.created_at DESC
    LIMIT 5
");
$stmt->execute([$customer_id]);
$recent_quotes = $stmt->fetchAll();

// İstatistikler
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_quotes,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_quotes,
        SUM(CASE WHEN status = 'accepted' THEN 
            (SELECT SUM((qi.unit_price * qi.quantity) + (qi.unit_price * qi.quantity * qi.vat_rate / 100))
             FROM quote_items qi WHERE qi.quote_id = q.id)
        ELSE 0 END) as total_sales
    FROM quotes q
    WHERE q.customer_id = ?
");
$stmt->execute([$customer_id]);
$stats = $stmt->fetch();

$stmt = $db->prepare("SELECT COUNT(*) as total_interactions FROM customer_interactions WHERE customer_id = ?");
$stmt->execute([$customer_id]);
$interaction_count = $stmt->fetch()['total_interactions'];

$stmt = $db->prepare("SELECT COUNT(*) as total_notes FROM customer_notes WHERE customer_id = ? AND (is_private = 0 OR user_id = ?)");
$stmt->execute([$customer_id, $_SESSION['user_id']]);
$notes_count = $stmt->fetch()['total_notes'];

// İletişim türleri
$interaction_types = [
    'call' => ['icon' => 'fas fa-phone', 'label' => 'Telefon', 'color' => 'primary'],
    'email' => ['icon' => 'fas fa-envelope', 'label' => 'E-posta', 'color' => 'info'],
    'meeting' => ['icon' => 'fas fa-users', 'label' => 'Toplantı', 'color' => 'success'],
    'note' => ['icon' => 'fas fa-sticky-note', 'label' => 'Not', 'color' => 'warning'],
    'whatsapp' => ['icon' => 'fab fa-whatsapp', 'label' => 'WhatsApp', 'color' => 'success'],
    'sms' => ['icon' => 'fas fa-sms', 'label' => 'SMS', 'color' => 'secondary']
];

$status_labels = [
    'draft' => ['text' => 'Taslak', 'class' => 'secondary'],
    'sent' => ['text' => 'Gönderildi', 'class' => 'primary'],
    'accepted' => ['text' => 'Kabul Edildi', 'class' => 'success'],
    'rejected' => ['text' => 'Reddedildi', 'class' => 'danger']
];

require_once '../../includes/header.php';
?>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">
            <?php echo htmlspecialchars($customer['company_name']); ?>
        </h1>
        <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Müşteri detayları ve iletişim geçmişi</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <div class="btn-group">
            <a href="../quotes/create.php?customer_id=<?php echo $customer_id; ?>" class="btn btn-primary">
                <i class="fas fa-file-invoice"></i> Yeni Teklif
            </a>
            <button type="button" class="btn btn-success" onclick="(function(){var m=document.getElementById('addInteractionModal');m.style.display='block';m.classList.add('show');m.removeAttribute('aria-hidden');document.body.classList.add('modal-open');if(!document.querySelector('.modal-backdrop')){var b=document.createElement('div');b.className='modal-backdrop fade show';document.body.appendChild(b);}})()">
                <i class="fas fa-plus"></i> İletişim Ekle
            </button>
            <button type="button" class="btn btn-info" onclick="(function(){var m=document.getElementById('addNoteModal');m.style.display='block';m.classList.add('show');m.removeAttribute('aria-hidden');document.body.classList.add('modal-open');if(!document.querySelector('.modal-backdrop')){var b=document.createElement('div');b.className='modal-backdrop fade show';document.body.appendChild(b);}})()">
                <i class="fas fa-sticky-note"></i> Not Ekle
            </button>
            <a href="edit.php?id=<?php echo $customer_id; ?>" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Düzenle
            </a>
        </div>
        <a href="list.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Geri Dön
        </a>
    </div>
</div>

<!-- İstatistik Kartları -->
<div class="stats-grid mb-4">
    <div class="stat-card primary">
        <div class="stat-icon">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-value"><?php echo $stats['total_quotes']; ?></div>
        <div class="stat-label">Toplam Teklif</div>
        <div class="stat-change">
            <span><?php echo $stats['accepted_quotes']; ?> kabul edildi</span>
        </div>
    </div>
    
    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-lira-sign"></i>
        </div>
        <div class="stat-value"><?php echo number_format($stats['total_sales'] ?? 0, 0, ',', '.'); ?> ₺</div>
        <div class="stat-label">Toplam Satış</div>
        <div class="stat-change">
            <span>Kabul edilen teklifler</span>
        </div>
    </div>
    
    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-comments"></i>
        </div>
        <div class="stat-value"><?php echo $interaction_count; ?></div>
        <div class="stat-label">İletişim Kaydı</div>
        <div class="stat-change">
            <span>Toplam etkileşim</span>
        </div>
    </div>
    
    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="fas fa-sticky-note"></i>
        </div>
        <div class="stat-value"><?php echo $notes_count; ?></div>
        <div class="stat-label">Not Sayısı</div>
        <div class="stat-change">
            <span>Aktif notlar</span>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sol Kolon -->
    <div class="col-md-4">
        <!-- Müşteri Bilgileri -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-info-circle"></i> Müşteri Bilgileri
                </h5>
            </div>
            <div class="card-body">
                <div class="customer-info">
                    <div class="info-item">
                        <label>Şirket Adı:</label>
                        <span><?php echo htmlspecialchars($customer['company_name']); ?></span>
                    </div>
                    
                    <?php if ($customer['contact_person']): ?>
                    <div class="info-item">
                        <label>İletişim Kişisi:</label>
                        <span><?php echo htmlspecialchars($customer['contact_person']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($customer['email']): ?>
                    <div class="info-item">
                        <label>E-posta:</label>
                        <span>
                            <a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>">
                                <?php echo htmlspecialchars($customer['email']); ?>
                            </a>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($customer['phone']): ?>
                    <div class="info-item">
                        <label>Telefon:</label>
                        <span>
                            <a href="tel:<?php echo htmlspecialchars($customer['phone']); ?>">
                                <?php echo htmlspecialchars($customer['phone']); ?>
                            </a>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($customer['address']): ?>
                    <div class="info-item">
                        <label>Adres:</label>
                        <span><?php echo nl2br(htmlspecialchars($customer['address'])); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($customer['tax_office']): ?>
                    <div class="info-item">
                        <label>Vergi Dairesi:</label>
                        <span><?php echo htmlspecialchars($customer['tax_office']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($customer['tax_number']): ?>
                    <div class="info-item">
                        <label>Vergi No:</label>
                        <span><?php echo htmlspecialchars($customer['tax_number']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-item">
                        <label>Kayıt Tarihi:</label>
                        <span><?php echo formatDate($customer['created_at'], 'd.m.Y H:i'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Son Notlar -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-sticky-note"></i> Müşteri Notları
                </h5>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="(function(){var m=document.getElementById('addNoteModal');m.style.display='block';m.classList.add('show');m.removeAttribute('aria-hidden');document.body.classList.add('modal-open');if(!document.querySelector('.modal-backdrop')){var b=document.createElement('div');b.className='modal-backdrop fade show';document.body.appendChild(b);}})()">
                        <i class="fas fa-plus"></i> Ekle
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_notes)): ?>
                    <div class="text-center py-3">
                        <small class="text-muted">Henüz not bulunmuyor</small>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_notes as $note): ?>
                        <div class="note-item" style="border-left-color: <?php echo $note['color']; ?>;">
                            <div class="note-content">
                                <?php echo nl2br(htmlspecialchars(substr($note['note'], 0, 100))); ?>
                                <?php if (strlen($note['note']) > 100): ?>...<?php endif; ?>
                            </div>
                            <div class="note-meta">
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($note['user_name']); ?> • 
                                    <?php echo formatDate($note['created_at'], 'd.m.Y'); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sağ Kolon -->
    <div class="col-md-8">
        <!-- Tabs -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" onclick="
                            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show', 'active'));
                            this.classList.add('active');
                            document.getElementById('interactions').classList.add('show', 'active');
                        " type="button">
                            <i class="fas fa-comments"></i> İletişim Geçmişi
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" onclick="
                            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show', 'active'));
                            this.classList.add('active');
                            document.getElementById('quotes').classList.add('show', 'active');
                        " type="button">
                            <i class="fas fa-file-invoice"></i> Teklifler
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- İletişim Geçmişi -->
                    <div class="tab-pane fade show active" id="interactions" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>İletişim Geçmişi</h6>
                            <button type="button" class="btn btn-sm btn-primary" onclick="(function(){var m=document.getElementById('addInteractionModal');m.style.display='block';m.classList.add('show');m.removeAttribute('aria-hidden');document.body.classList.add('modal-open');if(!document.querySelector('.modal-backdrop')){var b=document.createElement('div');b.className='modal-backdrop fade show';document.body.appendChild(b);}})()">
                                <i class="fas fa-plus"></i> Ekle
                            </button>
                        </div>
                        
                        <?php if (empty($recent_interactions)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-comments fa-2x text-muted mb-2"></i>
                                <p class="text-muted">Henüz iletişim kaydı bulunmuyor</p>
                                <button type="button" class="btn btn-primary btn-sm" onclick="(function(){var m=document.getElementById('addInteractionModal');m.style.display='block';m.classList.add('show');m.removeAttribute('aria-hidden');document.body.classList.add('modal-open');if(!document.querySelector('.modal-backdrop')){var b=document.createElement('div');b.className='modal-backdrop fade show';document.body.appendChild(b);}})()">
                                    <i class="fas fa-plus"></i> İlk Kaydı Oluştur
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="timeline-compact">
                                <?php foreach ($recent_interactions as $interaction): ?>
                                    <div class="timeline-item-compact">
                                        <div class="timeline-marker-compact">
                                            <i class="<?php echo $interaction_types[$interaction['type']]['icon']; ?> text-<?php echo $interaction_types[$interaction['type']]['color']; ?>"></i>
                                        </div>
                                        <div class="timeline-content-compact">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($interaction['subject']); ?></h6>
                                                    <small class="text-muted">
                                                        <span class="badge bg-<?php echo $interaction_types[$interaction['type']]['color']; ?> me-1">
                                                            <?php echo $interaction_types[$interaction['type']]['label']; ?>
                                                        </span>
                                                        <?php echo htmlspecialchars($interaction['user_name']); ?> • 
                                                        <?php echo formatDate($interaction['created_at'], 'd.m.Y H:i'); ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <?php if ($interaction['description']): ?>
                                                <p class="mb-0 mt-2 text-muted small">
                                                    <?php echo nl2br(htmlspecialchars(substr($interaction['description'], 0, 150))); ?>
                                                    <?php if (strlen($interaction['description']) > 150): ?>...<?php endif; ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Teklifler -->
                    <div class="tab-pane fade" id="quotes" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>Son Teklifler</h6>
                            <a href="../quotes/list.php?customer_id=<?php echo $customer_id; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-external-link-alt"></i> Tümünü Gör
                            </a>
                        </div>
                        
                        <?php if (empty($recent_quotes)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-invoice fa-2x text-muted mb-2"></i>
                                <p class="text-muted">Henüz teklif bulunmuyor</p>
                                <a href="../quotes/create.php?customer_id=<?php echo $customer_id; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> İlk Teklifi Oluştur
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Teklif No</th>
                                            <th>Tarih</th>
                                            <th>Tutar</th>
                                            <th>Durum</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_quotes as $quote): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($quote['quote_number']); ?></strong>
                                                </td>
                                                <td><?php echo formatDate($quote['created_at'], 'd.m.Y'); ?></td>
                                                <td>
                                                    <strong><?php echo number_format($quote['calculated_total'] ?? 0, 0, ',', '.'); ?> ₺</strong>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $status_labels[$quote['status']]['class']; ?>">
                                                        <?php echo $status_labels[$quote['status']]['text']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="../quotes/view.php?id=<?php echo $quote['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.customer-info .info-item {
    display: flex;
    margin-bottom: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--border-light);
}

.customer-info .info-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.customer-info .info-item label {
    font-weight: 600;
    color: var(--text-muted);
    min-width: 120px;
    margin-bottom: 0;
    font-size: 0.85rem;
}

.customer-info .info-item span {
    color: var(--text-primary);
    flex: 1;
}

.note-item {
    padding: 0.75rem;
    border-bottom: 1px solid var(--border-light);
    border-left: 3px solid;
}

.note-item:last-child {
    border-bottom: none;
}

.note-content {
    font-size: 0.9rem;
    line-height: 1.4;
    margin-bottom: 0.5rem;
}

.timeline-compact {
    position: relative;
}

.timeline-item-compact {
    display: flex;
    margin-bottom: 1rem;
}

.timeline-marker-compact {
    width: 2rem;
    height: 2rem;
    background: var(--bg-secondary);
    border: 2px solid var(--border-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    margin-right: 1rem;
    flex-shrink: 0;
}

.timeline-content-compact {
    flex: 1;
    padding: 0.5rem 0;
}

.timeline-content-compact h6 {
    font-size: 0.9rem;
    font-weight: 600;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

/* Tab stilleri */
.nav-tabs .nav-link {
    border: 1px solid transparent;
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
    color: var(--text-muted);
    background: none;
    transition: all 0.2s ease;
}

.nav-tabs .nav-link:hover {
    border-color: var(--border-color);
    color: var(--text-primary);
}

.nav-tabs .nav-link.active {
    color: var(--primary-color);
    background-color: var(--bg-primary);
    border-color: var(--border-color) var(--border-color) var(--bg-primary);
}

.tab-content {
    padding: 0;
    background: transparent;
}

.tab-pane {
    padding: 1rem 0;
}

/* Modal butonları için z-index */
.modal {
    z-index: 1055;
}

.modal-backdrop {
    z-index: 1050;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .customer-info .info-item {
        flex-direction: column;
    }
    
    .customer-info .info-item label {
        min-width: auto;
        margin-bottom: 0.25rem;
    }
    
    .nav-tabs {
        flex-wrap: wrap;
    }
    
    .nav-tabs .nav-link {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
}
</style>

<!-- İletişim Ekleme Modalı -->
<div class="modal fade" id="addInteractionModal" tabindex="-1" aria-labelledby="addInteractionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInteractionModalLabel">
                    <i class="fas fa-plus"></i> Yeni İletişim Kaydı Ekle
                </h5>
                <button type="button" class="btn-close" onclick="(function(){var m=document.getElementById('addInteractionModal');m.style.display='none';m.classList.remove('show');m.setAttribute('aria-hidden','true');document.body.classList.remove('modal-open');var b=document.querySelector('.modal-backdrop');if(b)b.remove();})();" aria-label="Close"></button>
            </div>
            <form id="addInteractionForm">
                <div class="modal-body">
                    <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                    
                    <div class="mb-3">
                        <label for="interactionType" class="form-label">İletişim Türü *</label>
                        <select class="form-select" id="interactionType" name="type" required>
                            <option value="">Seçin...</option>
                            <option value="call">Telefon Görüşmesi</option>
                            <option value="email">E-posta</option>
                            <option value="meeting">Toplantı</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="sms">SMS</option>
                            <option value="note">Genel Not</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="interactionSubject" class="form-label">Konu *</label>
                        <input type="text" class="form-control" id="interactionSubject" name="subject" 
                               placeholder="İletişim konusu..." required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="interactionDescription" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="interactionDescription" name="description" rows="4"
                                  placeholder="İletişim detayları..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="(function(){var m=document.getElementById('addInteractionModal');m.style.display='none';m.classList.remove('show');m.setAttribute('aria-hidden','true');document.body.classList.remove('modal-open');var b=document.querySelector('.modal-backdrop');if(b)b.remove();})();">İptal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Not Ekleme Modalı -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addNoteModalLabel">
                    <i class="fas fa-sticky-note"></i> Yeni Not Ekle
                </h5>
                <button type="button" class="btn-close" onclick="(function(){var m=document.getElementById('addNoteModal');m.style.display='none';m.classList.remove('show');m.setAttribute('aria-hidden','true');document.body.classList.remove('modal-open');var b=document.querySelector('.modal-backdrop');if(b)b.remove();})();" aria-label="Close"></button>
            </div>
            <form id="addNoteForm">
                <div class="modal-body">
                    <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                    
                    <div class="mb-3">
                        <label for="noteContent" class="form-label">Not İçeriği *</label>
                        <textarea class="form-control" id="noteContent" name="note" rows="5" 
                                  placeholder="Not içeriği..." required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="noteColor" class="form-label">Renk</label>
                                <select class="form-select" id="noteColor" name="color">
                                    <option value="#fbbf24">Sarı</option>
                                    <option value="#3b82f6">Mavi</option>
                                    <option value="#10b981">Yeşil</option>
                                    <option value="#ef4444">Kırmızı</option>
                                    <option value="#8b5cf6">Mor</option>
                                    <option value="#f97316">Turuncu</option>
                                    <option value="#6b7280">Gri</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="notePrivate" name="is_private" value="1">
                                    <label class="form-check-label" for="notePrivate">
                                        Özel not (sadece ben görebilirim)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="(function(){var m=document.getElementById('addNoteModal');m.style.display='none';m.classList.remove('show');m.setAttribute('aria-hidden','true');document.body.classList.remove('modal-open');var b=document.querySelector('.modal-backdrop');if(b)b.remove();})();">İptal</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<?php require_once '../../includes/footer.php'; ?>

<script>
// Modal fonksiyonları - Global scope'da tanımla
window.openModal = function(modalId) {
    console.log('Modal açılıyor:', modalId);
    const modal = document.getElementById(modalId);
    modal.style.display = 'block';
    modal.classList.add('show');
    modal.removeAttribute('aria-hidden'); // Aria-hidden sorununu çöz
    document.body.classList.add('modal-open');
    
    // Backdrop ekle
    if (!document.querySelector('.modal-backdrop')) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }
};

window.closeModal = function(modalId) {
    console.log('Modal kapatılıyor:', modalId);
    const modal = document.getElementById(modalId);
    modal.style.display = 'none';
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true'); // Aria-hidden'ı geri ekle
    document.body.classList.remove('modal-open');
    
    // Backdrop'u kaldır
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
};

// Form submit işleyicileri
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sayfa yüklendi, form işleyicileri ekleniyor...');
    
    // İletişim ekleme formu
    const interactionForm = document.getElementById('addInteractionForm');
    if (interactionForm) {
        interactionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Kaydediliyor...';
            
            console.log('İletişim formu gönderiliyor...');
            console.log('Form data:', Object.fromEntries(formData));
            
            fetch('add_interaction_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers.get('content-type'));
                
                // Önce text olarak al
                return response.text().then(text => {
                    console.log('Raw response:', text);
                    
                    // JSON parse et
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        console.error('Response text:', text);
                        throw new Error('Sunucu geçersiz yanıt döndürdü. Console\'u kontrol edin.');
                    }
                });
            })
            .then(data => {
                console.log('Parsed data:', data);
                if (data.success) {
                    // Başarı mesajı göster
                    alert('İletişim kaydı başarıyla eklendi!');
                    
                    // Sayfayı yenile
                    window.location.reload();
                } else {
                    alert('Hata: ' + (data.error || 'Bir hata oluştu'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Interaction AJAX Error:', error);
                alert('İletişim ekleme hatası: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // Not ekleme formu
    const noteForm = document.getElementById('addNoteForm');
    if (noteForm) {
        noteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Kaydediliyor...';
            
            console.log('Not formu gönderiliyor...');
            console.log('Form data:', Object.fromEntries(formData));
            
            fetch('add_note_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers.get('content-type'));
                
                // Önce text olarak al
                return response.text().then(text => {
                    console.log('Raw response:', text);
                    
                    // JSON parse et
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        console.error('Response text:', text);
                        throw new Error('Sunucu geçersiz yanıt döndürdü. Console\'u kontrol edin.');
                    }
                });
            })
            .then(data => {
                console.log('Parsed data:', data);
                if (data.success) {
                    // Başarı mesajı göster
                    alert('Not başarıyla eklendi!');
                    
                    // Sayfayı yenile
                    window.location.reload();
                } else {
                    alert('Hata: ' + (data.error || 'Bir hata oluştu'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Note AJAX Error:', error);
                alert('Not ekleme hatası: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});
</script>