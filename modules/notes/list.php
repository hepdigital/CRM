<?php
// modules/notes/list.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

checkAuth();

$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;

// Müşteri bilgisini al
$customer = null;
if ($customer_id) {
    $stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch();
    
    if (!$customer) {
        header('Location: ../customers/list.php');
        exit;
    }
}

// Notları getir
$where_clause = $customer_id ? 'WHERE cn.customer_id = ?' : 'WHERE (cn.is_private = 0 OR cn.user_id = ?)';
$params = $customer_id ? [$customer_id] : [$_SESSION['user_id']];

$sql = "
    SELECT 
        cn.*,
        c.company_name,
        u.full_name as user_name
    FROM customer_notes cn
    JOIN customers c ON cn.customer_id = c.id
    JOIN users u ON cn.user_id = u.id
    $where_clause
    ORDER BY cn.created_at DESC
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$notes = $stmt->fetchAll();

// Hızlı not ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_note'])) {
    $note_text = trim($_POST['note']);
    $note_customer_id = (int)$_POST['customer_id'];
    $color = $_POST['color'] ?? '#fbbf24';
    $is_private = isset($_POST['is_private']) ? 1 : 0;
    
    if (!empty($note_text) && $note_customer_id) {
        try {
            $stmt = $db->prepare("
                INSERT INTO customer_notes (customer_id, user_id, note, color, is_private)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$note_customer_id, $_SESSION['user_id'], $note_text, $color, $is_private]);
            
            // Sayfayı yenile
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } catch (Exception $e) {
            $error = 'Not eklenirken hata oluştu: ' . $e->getMessage();
        }
    }
}

// Not silme
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $note_id = (int)$_GET['delete'];
    
    // Sadece kendi notunu veya admin silebilir
    $stmt = $db->prepare("SELECT user_id FROM customer_notes WHERE id = ?");
    $stmt->execute([$note_id]);
    $note = $stmt->fetch();
    
    if ($note && ($note['user_id'] == $_SESSION['user_id'] || $_SESSION['role'] === 'admin')) {
        $stmt = $db->prepare("DELETE FROM customer_notes WHERE id = ?");
        $stmt->execute([$note_id]);
        
        header("Location: " . str_replace('&delete=' . $note_id, '', $_SERVER['REQUEST_URI']));
        exit;
    }
}

// Müşterileri getir (hızlı not için)
if (!$customer_id) {
    $customers = $db->query("SELECT id, company_name FROM customers ORDER BY company_name")->fetchAll();
}

require_once '../../includes/header.php';
?>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">
            Müşteri Notları
            <?php if ($customer): ?>
                - <?php echo htmlspecialchars($customer['company_name']); ?>
            <?php endif; ?>
        </h1>
        <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Hızlı notlar ve hatırlatmalar</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <?php if ($customer_id): ?>
            <a href="../customers/view.php?id=<?php echo $customer_id; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Müşteriye Dön
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<!-- Hızlı Not Ekleme -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-plus"></i> Hızlı Not Ekle
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" class="quick-note-form">
            <input type="hidden" name="quick_note" value="1">
            
            <?php if (!$customer_id): ?>
                <div class="mb-3">
                    <label class="form-label">Müşteri</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">Müşteri seçin...</option>
                        <?php foreach ($customers as $cust): ?>
                            <option value="<?php echo $cust['id']; ?>">
                                <?php echo htmlspecialchars($cust['company_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
            <?php endif; ?>
            
            <div class="mb-3">
                <label class="form-label">Not</label>
                <textarea name="note" class="form-control" rows="3" 
                          placeholder="Notunuzu buraya yazın..." required></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Renk</label>
                        <div class="color-picker">
                            <input type="radio" name="color" value="#fbbf24" id="color1" checked>
                            <label for="color1" class="color-option" style="background: #fbbf24;"></label>
                            
                            <input type="radio" name="color" value="#ef4444" id="color2">
                            <label for="color2" class="color-option" style="background: #ef4444;"></label>
                            
                            <input type="radio" name="color" value="#10b981" id="color3">
                            <label for="color3" class="color-option" style="background: #10b981;"></label>
                            
                            <input type="radio" name="color" value="#3b82f6" id="color4">
                            <label for="color4" class="color-option" style="background: #3b82f6;"></label>
                            
                            <input type="radio" name="color" value="#8b5cf6" id="color5">
                            <label for="color5" class="color-option" style="background: #8b5cf6;"></label>
                            
                            <input type="radio" name="color" value="#6b7280" id="color6">
                            <label for="color6" class="color-option" style="background: #6b7280;"></label>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="is_private" class="form-check-input" id="is_private">
                            <label class="form-check-label" for="is_private">
                                <i class="fas fa-lock"></i> Sadece benim görebileceğim
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Not Ekle
            </button>
        </form>
    </div>
</div>

<!-- Notlar -->
<div class="notes-container">
    <?php if (empty($notes)): ?>
        <div class="text-center py-5">
            <i class="fas fa-sticky-note fa-3x text-muted mb-3"></i>
            <h5>Henüz not bulunmuyor</h5>
            <p class="text-muted">İlk notunuzu oluşturmak için yukarıdaki formu kullanın.</p>
        </div>
    <?php else: ?>
        <div class="notes-grid">
            <?php foreach ($notes as $note): ?>
                <div class="note-card" style="border-left-color: <?php echo $note['color']; ?>;">
                    <div class="note-header">
                        <div class="note-info">
                            <?php if (!$customer_id): ?>
                                <h6 class="note-customer"><?php echo htmlspecialchars($note['company_name']); ?></h6>
                            <?php endif; ?>
                            <small class="text-muted">
                                <?php echo htmlspecialchars($note['user_name']); ?> • 
                                <?php echo formatDate($note['created_at'], 'd.m.Y H:i'); ?>
                                <?php if ($note['is_private']): ?>
                                    • <i class="fas fa-lock" title="Özel not"></i>
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="note-actions">
                            <?php if ($note['user_id'] == $_SESSION['user_id'] || $_SESSION['role'] === 'admin'): ?>
                                <a href="?<?php echo $_SERVER['QUERY_STRING']; ?>&delete=<?php echo $note['id']; ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Bu notu silmek istediğinizden emin misiniz?')"
                                   title="Sil">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="note-content">
                        <?php echo nl2br(htmlspecialchars($note['note'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.color-picker {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.color-picker input[type="radio"] {
    display: none;
}

.color-option {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
}

.color-picker input[type="radio"]:checked + .color-option {
    border-color: var(--text-primary);
    transform: scale(1.1);
}

.notes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.note-card {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-left: 4px solid;
    border-radius: var(--radius-md);
    padding: 1rem;
    transition: all 0.2s;
}

.note-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.note-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.note-customer {
    margin: 0 0 0.25rem 0;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-primary);
}

.note-content {
    color: var(--text-secondary);
    line-height: 1.5;
    word-wrap: break-word;
}

.note-actions {
    opacity: 0;
    transition: opacity 0.2s;
}

.note-card:hover .note-actions {
    opacity: 1;
}

@media (max-width: 768px) {
    .notes-grid {
        grid-template-columns: 1fr;
    }
    
    .note-actions {
        opacity: 1;
    }
}
</style>

<script>
// Auto-resize textarea
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('textarea[name="note"]');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>