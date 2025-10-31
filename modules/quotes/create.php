<?php
ob_start();
require_once '../../includes/header.php';

$message = '';
$messageType = '';

// Ön tanımlı müşteri ID'si var mı?
$selectedCustomerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;

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
        p.category_id,       -- Kategori ID'sini de alıyoruz
        pc.name as category_name -- Kategori adını da alıyoruz
    FROM products p
    LEFT JOIN product_categories pc ON p.category_id = pc.id
    WHERE p.stock_quantity > 0 
    ORDER BY pc.name, p.name -- Önce kategoriye, sonra ürün adına göre sırala
");
$products = $stmt->fetchAll();

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
error_log('POST Verileri: ' . print_r($_POST, true));
    try {
        $db->beginTransaction();

        // Temel teklif bilgilerini al ve doğrula
        $customerId = (int)$_POST['customer_id'];
        if (!$customerId) {
            throw new Exception('Müşteri seçimi zorunludur.');
        }

        $validUntil = !empty($_POST['valid_until']) ? $_POST['valid_until'] : null;
        $notes = isset($_POST['notes']) ? $_POST['notes'] : [];
        $quoteNumber = 'TKL-' . date('Ymd') . '-' . strtoupper(uniqid());

        // Ürünleri kontrol et
        if (!isset($_POST['products']) || !is_array($_POST['products'])) {
            throw new Exception('Lütfen en az bir ürün ekleyin.');
        }

        // Toplam tutarları hesapla
        $totalAmount = 0;
        $vatAmount = 0;

        // Ürünleri doğrula ve toplamları hesapla
        foreach ($_POST['products'] as $item) {
            $productId = isset($item['product_id']) ? (int)$item['product_id'] : 0;
            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
            
            // Form'dan gelen fiyat ve KDV oranını kullan (gazebo konfigüratöründen gelebilir)
            $unitPrice = isset($item['unit_price']) ? floatval($item['unit_price']) : 0;
            $vatRate = isset($item['vat_rate']) ? floatval($item['vat_rate']) : 0;

            if (!$productId || !$quantity) {
                continue; // Geçersiz ürün, atla
            }

            // Ürün kontrolü
            $checkStmt = $db->prepare("SELECT * FROM products WHERE id = ?");
            $checkStmt->execute([$productId]);
            $product = $checkStmt->fetch();

            if (!$product) {
                throw new Exception("Ürün bulunamadı: ID $productId");
            }

            // Eğer form'dan fiyat gelmemişse veritabanından al
            if (!$unitPrice) {
                $unitPrice = floatval($product['price']);
            }
            
            // Eğer form'dan KDV oranı gelmemişse veritabanından al
            if (!$vatRate) {
                $vatRate = floatval($product['vat_rate']);
            }
            
            $itemTotal = $quantity * $unitPrice;
            $itemVat = $itemTotal * ($vatRate / 100);
            
            $totalAmount += $itemTotal + $itemVat;
            $vatAmount += $itemVat;
        }

        // Teklifi oluştur
        $stmt = $db->prepare("
            INSERT INTO quotes (
                customer_id, quote_number, total_amount, vat_amount,
                status, valid_until, notes, created_by, created_at
            ) VALUES (
                :customer_id, :quote_number, :total_amount, :vat_amount,
                'draft', :valid_until, :notes, :created_by, NOW()
            )
        ");

        $stmt->execute([
            ':customer_id' => $customerId,
            ':quote_number' => $quoteNumber,
            ':total_amount' => $totalAmount,
            ':vat_amount' => $vatAmount,
            ':valid_until' => $validUntil,
            ':notes' => implode("\n", array_filter($notes)),
            ':created_by' => $_SESSION['user_id']
        ]);

        $quoteId = $db->lastInsertId();

        // Teklif kalemlerini ekle
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
            
            // Form'dan gelen fiyat ve KDV oranını kullan
            $unitPrice = isset($item['unit_price']) ? floatval($item['unit_price']) : 0;
            $vatRate = isset($item['vat_rate']) ? floatval($item['vat_rate']) : 0;

            if (!$productId || !$quantity) {
                continue;
            }

            // Eğer form'dan değerler gelmemişse veritabanından al
            if (!$unitPrice || !$vatRate) {
                $productStmt = $db->prepare("SELECT price, vat_rate FROM products WHERE id = ?");
                $productStmt->execute([$productId]);
                $productData = $productStmt->fetch();
                
                if (!$unitPrice) $unitPrice = floatval($productData['price']);
                if (!$vatRate) $vatRate = floatval($productData['vat_rate']);
            }
            
            // Bu satır için toplam tutar hesapla
            $itemSubtotal = $quantity * $unitPrice;
            $itemVat = $itemSubtotal * ($vatRate / 100);
            $itemTotal = $itemSubtotal + $itemVat;

            $stmt->execute([
                ':quote_id' => $quoteId,
                ':product_id' => $productId,
                ':quantity' => $quantity,
                ':unit_price' => $unitPrice,
                ':vat_rate' => $vatRate,
                ':total_amount' => $itemTotal
            ]);
        }

        $db->commit();

        // Müşteri bilgisini al
        $customerStmt = $db->prepare("SELECT company_name FROM customers WHERE id = ?");
        $customerStmt->execute([$customerId]);
        $customer = $customerStmt->fetch();

        // Bildirim oluştur
        $notificationManager->createNewQuoteNotification(
            $quoteNumber,
            $customer['company_name'],
            $totalAmount
        );

        if (isset($_POST['generate_pdf'])) {
            header("Location: generate_pdf.php?id=" . $quoteId);
            exit;
        }

        header("Location: view.php?id=" . $quoteId . "&success=1");
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
        
        // Hata logla
        error_log('Teklif oluşturma hatası: ' . $e->getMessage());
    }
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Yeni Teklif Oluştur</h5>
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
                <!-- Sol Kolon: Müşteri Bilgileri -->
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
                                                <?php echo $selectedCustomerId == $customer['id'] ? 'selected' : ''; ?>>
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
                                       value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Teklif Notları</label>
                                <div id="notesContainer">
                                    <?php
                                    $defaultNotes = [
                                        'Bu teklif 30 gün geçerlidir.',
                                        'Fiyatlara KDV dahildir.',
                                        'Teslimat süresi sipariş onayından sonra 5 iş günüdür.'
                                    ];
                                    foreach ($defaultNotes as $note): ?>
                                        <div class="input-group mb-2">
                                            <input type="text" name="notes[]" class="form-control" 
                                                   value="<?php echo htmlspecialchars($note); ?>">
                                            <button type="button" class="btn btn-outline-danger" onclick="removeNote(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="addNote()">
                                    <i class="fas fa-plus"></i> Not Ekle
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sağ Kolon: Ürün Seçimi -->
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Ürün Seçimi</h6>

<div class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" id="productSearch" class="form-control" placeholder="Ürün ara...">
    </div>
    <div class="col-md-4">
        <select id="categoryFilter" class="form-select">
            <option value="all">Tüm Kategoriler</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#gazeboModal" onclick="console.log('Gazebo butonu tıklandı')">
            <i class="fas fa-home"></i> Gazebo Konfiguratörü
        </button>
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
                                        <tr id="noProductRow">
                                            <td colspan="6" class="text-center">Henüz ürün eklenmedi.</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-end">Ara Toplam:</td>
                                            <td colspan="2" id="subtotal">0,00 TL</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end">KDV Toplam:</td>
                                            <td colspan="2" id="vatTotal">0,00 TL</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>Genel Toplam:</strong></td>
                                            <td colspan="2" id="grandTotal">0,00 TL</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" name="save" class="btn btn-primary">
                    <i class="fas fa-save"></i> Kaydet
                </button>
                <button type="submit" name="generate_pdf" class="btn btn-success">
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
document.addEventListener('DOMContentLoaded', function() {
    const productSearchInput = document.getElementById('productSearch');
    const categoryFilterSelect = document.getElementById('categoryFilter');

    function filterProducts() {
        const searchTerm = productSearchInput.value.toLowerCase();
        const selectedCategory = categoryFilterSelect.value;

        document.querySelectorAll('.product-item').forEach(item => {
            const productName = item.dataset.name;
            const productCategory = item.dataset.categoryId;

            const nameMatch = productName.includes(searchTerm);
            const categoryMatch = (selectedCategory === 'all' || productCategory === selectedCategory);

            if (nameMatch && categoryMatch) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    productSearchInput.addEventListener('input', filterProducts);
    categoryFilterSelect.addEventListener('change', filterProducts);
});

// Not ekleme/silme
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
        // addProductToQuote fonksiyonunda tr.innerHTML kısmını güncelle
tr.innerHTML = `
    <td>
        ${product.name}
        <input type="hidden" name="products[${product.id}][product_id]" value="${product.id}">
        <input type="hidden" name="products[${product.id}][quantity]" class="hidden-quantity" value="1">
        <input type="hidden" name="products[${product.id}][unit_price]" class="hidden-price" value="${product.price}">
        <input type="hidden" name="products[${product.id}][vat_rate]" class="hidden-vat" value="${product.vat_rate}">
    </td>
    <td>
        <input type="number" class="form-control form-control-sm quantity-input" 
               value="1" min="1" max="${product.stock_quantity}">
    </td>
    <td>
        <input type="number" class="form-control form-control-sm price-input" 
               value="${product.price}" min="0" step="0.01">
    </td>
    <td class="text-end">%${parseFloat(product.vat_rate).toFixed(2)}</td>
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
    const productId = tr.id.replace('product-', '');
    const hiddenPrice = tr.querySelector(`input[name="products[${productId}][unit_price]"]`);
    hiddenPrice.value = this.value;
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
    // Doğru hidden input seçicisi
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
	
function formatMoney(amount) {
   return new Intl.NumberFormat('tr-TR', {
       minimumFractionDigits: 2,
       maximumFractionDigits: 2
   }).format(amount);
}

function calculateRowTotal(price, quantity, vatRate) {
   const subtotal = price * quantity;
   const vat = subtotal * (vatRate / 100);
   return subtotal + vat;
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
            // Price değerini artık input field'dan alıyoruz
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
        document.getElementById('grandTotal').textContent = formatMoney(subtotal + vatTotal) + ' TL';
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

// Form doğrulama
document.getElementById('quoteForm').addEventListener('submit', function(event) {
    if (!this.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
        this.classList.add('was-validated');
        return;
    }

    // Ürünleri kontrol et
    const products = document.querySelectorAll('#selectedProducts tbody tr:not(#noProductRow)');
    if (products.length === 0) {
        event.preventDefault();
        alert('Lütfen en az bir ürün ekleyin.');
        return;
    }

    // Her bir ürün için hidden input oluşturalım
    products.forEach(row => {
        const productId = row.id.replace('product-', '');
        const quantity = row.querySelector('.quantity-input').value;
        const price = row.querySelector('.price-input').value;
        
        // Hidden input'ları oluştur
        const productIdInput = document.createElement('input');
        productIdInput.type = 'hidden';
        productIdInput.name = `products[${productId}][product_id]`;
        productIdInput.value = productId;

        const quantityInput = document.createElement('input');
        quantityInput.type = 'hidden';
        quantityInput.name = `products[${productId}][quantity]`;
        quantityInput.value = quantity;

        const priceInput = document.createElement('input');
        priceInput.type = 'hidden';
        priceInput.name = `products[${productId}][price]`;
        priceInput.value = price;

        this.appendChild(productIdInput);
        this.appendChild(quantityInput);
        this.appendChild(priceInput);

        // Debug için
        console.log(`Ürün ID: ${productId}, Miktar: ${quantity}, Fiyat: ${price}`);
    });
});

// addProductsToQuoteForm fonksiyonu artık gerekli değil
// Konfigüratör doğrudan addProductToQuote fonksiyonunu kullanıyor
</script>

<!-- Gazebo Konfiguratör Modal -->
<?php include 'gazebo_configurator.php'; ?>