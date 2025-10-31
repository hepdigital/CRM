<?php
// modules/products/add.php
require_once '../../includes/header.php';

// Dış URL'den görsel indirme fonksiyonu
function downloadImageFromUrl($url) {
    if (empty($url)) return '';
    
    // Zaten yerel dosya mı kontrol et
    if (strpos($url, 'http') !== 0) {
        return $url;
    }
    
    try {
        // Hedef klasörü kontrol et
        $uploadDir = '../../assets/uploads/products/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Dosya uzantısını belirle
        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
        $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : 'jpg';
        
        // Geçerli uzantı kontrolü
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            $extension = 'jpg';
        }
        
        // Benzersiz dosya adı oluştur
        $fileName = 'product_' . uniqid() . '.' . $extension;
        $filePath = $uploadDir . $fileName;
        
        // Dosyayı indir (daha güvenli yöntem)
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'follow_location' => true,
                'max_redirects' => 5
            ]
        ]);
        
        $imageData = file_get_contents($url, false, $context);
        if ($imageData === false) {
            throw new Exception("Görsel indirilemedi: $url");
        }
        
        // Dosya boyutunu kontrol et (5MB limit)
        if (strlen($imageData) > 5 * 1024 * 1024) {
            throw new Exception("Görsel boyutu çok büyük (5MB limit)");
        }
        
        // Dosyayı kaydet
        if (file_put_contents($filePath, $imageData) === false) {
            throw new Exception("Görsel kaydedilemedi");
        }
        
        // Yerel URL döndür
        return 'assets/uploads/products/' . $fileName;
        
    } catch (Exception $e) {
        throw new Exception("Görsel işleme hatası: " . $e->getMessage());
    }
}

$message = '';
$messageType = '';

$stmt_cat = $db->query("SELECT * FROM product_categories ORDER BY name");
$categories = $stmt_cat->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = floatval($_POST['price']);
        $stock_quantity = intval($_POST['stock_quantity']);
        $vat_rate = floatval($_POST['vat_rate']);
        $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $image_url = '';

        // Görsel yükleme işlemi
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../assets/uploads/products/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($fileExtension, $allowedExtensions)) {
                throw new Exception('Geçersiz dosya formatı. Sadece JPG, PNG ve GIF dosyaları yüklenebilir.');
            }

            $fileName = uniqid('product_') . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
    $image_url = getSiteUrl() . '/assets/uploads/products/' . $fileName;
} else {
                throw new Exception('Dosya yüklenirken bir hata oluştu.');
            }
        } elseif (!empty($_POST['image_url'])) {
            // Dış URL'den görsel indir
            $image_url = downloadImageFromUrl($_POST['image_url']);
        }

        // Veritabanına kaydetme
        $stmt = $db->prepare("
            INSERT INTO products (name, description, price, stock_quantity, vat_rate, category_id, image_url)
            VALUES (:name, :description, :price, :stock_quantity, :vat_rate, :category_id, :image_url)
        ");

        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':stock_quantity' => $stock_quantity,
            ':vat_rate' => $vat_rate,
            ':category_id' => $category_id,
            ':image_url' => $image_url
        ]);

        // Düşük stok bildirimi
        $lowStockThreshold = 10;
        if ($stock_quantity < $lowStockThreshold) {
            $notificationManager->createLowStockNotification(
                $name,
                $stock_quantity,
                $lowStockThreshold
            );
        }

        $message = 'Ürün başarıyla eklendi.';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Yeni Ürün Ekle</h5>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Ürün Adı</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
    <label class="form-label">Kategori</label>
    <select name="category_id" class="form-select">
        <option value="">Kategori Seçin...</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?php echo $category['id']; ?>"
                <?php if (isset($product) && $product['category_id'] == $category['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($category['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Fiyat</label>
                                <input type="number" name="price" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Stok Miktarı</label>
                                <input type="number" name="stock_quantity" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">KDV Oranı</label>
                                <select name="vat_rate" class="form-control" required>
                                    <option value="0">%0</option>
                                    <option value="1">%1</option>
                                    <option value="8">%8</option>
									<option value="10">%10</option>
                                    <option value="18">%18</option>
									<option value="20">%20</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Ürün Görseli</label>
                        <input type="file" name="image" class="form-control mb-2" accept="image/*">
                        <div class="text-center">
                            <img id="imagePreview" src="#" alt="Görsel Önizleme" 
                                 class="img-thumbnail d-none" style="max-height: 200px;">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">veya Görsel URL'si</label>
                        <input type="url" name="image_url" class="form-control">
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Kaydet
                </button>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Görsel önizleme
document.querySelector('input[name="image"]').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const file = e.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        }
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('d-none');
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>