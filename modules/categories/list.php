<?php
// modules/categories/list.php
require_once '../../includes/header.php';

// Sadece admin erişebilir
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

// Kategorileri ve her kategorideki ürün sayısını getir
try {
    $stmt = $db->query("
        SELECT 
            pc.id, 
            pc.name, 
            pc.created_at,
            COUNT(p.id) as product_count
        FROM product_categories pc
        LEFT JOIN products p ON pc.id = p.category_id
        GROUP BY pc.id, pc.name, pc.created_at
        ORDER BY pc.name ASC
    ");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    die("Kategoriler getirilirken bir hata oluştu: " . $e->getMessage());
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Ürün Kategorileri</h5>
        <a href="add.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Yeni Kategori Ekle
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Kategori başarıyla eklendi.</div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kategori Adı</th>
                        <th>Ürün Sayısı</th>
                        <th>Oluşturulma Tarihi</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Henüz kategori eklenmemiş.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo $category['product_count']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d.m.Y', strtotime($category['created_at'])); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit.php?id=<?php echo $category['id']; ?>" class="btn btn-warning" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger" 
                                            onclick="deleteCategory(<?php echo $category['id']; ?>, <?php echo $category['product_count']; ?>)"
                                            title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function deleteCategory(id, productCount) {
    if (productCount > 0) {
        alert('Bu kategoriye ait ürünler olduğu için silinemez. Lütfen önce ürünlerin kategorisini değiştirin.');
        return;
    }

    if (confirm('Bu kategoriyi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
        fetch('delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Hata: ' + (data.error || 'Bir hata oluştu.'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('İşlem sırasında bir hata oluştu.');
        });
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>