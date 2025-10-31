<?php
// modules/categories/delete.php
require_once '../../config/database.php';
session_start();

header('Content-Type: application/json');

// Yetki ve istek kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Yetkisiz erişim.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz istek.']);
    exit;
}

$id = (int)$_POST['id'];

try {
    // Kategoriyi kullanan ürün var mı kontrol et
    $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$id]);
    $productCount = $stmt->fetchColumn();

    if ($productCount > 0) {
        throw new Exception('Bu kategoriye ait ürünler bulunduğu için silinemez.');
    }

    // Kategoriyi sil
    $stmt = $db->prepare("DELETE FROM product_categories WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Kategori bulunamadı veya silinemedi.');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>