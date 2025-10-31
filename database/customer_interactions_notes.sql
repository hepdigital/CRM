-- Customer Interactions ve Notes tabloları

-- Customer Interactions tablosu
CREATE TABLE IF NOT EXISTS `customer_interactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('call','email','meeting','whatsapp','sms','note') NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text,
  `interaction_date` datetime NOT NULL,
  `status` enum('completed','pending','follow_up') DEFAULT 'completed',
  `follow_up_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `user_id` (`user_id`),
  KEY `interaction_date` (`interaction_date`),
  KEY `status` (`status`),
  CONSTRAINT `customer_interactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_interactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Notes tablosu
CREATE TABLE IF NOT EXISTS `customer_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `note` text NOT NULL,
  `color` varchar(7) DEFAULT '#ffc107',
  `is_private` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `user_id` (`user_id`),
  KEY `is_private` (`is_private`),
  CONSTRAINT `customer_notes_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_notes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Örnek veriler (opsiyonel)
INSERT IGNORE INTO `customer_interactions` (`customer_id`, `user_id`, `type`, `subject`, `description`, `interaction_date`, `status`) VALUES
(1, 1, 'call', 'İlk görüşme', 'Müşteri ile ilk telefon görüşmesi yapıldı. Gazebo tente ihtiyacı var.', '2024-10-30 10:00:00', 'completed'),
(1, 1, 'email', 'Teklif gönderildi', 'Müşteriye e-posta ile teklif gönderildi.', '2024-10-30 14:30:00', 'pending');

INSERT IGNORE INTO `customer_notes` (`customer_id`, `user_id`, `title`, `note`, `color`, `is_private`) VALUES
(1, 1, 'Önemli Not', 'Bu müşteri çok titiz, detaylı bilgi vermek gerekiyor.', '#dc3545', 0),
(1, 1, 'Takip', 'Hafta sonunda tekrar aranacak.', '#28a745', 1);