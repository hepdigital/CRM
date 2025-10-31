<?php
// modules/interactions/list.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

checkAuth();

$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Müşteri bilgisini al
$customer = null;
if ($customer_id) {
    $stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch();
}

// Filtreleme
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$user_filter = isset($_GET['user']) ? $_GET['user'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Sorgu oluştur
$where_conditions = [];
$params = [];

if ($customer_id) {
    $where_conditions[] = "ci.customer_id = ?";
    $params[] = $customer_id;
}

if ($type_filter) {
    $where_conditions[] = "ci.type = ?";
    $params[] = $type_filter;
}

if ($user_filter) {
    $where_conditions[] = "ci.user_id = ?";
    $params[] = $user_filter;
}

if ($date_from) {
    $where_conditions[] = "DATE(ci.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "DATE(ci.created_at) <= ?";
    $params[] = $date_to;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// İletişim kayıtlarını getir
$sql = "
    SELECT 
        ci.*,
        c.company_name,
        u.full_name as user_name
    FROM customer_interactions ci
    JOIN customers c ON ci.customer_id = c.id
    JOIN users u ON ci.user_id = u.id
    $where_clause
    ORDER BY ci.created_at DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$interactions = $stmt->fetchAll();

// Toplam kayıt sayısı
$count_sql = "
    SELECT COUNT(*) as total
    FROM customer_interactions ci
    JOIN customers c ON ci.customer_id = c.id
    JOIN users u ON ci.user_id = u.id
    $where_clause
";
$count_stmt = $db->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetch()['total'];
$total_pages = ceil($total_records / $limit);

// Kullanıcıları getir (filtre için)
$users = $db->query("SELECT id, full_name FROM users ORDER BY full_name")->fetchAll();

// İletişim türleri
$interaction_types = [
    'call' => ['icon' => 'fas fa-phone', 'label' => 'Telefon', 'color' => 'primary'],
    'email' => ['icon' => 'fas fa-envelope', 'label' => 'E-posta', 'color' => 'info'],
    'meeting' => ['icon' => 'fas fa-users', 'label' => 'Toplantı', 'color' => 'success'],
    'note' => ['icon' => 'fas fa-sticky-note', 'label' => 'Not', 'color' => 'warning'],
    'whatsapp' => ['icon' => 'fab fa-whatsapp', 'label' => 'WhatsApp', 'color' => 'success'],
    'sms' => ['icon' => 'fas fa-sms', 'label' => 'SMS', 'color' => 'secondary']
];

$outcome_labels = [
    'positive' => ['label' => 'Olumlu', 'color' => 'success'],
    'negative' => ['label' => 'Olumsuz', 'color' => 'danger'],
    'neutral' => ['label' => 'Nötr', 'color' => 'secondary'],
    'follow_up_needed' => ['label' => 'Takip Gerekli', 'color' => 'warning']
];

require_once '../../includes/header.php';
?>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">
            İletişim Geçmişi
            <?php if ($customer): ?>
                - <?php echo htmlspecialchars($customer['company_name']); ?>
            <?php endif; ?>
        </h1>
        <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Müşteri iletişim kayıtları ve notları</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="add.php<?php echo $customer_id ? '?customer_id=' . $customer_id : ''; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Yeni İletişim
        </a>
        <?php if ($customer_id): ?>
            <a href="../customers/view.php?id=<?php echo $customer_id; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Müşteriye Dön
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Filtreler -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <?php if ($customer_id): ?>
                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
            <?php endif; ?>
            
            <div class="col-md-2">
                <label class="form-label">Tür</label>
                <select name="type" class="form-select">
                    <option value="">Tümü</option>
                    <?php foreach ($interaction_types as $type => $config): ?>
                        <option value="<?php echo $type; ?>" <?php echo $type_filter === $type ? 'selected' : ''; ?>>
                            <?php echo $config['label']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Kullanıcı</label>
                <select name="user" class="form-select">
                    <option value="">Tümü</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo $user_filter == $user['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Başlangıç</label>
                <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Bitiş</label>
                <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrele
                    </button>
                    <a href="list.php<?php echo $customer_id ? '?customer_id=' . $customer_id : ''; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- İletişim Listesi -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($interactions)): ?>
            <div class="text-center py-5">
                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                <h5>Henüz iletişim kaydı bulunmuyor</h5>
                <p class="text-muted">İlk iletişim kaydınızı oluşturmak için yukarıdaki butonu kullanın.</p>
            </div>
        <?php else: ?>
            <div class="timeline">
                <?php foreach ($interactions as $interaction): ?>
                    <div class="timeline-item <?php echo $interaction['is_important'] ? 'important' : ''; ?>">
                        <div class="timeline-marker">
                            <i class="<?php echo $interaction_types[$interaction['type']]['icon']; ?> text-<?php echo $interaction_types[$interaction['type']]['color']; ?>"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <?php echo htmlspecialchars($interaction['subject']); ?>
                                            <?php if ($interaction['is_important']): ?>
                                                <i class="fas fa-star text-warning ms-1" title="Önemli"></i>
                                            <?php endif; ?>
                                        </h6>
                                        <div class="text-muted small">
                                            <span class="badge bg-<?php echo $interaction_types[$interaction['type']]['color']; ?> me-2">
                                                <?php echo $interaction_types[$interaction['type']]['label']; ?>
                                            </span>
                                            <?php if (!$customer_id): ?>
                                                <strong><?php echo htmlspecialchars($interaction['company_name']); ?></strong> •
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($interaction['user_name']); ?> •
                                            <?php echo formatDate($interaction['created_at'], 'd.m.Y H:i'); ?>
                                            <?php if ($interaction['contact_person']): ?>
                                                • <?php echo htmlspecialchars($interaction['contact_person']); ?>
                                            <?php endif; ?>
                                            <?php if ($interaction['duration']): ?>
                                                • <?php echo $interaction['duration']; ?> dk
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="edit.php?id=<?php echo $interaction['id']; ?>">
                                                <i class="fas fa-edit"></i> Düzenle
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="delete.php?id=<?php echo $interaction['id']; ?>" 
                                                   onclick="return confirm('Bu kaydı silmek istediğinizden emin misiniz?')">
                                                <i class="fas fa-trash"></i> Sil
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($interaction['description']): ?>
                                <div class="timeline-body">
                                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($interaction['description'])); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="timeline-footer">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php if ($interaction['outcome'] !== 'neutral'): ?>
                                            <span class="badge bg-<?php echo $outcome_labels[$interaction['outcome']]['color']; ?>">
                                                <?php echo $outcome_labels[$interaction['outcome']]['label']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($interaction['next_action']): ?>
                                        <div class="text-end">
                                            <small class="text-muted">
                                                <strong>Sonraki Aksiyon:</strong> <?php echo htmlspecialchars($interaction['next_action']); ?>
                                                <?php if ($interaction['next_action_date']): ?>
                                                    <br><i class="fas fa-calendar"></i> <?php echo formatDate($interaction['next_action_date'], 'd.m.Y H:i'); ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Sayfalama -->
<?php if ($total_pages > 1): ?>
<nav class="mt-4">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $customer_id ? '&customer_id=' . $customer_id : ''; ?><?php echo $type_filter ? '&type=' . $type_filter : ''; ?><?php echo $user_filter ? '&user=' . $user_filter : ''; ?><?php echo $date_from ? '&date_from=' . $date_from : ''; ?><?php echo $date_to ? '&date_to=' . $date_to : ''; ?>">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<style>
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border-color);
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-item.important .timeline-content {
    border-left: 3px solid var(--warning-color);
    background: rgba(251, 191, 36, 0.05);
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.5rem;
    width: 2rem;
    height: 2rem;
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}

.timeline-content {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 1rem;
    margin-left: 1rem;
}

.timeline-header h6 {
    color: var(--text-primary);
    font-weight: 600;
}

.timeline-body {
    margin: 1rem 0;
    padding-top: 1rem;
    border-top: 1px solid var(--border-light);
}

.timeline-footer {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-light);
}
</style>

<?php require_once '../../includes/footer.php'; ?>