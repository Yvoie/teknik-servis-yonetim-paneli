<?php
// Eğer zaten giriş yapılmışsa ana sayfaya yönlendir
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    // Proje kök URL'sini dinamik olarak alalım
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $project_root_folder = "teknik_servis"; // Proje klasörünüzün adı
    $project_root_url = $protocol . $host . "/" . $project_root_folder;

    header("location: " . $project_root_url . "/index.php");
    exit;
}
// require_once 'config/db.php'; // Bu sayfada DB bağlantısına gerek yok
$username = ""; // Hata durumunda input'ta kalması için
$login_err_message = ""; // Hata mesajını tutmak için

if (isset($_SESSION["login_err"])) {
    $login_err_message = $_SESSION["login_err"];
    unset($_SESSION["login_err"]); // Mesajı gösterdikten sonra temizle
}
if (isset($_SESSION["form_username"])) { // Eğer action_login.php'den kullanıcı adı geri gönderildiyse
    $username = htmlspecialchars($_SESSION["form_username"]);
    unset($_SESSION["form_username"]);
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi - Uludağ Teknik Servis</title>
    <?php
        if (!isset($project_root_url)) { // Eğer yukarıda tanımlanmadıysa
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            $project_root_folder = "teknik_servis";
            $project_root_url = $protocol . $host . "/" . $project_root_folder;
        }
    ?>
    <link rel="stylesheet" href="<?php echo $project_root_url; ?>/css/style.css"> <!-- Ana stil dosyası -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body.login-page {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            overflow: hidden; /* Animasyonlu arka planın taşmasını engelle */
        }

        .login-container {
            position: relative; /* Animasyonlu arka plan için */
            z-index: 1; /* Formun arka planın üzerinde kalması için */
            background-color: rgba(255, 255, 255, 0.95);
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 420px;
            text-align: center;
            animation: fadeInForm 0.8s ease-out;
        }

        @keyframes fadeInForm {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container h2 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 2em;
        }

        .login-container p.subtitle {
            color: #777;
            margin-bottom: 30px;
            font-size: 0.95em;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left; /* Label'ları sola yasla */
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 0.9em;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1em;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.3s ease;
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        .btn-login:active {
            transform: translateY(0px);
        }

        .alert-login-error { /* Özel hata mesajı stili */
            color: #D8000C;
            background-color: #FFD2D2;
            border: 1px solid #D8000C;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-size: 0.9em;
            animation: shakeError 0.5s;
        }
        
        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
        }

        /* Arka plan animasyonu için (opsiyonel) */
        .bg-bubbles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0; /* Formun arkasında kalması için */
            overflow: hidden;
        }

        .bg-bubbles li {
            position: absolute;
            list-style: none;
            display: block;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.15);
            bottom: -160px; /* Başlangıçta görünmesin */
            animation: 등장-etme 25s infinite; /* Türkçe karakter sorun yaratabilir, 'bubble-up' gibi bir isim daha iyi */
            animation-timing-function: linear;
            border-radius: 50%;
        }
        /* ... (bg-bubbles li:nth-child ve @keyframes bubble-up stilleri aşağıda) ... */
        .bg-bubbles li:nth-child(1){ left: 10%; animation-duration: 13s; animation-delay: 0s; width: 20px; height: 20px;}
        .bg-bubbles li:nth-child(2){ left: 20%; animation-duration: 17s; animation-delay: 1s; width: 30px; height: 30px;}
        .bg-bubbles li:nth-child(3){ left: 25%; animation-duration: 15s; animation-delay: 3s; }
        .bg-bubbles li:nth-child(4){ left: 40%; animation-duration: 18s; animation-delay: 0s; width: 35px; height: 35px;}
        .bg-bubbles li:nth-child(5){ left: 70%; animation-duration: 12s; animation-delay: 1s; }
        .bg-bubbles li:nth-child(6){ left: 80%; animation-duration: 22s; animation-delay: 3s; width: 25px; height: 25px;}
        .bg-bubbles li:nth-child(7){ left: 32%; animation-duration: 16s; animation-delay: 2s; width: 15px; height: 15px;}
        .bg-bubbles li:nth-child(8){ left: 55%; animation-duration: 20s; animation-delay: 4s; }
        .bg-bubbles li:nth-child(9){ left: 25%; animation-duration: 14s; animation-delay: 2s; width: 28px; height: 28px;}
        .bg-bubbles li:nth-child(10){ left: 90%; animation-duration: 19s; animation-delay: 5s; }

        @keyframes 등장-etme { /* veya 'bubble-up' */
            0%{ transform: translateY(0); opacity: 0.7; }
            100%{ transform: translateY(-1000px) rotate(600deg); opacity: 0; }
        }

    </style>
</head>
<body class="login-page"> <?php // Body'ye class ekledik ?>

    <ul class="bg-bubbles"> <?php // Animasyonlu arka plan için ?>
        <li></li><li></li><li></li><li></li><li></li>
        <li></li><li></li><li></li><li></li><li></li>
    </ul>

    <div class="login-container">
        <h2>Uludağ Teknik Servis Yönetim Paneli Girişi</h2>
        <p class="subtitle">Lütfen devam etmek için yönetici bilgilerinizi girin.</p>

        <?php 
        if(!empty($login_err_message)){
            echo '<div class="alert-login-error">' . htmlspecialchars($login_err_message) . '</div>';
        }        
        ?>

        <form action="<?php echo $project_root_url; ?>/action_login.php" method="post" novalidate>
            <div class="form-group">
                <label for="username">Kullanıcı Adı</label>
                <input type="text" name="username" id="username" class="form-control" value="<?php echo $username; ?>" required autofocus>
            </div>    
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-group" style="margin-top: 30px;">
                <input type="submit" class="btn-login" value="Giriş Yap">
            </div>
        </form>
    </div>    
</body>
</html>