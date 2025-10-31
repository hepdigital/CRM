<?php
// modules/quotes/edit.php
ob_start();
require_once '../../includes/header.php';

$message = '';
$messageType = '';

// ID kontrolü
if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}

$id = (int)$_GET['id'];

// Teklif bilgilerini getir
try {
    $stmt = $db->prepare("
        SELECT q.*, c.company_name, c.contact_person
        FROM quotes q
        JOIN customers c ON q.customer_id = c.id
        WHERE q.id = ? AND q.status = 'draft'
    ");
    $stmt->execute([$id]);
    $quote = $stmt->fetch();

    if (!$quote) {
        header('Location: list.php');
        exit;
    }

    // Teklif kalemlerini getir
    $stmt = $db->prepare("
        SELECT qi.*, p.name as product_name, p.image_url, p.stock_quantity
        FROM quote_items qi
        JOIN products p ON qi.product_id = p.id
        WHERE qi.quote_id = ?
        ORDER BY qi.id
    ");
    $stmt->execute([$id]);
    $quoteItems = $stmt->fetchAll();

} catch (Exception $e) {
    die("Teklif bilgileri alınamadı: " . $e->getMessage());
}

// Tüm müşterileri getir
$stmt = $db->query("SELECT id, company_name, contact_person FROM customers ORDER BY company_name");
$customers = $stmt->fetchAll();

$stmt_cat = $db->query("SELECT * FROM product_categories ORDER BY name");
$categories = $stmt_cat->fetchAll();

// Tüm ürünleri getir
$stmt = $db->query("
    SELECT 
        p.id, 
        p.name, 
        p.price, 
        p.stock_quantity, 
        p.vat_rate, 
        p.image_url,
        p.category_id,
        pc.name as category_name
    FROM products p
    LEFT JOIN product_categories pc ON p.category_id = pc.id
    WHERE p.stock_quantity > 0 
    ORDER BY pc.name, p.name
");
$products = $stmt->fetchAll();

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        $customerId = (int)$_POST['customer_id'];
        $validUntil = !empty($_POST['valid_until']) ? $_POST['valid_until'] : null;
        $notes = isset($_POST['notes']) ? $_POST['notes'] : [];

        // Toplam tutarları hesapla
        $totalAmount = 0;
        $vatAmount = 0;

        // Eski teklif kalemlerini sil
        $stmt = $db->prepare("DELETE FROM quote_items WHERE quote_id = ?");
        $stmt->execute([$id]);

        // Yeni teklif kalemlerini ekle
        if (isset($_POST['products']) && is_array($_POST['products'])) {
            $stmt = $db->prepare("
                INSERT INTO quote_items (
                    quote_id, product_id, quantity, unit_price,
                    vat_rate, total_amount
                ) VALUES (
                    :quote_id, :product_id, :quantity, :unit_price,
                    :vat_rate, :total_amount
                )
            ");

            foreach ($_POST['products'] as $item) {
    $productId = isset($item['product_id']) ? (int)$item['product_id'] : 0;
    $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
    $price = isset($item['price']) ? floatval($item['price']) : 0;

    // Ürün kontrolü sadece VAT rate için
    $productStmt = $db->prepare("SELECT vat_rate FROM products WHERE id = ?");
    $productStmt->execute([$productId]);
    $product = $productStmt->fetch();

    if (!$product || $quantity <= 0) {
        continue;
    }

    $vatRate = floatval($product['vat_rate']);
    $itemSubtotal = $quantity * $price;
    $itemVat = $itemSubtotal * ($vatRate / 100);
    $itemTotal = $itemSubtotal + $itemVat;

    $stmt->execute([
        ':quote_id' => $id,
        ':product_id' => $productId,
        ':quantity' => $quantity,
        ':unit_price' => $price,
        ':vat_rate' => $vatRate,
        ':total_amount' => $itemTotal
    ]);
}
        }

        // Teklifi güncelle
        $stmt = $db->prepare("
            UPDATE quotes 
            SET customer_id = :customer_id,
                total_amount = :total_amount,
                vat_amount = :vat_amount,
                valid_until = :valid_until,
                notes = :notes
            WHERE id = :id
        ");

        $stmt->execute([
            ':customer_id' => $customerId,
            ':total_amount' => $totalAmount,
            ':vat_amount' => $vatAmount,
            ':valid_until' => $validUntil,
            ':notes' => implode("\n", array_filter($notes)),
            ':id' => $id
        ]);

        $db->commit();

if (isset($_POST['generate_pdf'])) {
    header("Location: generate_pdf.php?id=" . $id);  // $quoteId yerine $id
    exit;
}

header("Location: view.php?id=" . $id . "&success=1");  // $quoteId yerine $id
exit;

    } catch (Exception $e) {
        $db->rollBack();
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
        error_log('Teklif güncelleme hatası: ' . $e->getMessage());
    }
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Teklif Düzenle</h5>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form method="POST" id="quoteForm" class="needs-validation" novalidate>
            <div class="row">
                <!-- Sol Kolon: Müşteri Bilgileri ve Genel Ayarlar -->
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Müşteri Bilgileri</h6>
                            <div class="mb-3">
                                <label class="form-label">Müşteri Seçin *</label>
                                <select name="customer_id" class="form-select" required>
                                    <option value="">Seçin...</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo $customer['id']; ?>"
                                                <?php echo $quote['customer_id'] == $customer['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($customer['company_name']); ?>
                                            <?php if ($customer['contact_person']): ?>
                                                (<?php echo htmlspecialchars($customer['contact_person']); ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Geçerlilik Tarihi</label>
                                <input type="date" name="valid_until" class="form-control"
                                       value="<?php echo $quote['valid_until'] ? date('Y-m-d', strtotime($quote['valid_until'])) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Teklif Notları</label>
                                <div id="notesContainer">
                                    <?php 
                                    $notes = explode("\n", $quote['notes']);
                                    foreach ($notes as $note): 
                                        if (trim($note)):
                                    ?>
                                        <div class="input-group mb-2">
                                            <input type="text" name="notes[]" class="form-control" 
                                                   value="<?php echo htmlspecialchars($note); ?>">
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="removeNote(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="addNote()">
                                    <i class="fas fa-plus"></i> Not Ekle
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sağ Kolon: Ürün Seçimi ve Liste -->
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Ürün Seçimi</h6>
                            
                            <!-- Ürün Arama -->
                            <div class="row g-2 mb-3">
    <div class="col-md-6">
        <input type="text" id="productSearch" class="form-control" placeholder="Ürün ara...">
    </div>
    <div class="col-md-6">
        <select id="categoryFilter" class="form-select">
            <option value="all">Tüm Kategoriler</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

                            <!-- Ürün Grid -->
                            <div class="row">
    <div class="col-12" style="height: 400px; overflow-y: auto;">
        <div class="row row-cols-1 row-cols-md-4 g-3" id="productGrid">
    <?php 
    $currentCategory = null;
    foreach ($products as $product): 
        // Kategori değiştiğinde yeni bir başlık yazdır
        if ($product['category_name'] !== $currentCategory):
            $currentCategory = $product['category_name'];
    ?>
        <div class="col-12 mt-3">
            <h5 class="border-bottom pb-2"><?php echo htmlspecialchars($currentCategory ?: 'Diğer Ürünler'); ?></h5>
        </div>
    <?php endif; ?>

    <div class="col product-item" 
     data-name="<?php echo htmlspecialchars(strtolower($product['name'])); ?>"
     data-category-id="<?php echo $product['category_id'] ?: '0'; ?>">
     <div class="card h-100">
            <?php if ($product['image_url']): ?>
                <img src="<?php echo htmlspecialchars(getImageUrl($product['image_url'])); ?>" 
                     class="card-img-top p-2" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     style="height: 80px; object-fit: contain;"
                     onerror="this.style.display='none';">
            <?php else: ?>
                <div class="card-img-top p-2 d-flex align-items-center justify-content-center bg-light" 
                     style="height: 80px;">
                    <i class="fas fa-image text-muted"></i>
                </div>
            <?php endif; ?>
            <div class="card-body p-2">
                <h6 class="card-title" style="font-size: 0.9rem;"><?php echo htmlspecialchars($product['name']); ?></h6>
                <p class="card-text mb-1">
                    <small class="text-muted">Stok: <?php echo $product['stock_quantity']; ?> adet</small>
                    <br>
                    <strong><?php echo number_format($product['price'], 2, ',', '.'); ?> TL</strong>
                    <small class="text-muted">(+%<?php echo $product['vat_rate']; ?> KDV)</small>
                </p>
                <button type="button" class="btn btn-sm btn-primary w-100" 
                        onclick="addProductToQuote(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                    <i class="fas fa-plus"></i> Ekle
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
    </div>
</div>

                            <!-- Seçilen Ürünler Tablosu -->
                            <div class="table-responsive">
                                <table class="table table-bordered" id="selectedProducts">
                                    <thead>
                                        <tr>
                                            <th>Ürün</th>
                                            <th style="width: 100px;">Adet</th>
                                            <th style="width: 150px;">Birim Fiyat</th>
                                            <th style="width: 100px;">KDV</th>
                                            <th style="width: 150px;">Toplam</th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($quoteItems as $item): ?>
<tr id="product-<?php echo $item['product_id']; ?>">
    <td>
        <?php echo htmlspecialchars($item['product_name']); ?>
        <input type="hidden" name="products[<?php echo $item['product_id']; ?>][product_id]" value="<?php echo $item['product_id']; ?>">
        <input type="hidden" name="products[<?php echo $item['product_id']; ?>][quantity]" class="hidden-quantity" value="<?php echo $item['quantity']; ?>">
        <input type="hidden" name="products[<?php echo $item['product_id']; ?>][price]" class="hidden-price" value="<?php echo $item['unit_price']; ?>">
    </td>
    <td>
        <input type="number" class="form-control form-control-sm quantity-input" 
               value="<?php echo $item['quantity']; ?>" min="1" 
               max="<?php echo $item['stock_quantity']; ?>">
    </td>
    <td>
        <input type="number" class="form-control form-control-sm price-input" 
               value="<?php echo $item['unit_price']; ?>" min="0" step="0.01">
    </td>
    <td class="text-end">%<?php echo $item['vat_rate']; ?></td>
    <td class="text-end row-total">
        <?php echo number_format($item['total_amount'], 2, ',', '.'); ?> TL
    </td>
    <td>
        <button type="button" class="btn btn-sm btn-danger" onclick="removeProduct(this)">
            <i class="fas fa-trash"></i>
        </button>
    </td>
</tr>
<?php endforeach; ?>
                                        <?php if (empty($quoteItems)): ?>
                                        <tr id="noProductRow">
                                            <td colspan="6" class="text-center">Henüz ürün eklenmedi.</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-end">Ara Toplam:</td>
                                            <td colspan="2" id="subtotal">
                                                <?php echo number_format($quote['total_amount'] - $quote['vat_amount'], 2, ',', '.'); ?> TL
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end">KDV Toplam:</td>
                                            <td colspan="2" id="vatTotal">
                                                <?php echo number_format($quote['vat_amount'], 2, ',', '.'); ?> TL
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>Genel Toplam:</strong></td>
                                            <td colspan="2" id="grandTotal">
                                                <strong><?php echo number_format($quote['total_amount'], 2, ',', '.'); ?> TL</strong>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Butonları -->
            <div class="mt-4">
                <button type="submit" name="save" class="btn btn-primary" onclick="return validateForm()">
                    <i class="fas fa-save"></i> Kaydet
                </button>
                <button type="submit" name="generate_pdf" class="btn btn-success" onclick="return validateForm()">
                    <i class="fas fa-file-pdf"></i> Kaydet ve PDF İndir
                </button>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Sayfa tamamen yüklendiğinde çalışacak kodlar
document.addEventListener('DOMContentLoaded', function() {
    const productSearchInput = document.getElementById('productSearch');
    const categoryFilterSelect = document.getElementById('categoryFilter');

    // Arama ve kategori filtresini BİRLİKTE yöneten ana fonksiyon
    function filterProducts() {
        const searchTerm = productSearchInput.value.toLowerCase();
        const selectedCategory = categoryFilterSelect.value;

        document.querySelectorAll('.product-item').forEach(item => {
            const productName = item.dataset.name;
            const productCategory = item.dataset.categoryId;

            // Koşulları kontrol et
            const nameMatch = productName.includes(searchTerm);
            const categoryMatch = (selectedCategory === 'all' || productCategory === selectedCategory);

            // Ürün sadece her iki koşul da doğru ise gösterilir
            if (nameMatch && categoryMatch) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Hem arama kutusuna hem de kategori filtresine event listener ekle
    productSearchInput.addEventListener('input', filterProducts);
    categoryFilterSelect.addEventListener('change', filterProducts);
    
    // --- Mevcut Diğer Fonksiyonlarınız ---
    
    // Mevcut ürünlerin quantity ve price inputları için event listener ekle
    document.querySelectorAll('#selectedProducts .quantity-input').forEach(input => {
        input.addEventListener('input', function() {
            updateQuantity(this);
        });
    });

    document.querySelectorAll('#selectedProducts .price-input').forEach(input => {
        input.addEventListener('input', function() {
            updateRowTotal(this.closest('tr'));
        });
    });

    // İlk yüklemede toplamları hesapla
    updateTotals();
});

// Not ekleme/silme fonksiyonları
function addNote() {
    const container = document.getElementById('notesContainer');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" name="notes[]" class="form-control">
        <button type="button" class="btn btn-outline-danger" onclick="removeNote(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeNote(button) {
    button.closest('.input-group').remove();
}

// Ürün ekleme ve hesaplama fonksiyonları
function addProductToQuote(product) {
    try {
        const tbody = document.querySelector('#selectedProducts tbody');
        const noProductRow = document.getElementById('noProductRow');
        
        if (noProductRow) {
            noProductRow.remove();
        }

        const rowId = 'product-' + product.id;
        const existingRow = document.getElementById(rowId);

        if (existingRow) {
            const quantityInput = existingRow.querySelector('input.quantity-input');
            const currentQuantity = parseInt(quantityInput.value) || 1;
            if (currentQuantity < product.stock_quantity) {
                quantityInput.value = currentQuantity + 1;
                updateQuantity(quantityInput);
            }
            return;
        }

        const tr = document.createElement('tr');
        tr.id = rowId;
        tr.innerHTML = `
            <td>
                ${product.name}
                <input type="hidden" name="products[${product.id}][product_id]" value="${product.id}">
                <input type="hidden" name="products[${product.id}][quantity]" class="hidden-quantity" value="1">
                <input type="hidden" name="products[${product.id}][price]" class="hidden-price" value="${product.price}">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm quantity-input" 
                       value="1" min="1" max="${product.stock_quantity}">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm price-input" 
                       value="${product.price}" min="0" step="0.01">
            </td>
            <td class="text-end">%${product.vat_rate}</td>
            <td class="text-end row-total"></td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeProduct(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        
        const quantityInput = tr.querySelector('.quantity-input');
        quantityInput.addEventListener('input', function() {
            updateQuantity(this);
        });
        
        const priceInput = tr.querySelector('.price-input');
        priceInput.addEventListener('input', function() {
            updateRowTotal(tr);
        });

        updateRowTotal(tr);
    } catch (error) {
        console.error('Ürün ekleme hatası:', error);
    }
}


function updateQuantity(input) {
    const row = input.closest('tr');
    const productId = row.id.replace('product-', '');
    const hiddenQuantity = row.querySelector(`input[name="products[${productId}][quantity]"]`);
    let quantity = parseInt(input.value) || 1;
    
    if (quantity < 1) quantity = 1;
    const maxStock = parseInt(input.getAttribute('max'));
    if (quantity > maxStock) {
        quantity = maxStock;
        alert('Maksimum stok miktarını aşamazsınız!');
    }
    
    input.value = quantity;
    hiddenQuantity.value = quantity;
    updateRowTotal(row);
}

function updateRowTotal(row) {
    try {
        const quantity = parseInt(row.querySelector('.quantity-input').value) || 1;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const vatRateText = row.querySelector('td:nth-child(4)').textContent;
        const vatRate = parseFloat(vatRateText.replace('%', ''));
        
        const total = calculateRowTotal(price, quantity, vatRate);
        row.querySelector('.row-total').textContent = formatMoney(total) + ' TL';
        
        updateTotals();
    } catch (error) {
        console.error('Satır güncelleme hatası:', error);
    }
}

function updateTotals() {
    try {
        let subtotal = 0;
        let vatTotal = 0;

        document.querySelectorAll('#selectedProducts tbody tr:not(#noProductRow)').forEach(row => {
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const quantity = parseInt(row.querySelector('.quantity-input').value) || 1;
            const vatRateText = row.querySelector('td:nth-child(4)').textContent;
            const vatRate = parseFloat(vatRateText.replace('%', ''));

            const rowSubtotal = price * quantity;
            const rowVat = rowSubtotal * (vatRate / 100);

            subtotal += rowSubtotal;
            vatTotal += rowVat;
        });

        document.getElementById('subtotal').textContent = formatMoney(subtotal) + ' TL';
        document.getElementById('vatTotal').textContent = formatMoney(vatTotal) + ' TL';
        document.getElementById('grandTotal').innerHTML = '<strong>' + formatMoney(subtotal + vatTotal) + ' TL</strong>';
    } catch (error) {
        console.error('Toplam hesaplama hatası:', error);
    }
}
    
function removeProduct(button) {
    const tr = button.closest('tr');
    tr.remove();
    
    const tbody = document.querySelector('#selectedProducts tbody');
    if (tbody.children.length === 0) {
        tbody.innerHTML = `
            <tr id="noProductRow">
                <td colspan="6" class="text-center">Henüz ürün eklenmedi.</td>
            </tr>
        `;
    }
    
    updateTotals();
}

function calculateRowTotal(price, quantity, vatRate) {
    const subtotal = price * quantity;
    const vat = subtotal * (vatRate / 100);
    return subtotal + vat;
}

function formatMoney(amount) {
    return new Intl.NumberFormat('tr-TR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

function validateForm() {
    const form = document.getElementById('quoteForm');
    
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return false;
    }

    const products = document.querySelectorAll('#selectedProducts tbody tr:not(#noProductRow)');
    if (products.length === 0) {
        alert('Lütfen en az bir ürün ekleyin.');
        return false;
    }

    products.forEach(row => {
        const productId = row.id.replace('product-', '');
        const price = row.querySelector('.price-input').value;
        row.querySelector(`input[name="products[${productId}][price]"]`).value = price;
    });

    return true;
}

// Form doğrulama için Bootstrap kodu
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
</script>