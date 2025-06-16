<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Eğer zaten giriş yapılmışsa ana sayfaya yönlendir
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

// Sabit admin bilgileri
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', '123456'); // Gerçek bir uygulamada şifre hash'lenmeli!

$username = $password = "";
$login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $login_err = "Lütfen kullanıcı adınızı girin.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $login_err = "Lütfen şifrenizi girin.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($login_err)) {
        if ($username == ADMIN_USERNAME && $password == ADMIN_PASSWORD) {
            // Şifre doğru, yeni bir session başlat
            $_SESSION["loggedin"] = true;
            $_SESSION["username"] = $username; // İstersen kullanıcı adını da saklayabilirsin
            
            // Ana sayfaya yönlendir
            header("location: index.php");
            exit;
        } else {
            // Şifre veya kullanıcı adı yanlış
            $login_err = "Geçersiz kullanıcı adı veya şifre.";
        }
    }
    
    if(!empty($login_err)){
        $_SESSION["login_err"] = $login_err;
        if(!empty($login_err)){
        $_SESSION["login_err"] = $login_err;
        $_SESSION["form_username"] = $username; // Başarısız girişte kullanıcı adını session'a kaydet
        header("location: login.php");
        exit;
    }
        header("location: login.php");
        exit;
    }
} else {
    // POST değilse login sayfasına yönlendir
    header("location: login.php");
    exit;
}
?>