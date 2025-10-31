<?php
// modules/products/delete.php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// AJAX isteği kontrolü
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ID kontrolü
if (!isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID parameter is required']);
    exit;
}

$id = (int)$_POST['id'];

try {
    // Önce ürün bilgilerini al (görsel silmek için)
    $stmt = $db->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    // Ürünü sil
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    // Eğer silme başarılıysa ve görsel varsa, görseli de sil
    if ($stmt->rowCount() > 0 && $product && $product['image_url']) {
        $imagePath = '../../' . ltrim($product['image_url'], '/');
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}