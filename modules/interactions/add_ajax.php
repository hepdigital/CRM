<?php
// modules/interactions/add_ajax.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');

// Debug için POST verilerini logla
error_log('Interactions AJAX - POST data: ' . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

checkAuth();

try {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $type = $_POST['type'] ?? '';
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Modalda bulunan alanların şemaya uygun eşlemesi
    // Not: Veritabanında interaction_date ve status kolonu yok.
    // follow_up_date alanını next_action_date olarak değerlendiriyoruz.
    $next_action_date = !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : null;

    // Opsiyonel şema alanları (modalda yok, default değerler veriyoruz)
    $contact_person = null;
    $duration = null;
    $outcome = 'neutral';
    $next_action = '';
    $is_important = 0;

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

    // İletişim kaydını ekle - mevcut tablo şemasına göre
    $stmt = $db->prepare("
        INSERT INTO customer_interactions (
            customer_id, user_id, type, subject, description,
            contact_person, duration, outcome, next_action, next_action_date, is_important
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");

    $stmt->execute([
        $customer_id,
        $_SESSION['user_id'],
        $type,
        $subject,
        $description,
        $contact_person,
        $duration,
        $outcome,
        $next_action,
        $next_action_date,
        $is_important
    ]);

    error_log('Interactions AJAX - Başarılı: ID ' . $db->lastInsertId());
    echo json_encode(['success' => true, 'message' => 'İletişim kaydı başarıyla eklendi']);

} catch (Exception $e) {
    error_log('Interactions AJAX - Hata: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>