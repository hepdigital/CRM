<?php
// modules/products/export.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Ürünleri kategori adıyla birlikte getir
$stmt = $db->query("\n    SELECT \n        p.*,\n        pc.name AS category_name\n    FROM products p\n    LEFT JOIN product_categories pc ON pc.id = p.category_id\n    ORDER BY p.created_at DESC\n");
$products = $stmt->fetchAll();

// CSV dosya adı oluştur
$filename = 'urunler_' . date('Y-m-d_H-i-s') . '.csv';

// HTTP başlıklarını ayarla
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// BOM ekle (Excel'de Türkçe karakterler için)
echo "\xEF\xBB\xBF";

// CSV başlıkları - veritabanı yapısına uygun
$headers = ['id', 'product_categories', 'name', 'stock_code', 'description', 'price', 'stock_quantity', 'vat_rate', 'image_url'];

// CSV çıktısını oluştur
$output = fopen('php://output', 'w');

// Başlıkları yaz
fputcsv($output, $headers);

// Ürün verilerini yaz
foreach ($products as $product) {
    $row = [
        $product['id'],
        $product['category_name'] ?? '',
        $product['name'],
        $product['stock_code'] ?? '',
        $product['description'] ?? '',
        number_format($product['price'], 2, '.', ''),
        $product['stock_quantity'],
        $product['vat_rate'],
        $product['image_url'] ?? ''
    ];
    fputcsv($output, $row);
}

fclose($output);
exit;
?>
