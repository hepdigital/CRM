<?php
// includes/functions.php

// Para formatı
function formatMoney($amount) {
    return number_format($amount, 2, ',', '.');
}

// Güvenli string
function sanitize($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Tarih formatı
function formatDate($date, $format = 'd.m.Y') {
    return date($format, strtotime($date));
}

function getSiteUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    return $protocol . $domainName;
}

// Dosya yükleme
function uploadFile($file, $destination, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    try {
        // Dosya var mı ve hata yok mu kontrol et
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Geçersiz dosya parametresi.');
        }

        // Dosya yükleme hatasını kontrol et
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('Dosya yüklenmedi.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('Dosya boyutu çok büyük.');
            default:
                throw new Exception('Bilinmeyen bir hata oluştu.');
        }

        // Dosya boyutunu kontrol et (5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('Dosya boyutu 5MB\'dan büyük olamaz.');
        }

        // Dosya uzantısını kontrol et
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $fileType = $finfo->file($file['tmp_name']);
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('Geçersiz dosya türü.');
        }

        // Yükleme dizinini kontrol et
        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }

        // Benzersiz dosya adı oluştur
        $fileName = uniqid() . '.' . $extension;
        $targetPath = $destination . '/' . $fileName;

        // Dosyayı taşı
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Dosya yüklenirken bir hata oluştu.');
        }

        return $fileName;

    } catch (Exception $e) {
        throw new Exception('Dosya yükleme hatası: ' . $e->getMessage());
    }
}

// Dosya silme
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Teklif numarası oluşturma
function generateQuoteNumber() {
    return 'TKL-' . date('Ymd') . '-' . strtoupper(uniqid());
}

// Durum badge'i oluşturma
function getStatusBadge($status) {
    $statusLabels = [
        'draft' => ['text' => 'Taslak', 'class' => 'secondary'],
        'sent' => ['text' => 'Gönderildi', 'class' => 'primary'],
        'accepted' => ['text' => 'Kabul Edildi', 'class' => 'success'],
        'rejected' => ['text' => 'Reddedildi', 'class' => 'danger']
    ];

    $label = $statusLabels[$status] ?? ['text' => 'Bilinmiyor', 'class' => 'secondary'];
    
    return sprintf(
        '<span class="badge bg-%s">%s</span>',
        $label['class'],
        $label['text']
    );
}

// Güvenli URL oluşturma
function slugify($text) {
    // Türkçe karakterleri değiştir
    $text = str_replace(
        ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'],
        ['i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c'],
        $text
    );

    // Diğer özel karakterleri temizle
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);

    return $text;
}

// Aktif menü item'ını belirleme
function isActiveMenu($path) {
    return strpos($_SERVER['PHP_SELF'], $path) !== false ? 'active' : '';
}

// Flash mesajı oluşturma
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Flash mesajını gösterme
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return sprintf(
            '<div class="alert alert-%s alert-dismissible fade show" role="alert">%s
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>',
            $message['type'],
            $message['message']
        );
    }
    return '';
}

// CSRF token oluşturma
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF token kontrolü
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        throw new Exception('CSRF token doğrulaması başarısız.');
    }
}

// Görsel URL'sini düzeltme fonksiyonu
function getImageUrl($imageUrl) {
    if (empty($imageUrl)) {
        return '';
    }
    
    // Dış URL ise olduğu gibi döndür
    if (strpos($imageUrl, 'http') === 0) {
        return $imageUrl;
    }
    
    // Yerel dosya ise tam yol oluştur
    // Mevcut sayfanın konumuna göre base path belirle
    $currentPath = $_SERVER['PHP_SELF'];
    
    if (strpos($currentPath, '/modules/products/') !== false) {
        // modules/products/ klasöründeyiz
        return '../../' . ltrim($imageUrl, '/');
    } elseif (strpos($currentPath, '/modules/quotes/') !== false) {
        // modules/quotes/ klasöründeyiz
        return '../../' . ltrim($imageUrl, '/');
    } elseif (strpos($currentPath, '/modules/') !== false) {
        // Diğer modules/ klasörlerindeyiz
        return '../' . ltrim($imageUrl, '/');
    } else {
        // Ana dizindeyiz
        return ltrim($imageUrl, '/');
    }
}