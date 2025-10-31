<?php
// modules/products/edit.php
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

// Ürün ID kontrolü
if (!isset($_GET['id'])) {
   header('Location: list.php');
   exit;
}

$id = (int)$_GET['id'];

// Mevcut ürün bilgilerini getir
try {
   $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
   $stmt->execute([$id]);
   $product = $stmt->fetch();

   if (!$product) {
       header('Location: list.php');
       exit;
   }
} catch (Exception $e) {
   die("Ürün bilgileri alınamadı: " . $e->getMessage());
}

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   try {
       $name = $_POST['name'];
       $description = $_POST['description'];
       $price = floatval($_POST['price']);
       $stock_quantity = intval($_POST['stock_quantity']);
       $vat_rate = floatval($_POST['vat_rate']);
       $category_id = isset($_POST['category_id']) && !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null; // DÜZELTME: Formdan category_id'yi al
       $image_url = $product['image_url']; // Mevcut görsel

       // Yeni görsel yüklendi mi?
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
               // Eski görseli sil (eğer varsa ve yerel olarak yüklenmişse)
               if ($image_url && strpos($image_url, getSiteUrl()) === 0 && file_exists('../../' . ltrim(parse_url($image_url, PHP_URL_PATH), '/'))) {
                   unlink('../../' . ltrim(parse_url($image_url, PHP_URL_PATH), '/'));
               }
               $image_url = getSiteUrl() . '/assets/uploads/products/' . $fileName;
           }
       } elseif (!empty($_POST['image_url'])) {
           // Dış URL'den görsel indir
           $image_url = downloadImageFromUrl($_POST['image_url']);
       }

       // Veritabanını güncelle
       $stmt = $db->prepare("
           UPDATE products 
           SET name = :name, 
               description = :description, 
               price = :price, 
               stock_quantity = :stock_quantity, 
               vat_rate = :vat_rate, 
               image_url = :image_url,
               category_id = :category_id -- DÜZELTME: Sorguya eklendi
           WHERE id = :id
       ");

       $stmt->execute([
           ':name' => $name,
           ':description' => $description,
           ':price' => $price,
           ':stock_quantity' => $stock_quantity,
           ':vat_rate' => $vat_rate,
           ':image_url' => $image_url,
           ':category_id' => $category_id, // DÜZELTME: Parametre olarak eklendi
           ':id' => $id
       ]);

       // Düşük stok bildirimi (stok değiştiyse ve düşükse)
       $lowStockThreshold = 10;
       if ($stock_quantity < $lowStockThreshold && $stock_quantity != $product['stock_quantity']) {
           $notificationManager->createLowStockNotification(
               $name,
               $stock_quantity,
               $lowStockThreshold
           );
       }

       $message = 'Ürün başarıyla güncellendi.';
       $messageType = 'success';

       // Ürün bilgilerini tekrar getir
       $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
       $stmt->execute([$id]);
       $product = $stmt->fetch();

   } catch (Exception $e) {
       $message = 'Hata: ' . $e->getMessage();
       $messageType = 'danger';
   }
}
?>

<div class="card">
   <div class="card-header">
       <h5 class="mb-0">Ürün Düzenle</h5>
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
                       <input type="text" name="name" class="form-control" 
                              value="<?php echo htmlspecialchars($product['name']); ?>" required>
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
                       <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                   </div>

                   <div class="row">
                       <div class="col-md-4">
                           <div class="mb-3">
                               <label class="form-label">Fiyat</label>
                               <input type="number" name="price" class="form-control" step="0.01" 
                                      value="<?php echo $product['price']; ?>" required>
                           </div>
                       </div>
                       <div class="col-md-4">
                           <div class="mb-3">
                               <label class="form-label">Stok Miktarı</label>
                               <input type="number" name="stock_quantity" class="form-control" 
                                      value="<?php echo $product['stock_quantity']; ?>" required>
                           </div>
                       </div>
                       <div class="col-md-4">
                           <div class="mb-3">
                               <label class="form-label">KDV Oranı</label>
                               <select name="vat_rate" class="form-control" required>
                                   <?php
                                   $vatRates = [0, 1, 8, 10, 18, 20];
                                   foreach ($vatRates as $rate) {
                                       $selected = ($rate == $product['vat_rate']) ? 'selected' : '';
                                       echo "<option value=\"{$rate}\" {$selected}>%{$rate}</option>";
                                   }
                                   ?>
                               </select>
                           </div>
                       </div>
                   </div>
               </div>

               <div class="col-md-4">
                   <div class="mb-3">
                       <label class="form-label">Mevcut Görsel</label>
                       <?php if ($product['image_url']): ?>
                           <img src="<?php echo htmlspecialchars(getImageUrl($product['image_url'])); ?>" 
                                alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                class="img-thumbnail d-block mb-2" style="max-height: 200px;"
                                onerror="this.style.display='none';">
                       <?php endif; ?>
                       
                       <label class="form-label">Yeni Görsel Yükle</label>
                       <input type="file" name="image" class="form-control mb-2" accept="image/*">
                       <div class="text-center">
                           <img id="imagePreview" src="#" alt="Görsel Önizleme" 
                                class="img-thumbnail d-none" style="max-height: 200px;">
                       </div>
                   </div>
                   
                   <div class="mb-3">
                       <label class="form-label">veya Görsel URL'si</label>
                       <input type="url" name="image_url" class="form-control" 
                              value="<?php echo htmlspecialchars($product['image_url']); ?>">
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