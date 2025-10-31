<?php
// modules/interactions/delete.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

checkAuth();

$interaction_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$interaction_id) {
    header('Location: list.php');
    exit;
}

// İletişim kaydını getir
$stmt = $db->prepare("SELECT * FROM customer_interactions WHERE id = ?");
$stmt->execute([$interaction_id]);
$interaction = $stmt->fetch();

if (!$interaction) {
    header('Location: list.php');
    exit;
}

// Sadece kendi kaydını veya admin silebilir
if ($interaction['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
    header('Location: list.php');
    exit;
}

try {
    $stmt = $db->prepare("DELETE FROM customer_interactions WHERE id = ?");
    $stmt->execute([$interaction_id]);
    
    // Başarılı silme sonrası yönlendir
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'list.php';
    header("Location: $redirect_url");
    exit;
    
} catch (Exception $e) {
    // Hata durumunda geri yönlendir
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'list.php';
    header("Location: $redirect_url?error=delete_failed");
    exit;
}
?>