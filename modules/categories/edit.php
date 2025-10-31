<?php
// modules/categories/edit.php
require_once '../../includes/header.php';

// Sadece admin erişebilir
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}

$id = (int)$_GET['id'];
$message = '';
$messageType = '';

// Form gönderildiğinde kategoriyi güncelle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $message = 'Kategori adı zorunludur.';
        $messageType = 'danger';
    } else {
        try {
            // Başka bir kategoride aynı isim var mı kontrol et
            $stmt = $db->prepare("SELECT COUNT(*) FROM product_categories WHERE name = ? AND id != ?");
            $stmt->execute([$name, $id]);
            if ($stmt->fetchColumn() > 0) {
                $message = 'Bu kategori adı zaten kullanılıyor.';
                $messageType = 'danger';
            } else {
                // Kategoriyi güncelle
                $stmt = $db->prepare("UPDATE product_categories SET name = ? WHERE id = ?");
                $stmt->execute([$name, $id]);
                
                $message = 'Kategori başarıyla güncellendi.';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = 'Bir hata oluştu: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// ---- SAYFANIN HER YÜKLENMESİNDE ÇALIŞACAK KODLAR ----

// Kategori bilgilerini her zaman en güncel haliyle getir
try {
    $stmt = $db->prepare("SELECT * FROM product_categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();

    if (!$category) {
        header('Location: list.php');
        exit;
    }
} catch (Exception $e) {
    die("Kategori bilgisi alınamadı: " . $e->getMessage());
}

// Bu kategoriye ait olmayan ürünleri her zaman getir
try {
    $products_to_add_stmt = $db->prepare("
        SELECT id, name, stock_quantity 
        FROM products 
        WHERE category_id IS NULL OR category_id != ?
        ORDER BY name
    ");
    $products_to_add_stmt->execute([$id]);
    $products_to_add = $products_to_add_stmt->fetchAll();
} catch (Exception $e) {
    $products_to_add = [];
    $message = 'Eklenecek ürünler getirilirken bir hata oluştu.';
    $messageType = 'danger';
}

// Bu kategoriye ait olan ürünleri her zaman getir
try {
    $stmt_products = $db->prepare("SELECT id, name, price, stock_quantity FROM products WHERE category_id = ? ORDER BY name");
    $stmt_products->execute([$id]);
    $products_in_category = $stmt_products->fetchAll();
} catch (Exception $e) {
    $products_in_category = [];
    echo '<div class="alert alert-danger">Ürünler yüklenirken bir hata oluştu.</div>';
}

?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Kategori Düzenle</h5>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label">Kategori Adı *</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?php echo htmlspecialchars($category['name']); ?>" required>
                <div class="invalid-feedback">
                    Lütfen bir kategori adı girin.
                </div>
            </div>

            <div class="mt-4">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus"></i> Bu Kategoriye Ürün Ekle
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Güncelle
                </button>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri
                </a>
            </div>
        </form>

        <hr class="my-4">
        <h5 class="mb-3">Bu Kategorideki Ürünler</h5>
        
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Fiyat</th>
                        <th>Stok</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products_in_category)): ?>
                        <tr>
                            <td colspan="4" class="text-center">Bu kategoride ürün bulunmuyor.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products_in_category as $prod): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prod['name']); ?></td>
                                <td><?php echo number_format($prod['price'], 2, ',', '.'); ?> TL</td>
                                <td><?php echo $prod['stock_quantity']; ?></td>
                                <td>
                                    <a href="../products/edit.php?id=<?php echo $prod['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        Ürünü Düzenle
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addProductModalLabel">"<?php echo htmlspecialchars($category['name']); ?>" Kategorisine Ürün Ekle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <div class="mb-3">
            <input type="text" id="modalProductSearch" class="form-control" placeholder="Eklenecek ürünü ara...">
        </div>
        <p>Bu kategoriye eklemek istediğiniz ürünleri seçin.</p>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px;">Seç</th>
                        <th>Ürün Adı</th>
                        <th>Mevcut Stok</th>
                    </tr>
                </thead>
                <tbody id="modalProductList">
                    <?php if (empty($products_to_add)): ?>
                        <tr class="no-results-row">
                            <td colspan="3" class="text-center">Eklenecek yeni ürün bulunmuyor.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products_to_add as $product_add): ?>
                            <tr class="product-row-modal">
                                <td>
                                    <input class="form-check-input" type="checkbox" value="<?php echo $product_add['id']; ?>" name="product_ids[]">
                                </td>
                                <td class="product-name-modal"><?php echo htmlspecialchars($product_add['name']); ?></td>
                                <td><?php echo $product_add['stock_quantity']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
        <button type="button" class="btn btn-primary" onclick="assignProductsToCategory()">Seçilenleri Ekle</button>
      </div>
    </div>
  </div>
</div>

<script>
// Form doğrulama (Bootstrap için)
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

// Modal içi arama fonksiyonu
document.getElementById('modalProductSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#modalProductList .product-row-modal');
    
    rows.forEach(row => {
        const productName = row.querySelector('.product-name-modal').textContent.toLowerCase();
        if (productName.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Seçilen ürünleri kategoriye atama fonksiyonu
function assignProductsToCategory() {
    const checkboxes = document.querySelectorAll('#addProductModal input[name="product_ids[]"]:checked');
    const productIds = Array.from(checkboxes).map(cb => cb.value);

    if (productIds.length === 0) {
        alert('Lütfen en az bir ürün seçin.');
        return;
    }

    const categoryId = <?php echo $id; ?>;

    fetch('assign_products.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            category_id: categoryId,
            product_ids: productIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Ürünler kategoriye başarıyla eklendi.');
            window.location.reload(); // Sayfayı yenileyerek güncel listeyi göster
        } else {
            alert('Hata: ' + (data.error || 'Bilinmeyen bir hata oluştu.'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('İşlem sırasında bir ağ hatası oluştu.');
    });
}
</script>

<?php require_once '../../includes/footer.php'; ?>