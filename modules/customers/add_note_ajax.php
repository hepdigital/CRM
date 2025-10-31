<?php
// modules/customers/add_note_ajax.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');

// Debug için POST verilerini logla
error_log('Customer Notes AJAX - POST data: ' . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

checkAuth();

try {
    $customer_id = (int)$_POST['customer_id'];
    $note = $_POST['note'];
    $color = $_POST['color'] ?? '#ffc107';
    $is_private = isset($_POST['is_private']) ? 1 : 0;
    
    // Validasyon
    if (!$customer_id || !$note) {
        throw new Exception('Gerekli alanlar eksik');
    }
    
    // Müşteri var mı kontrol et
    $stmt = $db->prepare("SELECT id FROM customers WHERE id = ?");
    $stmt->execute([$customer_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Müşteri bulunamadı');
    }
    
    // Notu ekle - VERİTABANI ŞEMASINA UYGUN (title sütunu yok)
    $stmt = $db->prepare("
        INSERT INTO customer_notes (
            customer_id, user_id, note, color, is_private, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, NOW(), NOW()
        )
    ");
    
    $stmt->execute([
        $customer_id,
        $_SESSION['user_id'],
        $note,
        $color,
        $is_private
    ]);
    
    error_log('Customer Notes AJAX - Başarılı: ID ' . $db->lastInsertId());
    echo json_encode(['success' => true, 'message' => 'Not başarıyla eklendi']);
    
} catch (Exception $e) {
    error_log('Customer Notes AJAX - Hata: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>