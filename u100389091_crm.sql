-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1:3306
-- Üretim Zamanı: 31 Eki 2025, 11:01:39
-- Sunucu sürümü: 11.8.3-MariaDB-log
-- PHP Sürümü: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `u100389091_crm`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `company_settings`
--

CREATE TABLE `company_settings` (
  `id` int(11) NOT NULL,
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
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_branch` varchar(255) DEFAULT NULL,
  `account_holder` varchar(255) DEFAULT NULL,
  `iban` varchar(50) DEFAULT NULL,
  `swift_code` varchar(20) DEFAULT NULL,
  `pdf_header_color` varchar(7) DEFAULT '#e94e1a' COMMENT 'Hex renk kodu',
  `pdf_footer_text` text DEFAULT NULL,
  `pdf_notes` text DEFAULT NULL COMMENT 'Varsayılan teklif notları',
  `email_signature` text DEFAULT NULL,
  `email_footer` text DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'TL',
  `timezone` varchar(50) DEFAULT 'Europe/Istanbul',
  `date_format` varchar(20) DEFAULT 'd.m.Y',
  `decimal_places` int(2) DEFAULT 2,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `company_settings`
--

INSERT INTO `company_settings` (`id`, `company_name`, `company_title`, `address`, `phone`, `phone_2`, `email`, `website`, `tax_office`, `tax_number`, `logo_url`, `signature_url`, `bank_name`, `bank_branch`, `account_holder`, `iban`, `swift_code`, `pdf_header_color`, `pdf_footer_text`, `pdf_notes`, `email_signature`, `email_footer`, `facebook_url`, `instagram_url`, `linkedin_url`, `twitter_url`, `currency`, `timezone`, `date_format`, `decimal_places`, `created_at`, `updated_at`) VALUES
(1, 'SKY Görsel Çözümler', 'Sky Görsel Çözümler Reklamcılık San. ve Tic. Ltd. Şti.', 'Kuzuluk Topçusırtı Mah. Topçu Sırtı (Kuzuluk) Cad. No:81 Akyazı / SAKARYA', '+90 546 915 53 04', '+90 546 931 27 67', 'info@oteldeposu.com', 'https://skygorselcozumler.com', 'Gümrükönü', '46515152', '/assets/uploads/logo_1761752496.png', '', 'Halkbank', '', 'Hotel Deposu Kozmetik Tekstil Danışmanlık San. Tic. Ltd. Şti', 'TR13 0004 6005 9688 8000 0797 16', '', '#21126e', 'asdawd\r\nadres  •  +90 5505000  •  +90 505055050  •  info@skygorsel.com', '• 10 gün içerisinde yapılmaktadır.\r\n• Nakliye İstanbul içi tarafımıza, İstanbul dışı alıcıya aittir.', '', '', '', '', '', '', 'TL', 'Europe/Istanbul', 'd.m.Y', 2, '2025-10-28 23:10:07', '2025-10-30 21:14:36');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_office` varchar(100) DEFAULT NULL,
  `tax_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Tablo döküm verisi `customers`
--

INSERT INTO `customers` (`id`, `company_name`, `contact_person`, `email`, `phone`, `address`, `tax_office`, `tax_number`, `created_at`, `updated_at`) VALUES
(5, 'LİBYA HOLDING', '', '', '', '', NULL, NULL, '2025-01-20 06:01:15', '2025-01-20 06:01:15'),
(6, 'KARTAL PALACE TAKSİM', 'ERHAN BEY', '', '', '', NULL, NULL, '2025-01-20 07:54:17', '2025-01-20 07:54:17'),
(7, 'COZZY GRUP', '', '', '', '', NULL, NULL, '2025-01-22 05:59:19', '2025-01-22 05:59:19'),
(8, 'COORDINAT SUIT', 'İBRAHİM BEY', '', '', '', NULL, NULL, '2025-01-22 08:21:52', '2025-01-22 08:21:52'),
(9, 'DİKAWN ENDÜSTRİYEL', 'ÖZKAN BEY', '', '', '', NULL, NULL, '2025-01-23 08:00:42', '2025-01-23 08:00:42'),
(10, 'NOVA PARK SUİTE HOTEL', 'ONUR BEY', '', '', '', NULL, NULL, '2025-01-23 09:37:46', '2025-01-23 09:37:46'),
(11, 'AEROSER', 'MELEK HANIM', '', '', '', NULL, NULL, '2025-01-23 10:26:49', '2025-01-23 10:26:49'),
(12, 'ROYAL PERA HOTEL', 'YASİN BEY', '', '', '', NULL, NULL, '2025-01-23 10:46:33', '2025-01-23 10:46:33'),
(13, 'HOTEL RABİS', 'ÖMER GÜNAYDIN', '', '', '', NULL, NULL, '2025-01-27 14:02:45', '2025-01-27 14:02:45'),
(14, 'BEST OTEL ALANYA', 'SÜLEYMAN BEY', '', '', '', NULL, NULL, '2025-01-27 14:18:14', '2025-01-27 14:18:14'),
(15, 'BOSNA FİRMASI', '', '', '', '', NULL, NULL, '2025-02-04 07:52:08', '2025-02-04 07:52:08'),
(16, 'METROPOL TIP KARŞIYAKA', 'FERHAT BEY', '', '', '', NULL, NULL, '2025-02-07 07:46:51', '2025-02-07 07:46:51'),
(17, 'TRABZON PANAGİA PREMİER OTEL', 'SEHER HANIM', '', '05446733561', 'TRABZON', NULL, NULL, '2025-03-13 09:50:56', '2025-03-13 09:50:56'),
(18, 'MORFONİ MOBİLYA', 'ERAY BEY', '', '', '', NULL, NULL, '2025-03-13 11:30:11', '2025-03-13 11:30:11'),
(20, 'MONTEBELLO HOTELS VILLAS', 'HAKAN GÜNAL', '', '05364939629', '', NULL, NULL, '2025-03-26 06:23:15', '2025-03-26 06:23:15'),
(21, 'ENTEGRES Bilişim Teknolojileri', 'CEM BEY', '', '', '', NULL, NULL, '2025-03-26 12:45:16', '2025-03-26 12:45:16'),
(22, 'İZMİR  OTEL', '', '', '', '', NULL, NULL, '2025-04-07 11:52:54', '2025-04-07 11:52:54'),
(23, 'OBAM TERMAL RESORT OTEL SPA', '', '', '05303213745', '', NULL, NULL, '2025-04-07 12:21:47', '2025-04-07 12:21:47'),
(24, 'TEKCANLAR A.Ş', 'ÇAĞATAY BEY', '', '5337720494', 'İZMİR', NULL, NULL, '2025-04-09 06:52:22', '2025-04-09 06:52:22'),
(25, 'SORRISO HOTEL FATİH', 'HASAN BEY', '', '', '', NULL, NULL, '2025-04-09 09:07:44', '2025-04-09 09:07:44'),
(26, 'GRAND MOON HOTEL', 'SİNAN BEY', '', '05413753456', '', NULL, NULL, '2025-04-16 14:08:45', '2025-04-16 14:08:45'),
(27, 'DELTA OTEL', 'MELİH BEY', '', '05342634651', '', NULL, NULL, '2025-04-16 14:21:07', '2025-04-16 14:21:07'),
(29, 'MONTEBELLO HOTELS VILLAS', '', '', '', '', NULL, NULL, '2025-04-22 12:14:56', '2025-04-22 12:14:56'),
(30, 'AYVALIK BEACH  HOTEL', '', '', '', '', NULL, NULL, '2025-04-24 13:00:45', '2025-04-24 13:00:45'),
(31, 'MK GIDA PAZARLAMA TAŞ BAS YAY TİC LTD ŞTİ', 'PUFFİN SUIT', 'info@puffinsuites.com', '0(212) 227 0229', 'SİNANPAŞA MAH ÇELEBİOĞLU SK NO 9-11 BEŞİKTAŞ / İSTANBUL', NULL, NULL, '2025-05-13 08:15:36', '2025-05-13 08:15:36'),
(32, 'DOĞAN TUR TURİZM EMLAK İNŞAAT TAAH.NAKLİYAT AKARYAKIT LPG OTOGAZ İTHALAT ve İHRACAT SANAYİ VE TİCARET A.Ş.', 'Kerim KARAYALÇIN', '', '0(530) 960 24 72', 'JİVA OTEL Çalış Plajı 1104 Sok. No:14 Fethiye/ MUĞLA', NULL, NULL, '2025-05-13 09:34:29', '2025-05-13 09:34:29'),
(33, 'SALKIM HOTEL', 'KERİM BEY', '', '05334101277', '', NULL, NULL, '2025-05-13 09:42:59', '2025-05-13 09:42:59'),
(34, 'MARLEN RESİDANCE HOTEL', 'RAZİYE HANIM', '', '05367659443', '', NULL, NULL, '2025-05-14 08:01:14', '2025-05-14 08:01:14'),
(35, 'ORHAN BEY', '', '', '', '', NULL, NULL, '2025-05-15 10:56:32', '2025-05-15 10:56:32'),
(36, 'Sedora Turizm Tarım ve Gıda San. Tic. Ltd.Şti', 'Merve Hanım', '', '0(507) 077 7155', 'köyün kendisi behramkale küme evler no:361 ayvacık çanakkale', NULL, NULL, '2025-05-15 12:25:26', '2025-05-15 12:25:26'),
(37, 'Hotel Black Tulip', 'Murat bey', '', '0(542) 139 8618', '', NULL, NULL, '2025-05-16 12:15:06', '2025-05-16 12:15:06'),
(38, 'SYMBOL KAPADOKYA', 'Hatice hanım', '', '', '', NULL, NULL, '2025-05-20 06:20:46', '2025-05-20 06:20:46'),
(39, 'ŞENOCAK DIŞ TİCARET VE TUR.SAN.LTD.ŞTİ', 'Özlem Hanım', '', '0(549) 254 7702', 'TERSANE CAD N0 .111 Beyoğlu, İstanbul', NULL, NULL, '2025-05-20 07:19:32', '2025-05-20 10:25:26'),
(40, 'BİRİZ OTELCİLİK VE TURİZM TİC. LTD. ŞTİ.', 'Hakan Bey', '', '0(532) 379 59 70', 'Ortaköy mah. Mandıra sok. No:34/5 BEŞİKTAŞ /İSTANBUL', NULL, NULL, '2025-05-20 10:31:52', '2025-05-20 10:31:52'),
(41, 'HİMA TURİZM SANAYİ TİCARET LİMİTED ŞİRKETİ', 'Murat Bey', '', '0(542) 139 86 18', 'HOCAPAŞA MAH. HÜDAVENDİGAR CAD. HOTEL BLACK\r\nTULIP NO: 8 FATİH/ İSTANBUL', NULL, NULL, '2025-05-20 13:32:48', '2025-05-20 13:32:48'),
(42, 'ASİL BY TURİZM OTELCİLİK TİCARET LİMİTED ŞİRKETİ', 'Murat Bey', '', '0(542)139 86 18', 'HOCAPAŞA MAH. HÜDAVENDİGAR CAD. NO: 21 FATİH/İSTANBUL', NULL, NULL, '2025-05-20 13:37:41', '2025-05-20 13:37:41'),
(43, 'YURTSEVER TURİZM VE GIDA İNŞAAT TİCARET ANONİM ŞİRKETİ', 'Murat Bey', '', '0(542) 139 86 18', 'HOCAPAŞA MAH. KARGILI ÇIKMAZI SK. CELAL ORMAN IŞ\r\nHANI NO: 6 İÇ KAPI NO: 1 FATİH/ İSTANBUL', NULL, NULL, '2025-05-20 13:44:53', '2025-05-20 13:44:53'),
(44, 'ÖZBAR GIDA TURİZM İNŞAAT SANAYİ VE TİCARET LİMİTED ŞİRKETİ', 'Murat Bey', '', '0(542) 139 86 18', 'HOCAPAŞA MAH. HÜDAVENDİGAR ÇIKMAZI SK. NO: 1 FATİH/İSTANBUL', NULL, NULL, '2025-05-20 13:46:34', '2025-05-20 13:46:34'),
(45, 'ARMİN OTELCİLİK TURZ. İNŞ. TİC. LTD. ŞTİ.', 'Ömer Faruk KALELİ', '', '0(507) 864 9925', 'Hacıilyas mah. Agah efendi sok. 2/1 AMASYA', NULL, NULL, '2025-05-21 08:16:52', '2025-05-21 08:16:52'),
(46, 'SELCAN OFSET MAATBACILIK REKLAM TURİZM', 'Can Berkay ŞAHİN', '', '0(542) 609 5323', '', NULL, NULL, '2025-05-21 09:49:50', '2025-05-21 09:49:50'),
(47, 'MASqCOD COLLECTİON', '', '', '', '', NULL, NULL, '2025-05-22 05:16:44', '2025-05-22 05:16:44'),
(48, 'BİLEK HOTEL  LEVENT', '', '', '0(212) 324 2024', 'Sultan Selim Mahallesi, Levent, Eski Büyükdere Cd. 33/A D:34415 4, 34415 Kağıthane/İstanbul', NULL, NULL, '2025-05-22 07:35:49', '2025-05-22 07:35:49'),
(49, 'Helen Otelcilik A.Ş. TAKSİM', '', '', '0(532) 326 8695', 'Gümüssuyu Mh. Siraselviler Cd.  No:7/1 Taksim  34437 Beyoglu ISTANBUL', NULL, NULL, '2025-05-22 09:03:42', '2025-05-22 09:03:42'),
(50, 'Kemal Stone House', 'Kemal bey', '', '0(534) 795 8550', 'Tekelli mahallesi Karlık sok no:47\r\nUçhisar kasabası/NEVŞEHİR', NULL, NULL, '2025-05-23 06:21:21', '2025-05-23 06:21:21'),
(51, 'HOTEL TORUN - Kamil Alper TORUN', 'Kamil Alper TORUN', 'info@hoteltorun.com.tr', '0(212) 534 4550', 'Topkapı Mahallesi, Hamam Odaları Aralığı No: 9 Topkapı - İstanbul', NULL, NULL, '2025-05-23 10:22:34', '2025-05-23 10:22:34'),
(52, 'The print house hotel', 'Jeyhun Bey', '', '', '', NULL, NULL, '2025-05-23 10:28:14', '2025-05-23 10:28:14'),
(53, 'Murat bey', '', '', '0(542) 139 86 18', '', NULL, NULL, '2025-06-04 11:59:45', '2025-06-04 11:59:45'),
(54, 'PEL GALATA OTELCİLİK TURİZM SEYAHAT VE ORGANİZASYON HİZMETLERİ SANAYİ VE TİCARET LİMİTED ŞİRKETİ', '0(507) 565 5054', '', '', 'ŞAHKULU MAH. KÜÇÜK HENDEK CAD. NO: 11 İÇ KAPI NO: 1\r\nBEYOĞLU/ İSTANBUL', NULL, NULL, '2025-06-10 10:26:38', '2025-06-10 10:33:47'),
(55, 'EFES PRENSES TURIZM OTELCİLİK', 'MURAT BEY', '', '', '', NULL, NULL, '2025-06-13 08:36:22', '2025-06-13 08:36:22'),
(56, 'SNR OTELCİLİK MEDİCAL TEKSTİL OTO KİRALAMA TURIZM SANAYİ  TİCARET LİMİTED ŞİRKETİ', 'AYŞE HANIM', '', '05360615463', 'BURSA', NULL, NULL, '2025-06-16 12:54:15', '2025-06-16 12:54:15'),
(57, 'SNR OTELCİLİK MEDİCAL TEKSTİL OTO KİRALAMA TURIZM SANAYİ  TİCARET LİMİTED ŞİRKETİ', 'AYŞE HANIM', '', '', '', NULL, NULL, '2025-06-17 06:18:19', '2025-06-17 06:18:19'),
(58, 'SVENGALİ DESINGNS', 'MR JEREMY TABANSİ', 'jeremy@svengalidesigns.com', '+2348022239716', 'nıjerya', NULL, NULL, '2025-06-17 06:49:22', '2025-06-17 06:49:22'),
(59, 'MURAT BEY', '', '', '', '', NULL, NULL, '2025-06-17 07:35:34', '2025-06-17 07:35:34'),
(60, 'THE ABIES', 'KÜRŞAT BEY', '', '', '', NULL, NULL, '2025-06-17 09:19:24', '2025-06-17 09:19:24'),
(61, 'AYVALIK BEACH HOTEL', '', '', '', '', NULL, NULL, '2025-06-17 11:34:48', '2025-06-17 11:34:48'),
(62, 'TALEN HOTEL', '', '', '', '', NULL, NULL, '2025-06-18 05:53:55', '2025-06-18 05:53:55'),
(63, 'NEVAL TURIZM OTELCİLİK KUYUMCULUK HALICILIK VE SANAYİ VE TİCARET LİMİTED ŞİRKETİ', 'MURAT BEY', '', '05421398618', '', NULL, NULL, '2025-06-18 10:15:03', '2025-06-18 13:50:01'),
(64, 'AVEC HOTEL SEYEHAT TURIZM', '', '', '', '', NULL, NULL, '2025-06-20 14:14:56', '2025-06-20 14:14:56'),
(65, 'WALTON HOTEL GALATA', '', '', '', '', NULL, NULL, '2025-06-25 08:35:25', '2025-06-25 08:35:25'),
(66, 'WALTON HOTEL GALATA', '', '', '', '', NULL, NULL, '2025-06-25 08:35:35', '2025-06-25 08:35:35'),
(67, 'WALTON HOTEL GALATA', '', '', '', 'GALATA', NULL, NULL, '2025-06-25 08:35:45', '2025-06-25 08:35:45'),
(68, 'VEZİRKÖPRÜ BELEDİYESİ', '', '', '', '', NULL, NULL, '2025-06-26 11:20:45', '2025-06-26 11:20:45'),
(69, 'BY DEDEMAN', '', '', '', '', NULL, NULL, '2025-06-27 04:19:18', '2025-06-27 04:19:18'),
(70, 'STELLA TAKSİM', '', '', '', '', NULL, NULL, '2025-07-04 05:38:16', '2025-07-04 05:38:16'),
(71, 'LÜTFÜ BEY KONAĞI', 'LÜTFÜ BEY', '', '', '', NULL, NULL, '2025-07-08 04:55:05', '2025-07-08 04:59:32'),
(72, 'HOSTEL LEBENCH', '', '', '', '', NULL, NULL, '2025-07-08 05:01:28', '2025-07-08 05:01:28'),
(73, 'dream stonehouse', 'sultan hanım', '', '05398594925', 'nevşehir/kapadokya', NULL, NULL, '2025-07-16 09:47:04', '2025-07-16 09:47:04'),
(74, 'İKON YAPI ve GAYRİMENKUL TİC.A.Ş.', 'NEŞE HANIM', '', '05423725278', '', NULL, NULL, '2025-07-16 10:27:36', '2025-07-16 10:27:36'),
(75, 'ARAR TURİZM İNŞ.PETROL GIDA TİC.AN.ŞTİ.', 'SÜLEYMAN BEY', '', '05433400621', '', NULL, NULL, '2025-07-17 07:42:04', '2025-07-17 07:42:04'),
(76, 'buket kimya', 'sultan hanım', '', '05059489916', '', NULL, NULL, '2025-07-17 13:04:20', '2025-07-17 13:04:20'),
(77, 'HOTEL KLAS', 'HOTEL KLAS', '', '05356549644', '', NULL, NULL, '2025-07-25 09:11:01', '2025-07-25 09:11:01'),
(79, 'ZONGULDAK OTEL', '', '', '', '', NULL, NULL, '2025-07-28 06:12:03', '2025-07-28 06:12:03'),
(80, 'HUBİ HOTELCİLİK ve TURİZM LTD.ŞTİ.', 'YASİN BEY', '', '05452272664', '', NULL, NULL, '2025-07-28 11:31:46', '2025-07-28 11:31:46'),
(81, 'PRİME BOSPHORUS', 'BERK BEY', '', '05342492929', '', NULL, NULL, '2025-07-28 13:38:57', '2025-07-28 13:38:57'),
(83, 'ZHEJIANG WILLING FOREIGN TR CO MAKINA', 'EYLÜL HANIM', '', '', '', NULL, NULL, '2025-08-14 04:53:01', '2025-08-14 04:53:01'),
(84, 'ORANGE COUNTY HOTEL', 'HÜLYA HANIM', '', '', '', NULL, NULL, '2025-08-14 08:38:00', '2025-08-14 08:38:00'),
(85, 'TUBA HANIM', '', '', '', 'KAPADOKYA', NULL, NULL, '2025-08-19 12:27:24', '2025-08-19 12:27:24'),
(86, 'ÖZEL PENDİK HASTANESİ', '', '', '', '', NULL, NULL, '2025-08-20 10:31:30', '2025-08-20 10:31:30'),
(87, 'TÜLAY HANIM', '', '', '', '', NULL, NULL, '2025-08-27 04:42:16', '2025-08-27 04:42:16'),
(88, 'HOTEL quantum', '', '', '', '', NULL, NULL, '2025-09-03 07:41:46', '2025-09-03 07:41:46'),
(89, 'MEHMET CANTÜRK', 'MEHMET CANTÜRK', 'mehmetcanturk@hotmail.com', '05334988905', 'yeşiltepe mah. atik sk. venedik apt. no:11 daire:08', NULL, NULL, '2025-09-05 14:44:28', '2025-09-05 14:44:28'),
(91, 'MİSAFİR 1', '', '', '', '', NULL, NULL, '2025-10-15 10:44:04', '2025-10-15 10:44:04'),
(92, '360 Tasarım', 'Ogün Öztürk', 'oogunoozturk@gmail.com', '054272612312', 'Demirciler Sitesi, 3. Yol, No:41', NULL, NULL, '2025-10-30 21:17:20', '2025-10-30 21:17:20');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_interactions`
--

CREATE TABLE `customer_interactions` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('call','email','meeting','note','whatsapp','sms') NOT NULL DEFAULT 'note',
  `subject` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'Dakika cinsinden süre',
  `outcome` enum('positive','negative','neutral','follow_up_needed') DEFAULT 'neutral',
  `next_action` text DEFAULT NULL,
  `next_action_date` datetime DEFAULT NULL,
  `is_important` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `customer_interactions`
--

INSERT INTO `customer_interactions` (`id`, `customer_id`, `user_id`, `type`, `subject`, `description`, `contact_person`, `duration`, `outcome`, `next_action`, `next_action_date`, `is_important`, `created_at`, `updated_at`) VALUES
(1, 11, 4, 'call', 'Fiyat teklifi görüşmesi', 'iletşim detayları paylaşılacak sonra', '', NULL, 'follow_up_needed', 'aranacak', '2025-10-30 02:03:00', 1, '2025-10-28 23:03:07', '2025-10-28 23:03:07');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_notes`
--

CREATE TABLE `customer_notes` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `is_private` tinyint(1) DEFAULT 0 COMMENT 'Sadece oluşturan kullanıcı görebilir',
  `color` varchar(7) DEFAULT '#fbbf24' COMMENT 'Hex renk kodu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `customer_notes`
--

INSERT INTO `customer_notes` (`id`, `customer_id`, `user_id`, `note`, `is_private`, `color`, `created_at`, `updated_at`) VALUES
(1, 75, 4, 'deneme notu', 0, '#10b981', '2025-10-28 23:02:13', '2025-10-28 23:02:13'),
(2, 75, 4, 'xcvxcv', 0, '#8b5cf6', '2025-10-28 23:11:38', '2025-10-28 23:11:38'),
(3, 76, 4, 'vbn', 0, '#6b7280', '2025-10-28 23:11:45', '2025-10-28 23:11:45');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dashboard_widgets`
--

CREATE TABLE `dashboard_widgets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `widget_type` varchar(50) NOT NULL,
  `position_x` int(11) DEFAULT 0,
  `position_y` int(11) DEFAULT 0,
  `width` int(11) DEFAULT 4,
  `height` int(11) DEFAULT 3,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Tablo döküm verisi `dashboard_widgets`
--

INSERT INTO `dashboard_widgets` (`id`, `user_id`, `widget_type`, `position_x`, `position_y`, `width`, `height`, `settings`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, 4, 'stats_cards', 0, 0, 12, 2, '{\"show_quotes\": true, \"show_customers\": true, \"show_products\": true, \"show_revenue\": true}', 1, '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(2, 4, 'recent_quotes', 0, 2, 8, 4, '{\"limit\": 5, \"show_status\": true}', 1, '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(3, 4, 'sales_chart', 8, 2, 4, 4, '{\"period\": \"month\", \"chart_type\": \"line\"}', 1, '2025-10-22 12:26:29', '2025-10-22 12:26:29');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL,
  `to_email` varchar(255) NOT NULL,
  `to_name` varchar(255) DEFAULT NULL,
  `subject` varchar(500) NOT NULL,
  `body` text NOT NULL,
  `is_html` tinyint(1) DEFAULT 1,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `attempts` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('info','success','warning','error','system') DEFAULT 'info',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `action_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Tablo döküm verisi `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `is_read`, `action_url`, `created_at`, `read_at`) VALUES
(1, 4, 'info', 'Hoş Geldiniz!', 'CRM Pro sistemine hoş geldiniz. Sistem özelliklerini keşfetmek için dashboard\'u inceleyebilirsiniz.', 1, '/', '2025-10-22 12:26:29', '2025-10-22 12:57:41'),
(2, 4, 'info', 'Yeni Teklif Oluşturuldu', 'AEROSER için TKL-20251025-68FC8C7310D87 numaralı teklif oluşturuldu. Tutar: 7.500,00 ₺', 1, '/modules/quotes/list.php', '2025-10-25 08:38:11', '2025-10-25 09:29:26'),
(3, 6, 'info', 'Yeni Teklif Oluşturuldu', 'AEROSER için TKL-20251025-68FC8C7310D87 numaralı teklif oluşturuldu. Tutar: 7.500,00 ₺', 0, '/modules/quotes/list.php', '2025-10-25 08:38:11', NULL),
(4, 4, 'success', 'Teklif Durumu Değişti', 'AEROSER için TKL-20251025-68FC8C7310D87 numaralı teklif durumu \'Taslak\' → \'Kabul Edildi\' olarak değiştirildi.', 1, '/modules/quotes/list.php', '2025-10-25 08:38:16', '2025-10-25 09:29:26'),
(5, 6, 'success', 'Teklif Durumu Değişti', 'AEROSER için TKL-20251025-68FC8C7310D87 numaralı teklif durumu \'Taslak\' → \'Kabul Edildi\' olarak değiştirildi.', 0, '/modules/quotes/list.php', '2025-10-25 08:38:16', NULL),
(6, 4, 'info', 'Yeni Teklif Oluşturuldu', 'ASİL BY TURİZM OTELCİLİK TİCARET LİMİTED ŞİRKETİ için TKL-20251028-690152720C784 numaralı teklif oluşturuldu. Tutar: 240,00 ₺', 1, '/modules/quotes/list.php', '2025-10-28 23:32:02', '2025-10-30 13:42:41'),
(7, 6, 'info', 'Yeni Teklif Oluşturuldu', 'ASİL BY TURİZM OTELCİLİK TİCARET LİMİTED ŞİRKETİ için TKL-20251028-690152720C784 numaralı teklif oluşturuldu. Tutar: 240,00 ₺', 0, '/modules/quotes/list.php', '2025-10-28 23:32:02', NULL),
(8, 4, 'info', 'Yeni Teklif Oluşturuldu', 'BİLEK HOTEL  LEVENT için TKL-20251029-690234D6443FA numaralı teklif oluşturuldu. Tutar: 360,00 ₺', 1, '/modules/quotes/list.php', '2025-10-29 15:37:58', '2025-10-30 13:42:41'),
(9, 6, 'info', 'Yeni Teklif Oluşturuldu', 'BİLEK HOTEL  LEVENT için TKL-20251029-690234D6443FA numaralı teklif oluşturuldu. Tutar: 360,00 ₺', 0, '/modules/quotes/list.php', '2025-10-29 15:37:58', NULL),
(10, 4, 'info', 'Yeni Teklif Oluşturuldu', 'AVEC HOTEL SEYEHAT TURIZM için TKL-20251029-6902355A9FD2D numaralı teklif oluşturuldu. Tutar: 480,00 ₺', 1, '/modules/quotes/list.php', '2025-10-29 15:40:10', '2025-10-30 13:42:41'),
(11, 6, 'info', 'Yeni Teklif Oluşturuldu', 'AVEC HOTEL SEYEHAT TURIZM için TKL-20251029-6902355A9FD2D numaralı teklif oluşturuldu. Tutar: 480,00 ₺', 0, '/modules/quotes/list.php', '2025-10-29 15:40:10', NULL),
(12, 4, 'info', 'Yeni Teklif Oluşturuldu', 'ARAR TURİZM İNŞ.PETROL GIDA TİC.AN.ŞTİ. için TKL-20251030-69036A8BA44A5 numaralı teklif oluşturuldu. Tutar: 25.681,00 ₺', 1, '/modules/quotes/list.php', '2025-10-30 13:39:23', '2025-10-30 13:42:41'),
(13, 6, 'info', 'Yeni Teklif Oluşturuldu', 'ARAR TURİZM İNŞ.PETROL GIDA TİC.AN.ŞTİ. için TKL-20251030-69036A8BA44A5 numaralı teklif oluşturuldu. Tutar: 25.681,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 13:39:23', NULL),
(14, 4, 'info', 'Yeni Teklif Oluşturuldu', 'COZZY GRUP için TKL-20251030-69036B8884908 numaralı teklif oluşturuldu. Tutar: 17.075,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 13:43:36', NULL),
(15, 6, 'info', 'Yeni Teklif Oluşturuldu', 'COZZY GRUP için TKL-20251030-69036B8884908 numaralı teklif oluşturuldu. Tutar: 17.075,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 13:43:36', NULL),
(16, 4, 'info', 'Yeni Teklif Oluşturuldu', 'buket kimya için TKL-20251030-69037A7F037F3 numaralı teklif oluşturuldu. Tutar: 93.760,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 14:47:27', NULL),
(17, 6, 'info', 'Yeni Teklif Oluşturuldu', 'buket kimya için TKL-20251030-69037A7F037F3 numaralı teklif oluşturuldu. Tutar: 93.760,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 14:47:27', NULL),
(18, 4, 'info', 'Yeni Teklif Oluşturuldu', 'COZZY GRUP için TKL-20251030-69037B28AFCD4 numaralı teklif oluşturuldu. Tutar: 23.220,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 14:50:16', NULL),
(19, 6, 'info', 'Yeni Teklif Oluşturuldu', 'COZZY GRUP için TKL-20251030-69037B28AFCD4 numaralı teklif oluşturuldu. Tutar: 23.220,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 14:50:16', NULL),
(20, 4, 'info', 'Yeni Teklif Oluşturuldu', 'BİRİZ OTELCİLİK VE TURİZM TİC. LTD. ŞTİ. için TKL-20251030-69037C0D1CE99 numaralı teklif oluşturuldu. Tutar: 42.560,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 14:54:05', NULL),
(21, 6, 'info', 'Yeni Teklif Oluşturuldu', 'BİRİZ OTELCİLİK VE TURİZM TİC. LTD. ŞTİ. için TKL-20251030-69037C0D1CE99 numaralı teklif oluşturuldu. Tutar: 42.560,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 14:54:05', NULL),
(22, 4, 'info', 'Yeni Teklif Oluşturuldu', 'BOSNA FİRMASI için TKL-20251030-690381F418296 numaralı teklif oluşturuldu. Tutar: 40.085,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 15:19:16', NULL),
(23, 6, 'info', 'Yeni Teklif Oluşturuldu', 'BOSNA FİRMASI için TKL-20251030-690381F418296 numaralı teklif oluşturuldu. Tutar: 40.085,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 15:19:16', NULL),
(24, 4, 'info', 'Yeni Teklif Oluşturuldu', 'ASİL BY TURİZM OTELCİLİK TİCARET LİMİTED ŞİRKETİ için TKL-20251030-690386D3BBE8F numaralı teklif oluşturuldu. Tutar: 35.465,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 15:40:03', NULL),
(25, 6, 'info', 'Yeni Teklif Oluşturuldu', 'ASİL BY TURİZM OTELCİLİK TİCARET LİMİTED ŞİRKETİ için TKL-20251030-690386D3BBE8F numaralı teklif oluşturuldu. Tutar: 35.465,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 15:40:03', NULL),
(26, 4, 'info', 'Yeni Teklif Oluşturuldu', 'ASİL BY TURİZM OTELCİLİK TİCARET LİMİTED ŞİRKETİ için TKL-20251030-6903879198424 numaralı teklif oluşturuldu. Tutar: 35.465,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 15:43:13', NULL),
(27, 6, 'info', 'Yeni Teklif Oluşturuldu', 'ASİL BY TURİZM OTELCİLİK TİCARET LİMİTED ŞİRKETİ için TKL-20251030-6903879198424 numaralı teklif oluşturuldu. Tutar: 35.465,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 15:43:13', NULL),
(28, 4, 'info', 'Yeni Teklif Oluşturuldu', 'BOSNA FİRMASI için TKL-20251030-6903896220EE5 numaralı teklif oluşturuldu. Tutar: 30.205,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 15:50:58', NULL),
(29, 6, 'info', 'Yeni Teklif Oluşturuldu', 'BOSNA FİRMASI için TKL-20251030-6903896220EE5 numaralı teklif oluşturuldu. Tutar: 30.205,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 15:50:58', NULL),
(30, 4, 'info', 'Yeni Teklif Oluşturuldu', 'ARAR TURİZM İNŞ.PETROL GIDA TİC.AN.ŞTİ. için TKL-20251030-6903D37FEFD32 numaralı teklif oluşturuldu. Tutar: 44.980,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 21:07:11', NULL),
(31, 6, 'info', 'Yeni Teklif Oluşturuldu', 'ARAR TURİZM İNŞ.PETROL GIDA TİC.AN.ŞTİ. için TKL-20251030-6903D37FEFD32 numaralı teklif oluşturuldu. Tutar: 44.980,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 21:07:11', NULL),
(32, 4, 'success', 'Teklif Durumu Değişti', 'ARAR TURİZM İNŞ.PETROL GIDA TİC.AN.ŞTİ. için TKL-20251030-6903D37FEFD32 numaralı teklif durumu \'Taslak\' → \'Kabul Edildi\' olarak değiştirildi.', 0, '/modules/quotes/list.php', '2025-10-30 21:08:19', NULL),
(33, 6, 'success', 'Teklif Durumu Değişti', 'ARAR TURİZM İNŞ.PETROL GIDA TİC.AN.ŞTİ. için TKL-20251030-6903D37FEFD32 numaralı teklif durumu \'Taslak\' → \'Kabul Edildi\' olarak değiştirildi.', 0, '/modules/quotes/list.php', '2025-10-30 21:08:19', NULL),
(34, 4, 'success', 'Yeni Müşteri Eklendi', 'Yeni müşteri kaydedildi: 360 Tasarım (Ogün Öztürk)', 0, '/modules/customers/list.php', '2025-10-30 21:17:20', NULL),
(35, 6, 'success', 'Yeni Müşteri Eklendi', 'Yeni müşteri kaydedildi: 360 Tasarım (Ogün Öztürk)', 0, '/modules/customers/list.php', '2025-10-30 21:17:20', NULL),
(36, 4, 'info', 'Yeni Teklif Oluşturuldu', '360 Tasarım için TKL-20251030-6903D676096C5 numaralı teklif oluşturuldu. Tutar: 45.167,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 21:19:50', NULL),
(37, 6, 'info', 'Yeni Teklif Oluşturuldu', '360 Tasarım için TKL-20251030-6903D676096C5 numaralı teklif oluşturuldu. Tutar: 45.167,00 ₺', 0, '/modules/quotes/list.php', '2025-10-30 21:19:50', NULL),
(38, 4, 'success', 'Teklif Durumu Değişti', '360 Tasarım için TKL-20251030-6903D676096C5 numaralı teklif durumu \'Taslak\' → \'Kabul Edildi\' olarak değiştirildi.', 0, '/modules/quotes/list.php', '2025-10-30 21:21:36', NULL),
(39, 6, 'success', 'Teklif Durumu Değişti', '360 Tasarım için TKL-20251030-6903D676096C5 numaralı teklif durumu \'Taslak\' → \'Kabul Edildi\' olarak değiştirildi.', 0, '/modules/quotes/list.php', '2025-10-30 21:21:36', NULL),
(40, 4, 'success', 'Teklif Durumu Değişti', '360 Tasarım için TKL-20251030-6903D676096C5 numaralı teklif durumu \'Kabul Edildi\' → \'Reddedildi\' olarak değiştirildi.', 0, '/modules/quotes/list.php', '2025-10-30 21:23:18', NULL),
(41, 6, 'success', 'Teklif Durumu Değişti', '360 Tasarım için TKL-20251030-6903D676096C5 numaralı teklif durumu \'Kabul Edildi\' → \'Reddedildi\' olarak değiştirildi.', 0, '/modules/quotes/list.php', '2025-10-30 21:23:18', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expiry` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock_code` varchar(100) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Tablo döküm verisi `products`
--

INSERT INTO `products` (`id`, `category_id`, `stock_code`, `name`, `description`, `price`, `stock_quantity`, `vat_rate`, `image_url`, `created_at`, `updated_at`) VALUES
(10001, 16, 'GAZ-2X2-690', '2x2 Gazebo 690 Denye Çatı Saçak', '', 1800.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/03/1-4.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10002, 16, 'GAZ-2X2-200', '2x2 Gazebo 200 Oxford Çatı Saçak', '', 2400.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/03/1-4.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10003, 16, 'GAZ-2X2-DIG', '2x2 Gazebo Dijital Baskı Çatı Saçak', '', 4900.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/03/1-4.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10004, 16, 'GAZ-2X3-690', '2x3 Gazebo 690 Denye Çatı Saçak', '', 2650.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/03/10-1-scaled.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10005, 16, 'GAZ-2X3-200', '2x3 Gazebo 200 Oxford Çatı Saçak', '', 3350.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/03/10-1-scaled.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10006, 16, 'GAZ-2X3-DIG', '2x3 Gazebo Dijital Baskı Çatı Saçak', '', 6200.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/03/10-1-scaled.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10007, 16, 'GAZ-3X3-690', '3x3 Gazebo 690 Denye Çatı Saçak', '', 2650.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/300x300-beyaz-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10008, 16, 'GAZ-3X3-200', '3x3 Gazebo 200 Oxford Çatı Saçak', '', 3350.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/300x300-beyaz-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10009, 16, 'GAZ-3X3-DIG', '3x3 Gazebo Dijital Baskı Çatı Saçak', '', 6200.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/300x300-beyaz-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10010, 16, 'GAZ-3X4.5-690', '3x4.5 Gazebo 690 Denye Çatı Saçak', '', 3900.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/01/1-2-768x686.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10011, 16, 'GAZ-3X4.5-200', '3x4.5 Gazebo 200 Oxford Çatı Saçak', '', 4800.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/01/1-2-768x686.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10012, 16, 'GAZ-3X4.5-DIG', '3x4.5 Gazebo Dijital Baskı Çatı Saçak', '', 9000.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/01/1-2-768x686.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10013, 16, 'GAZ-3X6-690', '3x6 Gazebo 690 Denye Çatı Saçak', '', 8500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/01/1-3-768x619.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10014, 16, 'GAZ-3X6-200', '3x6 Gazebo 200 Oxford Çatı Saçak', '', 9500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/01/1-3-768x619.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10015, 16, 'GAZ-3X6-DIG', '3x6 Gazebo Dijital Baskı Çatı Saçak', '', 15750.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/01/1-3-768x619.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10016, 16, 'GAZ-4X4-690', '4x4 Gazebo 690 Denye Çatı Saçak', '', 7000.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/01/3-4-768x768.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10017, 16, 'GAZ-4X4-200', '4x4 Gazebo 200 Oxford Çatı Saçak', '', 7300.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/01/3-4-768x768.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10018, 16, 'GAZ-4X4-DIG', '4x4 Gazebo Dijital Baskı Çatı Saçak', '', 13000.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/01/3-4-768x768.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10019, 16, 'GAZ-4X6-690', '4x6 Gazebo 690 Denye Çatı Saçak', '', 8500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/01/11-4-768x619.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10020, 16, 'GAZ-4X6-200', '4x6 Gazebo 200 Oxford Çatı Saçak', '', 9500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/01/11-4-768x619.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10021, 16, 'GAZ-4X6-DIG', '4x6 Gazebo Dijital Baskı Çatı Saçak', '', 15750.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/01/11-4-768x619.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10022, 16, 'GAZ-4X8-690', '4x8 Gazebo 690 Denye Çatı Saçak', '', 10000.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/01/11-4-768x619.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10023, 16, 'GAZ-4X8-200', '4x8 Gazebo 200 Oxford Çatı Saçak', '', 15500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/01/11-4-768x619.jpg', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(10024, 16, 'GAZ-4X8-DIG', '4x8 Gazebo Dijital Baskı Çatı Saçak', '', 22000.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2025/01/11-4-768x619.jpg1', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11001, 17, 'ISK-2X2-30C', '2x2 30luk Çelik İskelet', '', 2300.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/30luk-celik-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:34:30'),
(11002, 17, 'ISK-2X3-30C', '2x3 30luk Çelik İskelet', '', 3000.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/30luk-celik-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11003, 17, 'ISK-3X3-30C', '3x3 30luk Çelik İskelet', '', 2800.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/30luk-celik-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11004, 17, 'ISK-3X3-40C', '3x3 40luk Çelik İskelet', '', 4100.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/40lik-celik-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11005, 17, 'ISK-3X3-40A', '3x3 40lik Alüminyum İskelet', '', 12500.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/40lik-aluminyum-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11006, 17, 'ISK-3X3-52A', '3x3 52lik Alüminyum İskelet', '', 14500.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/52lik-aluminyum-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11007, 17, 'ISK-3X4.5-40C', '3x4.5 40luk Çelik İskelet', '', 5800.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/40lik-celik-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11008, 17, 'ISK-3X4.5-40A', '3x4.5 40lik Alüminyum İskelet', '', 13000.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/40lik-aluminyum-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11009, 17, 'ISK-3X4.5-52A', '3x4.5 52lik Alüminyum İskelet', '', 17500.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/52lik-aluminyum-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11010, 17, 'ISK-3X6-40C', '3x6 40luk Çelik İskelet', '', 7800.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/40lik-celik-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11011, 17, 'ISK-3X6-40A', '3x6 40lik Alüminyum İskelet', '', 18000.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/40lik-aluminyum-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11012, 17, 'ISK-3X6-52A', '3x6 52lik Alüminyum İskelet', '', 33000.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/52lik-aluminyum-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11013, 17, 'ISK-4X4-40C', '4x4 40luk Çelik İskelet', '', 7250.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/40lik-celik-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11014, 17, 'ISK-4X4-52A', '4x4 52lik Alüminyum İskelet', '', 22000.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/52lik-aluminyum-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11015, 17, 'ISK-4X6-40C', '4x6 40luk Çelik İskelet', '', 12000.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/40lik-celik-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11016, 17, 'ISK-4X6-52A', '4x6 52lik Alüminyum İskelet', '', 23500.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/52lik-aluminyum-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11017, 17, 'ISK-4X8-40C', '4x8 40luk Çelik İskelet', '', 13000.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/40lik-celik-iskelet-768x768.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(11018, 17, 'ISK-4X8-52A', '4x8 52lik Alüminyum İskelet', '', 26000.00, 100, 20.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/52lik-aluminyum-iskelet-768x768.webp120', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12001, 18, 'DUV-2M-TAM-690', '2m Tam Duvar 690 Denye', '', 950.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:15:16', '2025-10-30 13:34:16'),
(12002, 18, 'DUV-2M-TAM-200', '2m Tam Duvar 200 Oxford', '', 1200.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12003, 18, 'DUV-2M-TAM-DIG', '2m Tam Duvar Dijital Baskı', '', 1500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12004, 18, 'DUV-2M-YAR-690', '2m Yarım Duvar 690 Denye', '', 850.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12005, 18, 'DUV-2M-YAR-200', '2m Yarım Duvar 200 Oxford', '', 950.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12006, 18, 'DUV-2M-YAR-DIG', '2m Yarım Duvar Dijital Baskı', '', 1050.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12007, 18, 'DUV-2M-SEF', '2m Şeffaf Duvar', '', 1000.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-duvar.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12008, 18, 'DUV-2M-FER-690', '2m Fermuarlı Duvar 690 Denye', '', 1065.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12009, 18, 'DUV-2M-FER-200', '2m Fermuarlı Duvar 200 Oxford', '', 1250.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12010, 18, 'DUV-2M-FER-DIG', '2m Fermuarlı Duvar Dijital Baskı', '', 1500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12011, 18, 'DUV-2M-SIN', '2m Sineklik Duvar', '', 1000.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklik-duvar.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12012, 18, 'DUV-2M-KAP-690', '2m Kapılı Duvar 690 Denye', '', 1220.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12013, 18, 'DUV-2M-KAP-200', '2m Kapılı Duvar 200 Oxford', '', 1440.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12014, 18, 'DUV-2M-KAP-DIG', '2m Kapılı Duvar Dijital Baskı', '', 1560.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12015, 18, 'DUV-2M-SINKAP', '2m Sineklikli Kapı', '', 1620.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapi.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12016, 18, 'DUV-2M-KAPSE-690', '2m Kapı + Şeffaf Pencere 690 Denye', '', 1380.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapiseffaf-pencere.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12017, 18, 'DUV-2M-KAPSE-200', '2m Kapı + Şeffaf Pencere 200 Oxford', '', 1860.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapiseffaf-pencere.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12018, 18, 'DUV-2M-SINKAPSE-690', '2m Sineklikli Kapı + Şeffaf Perde 690 Denye', '', 1920.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapiseffaf-pencere.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12019, 18, 'DUV-2M-SINKAPSE-200', '2m Sineklikli Kapı + Şeffaf Perde 200 Oxford', '', 1860.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapiseffaf-pencere.webp', '2025-10-30 13:15:16', '2025-10-30 13:15:16'),
(12020, 18, 'DUV-2M-SEFSINPER-690', '2m Şeffaf Sineklik Perde + Kapı 690 Denye', '', 2040.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklikli-perdeseffaf-sineklikli-kapi.webp12021', '2025-10-30 13:15:16', '2025-10-30 13:33:08'),
(12021, 18, 'DUV-2M-SEFSINPER-200', '2m Şeffaf Sineklik Perde + Kapı 200 Oxford', '', 2160.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklikli-perdeseffaf-sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12022, 18, 'DUV-2M-SEFCAM-690', '2m Şeffaf Camlı Duvar 690 Denye', '', 1200.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12023, 18, 'DUV-2M-SEFCAM-200', '2m Şeffaf Camlı Duvar 200 Oxford', '', 1380.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12024, 18, 'DUV-2M-SEFCAMPER-690', '2m Şeffaf Camlı Perdeli Duvar 690 Denye', '', 1320.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-perdeli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12025, 18, 'DUV-2M-SEFCAMPER-200', '2m Şeffaf Camlı Perdeli Duvar 200 Oxford', '', 1500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-perdeli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12026, 18, 'DUV-2M-SEFSINPERCAM-690', '2m Şeffaf Sineklik Perdeli Camlı Duvar 690 Denye', '', 1440.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklik-perdeli-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12027, 18, 'DUV-2M-SEFSINPERCAM-200', '2m Şeffaf Sineklik Perdeli Camlı Duvar 200 Oxford', '', 1620.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklik-perdeli-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12028, 18, 'DUV-3M-TAM-690', '3m Tam Duvar 690 Denye', '', 1850.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12029, 18, 'DUV-3M-TAM-200', '3m Tam Duvar 200 Oxford', '', 1560.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12030, 18, 'DUV-3M-TAM-DIG', '3m Tam Duvar Dijital Baskı', '', 2250.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12031, 18, 'DUV-3M-YAR-690', '3m Yarım Duvar 690 Denye', '', 800.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12032, 18, 'DUV-3M-YAR-200', '3m Yarım Duvar 200 Oxford', '', 900.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12033, 18, 'DUV-3M-YAR-DIG', '3m Yarım Duvar Dijital Baskı', '', 1300.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12034, 18, 'DUV-3M-SEF-690', '3m Şeffaf Duvar 690 Denye', '', 1420.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12035, 18, 'DUV-3M-SEF-200', '3m Şeffaf Duvar 200 Oxford', '', 1800.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12036, 18, 'DUV-3M-FER-690', '3m Fermuarlı Duvar 690 Denye', '', 1300.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12037, 18, 'DUV-3M-FER-200', '3m Fermuarlı Duvar 200 Oxford', '', 1680.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12038, 18, 'DUV-3M-SIN-690', '3m Sineklik Duvar 690 Denye', '', 1420.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklik-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12039, 18, 'DUV-3M-SIN-200', '3m Sineklik Duvar 200 Oxford', '', 1800.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklik-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12040, 18, 'DUV-3M-KAP-690', '3m Kapılı Duvar 690 Denye', '', 1420.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12041, 18, 'DUV-3M-KAP-200', '3m Kapılı Duvar 200 Oxford', '', 1800.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12042, 18, 'DUV-3M-KAP-DIG', '3m Kapılı Duvar Dijital Baskı', '', 2160.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12043, 18, 'DUV-3M-SINKAP-690', '3m Sineklikli Kapı 690 Denye', '', 1650.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12044, 18, 'DUV-3M-SINKAP-200', '3m Sineklikli Kapı 200 Oxford', '', 2050.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12045, 18, 'DUV-3M-KAPSE-690', '3m Kapı + Şeffaf Pencere 690 Denye', '', 1650.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12046, 18, 'DUV-3M-KAPSE-200', '3m Kapı + Şeffaf Pencere 200 Oxford', '', 2050.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12047, 18, 'DUV-3M-SINKAPSE-690', '3m Sineklikli Kapı + Şeffaf Perde 690 Denye', '', 1900.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12048, 18, 'DUV-3M-SINKAPSE-200', '3m Sineklikli Kapı + Şeffaf Perde 200 Oxford', '', 2280.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12049, 18, 'DUV-3M-SEFSINPER-690', '3m Şeffaf Sineklik Perde + Kapı 690 Denye', '', 2370.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklikli-perdeseffaf-sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12050, 18, 'DUV-3M-SEFSINPER-200', '3m Şeffaf Sineklik Perde + Kapı 200 Oxford', '', 2760.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklikli-perdeseffaf-sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12051, 18, 'DUV-3M-SEFCAM-690', '3m Şeffaf Camlı Duvar 690 Denye', '', 1560.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12052, 18, 'DUV-3M-SEFCAM-200', '3m Şeffaf Camlı Duvar 200 Oxford', '', 2000.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12053, 18, 'DUV-3M-SEFCAMPER-690', '3m Şeffaf Camlı Perdeli Duvar 690 Denye', '', 1690.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-perdeli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12054, 18, 'DUV-3M-SEFCAMPER-200', '3m Şeffaf Camlı Perdeli Duvar 200 Oxford', '', 2100.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-perdeli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12055, 18, 'DUV-3M-SEFSINPERCAM-690', '3m Şeffaf Sineklik Perdeli Camlı Duvar 690 Denye', '', 1800.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklik-perdeli-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12056, 18, 'DUV-3M-SEFSINPERCAM-200', '3m Şeffaf Sineklik Perdeli Camlı Duvar 200 Oxford', '', 2220.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklik-perdeli-camli-duvar.webp12057', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12057, 18, 'DUV-4M-TAM-690', '4m Tam Duvar 690 Denye', '', 1600.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12058, 18, 'DUV-4M-TAM-200', '4m Tam Duvar 200 Oxford', '', 1900.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12059, 18, 'DUV-4M-TAM-DIG', '4m Tam Duvar Dijital Baskı', '', 2600.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12060, 18, 'DUV-4M-YAR-690', '4m Yarım Duvar 690 Denye', '', 1400.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12061, 18, 'DUV-4M-YAR-200', '4m Yarım Duvar 200 Oxford', '', 1500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12062, 18, 'DUV-4M-YAR-DIG', '4m Yarım Duvar Dijital Baskı', '', 2250.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12063, 18, 'DUV-4M-SEF-690', '4m Şeffaf Duvar 690 Denye', '', 1900.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12064, 18, 'DUV-4M-SEF-200', '4m Şeffaf Duvar 200 Oxford', '', 2250.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12065, 18, 'DUV-4M-FER-690', '4m Fermuarlı Duvar 690 Denye', '', 1700.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12066, 18, 'DUV-4M-FER-200', '4m Fermuarlı Duvar 200 Oxford', '', 2050.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12067, 18, 'DUV-4M-FER-DIG', '4m Fermuarlı Duvar Dijital Baskı', '', 3100.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12068, 18, 'DUV-4M-SIN-690', '4m Sineklik Duvar 690 Denye', '', 1900.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklik-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12069, 18, 'DUV-4M-SIN-200', '4m Sineklik Duvar 200 Oxford', '', 2250.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklik-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12070, 18, 'DUV-4M-KAP-690', '4m Kapılı Duvar 690 Denye', '', 1850.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12071, 18, 'DUV-4M-KAP-200', '4m Kapılı Duvar 200 Oxford', '', 2150.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12072, 18, 'DUV-4M-KAP-DIG', '4m Kapılı Duvar Dijital Baskı', '', 3200.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12073, 18, 'DUV-4M-SINKAP-690', '4m Sineklikli Kapı 690 Denye', '', 2350.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12074, 18, 'DUV-4M-SINKAP-200', '4m Sineklikli Kapı 200 Oxford', '', 2650.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12075, 18, 'DUV-4M-KAPSE-690', '4m Kapı + Şeffaf Pencere 690 Denye', '', 2200.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12076, 18, 'DUV-4M-KAPSE-200', '4m Kapı + Şeffaf Pencere 200 Oxford', '', 2500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12077, 18, 'DUV-4M-SINKAPSE-690', '4m Sineklikli Kapı + Şeffaf Perde 690 Denye', '', 2700.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12078, 18, 'DUV-4M-SINKAPSE-200', '4m Sineklikli Kapı + Şeffaf Perde 200 Oxford', '', 3100.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12079, 18, 'DUV-4M-SEFSINPER-690', '4m Şeffaf Sineklik Perde + Kapı 690 Denye', '', 2800.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklikli-perdeseffaf-sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12080, 18, 'DUV-4M-SEFSINPER-200', '4m Şeffaf Sineklik Perde + Kapı 200 Oxford', '', 3240.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklikli-perdeseffaf-sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12081, 18, 'DUV-4M-SEFCAM-690', '4m Şeffaf Camlı Duvar 690 Denye', '', 1850.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12082, 18, 'DUV-4M-SEFCAM-200', '4m Şeffaf Camlı Duvar 200 Oxford', '', 2150.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12083, 18, 'DUV-4M-SEFCAMPER-690', '4m Şeffaf Camlı Perdeli Duvar 690 Denye', '', 2000.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-perdeli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12084, 18, 'DUV-4M-SEFCAMPER-200', '4m Şeffaf Camlı Perdeli Duvar 200 Oxford', '', 2250.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-perdeli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12085, 18, 'DUV-4M-SEFSINPERCAM-690', '4m Şeffaf Sineklik Perdeli Camlı Duvar 690 Denye', '', 2200.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklik-perdeli-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12086, 18, 'DUV-4M-SEFSINPERCAM-200', '4m Şeffaf Sineklik Perdeli Camlı Duvar 200 Oxford', '', 2500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklik-perdeli-camli-duvar.webp12087', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12087, 18, 'DUV-4.5M-TAM-690', '4.5m Tam Duvar 690 Denye', '', 1850.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12088, 18, 'DUV-4.5M-TAM-200', '4.5m Tam Duvar 200 Oxford', '', 2100.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12089, 18, 'DUV-4.5M-TAM-DIG', '4.5m Tam Duvar Dijital Baskı', '', 3250.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12090, 18, 'DUV-4.5M-YAR-690', '4.5m Yarım Duvar 690 Denye', '', 1800.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12091, 18, 'DUV-4.5M-YAR-200', '4.5m Yarım Duvar 200 Oxford', '', 2100.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12092, 18, 'DUV-4.5M-YAR-DIG', '4.5m Yarım Duvar Dijital Baskı', '', 2250.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12093, 18, 'DUV-4.5M-SEF-690', '4.5m Şeffaf Duvar 690 Denye', '', 2250.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12094, 18, 'DUV-4.5M-SEF-200', '4.5m Şeffaf Duvar 200 Oxford', '', 2900.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12095, 18, 'DUV-4.5M-FER-690', '4.5m Fermuarlı Duvar 690 Denye', '', 1950.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12096, 18, 'DUV-4.5M-FER-200', '4.5m Fermuarlı Duvar 200 Oxford', '', 2250.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12097, 18, 'DUV-4.5M-FER-DIG', '4.5m Fermuarlı Duvar Dijital Baskı', '', 2760.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12098, 18, 'DUV-4.5M-SIN-690', '4.5m Sineklik Duvar 690 Denye', '', 2250.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklik-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12099, 18, 'DUV-4.5M-SIN-200', '4.5m Sineklik Duvar 200 Oxford', '', 2900.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklik-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12100, 18, 'DUV-4.5M-KAP-690', '4.5m Kapılı Duvar 690 Denye', '', 2050.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12101, 18, 'DUV-4.5M-KAP-200', '4.5m Kapılı Duvar 200 Oxford', '', 2250.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12102, 18, 'DUV-4.5M-KAP-DIG', '4.5m Kapılı Duvar Dijital Baskı', '', 3350.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12103, 18, 'DUV-4.5M-SINKAP-690', '4.5m Sineklikli Kapı 690 Denye', '', 2300.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12104, 18, 'DUV-4.5M-SINKAP-200', '4.5m Sineklikli Kapı 200 Oxford', '', 2800.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12105, 18, 'DUV-4.5M-KAPSE-690', '4.5m Kapı + Şeffaf Pencere 690 Denye', '', 2300.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12106, 18, 'DUV-4.5M-KAPSE-200', '4.5m Kapı + Şeffaf Pencere 200 Oxford', '', 2600.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12107, 18, 'DUV-4.5M-SINKAPSE-690', '4.5m Sineklikli Kapı + Şeffaf Perde 690 Denye', '', 2600.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12108, 18, 'DUV-4.5M-SINKAPSE-200', '4.5m Sineklikli Kapı + Şeffaf Perde 200 Oxford', '', 3250.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12109, 18, 'DUV-4.5M-SEFSINPER-690', '4.5m Şeffaf Sineklik Perde + Kapı 690 Denye', '', 2700.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklikli-perdeseffaf-sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12110, 18, 'DUV-4.5M-SEFSINPER-200', '4.5m Şeffaf Sineklik Perde + Kapı 200 Oxford', '', 3100.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklikli-perdeseffaf-sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12111, 18, 'DUV-4.5M-SEFCAM-690', '4.5m Şeffaf Camlı Duvar 690 Denye', '', 2550.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12112, 18, 'DUV-4.5M-SEFCAM-200', '4.5m Şeffaf Camlı Duvar 200 Oxford', '', 2800.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12113, 18, 'DUV-4.5M-SEFCAMPER-690', '4.5m Şeffaf Camlı Perdeli Duvar 690 Denye', '', 2750.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-perdeli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12114, 18, 'DUV-4.5M-SEFCAMPER-200', '4.5m Şeffaf Camlı Perdeli Duvar 200 Oxford', '', 3050.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-perdeli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12115, 18, 'DUV-4.5M-SEFSINPERCAM-690', '4.5m Şeffaf Sineklik Perdeli Camlı Duvar 690 Denye', '', 3000.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklik-perdeli-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12116, 18, 'DUV-4.5M-SEFSINPERCAM-200', '4.5m Şeffaf Sineklik Perdeli Camlı Duvar 200 Oxford', '', 3300.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklik-perdeli-camli-duvar.webp121', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12117, 18, 'DUV-6M-TAM-690', '6m Tam Duvar 690 Denye', '', 2600.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12118, 18, 'DUV-6M-TAM-200', '6m Tam Duvar 200 Oxford', '', 2750.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12119, 18, 'DUV-6M-TAM-DIG', '6m Tam Duvar Dijital Baskı', '', 4200.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12120, 18, 'DUV-6M-YAR-690', '6m Yarım Duvar 690 Denye', '', 2100.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12121, 18, 'DUV-6M-YAR-200', '6m Yarım Duvar 200 Oxford', '', 1900.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12122, 18, 'DUV-6M-YAR-DIG', '6m Yarım Duvar Dijital Baskı', '', 3400.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12123, 18, 'DUV-6M-SEF-690', '6m Şeffaf Duvar 690 Denye', '', 2700.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12124, 18, 'DUV-6M-SEF-200', '6m Şeffaf Duvar 200 Oxford', '', 3200.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12125, 18, 'DUV-6M-FER-690', '6m Fermuarlı Duvar 690 Denye', '', 2450.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12126, 18, 'DUV-6M-FER-200', '6m Fermuarlı Duvar 200 Oxford', '', 2820.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12127, 18, 'DUV-6M-FER-DIG', '6m Fermuarlı Duvar Dijital Baskı', '', 4900.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12128, 18, 'DUV-6M-SIN-690', '6m Sineklik Duvar 690 Denye', '', 2700.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklik-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12129, 18, 'DUV-6M-SIN-200', '6m Sineklik Duvar 200 Oxford', '', 3300.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklik-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12130, 18, 'DUV-6M-KAP-690', '6m Kapılı Duvar 690 Denye', '', 2650.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12131, 18, 'DUV-6M-KAP-200', '6m Kapılı Duvar 200 Oxford', '', 3050.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12132, 18, 'DUV-6M-KAP-DIG', '6m Kapılı Duvar Dijital Baskı', '', 5150.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12133, 18, 'DUV-6M-SINKAP-690', '6m Sineklikli Kapı 690 Denye', '', 3100.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12134, 18, 'DUV-6M-SINKAP-200', '6m Sineklikli Kapı 200 Oxford', '', 3550.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12135, 18, 'DUV-6M-KAPSE-690', '6m Kapı + Şeffaf Pencere 690 Denye', '', 3100.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12136, 18, 'DUV-6M-KAPSE-200', '6m Kapı + Şeffaf Pencere 200 Oxford', '', 3550.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12137, 18, 'DUV-6M-SINKAPSE-690', '6m Sineklikli Kapı + Şeffaf Perde 690 Denye', '', 3800.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12138, 18, 'DUV-6M-SINKAPSE-200', '6m Sineklikli Kapı + Şeffaf Perde 200 Oxford', '', 4350.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12139, 18, 'DUV-6M-SEFSINPER-690', '6m Şeffaf Sineklik Perde + Kapı 690 Denye', '', 3950.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklikli-perdeseffaf-sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12140, 18, 'DUV-6M-SEFSINPER-200', '6m Şeffaf Sineklik Perde + Kapı 200 Oxford', '', 4550.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklikli-perdeseffaf-sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12141, 18, 'DUV-6M-SEFCAM-690', '6m Şeffaf Camlı Duvar 690 Denye', '', 2650.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12142, 18, 'DUV-6M-SEFCAM-200', '6m Şeffaf Camlı Duvar 200 Oxford', '', 3000.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12143, 18, 'DUV-6M-SEFCAMPER-690', '6m Şeffaf Camlı Perdeli Duvar 690 Denye', '', 2750.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-perdeli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12144, 18, 'DUV-6M-SEFCAMPER-200', '6m Şeffaf Camlı Perdeli Duvar 200 Oxford', '', 3100.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-perdeli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12145, 18, 'DUV-6M-SEFSINPERCAM-690', '6m Şeffaf Sineklik Perdeli Camlı Duvar 690 Denye', '', 3150.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklik-perdeli-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12146, 18, 'DUV-6M-SEFSINPERCAM-200', '6m Şeffaf Sineklik Perdeli Camlı Duvar 200 Oxford', '', 3450.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklik-perdeli-camli-duvar.webp12147', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12147, 18, 'DUV-8M-TAM-690', '8m Tam Duvar 690 Denye', '', 3500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12148, 18, 'DUV-8M-TAM-200', '8m Tam Duvar 200 Oxford', '', 3900.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12149, 18, 'DUV-8M-TAM-DIG', '8m Tam Duvar Dijital Baskı', '', 5600.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12150, 18, 'DUV-8M-YAR-690', '8m Yarım Duvar 690 Denye', '', 2800.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12151, 18, 'DUV-8M-YAR-200', '8m Yarım Duvar 200 Oxford', '', 1950.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12152, 18, 'DUV-8M-YAR-DIG', '8m Yarım Duvar Dijital Baskı', '', 4500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12153, 18, 'DUV-8M-SEF-690', '8m Şeffaf Duvar 690 Denye', '', 3790.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12154, 18, 'DUV-8M-SEF-200', '8m Şeffaf Duvar 200 Oxford', '', 4500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12155, 18, 'DUV-8M-FER-690', '8m Fermuarlı Duvar 690 Denye', '', 3500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12156, 18, 'DUV-8M-FER-200', '8m Fermuarlı Duvar 200 Oxford', '', 4000.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12157, 18, 'DUV-8M-FER-DIG', '8m Fermuarlı Duvar Dijital Baskı', '', 6800.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12158, 18, 'DUV-8M-SIN-690', '8m Sineklik Duvar 690 Denye', '', 3750.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklik-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12159, 18, 'DUV-8M-SIN-200', '8m Sineklik Duvar 200 Oxford', '', 4500.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklik-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12160, 18, 'DUV-8M-KAP-690', '8m Kapılı Duvar 690 Denye', '', 3750.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12161, 18, 'DUV-8M-KAP-200', '8m Kapılı Duvar 200 Oxford', '', 4250.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12162, 18, 'DUV-8M-KAP-DIG', '8m Kapılı Duvar Dijital Baskı', '', 7050.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12163, 18, 'DUV-8M-SINKAP-690', '8m Sineklikli Kapı 690 Denye', '', 4200.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12164, 18, 'DUV-8M-SINKAP-200', '8m Sineklikli Kapı 200 Oxford', '', 4750.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12165, 18, 'DUV-8M-KAPSE-690', '8m Kapı + Şeffaf Pencere 690 Denye', '', 4200.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12166, 18, 'DUV-8M-KAPSE-200', '8m Kapı + Şeffaf Pencere 200 Oxford', '', 4750.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12167, 18, 'DUV-8M-SINKAPSE-690', '8m Sineklikli Kapı + Şeffaf Perde 690 Denye', '', 4700.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12168, 18, 'DUV-8M-SINKAPSE-200', '8m Sineklikli Kapı + Şeffaf Perde 200 Oxford', '', 5200.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapiseffaf-pencere.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12169, 18, 'DUV-8M-SEFSINPER-690', '8m Şeffaf Sineklik Perde + Kapı 690 Denye', '', 4900.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklikli-perdeseffaf-sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12170, 18, 'DUV-8M-SEFSINPER-200', '8m Şeffaf Sineklik Perde + Kapı 200 Oxford', '', 5200.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklikli-perdeseffaf-sineklikli-kapi.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12171, 18, 'DUV-8M-SEFCAM-690', '8m Şeffaf Camlı Duvar 690 Denye', '', 3850.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12172, 18, 'DUV-8M-SEFCAM-200', '8m Şeffaf Camlı Duvar 200 Oxford', '', 4350.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12173, 18, 'DUV-8M-SEFCAM-DIG', '8m Şeffaf Camlı Duvar Dijital Baskı', '', 6750.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12174, 18, 'DUV-8M-SEFCAMPER-690', '8m Şeffaf Camlı Perdeli Duvar 690 Denye', '', 4050.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-perdeli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12175, 18, 'DUV-8M-SEFCAMPER-200', '8m Şeffaf Camlı Perdeli Duvar 200 Oxford', '', 4600.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-perdeli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12176, 18, 'DUV-8M-SEFSINPERCAM-690', '8m Şeffaf Sineklik Perdeli Camlı Duvar 690 Denye', '', 4450.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklik-perdeli-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08'),
(12177, 18, 'DUV-8M-SEFSINPERCAM-200', '8m Şeffaf Sineklik Perdeli Camlı Duvar 200 Oxford', '', 4850.00, 100, 10.00, 'https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklik-perdeli-camli-duvar.webp', '2025-10-30 13:33:08', '2025-10-30 13:33:08');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Tablo döküm verisi `product_categories`
--

INSERT INTO `product_categories` (`id`, `name`, `created_at`) VALUES
(15, 'Gazebo Tente', '2025-10-30 11:03:41'),
(16, 'Gazebo Ana Ürün', '2025-10-30 12:50:53'),
(17, 'Gazebo İskelet', '2025-10-30 12:50:53'),
(18, 'Gazebo Duvar', '2025-10-30 12:50:53');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `quotes`
--

CREATE TABLE `quotes` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `quote_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `vat_amount` decimal(10,2) NOT NULL,
  `status` enum('draft','sent','accepted','rejected') DEFAULT 'draft',
  `valid_until` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Tablo döküm verisi `quotes`
--

INSERT INTO `quotes` (`id`, `customer_id`, `quote_number`, `total_amount`, `vat_amount`, `status`, `valid_until`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(269, 75, 'TKL-20251030-69036A8BA44A5', 25681.00, 3471.00, 'draft', '2025-11-29', 'Bu teklif 30 gün geçerlidir.\nFiyatlara KDV dahildir.\nTeslimat süresi sipariş onayından sonra 5 iş günüdür.', 4, '2025-10-30 13:39:23', '2025-10-30 13:39:23'),
(270, 7, 'TKL-20251030-69036B8884908', 0.00, 0.00, 'draft', '2025-11-29', 'Bu teklif 30 gün geçerlidir.\nFiyatlara KDV dahildir.\nTeslimat süresi sipariş onayından sonra 5 iş günüdür.', 4, '2025-10-30 13:43:36', '2025-10-30 13:46:36'),
(271, 76, 'TKL-20251030-69037A7F037F3', 93760.00, 13160.00, 'draft', '2025-11-29', 'Bu teklif 30 gün geçerlidir.\nFiyatlara KDV dahildir.\nTeslimat süresi sipariş onayından sonra 5 iş günüdür.', 4, '2025-10-30 14:47:27', '2025-10-30 14:47:27'),
(272, 7, 'TKL-20251030-69037B28AFCD4', 23220.00, 2770.00, 'draft', '2025-11-29', 'Bu teklif 30 gün geçerlidir.\nFiyatlara KDV dahildir.\nTeslimat süresi sipariş onayından sonra 5 iş günüdür.', 4, '2025-10-30 14:50:16', '2025-10-30 14:50:16'),
(273, 40, 'TKL-20251030-69037C0D1CE99', 42560.00, 4960.00, 'draft', '2025-11-29', 'Bu teklif 30 gün geçerlidir.\nFiyatlara KDV dahildir.\nTeslimat süresi sipariş onayından sonra 5 iş günüdür.', 4, '2025-10-30 14:54:05', '2025-10-30 14:54:05'),
(274, 15, 'TKL-20251030-690381F418296', 40085.00, 4735.00, 'draft', '2025-11-29', 'Bu teklif 30 gün geçerlidir.\nFiyatlara KDV dahildir.\nTeslimat süresi sipariş onayından sonra 5 iş günüdür.', 4, '2025-10-30 15:19:16', '2025-10-30 15:19:16'),
(275, 42, 'TKL-20251030-690386D3BBE8F', 35465.00, 4315.00, 'draft', '2025-11-29', 'Bu teklif 30 gün geçerlidir.\nFiyatlara KDV dahildir.\nTeslimat süresi sipariş onayından sonra 5 iş günüdür.', 4, '2025-10-30 15:40:03', '2025-10-30 15:40:03'),
(276, 42, 'TKL-20251030-6903879198424', 35465.00, 4315.00, 'draft', '2025-11-29', 'Bu teklif 30 gün geçerlidir.\nFiyatlara KDV dahildir.\nTeslimat süresi sipariş onayından sonra 5 iş günüdür.', 4, '2025-10-30 15:43:13', '2025-10-30 15:43:13'),
(277, 15, 'TKL-20251030-6903896220EE5', 30205.00, 3405.00, 'draft', '2025-11-29', 'Bu teklif 30 gün geçerlidir.\nFiyatlara KDV dahildir.\nTeslimat süresi sipariş onayından sonra 5 iş günüdür.', 4, '2025-10-30 15:50:58', '2025-10-30 15:50:58'),
(278, 75, 'TKL-20251030-6903D37FEFD32', 0.00, 0.00, 'accepted', '2025-11-29', 'Bu teklif 30 gün geçerlidir.\nFiyatlara KDV dahildir.\nTeslimat süresi sipariş onayından sonra 5 iş günüdür.', 4, '2025-10-30 21:07:11', '2025-10-30 21:08:19'),
(279, 92, 'TKL-20251030-6903D676096C5', 0.00, 0.00, 'rejected', '2025-11-29', 'Bu teklif 30 gün geçerlidir.\nFiyatlara KDV dahildir.\nTeslimat süresi sipariş onayından sonra 5 iş günüdür.', 4, '2025-10-30 21:19:50', '2025-10-30 21:23:18');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `quote_items`
--

CREATE TABLE `quote_items` (
  `id` int(11) NOT NULL,
  `quote_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `vat_rate` decimal(5,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Tablo döküm verisi `quote_items`
--

INSERT INTO `quote_items` (`id`, `quote_id`, `product_id`, `quantity`, `unit_price`, `vat_rate`, `total_amount`, `created_at`) VALUES
(1, 269, 10008, 1, 3350.00, 10.00, 25681.00, '2025-10-30 13:39:23'),
(2, 269, 11005, 1, 12500.00, 10.00, 25681.00, '2025-10-30 13:39:23'),
(3, 269, 12042, 1, 2050.00, 10.00, 25681.00, '2025-10-30 13:39:23'),
(4, 269, 12032, 1, 900.00, 10.00, 25681.00, '2025-10-30 13:39:23'),
(5, 269, 12036, 1, 1680.00, 10.00, 25681.00, '2025-10-30 13:39:23'),
(6, 269, 12052, 1, 2100.00, 10.00, 25681.00, '2025-10-30 13:39:23'),
(11, 270, 10009, 1, 6200.00, 10.00, 6820.00, '2025-10-30 13:46:36'),
(12, 270, 11004, 1, 4100.00, 20.00, 4920.00, '2025-10-30 13:46:36'),
(13, 270, 12030, 1, 2250.00, 10.00, 2475.00, '2025-10-30 13:46:36'),
(14, 270, 12033, 2, 1300.00, 10.00, 2860.00, '2025-10-30 13:46:36'),
(15, 271, 10002, 1, 2400.00, 10.00, 93760.00, '2025-10-30 14:47:27'),
(16, 271, 11015, 1, 12000.00, 10.00, 93760.00, '2025-10-30 14:47:27'),
(17, 271, 11017, 1, 13000.00, 10.00, 93760.00, '2025-10-30 14:47:27'),
(18, 271, 10024, 1, 22000.00, 10.00, 93760.00, '2025-10-30 14:47:27'),
(19, 271, 11018, 1, 26000.00, 10.00, 93760.00, '2025-10-30 14:47:27'),
(20, 271, 12168, 1, 6750.00, 10.00, 93760.00, '2025-10-30 14:47:27'),
(21, 272, 10017, 1, 7300.00, 10.00, 23220.00, '2025-10-30 14:50:16'),
(22, 272, 11013, 1, 7250.00, 10.00, 23220.00, '2025-10-30 14:50:16'),
(23, 272, 12081, 1, 2250.00, 10.00, 23220.00, '2025-10-30 14:50:16'),
(24, 272, 12071, 1, 2650.00, 10.00, 23220.00, '2025-10-30 14:50:16'),
(25, 272, 12063, 1, 2250.00, 10.00, 23220.00, '2025-10-30 14:50:16'),
(26, 273, 10021, 1, 15750.00, 10.00, 42560.00, '2025-10-30 14:54:05'),
(27, 273, 11015, 1, 12000.00, 10.00, 42560.00, '2025-10-30 14:54:05'),
(28, 273, 12062, 1, 2250.00, 10.00, 42560.00, '2025-10-30 14:54:05'),
(29, 273, 12119, 1, 4200.00, 10.00, 42560.00, '2025-10-30 14:54:05'),
(30, 273, 12122, 1, 3400.00, 10.00, 42560.00, '2025-10-30 14:54:05'),
(31, 274, 10021, 1, 15750.00, 10.00, 40085.00, '2025-10-30 15:19:16'),
(32, 274, 11015, 1, 12000.00, 10.00, 40085.00, '2025-10-30 15:19:16'),
(33, 274, 12119, 1, 4200.00, 10.00, 40085.00, '2025-10-30 15:19:16'),
(34, 274, 12122, 1, 3400.00, 10.00, 40085.00, '2025-10-30 15:19:16'),
(35, 275, 10021, 1, 15750.00, 10.00, 35465.00, '2025-10-30 15:40:03'),
(36, 275, 11015, 1, 12000.00, 10.00, 35465.00, '2025-10-30 15:40:03'),
(37, 275, 12122, 1, 3400.00, 10.00, 35465.00, '2025-10-30 15:40:03'),
(38, 276, 10021, 1, 15750.00, 10.00, 35465.00, '2025-10-30 15:43:13'),
(39, 276, 11015, 1, 12000.00, 10.00, 35465.00, '2025-10-30 15:43:13'),
(40, 276, 12122, 1, 3400.00, 10.00, 35465.00, '2025-10-30 15:43:13'),
(41, 277, 10018, 1, 13000.00, 10.00, 14300.00, '2025-10-30 15:50:58'),
(42, 277, 11013, 1, 7250.00, 20.00, 8700.00, '2025-10-30 15:50:58'),
(43, 277, 12062, 2, 2250.00, 10.00, 4950.00, '2025-10-30 15:50:58'),
(44, 277, 12066, 1, 2050.00, 10.00, 2255.00, '2025-10-30 15:50:58'),
(50, 278, 10021, 1, 15750.00, 10.00, 17325.00, '2025-10-30 21:08:03'),
(51, 278, 11015, 1, 12000.00, 20.00, 14400.00, '2025-10-30 21:08:03'),
(52, 278, 12059, 2, 2600.00, 10.00, 5720.00, '2025-10-30 21:08:03'),
(53, 278, 12119, 1, 4200.00, 10.00, 4620.00, '2025-10-30 21:08:03'),
(54, 278, 12130, 1, 2650.00, 10.00, 2915.00, '2025-10-30 21:08:03'),
(65, 279, 10021, 1, 15750.00, 10.00, 17325.00, '2025-10-30 21:21:15'),
(66, 279, 11015, 1, 12000.00, 20.00, 14400.00, '2025-10-30 21:21:15'),
(67, 279, 12059, 2, 2600.00, 10.00, 5720.00, '2025-10-30 21:21:15'),
(68, 279, 12119, 1, 4200.00, 10.00, 4620.00, '2025-10-30 21:21:15'),
(69, 279, 12126, 1, 2820.00, 10.00, 3102.00, '2025-10-30 21:21:15');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Tablo döküm verisi `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'company_name', 'Firma Adı A.Ş.', '2025-01-16 15:12:53', '2025-01-16 15:12:53'),
(2, 'company_address', 'Örnek Mahalle, Örnek Sokak No:1', '2025-01-16 15:12:53', '2025-01-16 15:12:53'),
(3, 'company_phone', '+90 123 456 7890', '2025-01-16 15:12:53', '2025-01-16 15:12:53'),
(4, 'company_email', 'info@firmaadi.com', '2025-01-16 15:12:53', '2025-01-16 15:12:53'),
(5, 'company_tax_office', 'Vergi Dairesi', '2025-01-16 15:12:53', '2025-01-16 15:12:53'),
(6, 'company_tax_number', '1234567890', '2025-01-16 15:12:53', '2025-01-16 15:12:53'),
(7, 'quote_note_template', 'Bu teklif 30 gün geçerlidir.\nFiyatlara KDV dahildir.\nTeslimat süresi sipariş onayından sonra 5 iş günüdür.', '2025-01-16 15:12:53', '2025-01-16 15:12:53');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','email','password','number','boolean') DEFAULT 'text',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Tablo döküm verisi `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'smtp_host', '', 'text', 'SMTP Sunucu Adresi', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(2, 'smtp_port', '587', 'number', 'SMTP Port', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(3, 'smtp_username', '', 'email', 'SMTP Kullanıcı Adı', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(4, 'smtp_password', '', 'password', 'SMTP Şifre', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(5, 'smtp_encryption', 'tls', 'text', 'SMTP Şifreleme (tls/ssl)', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(6, 'smtp_from_email', '', 'email', 'Gönderen E-posta', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(7, 'smtp_from_name', 'CRM Pro', 'text', 'Gönderen Adı', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(8, 'notification_email_enabled', '0', 'boolean', 'E-posta Bildirimleri Aktif', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(9, 'notification_system_enabled', '1', 'boolean', 'Sistem Bildirimleri Aktif', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(10, 'low_stock_threshold', '10', 'number', 'Düşük Stok Uyarı Eşiği', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(11, 'quote_expiry_days', '30', 'number', 'Teklif Geçerlilik Süresi (Gün)', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(12, 'backup_enabled', '0', 'boolean', 'Otomatik Yedekleme Aktif', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(13, 'backup_frequency', 'daily', 'text', 'Yedekleme Sıklığı', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(14, 'system_timezone', 'Europe/Istanbul', 'text', 'Sistem Saat Dilimi', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(15, 'date_format', 'd.m.Y', 'text', 'Tarih Formatı', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(16, 'currency_symbol', '₺', 'text', 'Para Birimi Sembolü', '2025-10-22 12:26:29', '2025-10-22 12:26:29'),
(17, 'items_per_page', '10', 'number', 'Sayfa Başına Kayıt Sayısı', '2025-10-22 12:26:29', '2025-10-22 12:26:29');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `two_factor_codes`
--

CREATE TABLE `two_factor_codes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(6) NOT NULL,
  `expiry` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role`, `last_login`, `created_at`, `updated_at`) VALUES
(4, 'admin', '$2y$10$eK4.jN3LFWFLxNbsaLycv.DraBITjVkKfsa2TJYj2oHezF/qCv84m', 'admin@example.com', 'Sema Öztürk', 'admin', '2025-10-31 10:55:50', '2025-01-16 16:30:51', '2025-10-31 10:55:50'),
(6, 'TUĞBA ÖZCAN', '$2y$10$WMHAIGelwjlcX8uwMk.d1.E4pwPwQx0qVhc95shAFkXiPSr9MoHFq', 'info@hepsibuklet.com', 'TUĞBA ÖZCAN', 'user', '2025-06-25 14:07:31', '2025-06-24 15:07:07', '2025-06-25 11:07:31');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_logs_user` (`user_id`);

--
-- Tablo için indeksler `company_settings`
--
ALTER TABLE `company_settings`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `customer_interactions`
--
ALTER TABLE `customer_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `type` (`type`),
  ADD KEY `created_at` (`created_at`);

--
-- Tablo için indeksler `customer_notes`
--
ALTER TABLE `customer_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `dashboard_widgets`
--
ALTER TABLE `dashboard_widgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Tablo için indeksler `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_attempts_ip` (`ip_address`),
  ADD KEY `idx_login_attempts_username` (`username`);

--
-- Tablo için indeksler `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `created_at` (`created_at`);

--
-- Tablo için indeksler `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_products_stock_code` (`stock_code`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_products_stock_code` (`stock_code`),
  ADD KEY `idx_products_name` (`name`);

--
-- Tablo için indeksler `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `quotes`
--
ALTER TABLE `quotes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quote_number` (`quote_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_quotes_customer` (`customer_id`);

--
-- Tablo için indeksler `quote_items`
--
ALTER TABLE `quote_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_quote_items_quote` (`quote_id`),
  ADD KEY `idx_quote_items_product` (`product_id`);

--
-- Tablo için indeksler `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Tablo için indeksler `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

--
-- Tablo için indeksler `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Tablo için indeksler `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- Tablo için AUTO_INCREMENT değeri `customer_interactions`
--
ALTER TABLE `customer_interactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `customer_notes`
--
ALTER TABLE `customer_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `dashboard_widgets`
--
ALTER TABLE `dashboard_widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- Tablo için AUTO_INCREMENT değeri `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12178;

--
-- Tablo için AUTO_INCREMENT değeri `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Tablo için AUTO_INCREMENT değeri `quotes`
--
ALTER TABLE `quotes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=280;

--
-- Tablo için AUTO_INCREMENT değeri `quote_items`
--
ALTER TABLE `quote_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- Tablo için AUTO_INCREMENT değeri `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Tablo için AUTO_INCREMENT değeri `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `customer_interactions`
--
ALTER TABLE `customer_interactions`
  ADD CONSTRAINT `customer_interactions_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_interactions_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `customer_notes`
--
ALTER TABLE `customer_notes`
  ADD CONSTRAINT `customer_notes_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_notes_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `dashboard_widgets`
--
ALTER TABLE `dashboard_widgets`
  ADD CONSTRAINT `dashboard_widgets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `quotes`
--
ALTER TABLE `quotes`
  ADD CONSTRAINT `quotes_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `quotes_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `quote_items`
--
ALTER TABLE `quote_items`
  ADD CONSTRAINT `quote_items_ibfk_1` FOREIGN KEY (`quote_id`) REFERENCES `quotes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quote_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Tablo kısıtlamaları `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Tablo kısıtlamaları `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  ADD CONSTRAINT `two_factor_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
