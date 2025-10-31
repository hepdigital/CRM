<?php
// modules/settings/upload_logo.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

checkAuth();
checkAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
    exit;
}

$uploadDir = '../../assets/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$response = ['success' => false, 'message' => '', 'url' => ''];

try {
    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Dosya yükleme hatası');
    }

    $file = $_FILES['logo'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Dosya türü kontrolü
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Sadece JPG, PNG, GIF ve WebP dosyaları yüklenebilir');
    }

    // Dosya boyutu kontrolü
    if ($file['size'] > $maxSize) {
        throw new Exception('Dosya boyutu 5MB\'dan büyük olamaz');
    }

    // Güvenli dosya adı oluştur
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'logo_' . time() . '.' . $extension;
    $filePath = $uploadDir . $fileName;

    // Dosyayı taşı
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Veritabanında güncelle
        $logoUrl = '/assets/uploads/' . $fileName;
        
        $stmt = $db->prepare("UPDATE company_settings SET logo_url = ? WHERE id = (SELECT id FROM (SELECT id FROM company_settings ORDER BY id DESC LIMIT 1) as temp)");
        $stmt->execute([$logoUrl]);

        $response = [
            'success' => true,
            'message' => 'Logo başarıyla yüklendi',
            'url' => $logoUrl
        ];
    } else {
        throw new Exception('Dosya yüklenirken bir hata oluştu');
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

echo json_encode($response);
?>