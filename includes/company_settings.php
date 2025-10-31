<?php
// includes/company_settings.php

class CompanySettings {
    private $db;
    private $settings;
    
    public function __construct($database) {
        $this->db = $database;
        $this->loadSettings();
    }
    
    // Ayarları yükle
    private function loadSettings() {
        try {
            $stmt = $this->db->query("SELECT * FROM company_settings ORDER BY id DESC LIMIT 1");
            $this->settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Eğer ayar yoksa varsayılan değerler
            if (!$this->settings) {
                $this->settings = $this->getDefaultSettings();
            }
        } catch (Exception $e) {
            $this->settings = $this->getDefaultSettings();
        }
    }
    
    // Varsayılan ayarlar
    private function getDefaultSettings() {
        return [
            'company_name' => 'Şirket Adı',
            'company_title' => 'Şirket Unvanı',
            'address' => '',
            'phone' => '',
            'phone_2' => '',
            'email' => '',
            'website' => '',
            'tax_office' => '',
            'tax_number' => '',
            'logo_url' => '',
            'signature_url' => '',
            'bank_name' => '',
            'bank_branch' => '',
            'account_holder' => '',
            'iban' => '',
            'swift_code' => '',
            'pdf_header_color' => '#e94e1a',
            'pdf_footer_text' => '',
            'pdf_notes' => '',
            'email_signature' => '',
            'email_footer' => '',
            'facebook_url' => '',
            'instagram_url' => '',
            'linkedin_url' => '',
            'twitter_url' => '',
            'currency' => 'TL',
            'timezone' => 'Europe/Istanbul',
            'date_format' => 'd.m.Y',
            'decimal_places' => 2
        ];
    }
    
    // Belirli bir ayarı getir
    public function get($key, $default = null) {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }
    
    // Tüm ayarları getir
    public function getAll() {
        return $this->settings;
    }
    
    // Şirket adını getir
    public function getCompanyName() {
        return $this->get('company_name', 'Şirket Adı');
    }
    
    // Şirket unvanını getir
    public function getCompanyTitle() {
        return $this->get('company_title', $this->getCompanyName());
    }
    
    // Tam adres getir
    public function getFullAddress() {
        $address = $this->get('address');
        $phone = $this->get('phone');
        $email = $this->get('email');
        
        $parts = [];
        if ($address) $parts[] = $address;
        if ($phone) $parts[] = $phone;
        if ($email) $parts[] = $email;
        
        return implode(' • ', $parts);
    }
    
    // Logo URL'ini getir
    public function getLogoUrl() {
        $logo = $this->get('logo_url');
        if ($logo && !filter_var($logo, FILTER_VALIDATE_URL)) {
            // Relative path ise absolute yap
            return $this->getSiteUrl() . '/' . ltrim($logo, '/');
        }
        return $logo;
    }
    
    // Site URL'ini getir
    private function getSiteUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'];
        return $protocol . $domainName;
    }
    
    // Para formatı
    public function formatMoney($amount, $showCurrency = true) {
        $decimals = $this->get('decimal_places', 2);
        $currency = $this->get('currency', 'TL');
        
        $formatted = number_format($amount, $decimals, ',', '.');
        
        if ($showCurrency) {
            $formatted .= ' ' . $currency;
        }
        
        return $formatted;
    }
    
    // Tarih formatı
    public function formatDate($date, $customFormat = null) {
        $format = $customFormat ?: $this->get('date_format', 'd.m.Y');
        
        if (is_string($date)) {
            $date = strtotime($date);
        }
        
        return date($format, $date);
    }
    
    // PDF için footer metni getir
    public function getPdfFooter() {
        $footer = $this->get('pdf_footer_text');
        if (!$footer) {
            // Varsayılan footer oluştur
            $company = $this->getCompanyTitle();
            $address = $this->get('address');
            $phone = $this->get('phone');
            $phone2 = $this->get('phone_2');
            $email = $this->get('email');
            
            $line1 = $company;
            
            $line2Parts = [];
            if ($address) $line2Parts[] = $address;
            if ($phone) $line2Parts[] = $phone;
            if ($phone2) $line2Parts[] = $phone2;
            if ($email) $line2Parts[] = $email;
            
            $line2 = implode('  •  ', $line2Parts);
            
            $footer = $line1 . "\n" . $line2;
        }
        
        return $footer;
    }
    
    // PDF için varsayılan notları getir
    public function getPdfNotes() {
        return $this->get('pdf_notes', '');
    }
    
    // Banka bilgilerini getir
    public function getBankInfo() {
        return [
            'bank_name' => $this->get('bank_name'),
            'bank_branch' => $this->get('bank_branch'),
            'account_holder' => $this->get('account_holder'),
            'iban' => $this->get('iban'),
            'swift_code' => $this->get('swift_code')
        ];
    }
    
    // E-posta imzası getir
    public function getEmailSignature() {
        $signature = $this->get('email_signature');
        if (!$signature) {
            // Varsayılan imza oluştur
            $company = $this->getCompanyName();
            $phone = $this->get('phone');
            $email = $this->get('email');
            $website = $this->get('website');
            
            $parts = [$company];
            if ($phone) $parts[] = 'Tel: ' . $phone;
            if ($email) $parts[] = 'E-posta: ' . $email;
            if ($website) $parts[] = 'Web: ' . $website;
            
            $signature = implode("\n", $parts);
        }
        
        return $signature;
    }
    
    // Sosyal medya linklerini getir
    public function getSocialMedia() {
        return [
            'facebook' => $this->get('facebook_url'),
            'instagram' => $this->get('instagram_url'),
            'linkedin' => $this->get('linkedin_url'),
            'twitter' => $this->get('twitter_url')
        ];
    }
    
    // Ayarları yenile (cache temizleme)
    public function refresh() {
        $this->loadSettings();
    }
    
    // Hex rengi RGB'ye çevir
    public function hexToRgb($hex) {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }
    
    // PDF başlık rengini RGB olarak getir
    public function getPdfHeaderColorRgb() {
        $color = $this->get('pdf_header_color', '#e94e1a');
        return $this->hexToRgb($color);
    }
}

// Global şirket ayarları instance'ı oluştur
if (!isset($companySettings)) {
    $companySettings = new CompanySettings($db);
}
?>