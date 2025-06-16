<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // XAMPP varsayılan kullanıcı
define('DB_PASSWORD', '');     // XAMPP varsayılan şifre boş
define('DB_NAME', 'teknik_servis'); // <<--- TABLOLARININ BULUNDUĞU VERİTABANI ADI

/* Veritabanına bağlanmayı dene */
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Bağlantıyı kontrol et
if($conn === false){
    die("HATA: Veritabanı bağlantısı kurulamadı. " . mysqli_connect_error());
}

// Karakter setini ayarla (Türkçe karakterler için önemli)
mysqli_set_charset($conn, "utf8mb4");

// Hata raporlamayı açabilirsin (geliştirme aşamasında faydalı olur)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
?>