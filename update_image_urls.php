// update_image_urls.php
<?php
require_once 'config/database.php';
require_once 'includes/functions.php'; // functions.php dosyasını dahil edin

$siteUrl = getSiteUrl();

// Görselleri güncelle - sadece /assets ile başlayan ve http ile başlamayanları
$stmt = $db->prepare("
    UPDATE products 
    SET image_url = CONCAT(?, image_url) 
    WHERE image_url LIKE '/assets/%' 
    AND image_url NOT LIKE 'http%'
");
$stmt->execute([$siteUrl]);

echo "Güncellenen ürün sayısı: " . $stmt->rowCount();
?>