<?php
// includes/notification.php

class NotificationManager {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Yeni bildirim oluştur
     */
    public function create($userId, $type, $title, $message, $actionUrl = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, type, title, message, action_url) 
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$userId, $type, $title, $message, $actionUrl]);
        } catch (Exception $e) {
            error_log("Notification creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kullanıcının bildirimlerini getir
     */
    public function getUserNotifications($userId, $limit = 10, $unreadOnly = false) {
        try {
            $sql = "SELECT * FROM notifications WHERE user_id = ?";
            $params = [$userId];
            
            if ($unreadOnly) {
                $sql .= " AND is_read = 0";
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ?";
            $params[] = $limit;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get notifications failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Bildirimi okundu olarak işaretle
     */
    public function markAsRead($notificationId, $userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE id = ? AND user_id = ?
            ");
            return $stmt->execute([$notificationId, $userId]);
        } catch (Exception $e) {
            error_log("Mark notification as read failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tüm bildirimleri okundu olarak işaretle
     */
    public function markAllAsRead($userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE user_id = ? AND is_read = 0
            ");
            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Mark all notifications as read failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Okunmamış bildirim sayısını getir
     */
    public function getUnreadCount($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM notifications 
                WHERE user_id = ? AND is_read = 0
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (Exception $e) {
            error_log("Get unread count failed: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Bildirimi sil
     */
    public function delete($notificationId, $userId) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM notifications 
                WHERE id = ? AND user_id = ?
            ");
            return $stmt->execute([$notificationId, $userId]);
        } catch (Exception $e) {
            error_log("Delete notification failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sistem bildirimi oluştur (tüm kullanıcılara)
     */
    public function createSystemNotification($type, $title, $message, $actionUrl = null) {
        try {
            // Tüm kullanıcıları getir
            $stmt = $this->db->query("SELECT id FROM users");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $success = true;
            foreach ($users as $user) {
                if (!$this->create($user['id'], $type, $title, $message, $actionUrl)) {
                    $success = false;
                }
            }
            
            return $success;
        } catch (Exception $e) {
            error_log("Create system notification failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Stok uyarısı bildirimi
     */
    public function createLowStockNotification($productName, $currentStock, $threshold) {
        $title = "Düşük Stok Uyarısı";
        $message = "{$productName} ürününün stoku kritik seviyede ({$currentStock} adet). Minimum stok: {$threshold}";
        $actionUrl = "/modules/products/list.php";
        
        return $this->createSystemNotification('warning', $title, $message, $actionUrl);
    }
    
    /**
     * Yeni teklif bildirimi
     */
    public function createNewQuoteNotification($quoteNumber, $customerName, $amount) {
        $title = "Yeni Teklif Oluşturuldu";
        $message = "{$customerName} için {$quoteNumber} numaralı teklif oluşturuldu. Tutar: " . number_format($amount, 2, ',', '.') . " ₺";
        $actionUrl = "/modules/quotes/list.php";
        
        return $this->createSystemNotification('info', $title, $message, $actionUrl);
    }
    
    /**
     * Teklif durumu değişikliği bildirimi
     */
    public function createQuoteStatusNotification($quoteNumber, $customerName, $oldStatus, $newStatus) {
        $statusLabels = [
            'draft' => 'Taslak',
            'sent' => 'Gönderildi',
            'accepted' => 'Kabul Edildi',
            'rejected' => 'Reddedildi'
        ];
        
        $title = "Teklif Durumu Değişti";
        $message = "{$customerName} için {$quoteNumber} numaralı teklif durumu '{$statusLabels[$oldStatus]}' → '{$statusLabels[$newStatus]}' olarak değiştirildi.";
        $actionUrl = "/modules/quotes/list.php";
        
        return $this->createSystemNotification('success', $title, $message, $actionUrl);
    }
}

// Global bildirim yöneticisi
$notificationManager = new NotificationManager($db);
?>


