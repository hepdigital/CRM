<?php
// modules/customers/delete.php
require_once '../../includes/header.php';

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
    // Önce müşteriye ait teklifleri kontrol et
    $stmt = $db->prepare("SELECT COUNT(*) FROM quotes WHERE customer_id = ?");
    $stmt->execute([$id]);
    $quoteCount = $stmt->fetchColumn();

    if ($quoteCount > 0) {
        throw new Exception('Bu müşteriye ait teklifler bulunduğu için silinemez.');
    }

    // Müşteriyi sil
    $stmt = $db->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Müşteri bulunamadı.');
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}