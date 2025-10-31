<?php
session_start();
require_once '../../config/database.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Oturum sona erdi']);
    exit;
}

// AJAX isteği kontrolü
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Geçersiz istek metodu']);
    exit;
}

// ID kontrolü
if (!isset($_POST['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID parametresi gerekli']);
    exit;
}

$id = (int)$_POST['id'];

try {
    $db->beginTransaction();

    // Teklifin durumunu kontrol et
    $stmt = $db->prepare("SELECT status FROM quotes WHERE id = ?");
    $stmt->execute([$id]);
    $quote = $stmt->fetch();

    if (!$quote) {
        throw new Exception('Teklif bulunamadı.');
    }

    if ($quote['status'] !== 'draft') {
        throw new Exception('Sadece taslak durumdaki teklifler silinebilir.');
    }

    // Önce teklif kalemlerini sil
    $stmt = $db->prepare("DELETE FROM quote_items WHERE quote_id = ?");
    $stmt->execute([$id]);

    // Sonra teklifi sil
    $stmt = $db->prepare("DELETE FROM quotes WHERE id = ?");
    $stmt->execute([$id]);

    $db->commit();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>