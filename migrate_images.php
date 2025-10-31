<?php
// migrate_images.php - Dış URL görsellerini yerel klasöre indirme scripti
require_once 'config/database.php';

echo "Görsel migrasyonu başlatılıyor...\n\n";

// Hedef klasörü kontrol et ve oluştur
$targetDir = 'assets/uploads/products';
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
    echo "Hedef klasör oluşturuldu: $targetDir\n";
}

// Dış URL'li ürünleri bul
$stmt = $db->query("SELECT id, name, image_url FROM products WHERE image_url IS NOT NULL AND image_url != ''");
$products = $stmt->fetchAll();

$successCount = 0;
$errorCount = 0;
$errors = [];

foreach ($products as $product) {
    $imageUrl = $product['image_url'];
    
    // Zaten yerel dosya mı kontrol et
    if (strpos($imageUrl, 'http') !== 0) {
        echo "Ürün ID {$product['id']} zaten yerel dosya: $imageUrl\n";
        continue;
    }
    
    echo "İşleniyor: Ürün ID {$product['id']} - {$product['name']}\n";
    echo "URL: $imageUrl\n";
    
    try {
        // Dosya uzantısını belirle
        $pathInfo = pathinfo(parse_url($imageUrl, PHP_URL_PATH));
        $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : 'jpg';
        
        // Geçerli uzantı kontrolü
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            $extension = 'jpg'; // Varsayılan uzantı
        }
        
        // Benzersiz dosya adı oluştur
        $fileName = 'product_' . $product['id'] . '_' . uniqid() . '.' . $extension;
        $filePath = $targetDir . '/' . $fileName;
        
        // Dosyayı indir
        $imageData = file_get_contents($imageUrl);
        if ($imageData === false) {
            throw new Exception("Dosya indirilemedi");
        }
        
        // Dosyayı kaydet
        if (file_put_contents($filePath, $imageData) === false) {
            throw new Exception("Dosya kaydedilemedi");
        }
        
        // Veritabanını güncelle
        $newImageUrl = 'assets/uploads/products/' . $fileName;
        $updateStmt = $db->prepare("UPDATE products SET image_url = ? WHERE id = ?");
        $updateStmt->execute([$newImageUrl, $product['id']]);
        
        echo "✓ Başarılı: $newImageUrl\n";
        $successCount++;
        
    } catch (Exception $e) {
        $errorMsg = "Ürün ID {$product['id']}: " . $e->getMessage();
        echo "✗ Hata: $errorMsg\n";
        $errors[] = $errorMsg;
        $errorCount++;
    }
    
    echo "\n";
}

echo "=== MİGRASYON TAMAMLANDI ===\n";
echo "Başarılı: $successCount\n";
echo "Hatalı: $errorCount\n";

if (!empty($errors)) {
    echo "\nHatalar:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}

echo "\nScript tamamlandı.\n";
?>
