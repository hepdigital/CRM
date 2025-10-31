<?php
// modules/categories/assign_products.php
require_once '../../config/database.php';
session_start();

header('Content-Type: application/json');

// Yetki ve istek kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Yetkisiz erişim.']);
    exit;
}

// Gelen JSON verisini al
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['category_id']) || !isset($data['product_ids']) || !is_array($data['product_ids'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz veya eksik veri.']);
    exit;
}

$categoryId = (int)$data['category_id'];
$productIds = $data['product_ids'];

if (empty($productIds)) {
    echo json_encode(['success' => true, 'message' => 'Seçili ürün yok.']);
    exit;
}

// Gelen ID'lerin sadece sayılardan oluştuğundan emin ol
$sanitizedProductIds = array_map('intval', $productIds);
$placeholders = implode(',', array_fill(0, count($sanitizedProductIds), '?'));

try {
    $db->beginTransaction();

    $stmt = $db->prepare("UPDATE products SET category_id = ? WHERE id IN ($placeholders)");
    
    // Parametreleri birleştir: [categoryId, productId1, productId2, ...]
    $params = array_merge([$categoryId], $sanitizedProductIds);
    
    $stmt->execute($params);

    $db->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
}