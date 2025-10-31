<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_USER', 'u100389091_crm');
define('DB_PASS', 'l|mu1dYU8');
define('DB_NAME', 'u100389091_crm');

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        )
    );
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}