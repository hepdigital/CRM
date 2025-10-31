<?php
// logout.php
session_start();

// Tüm session değişkenlerini temizle
$_SESSION = array();

// Session'ı yok et
session_destroy();

// Giriş sayfasına yönlendir
header('Location: login.php');
exit;
?>