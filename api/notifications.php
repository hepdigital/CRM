<?php
// api/notifications.php
header('Content-Type: application/json');
session_start();

require_once '../config/database.php';
require_once '../includes/notification.php';

// Sadece giriş yapmış kullanıcılar
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$userId = $_SESSION['user_id'];

try {
    switch ($method) {
        case 'GET':
            // Bildirimleri getir
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
            
            $notifications = $notificationManager->getUserNotifications($userId, $limit, $unreadOnly);
            $unreadCount = $notificationManager->getUnreadCount($userId);
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
            break;
            
        case 'POST':
            // Bildirimi okundu olarak işaretle
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? '';
            
            if ($action === 'mark_read') {
                $notificationId = $input['notification_id'] ?? null;
                if ($notificationId) {
                    $success = $notificationManager->markAsRead($notificationId, $userId);
                    echo json_encode(['success' => $success]);
                } else {
                    echo json_encode(['error' => 'Notification ID required']);
                }
            } elseif ($action === 'mark_all_read') {
                $success = $notificationManager->markAllAsRead($userId);
                echo json_encode(['success' => $success]);
            } elseif ($action === 'delete') {
                $notificationId = $input['notification_id'] ?? null;
                if ($notificationId) {
                    $success = $notificationManager->delete($notificationId, $userId);
                    echo json_encode(['success' => $success]);
                } else {
                    echo json_encode(['error' => 'Notification ID required']);
                }
            } else {
                echo json_encode(['error' => 'Invalid action']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>


