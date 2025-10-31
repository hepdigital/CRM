-- Şirket Ayarları Tablosu
CREATE TABLE `company_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) NOT NULL,
  `company_title` text DEFAULT NULL COMMENT 'Uzun şirket unvanı',
  `address` text DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `phone_2` varchar(100) DEFAULT NULL COMMENT 'İkinci telefon',
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `tax_office` varchar(100) DEFAULT NULL,
  `tax_number` varchar(50) DEFAULT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `signature_url` varchar(500) DEFAULT NULL COMMENT 'İmza görseli',
  
  -- Banka Bilgileri
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_branch` varchar(255) DEFAULT NULL,
  `account_holder` varchar(255) DEFAULT NULL,
  `iban` varchar(50) DEFAULT NULL,
  `swift_code` varchar(20) DEFAULT NULL,
  
  -- PDF Ayarları
  `pdf_header_color` varchar(7) DEFAULT '#e94e1a' COMMENT 'Hex renk kodu',
  `pdf_footer_text` text DEFAULT NULL,
  `pdf_notes` text DEFAULT NULL COMMENT 'Varsayılan teklif notları',
  
  -- E-posta Ayarları
  `email_signature` text DEFAULT NULL,
  `email_footer` text DEFAULT NULL,
  
  -- Sosyal Medya
  `facebook_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  
  -- Diğer Ayarlar
  `currency` varchar(10) DEFAULT 'TL',
  `timezone` varchar(50) DEFAULT 'Europe/Istanbul',
  `date_format` varchar(20) DEFAULT 'd.m.Y',
  `decimal_places` int(2) DEFAULT 2,
  
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan şirket ayarları ekle
INSERT INTO `company_settings` (
  `company_name`, 
  `company_title`, 
  `address`, 
  `phone`, 
  `phone_2`, 
  `email`, 
  `bank_name`, 
  `account_holder`, 
  `iban`,
  `pdf_footer_text`,
  `pdf_notes`
) VALUES (
  'Hotel Deposu Kozmetik Tekstil Danışmanlık San. Tic. Ltd. Şti.',
  'Hotel Deposu Kozmetik Tekstil Danışmanlık San. Tic. Ltd. Şti.',
  'Seyrantepe Mah. Sarmaşık Sk. No: 10 İç Kapı No: 3 Kağıthane / İstanbul',
  '+90 546 915 53 04',
  '+90 546 931 27 67',
  'info@oteldeposu.com',
  'Akbank T.A.Ş.',
  'Hotel Deposu Kozmetik Tekstil Danışmanlık San. Tic. Ltd. Şti',
  'TR13 0004 6005 9688 8000 0797 16',
  'Hotel Deposu Kozmetik Tekstil Danışmanlık San. Tic. Ltd. Şti.\nSeyrantepe Mah. Sarmaşık Sk. No: 10 İç Kapı No: 3 Kağıthane / İstanbul  •  +90 546 915 53 04  •  +90 546 931 27 67  •  info@oteldeposu.com',
  '• Teslimat, tasarım onayı verildikten sonra 10 gün içerisinde yapılmaktadır.\n• Nakliye İstanbul içi tarafımıza, İstanbul dışı alıcıya aittir.'
);