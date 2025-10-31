<?php
// modules/quotes/list.php
require_once '../../includes/header.php';

// Filtreler
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$dateStart = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$dateEnd = isset($_GET['date_end']) ? $_GET['date_end'] : '';

// WHERE koşulları
// WHERE koşulları
$where = [];
$params = [];

// Firma/Müşteri araması
if (!empty($_GET['search'])) {
    $where[] = "(c.company_name LIKE :search OR q.quote_number LIKE :search)";
    $params[':search'] = "%{$_GET['search']}%";
}

// Durum filtresi
if (!empty($_GET['status']) && $_GET['status'] !== 'all') {
    $where[] = "q.status = :status";
    $params[':status'] = $_GET['status'];
}

// Tarih aralığı filtreleri
if (!empty($_GET['date_start'])) {
    $where[] = "DATE(q.created_at) >= :date_start";
    $params[':date_start'] = $_GET['date_start'];
}

if (!empty($_GET['date_end'])) {
    $where[] = "DATE(q.created_at) <= :date_end";
    $params[':date_end'] = $_GET['date_end'];
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Sayfalama
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Toplam kayıt sayısı
$countStmt = $db->prepare("SELECT COUNT(*) FROM quotes q JOIN customers c ON q.customer_id = c.id {$whereClause}");
foreach ($params as $key => $value) { $countStmt->bindValue($key, $value); }
$countStmt->execute();
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

// Teklifleri getir (sayfalı)
$stmt = $db->prepare("
    SELECT 
        q.*,
        c.company_name,
        c.contact_person,
        u.full_name as created_by_name, -- EKLENDİ
        (SELECT COUNT(*) FROM quote_items qi WHERE qi.quote_id = q.id) as item_count,
        (
            SELECT SUM(
                (qi.unit_price * qi.quantity) + 
                (qi.unit_price * qi.quantity * qi.vat_rate / 100)
            )
            FROM quote_items qi 
            WHERE qi.quote_id = q.id
        ) as calculated_total
    FROM quotes q
    JOIN customers c ON q.customer_id = c.id
    LEFT JOIN users u ON q.created_by = u.id -- EKLENDİ
    {$whereClause}
    ORDER BY q.created_at DESC
    LIMIT :limit OFFSET :offset
");

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$quotes = $stmt->fetchAll();

// Durum açıklamaları
$statusLabels = [
    'draft' => ['text' => 'Taslak', 'class' => 'secondary'],
    'sent' => ['text' => 'Gönderildi', 'class' => 'primary'],
    'accepted' => ['text' => 'Kabul Edildi', 'class' => 'success'],
    'rejected' => ['text' => 'Reddedildi', 'class' => 'danger']
];
?>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">Teklif Listesi</h1>
        <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Tüm tekliflerinizi yönetin ve takip edin</p>
    </div>
    <div>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Yeni Teklif Oluştur
        </a>
    </div>
</div>

<!-- Modern Filters -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-filter"></i> Filtreler
        </h5>
    </div>
    <div class="card-body">
        <form method="GET">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-primary);">Arama</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Firma adı veya teklif no..."
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-primary);">Durum</label>
                    <select name="status" class="form-control">
                        <option value="all">Tüm Durumlar</option>
                        <?php foreach ($statusLabels as $key => $label): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo isset($_GET['status']) && $_GET['status'] === $key ? 'selected' : ''; ?>>
                                <?php echo $label['text']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-primary);">Başlangıç</label>
                    <input type="date" name="date_start" class="form-control" 
                           value="<?php echo $_GET['date_start'] ?? ''; ?>">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-primary);">Bitiş</label>
                    <input type="date" name="date_end" class="form-control" 
                           value="<?php echo $_GET['date_end'] ?? ''; ?>">
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrele
                    </button>
                    <a href="list.php" class="btn btn-secondary">
                        <i class="fas fa-sync"></i> Sıfırla
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modern Table Container -->
<div class="table-container">
    <div class="table-header">
        <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
            <div style="color: var(--text-muted); font-size: 0.9rem;">
                <i class="fas fa-file-invoice"></i> Toplam <?php echo number_format($total); ?> teklif
            </div>
        </div>
    </div>
    
    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Teklif No</th>
                    <th>Müşteri</th>
                    <th style="width: 100px;">Tarih</th>
                    <th style="width: 100px;">Kalem</th>
                    <th style="width: 120px;">Tutar</th>
                    <th style="width: 100px;">Durum</th>
                    <th style="width: 120px;">Oluşturan</th>
                    <th style="width: 140px;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quotes as $quote): ?>
                <tr>
                    <td>
                        <div>
                            <div style="font-weight: 600; color: var(--text-primary);">
                                <?php echo htmlspecialchars($quote['quote_number']); ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>
                            <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">
                                <?php echo htmlspecialchars($quote['company_name']); ?>
                            </div>
                            <?php if ($quote['contact_person']): ?>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($quote['contact_person']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div style="text-align: center;">
                            <div style="font-weight: 500; color: var(--text-primary);">
                                <?php echo date('d.m.Y', strtotime($quote['created_at'])); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                <?php echo date('H:i', strtotime($quote['created_at'])); ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="text-align: center;">
                            <div style="font-weight: 600; color: var(--text-primary); font-size: 1.1rem;">
                                <?php echo $quote['item_count']; ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">kalem</div>
                        </div>
                    </td>
                    <td>
                        <div style="text-align: center;">
                            <div style="font-weight: 600; color: var(--text-primary); font-size: 1.1rem;">
                                <?php echo number_format($quote['calculated_total'], 0, ',', '.'); ?> ₺
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="text-align: center;">
                            <span class="badge badge-<?php echo $statusLabels[$quote['status']]['class']; ?>">
                                <?php echo $statusLabels[$quote['status']]['text']; ?>
                            </span>
                        </div>
                    </td>
                    <td>
                        <div style="text-align: center;">
                            <div style="font-weight: 500; color: var(--text-primary);">
                                <?php echo htmlspecialchars($quote['created_by_name'] ?? 'Sistem'); ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <a href="view.php?id=<?php echo $quote['id']; ?>" 
                               class="btn btn-sm btn-info" title="Görüntüle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($quote['status'] === 'draft'): ?>
                            <a href="edit.php?id=<?php echo $quote['id']; ?>" 
                               class="btn btn-sm btn-warning" title="Düzenle">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <a href="generate_pdf.php?id=<?php echo $quote['id']; ?>" 
                               class="btn btn-sm btn-secondary" title="PDF İndir" target="_blank">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            <?php if ($quote['status'] === 'draft'): ?>
                            <button type="button" class="btn btn-sm btn-danger" 
                                    onclick="deleteQuote(<?php echo $quote['id']; ?>)"
                                    title="Sil">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($quotes)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i class="fas fa-file-invoice" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: var(--text-muted);"></i>
                        <div style="font-size: 1.1rem; margin-bottom: 0.5rem;">Henüz teklif bulunmuyor</div>
                        <div style="font-size: 0.9rem;">İlk teklifinizi oluşturmak için yukarıdaki "Yeni Teklif Oluştur" butonunu kullanın.</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<nav aria-label="Sayfalama" style="margin-top: 1rem;">
    <ul class="pagination">
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo max(1, $page - 1); ?>">Önceki</a>
        </li>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $p; ?>"><?php echo $p; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo min($totalPages, $page + 1); ?>">Sonraki</a>
        </li>
    </ul>
    <div class="text-muted" style="margin-top: .25rem;">
        Sayfa <?php echo $page; ?> / <?php echo $totalPages; ?> — Toplam <?php echo number_format($total); ?> teklif
    </div>
</nav>
<?php endif; ?>

<script>
function deleteQuote(id) {
    if (confirm('Bu teklifi silmek istediğinizden emin misiniz?')) {
        fetch('delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ id: id })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Hata: ' + (data.error || 'Bir hata oluştu'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu: ' + error.message);
        });
    }
}
    
function filterQuotes() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const dateStart = document.getElementById('dateStart').value;
    const dateEnd = document.getElementById('dateEnd').value;

    document.querySelectorAll('tbody tr').forEach(row => {
        const companyName = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        const quoteNumber = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
        const rowStatus = row.querySelector('.badge')?.textContent.toLowerCase() || '';
        const rowDate = row.querySelector('td:nth-child(3)')?.textContent || '';

        let show = true;

        // Arama filtresi
        if (searchTerm) {
            show = companyName.includes(searchTerm) || quoteNumber.includes(searchTerm);
        }

        // Durum filtresi
        if (show && status !== 'all') {
            show = rowStatus.includes(status.toLowerCase());
        }

        // Tarih filtreleri
        if (show && dateStart) {
            show = new Date(rowDate) >= new Date(dateStart);
        }
        if (show && dateEnd) {
            show = new Date(rowDate) <= new Date(dateEnd);
        }

        row.style.display = show ? '' : 'none';
    });
}

// Event listener'ları ekleyelim
const si = document.getElementById('searchInput'); if (si) si.addEventListener('input', filterQuotes);
const sf = document.getElementById('statusFilter'); if (sf) sf.addEventListener('change', filterQuotes);
const ds = document.getElementById('dateStart'); if (ds) ds.addEventListener('change', filterQuotes);
const de = document.getElementById('dateEnd'); if (de) de.addEventListener('change', filterQuotes);

// Filtreleri sıfırlama fonksiyonu
function resetFilters() {
    const si = document.getElementById('searchInput'); if (si) si.value = '';
    const sf = document.getElementById('statusFilter'); if (sf) sf.value = 'all';
    const ds = document.getElementById('dateStart'); if (ds) ds.value = '';
    const de = document.getElementById('dateEnd'); if (de) de.value = '';
    filterQuotes();
}
</script>

<?php require_once '../../includes/footer.php'; ?>