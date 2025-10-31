<?php
// modules/quotes/get_products.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_ids']) || !is_array($input['product_ids'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Product IDs required']);
    exit;
}

try {
    $productIds = array_map('intval', $input['product_ids']);
    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    
    $stmt = $db->prepare("
        SELECT 
            p.id,
            p.name,
            p.price,
            p.stock_quantity,
            p.vat_rate,
            p.image_url,
            pc.name as category_name,
            p.description
        FROM products p
        LEFT JOIN product_categories pc ON p.category_id = pc.id
        WHERE p.id IN ($placeholders)
        ORDER BY p.id
    ");
    
    $stmt->execute($productIds);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['products' => $products]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>