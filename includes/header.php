<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($project_root_url)) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $PROJECT_MAIN_FOLDER = "teknik_servis"; 
    $project_root_url = rtrim($protocol . $host . "/" . $PROJECT_MAIN_FOLDER, '/');
}

$current_page_for_redirect_check = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    if ($current_page_for_redirect_check != 'login.php' && $current_page_for_redirect_check != 'action_login.php') {
        header("location: " . $project_root_url . "/login.php");
        exit;
    }
}

$active_page_name = $current_page_for_redirect_check;
$current_directory_path = $_SERVER['REQUEST_URI'];
$current_dir_parts = explode('/', trim(parse_url($current_directory_path, PHP_URL_PATH), '/'));
$base_dir_index = array_search($PROJECT_MAIN_FOLDER, $current_dir_parts);
$current_module_dir = '';
$is_home_page = false;

if ($base_dir_index !== false) {
    if (isset($current_dir_parts[$base_dir_index + 1]) && !str_contains($current_dir_parts[$base_dir_index + 1], '.php')) {
        $current_module_dir = $current_dir_parts[$base_dir_index + 1];
    } elseif ($active_page_name == 'index.php' && (!isset($current_dir_parts[$base_dir_index + 1]) || $current_dir_parts[$base_dir_index + 1] == 'index.php')) {
        $current_module_dir = $PROJECT_MAIN_FOLDER; 
        $is_home_page = true;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrol Paneli | Uludağ Teknik Servis</title>
    <link rel="stylesheet" href="<?php echo $project_root_url; ?>/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --header-bg: #ffffff;
            --header-text: #2a3038;
            --header-border: #e0e0e0; 
            --nav-link-hover-bg: #f1f3f5;
            --nav-link-active-bg: #0069d9;
            --nav-link-active-text: #ffffff;
            --logout-btn-bg: #d9534f;
            --logout-btn-hover-bg: #c82333;
            --primary-accent: #0069d9;
            --body-bg: #f4f7f9;
            --content-wrapper-bg: #ffffff;
            --logo-color: #0056b3; 
        }
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--body-bg);
            margin:0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        main.container { flex-grow: 1; width: 100%; max-width: 100%; padding: 0; box-sizing: border-box; }
        .page-content-wrapper {
            max-width: 1500px;
            margin: 25px auto;
            padding: 30px 35px;
            background-color: var(--content-wrapper-bg);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            border-radius: 10px;
        }

        header.main-header {
            background: var(--header-bg);
            color: var(--header-text);
            padding: 0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.07);
            position: sticky;
            top: 0;
            z-index: 1030;
            width: 100%;
            border-bottom: 1px solid var(--header-border);
        }
        header.main-header.animate-header-gentle {
            animation: slideDownGentle 0.7s cubic-bezier(0.23, 1, 0.32, 1) 0.1s forwards; /* Küçük bir gecikme eklendi */
            opacity: 0; /* Animasyon başlangıcında gizli */
            animation-fill-mode: forwards;
        }
        .header-container {
            max-width: 1500px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
            height: 85px; /* Header yüksekliği biraz azaltıldı (95px'ten) */
        }
        .logo-section {
            display: flex;
            align-items: center;
        }
        .logo-section .site-logo-svg-mountain {
            width: 48px; /* Logo boyutu biraz küçültüldü (55px'ten) */
            height: 48px;
            margin-right: 12px; /* Boşluk ayarlandı */
            color: var(--logo-color);
            transition: transform 0.3s ease;
        }
        .logo-section:hover .site-logo-svg-mountain {
            transform: scale(1.1); /* Hover'da hafif büyüme */
        }
        .logo-section .site-brand-text .site-title-main {
            font-family: 'Poppins', sans-serif;
            font-size: 1.9em; /* Ana başlık küçültüldü (2.1em veya 2.4em'den) */
            margin: 0;
            font-weight: 600; /* Biraz daha ince */
            color: #212529; /* Daha standart bir siyah */
            letter-spacing: -0.5px;
            line-height: 1.1;
        }
        .logo-section .site-brand-text .panel-tag-main {
            font-size: 0.8em; /* Biraz küçültüldü */
            font-weight: 500;
            color: #5a6268; /* Biraz daha koyu gri */
            display: block;
            line-height: 1.2;
            margin-top: 3px;
        }

        header.main-header nav.main-navigation ul { list-style: none; margin: 0; padding: 0; display: flex; align-items: center; }
        header.main-header nav.main-navigation ul li { margin-left: 8px; } /* Linkler arası boşluk */
        header.main-header nav.main-navigation ul li:first-child { margin-left: 0; }
        header.main-header nav.main-navigation ul li a {
            color: #495057;
            text-decoration: none;
            padding: 10px 16px; /* Padding ayarlandı */
            border-radius: 6px;
            transition: all 0.2s ease-out;
            font-weight: 500; /* Biraz daha ince */
            font-size: 0.92em; /* Font küçültüldü */
            display: flex;
            align-items: center;
            position: relative;
        }
        header.main-header nav.main-navigation ul li a::after {
            content: ''; position: absolute; width: 0; height: 2px; display: block;
            margin-top: 4px; right: 50%; background: var(--primary-accent);
            transition: all 0.25s ease-out; bottom: 6px; transform: translateX(50%);
        }
        header.main-header nav.main-navigation ul li a:hover::after,
        header.main-header nav.main-navigation ul li a.active-nav-link::after {
            width: calc(100% - 20px); /* Padding'e göre ayarla */
        }

        header.main-header nav.main-navigation ul li a i { margin-right: 7px; font-size: 1em; color: #6c757d; transition: color 0.2s ease;}
        header.main-header nav.main-navigation ul li a:hover {
            background-color: var(--nav-link-hover-bg);
            color: var(--primary-accent);
        }
        header.main-header nav.main-navigation ul li a:hover i { color: var(--primary-accent); }
        header.main-header nav.main-navigation ul li a.active-nav-link {
            /* background-color: var(--nav-link-hover-bg); // Aktif linkte arka planı kaldırdım */
            color: var(--primary-accent);
            font-weight: 600; /* Aktif linki biraz daha kalın yap */
            box-shadow: none;
        }
        header.main-header nav.main-navigation ul li a.active-nav-link i { color: var(--primary-accent); }

        .user-actions-area { display: flex; align-items: center; }
        .user-actions-area .welcome-message-user { margin-right: 18px; font-size: 0.92em; color: #495057; }
        .user-actions-area .logout-button {
            background-color: transparent;
            color: var(--logout-btn-bg) !important;
            padding: 9px 16px;
            border-radius: 6px;
            font-size: 0.92em;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            border: 1px solid var(--logout-btn-bg);
            box-shadow: none;
        }
        .user-actions-area .logout-button:hover {
            background-color: var(--logout-btn-bg);
            color: white !important;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 3px 6px rgba(220, 53, 69, 0.25);
        }
        .user-actions-area .logout-button i { margin-right: 6px; }

        main.container > .alert {
            max-width: 1500px;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        
        @keyframes slideDownGentle {
            from { transform: translateY(-100%); opacity: 0; } /* Başlangıçta tamamen yukarıda */
            to { transform: translateY(0%); opacity: 1; }
        }
    </style>
</head>
<body>
    <header class="main-header <?php if($is_home_page) echo 'animate-header-gentle'; ?>">
        <div class="header-container">
            <div class="logo-section">
                <svg class="site-logo-svg-mountain" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="currentColor" stroke="none">
                    <path d="M32 4C18.745 4 8 18.836 8 32c0 3.204.63 6.263 1.758 9.065L2 58l14.935-7.758C19.737 51.37 25.608 52 32 52c13.255 0 24-14.836 24-28S45.255 4 32 4zm0 46c-5.203 0-10.043-.88-13.82-2.435l-1.287-.527-7.038 3.64L13.083 40l-.527-1.287C11.212 36.193 10 33.15 10 30c0-11.028 8.972-20 22-20s22 8.972 22 20-8.972 20-22 20z"/>
                    <path d="M32 14c-8.837 0-16 10.776-16 24s7.163 24 16 24 16-10.776 16-24-7.163-24-16-24zm0 40c-5.523 0-10-7.163-10-16s4.477-16 10-16 10 7.163 10 16-4.477 16-10 16z" opacity="0.3"/>
                    <path d="M25 30h14v2H25zM25 36h14v2H25zM29 42h6v2h-6z" opacity="0.6"/>
                </svg> <?php // Basit bir dağ/zirve yerine daha soyut bir logo denemesi veya gerçek dağ SVG'si kullanılabilir ?>

                <a href="<?php echo $project_root_url; ?>/index.php" style="text-decoration:none;">
                    <div class="site-brand-text">
                        <div class="site-title-main">Uludağ Teknik Servis</div>
                        <div class="panel-tag-main">Yönetim Paneli</div>
                    </div>
                </a>
            </div>
            <nav class="main-navigation">
                <ul>
                    <li><a href="<?php echo $project_root_url; ?>/index.php" class="<?php if($is_home_page) echo 'active-nav-link'; ?>"><i class="fas fa-home-alt"></i>Ana Sayfa</a></li>
                    <li><a href="<?php echo $project_root_url; ?>/musteri/" class="<?php if($current_module_dir == 'musteri') echo 'active-nav-link'; ?>"><i class="fas fa-users"></i>Müşteriler</a></li>
                    <li><a href="<?php echo $project_root_url; ?>/urun/" class="<?php if($current_module_dir == 'urun') echo 'active-nav-link'; ?>"><i class="fas fa-box-archive"></i>Ürünler</a></li>
                    <li><a href="<?php echo $project_root_url; ?>/servis/" class="<?php if($current_module_dir == 'servis') echo 'active-nav-link'; ?>"><i class="fas fa-headset"></i>Servisler</a></li>
                    <li><a href="<?php echo $project_root_url; ?>/yedekparca/" class="<?php if($current_module_dir == 'yedekparca') echo 'active-nav-link'; ?>"><i class="fas fa-tools"></i>Yedek Parçalar</a></li>
                    <li><a href="<?php echo $project_root_url; ?>/personel/" class="<?php if($current_module_dir == 'personel') echo 'active-nav-link'; ?>"><i class="fas fa-user-gear"></i>Personel</a></li>
                    <li><a href="<?php echo $project_root_url; ?>/fatura/" class="<?php if($current_module_dir == 'fatura') echo 'active-nav-link'; ?>"><i class="fas fa-file-invoice-dollar"></i>Faturalar</a></li>
                </ul>
            </nav>
            <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <div class="user-actions-area">
                    <span class="welcome-message-user">Hoşgeldin, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>!</span>
                    <a href="<?php echo $project_root_url; ?>/logout.php" class="logout-button"><i class="fas fa-power-off"></i>Çıkış</a>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <main class="container">
        <?php
        if (isset($_SESSION['page_message'])) {
            $message_type = isset($_SESSION['page_message_type']) ? $_SESSION['page_message_type'] : 'info';
            echo '<div class="alert alert-' . htmlspecialchars($message_type) . '">' . htmlspecialchars($_SESSION['page_message']) . '</div>';
            unset($_SESSION['page_message']);
            unset($_SESSION['page_message_type']);
        } elseif (isset($_SESSION['mesaj'])) {
             $mesaj_tur_fallback = isset($_SESSION['mesaj_tur']) ? $_SESSION['mesaj_tur'] : 'info';
             echo '<div class="alert alert-' . htmlspecialchars($mesaj_tur_fallback) . '">' . htmlspecialchars($_SESSION['mesaj']) . '</div>';
            unset($_SESSION['mesaj']);
            unset($_SESSION['mesaj_tur']);
        }
        ?>
        <?php // Her sayfanın kendi içeriği (genellikle .page-content-wrapper içine) burada başlayacak ?>