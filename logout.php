<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
// Tüm session değişkenlerini temizle
$_SESSION = array();
 
// Session'ı yok et
session_destroy();
 
// Login sayfasına yönlendir
header("location: login.php");
exit;
?>