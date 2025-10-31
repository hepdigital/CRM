<?php
// modules/settings/user_actions.php
require_once '../../includes/header.php';

// Yetki kontrolü
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Yetkisiz erişim']);
    exit;
}

// HTTP metodu kontrolü
$method = $_SERVER['REQUEST_METHOD'];
$response = ['success' => false];

try {
    switch ($method) {
        case 'POST':
            // JSON verisini al
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Gerekli alanları kontrol et
            $requiredFields = ['username', 'email', 'full_name'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception($field . ' alanı zorunludur.');
                }
            }

            // Kullanıcı adı ve e-posta benzersiz olmalı
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$data['username'], $data['email']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor.');
            }

            // Yeni kullanıcı oluştur
            $stmt = $db->prepare("
                INSERT INTO users (username, password, email, full_name, role)
                VALUES (:username, :password, :email, :full_name, :role)
            ");

            $stmt->execute([
                ':username' => $data['username'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':email' => $data['email'],
                ':full_name' => $data['full_name'],
                ':role' => $data['role'] ?? 'user'
            ]);

            $response['success'] = true;
            $response['message'] = 'Kullanıcı başarıyla oluşturuldu.';
            break;

        case 'PUT':
            // JSON verisini al
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                throw new Exception('Kullanıcı ID gerekli.');
            }

            // Mevcut kullanıcıyı kontrol et
            $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$data['id']]);
            if (!$stmt->fetch()) {
                throw new Exception('Kullanıcı bulunamadı.');
            }

            // Güncelleme sorgusu
            $sql = "UPDATE users SET 
                    username = :username,
                    email = :email,
                    full_name = :full_name,
                    role = :role";

            // Şifre değiştirilecekse ekle
            $params = [
                ':id' => $data['id'],
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':full_name' => $data['full_name'],
                ':role' => $data['role']
            ];

            if (!empty($data['password'])) {
                $sql .= ", password = :password";
                $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            $response['success'] = true;
            $response['message'] = 'Kullanıcı başarıyla güncellendi.';
            break;

        case 'DELETE':
            // JSON verisini al
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                throw new Exception('Kullanıcı ID gerekli.');
            }

            // Kendini silmeye çalışıyor mu?
            if ($data['id'] == $_SESSION['user_id']) {
                throw new Exception('Kendi hesabınızı silemezsiniz.');
            }

            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$data['id']]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Kullanıcı bulunamadı.');
            }

            $response['success'] = true;
            $response['message'] = 'Kullanıcı başarıyla silindi.';
            break;

        default:
            throw new Exception('Geçersiz metod.');
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

// JSON yanıtı döndür
header('Content-Type: application/json');
echo json_encode($response);