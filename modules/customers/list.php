<?php
// modules/customers/list.php
require_once '../../includes/header.php';

// Arama ve filtreleme
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
$params = array();

if (!empty($search)) {
    $where = "WHERE company_name LIKE :search 
              OR contact_person LIKE :search 
              OR email LIKE :search 
              OR phone LIKE :search";
    $params[':search'] = "%{$search}%";
}

// Sayfalama
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$start = ($page - 1) * $perPage;

// Toplam kayıt sayısı
$stmt = $db->prepare("SELECT COUNT(*) FROM customers " . $where);
$stmt->execute($params);
$totalRecords = $stmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Müşterileri getir
$stmt = $db->prepare("
    SELECT 
        c.*,
        COUNT(q.id) as total_quotes,
        SUM(CASE WHEN q.status = 'accepted' THEN 1 ELSE 0 END) as accepted_quotes
    FROM customers c
    LEFT JOIN quotes q ON c.id = q.customer_id
    {$where}
    GROUP BY c.id
    ORDER BY c.created_at DESC 
    LIMIT :start, :perPage
");

$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$customers = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Müşteri Listesi</h5>
        <a href="add.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Yeni Müşteri Ekle
        </a>
    </div>
    <div class="card-body">
        <!-- Arama formu -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" 
                       placeholder="Müşteri adı, kişi, e-posta veya telefon ile ara..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="fas fa-search"></i> Ara
                </button>
            </div>
        </form>

        <!-- Müşteri tablosu -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Firma Adı</th>
                        <th>İletişim Kişisi</th>
                        <th>E-posta</th>
                        <th>Telefon</th>
                        <th>Toplam Teklif</th>
                        <th>Kabul Edilen</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td>
                            <a href="view.php?id=<?php echo $customer['id']; ?>" class="text-decoration-none fw-bold">
                                <?php echo htmlspecialchars($customer['company_name']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($customer['contact_person']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                        <td><?php echo $customer['total_quotes']; ?></td>
                        <td><?php echo $customer['accepted_quotes']; ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="edit.php?id=<?php echo $customer['id']; ?>" 
                                   class="btn btn-warning" title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="../quotes/create.php?customer_id=<?php echo $customer['id']; ?>" 
                                   class="btn btn-success" title="Yeni Teklif Oluştur">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </a>
                                <button type="button" class="btn btn-danger" title="Sil"
                                        onclick="deleteCustomer(<?php echo $customer['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="7" class="text-center">Müşteri bulunamadı.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Sayfalama -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteCustomer(id) {
    if (confirm('Bu müşteriyi silmek istediğinizden emin misiniz?')) {
        fetch('delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Hata: ' + (data.error || 'Bir hata oluştu'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu');
        });
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>