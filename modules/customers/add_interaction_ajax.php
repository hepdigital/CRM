<?php
// modules/customers/add_interaction_ajax.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');

// Debug için POST verilerini logla
error_log('Customer Interactions AJAX - POST data: ' . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

checkAuth();

try {
    $customer_id = (int)$_POST['customer_id'];
    $type = $_POST['type'];
    $subject = $_POST['subject'];
    $description = $_POST['description'] ?? '';
    
    // Validasyon
    if (!$customer_id || !$type || !$subject) {
        throw new Exception('Gerekli alanlar eksik');
    }
    
    // Müşteri var mı kontrol et
    $stmt = $db->prepare("SELECT id FROM customers WHERE id = ?");
    $stmt->execute([$customer_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Müşteri bulunamadı');
    }
    
    // İletişim kaydını ekle - VERİTABANI ŞEMASINA UYGUN
    $stmt = $db->prepare("
        INSERT INTO customer_interactions (
            customer_id, user_id, type, subject, description, 
            interaction_date, status, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, NOW(), 'completed', NOW(), NOW()
        )
    ");
    
    $stmt->execute([
        $customer_id,
        $_SESSION['user_id'],
        $type,
        $subject,
        $description
    ]);
    
    error_log('Customer Interactions AJAX - Başarılı: ID ' . $db->lastInsertId());
    echo json_encode(['success' => true, 'message' => 'İletişim kaydı başarıyla eklendi']);
    
} catch (Exception $e) {
    error_log('Customer Interactions AJAX - Hata: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>