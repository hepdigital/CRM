<?php
// modules/products/list.php
require_once '../../includes/header.php';

// Tüm ürünleri getir (basit versiyon)
$stmt = $db->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();
?>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">Ürün Listesi</h1>
        <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Tüm ürünlerinizi yönetin ve düzenleyin</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="import.php" class="btn btn-secondary">
            <i class="fas fa-file-import"></i> CSV İçe Aktar
        </a>
        <a href="export.php" class="btn btn-success">
            <i class="fas fa-file-export"></i> CSV Dışa Aktar
        </a>
        <a href="image_manager.php" class="btn btn-info">
            <i class="fas fa-images"></i> Görsel Yöneticisi
        </a>
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Yeni Ürün Ekle
        </a>
    </div>
</div>

<!-- Modern Table Container -->
<div class="table-container">
    <div class="table-header">
        <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
            <div style="position: relative; flex: 1; max-width: 420px;">
                <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                <input type="text" id="productSearch" class="search-input" 
                       placeholder="Ürün adı ara..." 
                       autocomplete="off"
                       style="padding-left: 2.5rem;">
            </div>
            <div style="color: var(--text-muted); font-size: 0.9rem; margin-left: auto;">
                <i class="fas fa-info-circle"></i> Toplam <?php echo number_format(count($products)); ?> ürün
            </div>
        </div>
    </div>
    
    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">Görsel</th>
                    <th>Ürün Adı</th>
                    <th style="width: 120px;">Stok</th>
                    <th style="width: 120px;">Fiyat</th>
                    <th style="width: 80px;">KDV</th>
                    <th style="width: 120px;">İşlemler</th>
                </tr>
            </thead>
            <tbody id="productTableBody">
                <?php foreach ($products as $product): ?>
                <tr class="product-row" data-id="<?php echo $product['id']; ?>">
                    <td>
                        <?php if (!empty($product['image_url'])): ?>
                        <div style="width: 60px; height: 60px; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--border-color);">
                            <img src="<?php echo htmlspecialchars(getImageUrl($product['image_url'])); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;" loading="lazy"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display: none; width: 100%; height: 100%; background: var(--bg-tertiary); align-items: center; justify-content: center; color: var(--text-muted);">
                                <i class="fas fa-image"></i>
                            </div>
                        </div>
                        <?php else: ?>
                            <div style="width: 60px; height: 60px; background: var(--bg-tertiary); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--text-muted); border: 1px solid var(--border-color);">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div>
                            <div class="product-name" style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                Stok Kodu: <?php echo htmlspecialchars($product['stock_code'] ?? '—'); ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="text-align: center;">
                            <div style="font-weight: 600; color: var(--text-primary); font-size: 1.1rem;">
                                <?php echo number_format($product['stock_quantity']); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">adet</div>
                        </div>
                    </td>
                    <td>
                        <div style="text-align: center;">
                            <div style="font-weight: 600; color: var(--text-primary); font-size: 1.1rem;">
                                <?php echo number_format($product['price'], 2, ',', '.'); ?> ₺
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="text-align: center;">
                            <span class="badge badge-<?php echo $product['vat_rate'] > 0 ? 'primary' : 'secondary'; ?>">
                                %<?php echo number_format($product['vat_rate'], 0); ?>
                            </span>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <a href="edit.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-sm btn-warning" title="Düzenle">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" 
                                    onclick="deleteProduct(<?php echo $product['id']; ?>)"
                                    title="Sil">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: var(--text-muted);"></i>
                        <div style="font-size: 1.1rem; margin-bottom: 0.5rem;">Henüz ürün bulunmuyor</div>
                        <div style="font-size: 0.9rem;">İlk ürününüzü eklemek için yukarıdaki "Yeni Ürün Ekle" butonunu kullanın.</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Anlık arama fonksiyonu
document.getElementById('productSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.product-row');
    
    rows.forEach(row => {
        const productName = row.querySelector('.product-name').textContent.toLowerCase();
        if (productName.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Ürün silme fonksiyonu
function deleteProduct(id) {
    if (confirm('Bu ürünü silmek istediğinizden emin misiniz?')) {
        fetch('delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: 'id=' + id
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Hata: ' + (data.error || 'Bir hata oluştu'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('İşlem sırasında bir hata oluştu');
        });
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>