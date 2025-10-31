<?php
// modules/users/delete.php
require_once '../../includes/header.php';

// Sadece admin erişebilir
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Yetkisiz erişim']);
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
    echo json_encode(['error' => 'ID parametresi gerekli']);
    exit;
}

$id = (int)$_POST['id'];

try {
    // Kendi hesabını silmeye çalışıyor mu?
    if ($id === $_SESSION['user_id']) {
        throw new Exception('Kendi hesabınızı silemezsiniz.');
    }

    $db->beginTransaction();

    // İlişkili kayıtları temizle
    $tables = [
        'activity_logs',       // Aktivite logları
        'password_resets',     // Şifre sıfırlama talepleri
        'two_factor_codes'     // 2FA kodları
    ];

    foreach ($tables as $table) {
        $stmt = $db->prepare("DELETE FROM {$table} WHERE user_id = ?");
        $stmt->execute([$id]);
    }

    // Teklifleri güncelle (created_by alanını NULL yap)
    $stmt = $db->prepare("UPDATE quotes SET created_by = NULL WHERE created_by = ?");
    $stmt->execute([$id]);

    // Kullanıcıyı sil
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    $db->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>