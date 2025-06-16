<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            // ... (EKLEME KODU BURADA - DAHA ÖNCE GÜNCELLEDİĞİMİZ GİBİ) ...
            $musteri_id = isset($_POST['musteri_id']) ? intval($_POST['musteri_id']) : 0;
            $urun_tipi = isset($_POST['urun_tipi']) ? mysqli_real_escape_string($conn, trim($_POST['urun_tipi'])) : null;
            $marka = isset($_POST['marka']) ? mysqli_real_escape_string($conn, trim($_POST['marka'])) : null;
            $model = isset($_POST['model']) ? mysqli_real_escape_string($conn, trim($_POST['model'])) : null;
            $seri_numarasi = isset($_POST['seri_numarasi']) ? mysqli_real_escape_string($conn, trim($_POST['seri_numarasi'])) : null;

            if ($musteri_id <= 0 || empty($urun_tipi) || empty($marka)) {
                $_SESSION['mesaj'] = "Müşteri, Ürün Tipi ve Marka alanları zorunludur!";
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: ekle.php");
                exit();
            }

            $sql_add_urun = "CALL sp_Urun_Ekle($musteri_id, '$urun_tipi', '$marka', '$model', '$seri_numarasi')";

            if (mysqli_query($conn, $sql_add_urun)) {
                $yeniUrunID = null;
                if ($result_id = mysqli_store_result($conn)) {
                    if ($row_id = mysqli_fetch_assoc($result_id)) {
                        $yeniUrunID = $row_id['YeniUrunID'];
                    }
                    mysqli_free_result($result_id);
                }
                while(mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }

                $_SESSION['mesaj'] = "Yeni ürün başarıyla eklendi.";
                $_SESSION['mesaj_tur'] = "success";
                
                if (!empty($yeniUrunID)) {
                    header("Location: index.php?highlight_id=" . $yeniUrunID);
                } else {
                    $_SESSION['mesaj'] .= " (ID alınamadı, vurgulama yapılamadı.)";
                    header("Location: index.php");
                }
                exit();
            } else {
                $_SESSION['mesaj'] = "Ürün eklenirken bir hata oluştu: " . mysqli_error($conn);
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: index.php");
                exit();
            }
        }
        // ---------- YENİ EKLENEN/GÜNCELLENEN DÜZENLEME BLOĞU -----------
        elseif ($_POST['action'] == 'edit') {
            $urun_id_to_edit = isset($_POST['urun_id']) ? intval($_POST['urun_id']) : 0;
            $musteri_id = isset($_POST['musteri_id']) ? intval($_POST['musteri_id']) : 0;
            $urun_tipi = isset($_POST['urun_tipi']) ? mysqli_real_escape_string($conn, trim($_POST['urun_tipi'])) : null;
            $marka = isset($_POST['marka']) ? mysqli_real_escape_string($conn, trim($_POST['marka'])) : null;
            $model = isset($_POST['model']) ? mysqli_real_escape_string($conn, trim($_POST['model'])) : null;
            $seri_numarasi = isset($_POST['seri_numarasi']) ? mysqli_real_escape_string($conn, trim($_POST['seri_numarasi'])) : null;

            if ($urun_id_to_edit <= 0) {
                $_SESSION['mesaj'] = "Düzenleme için geçersiz Ürün ID!";
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: index.php");
                exit();
            }
            if ($musteri_id <= 0 || empty($urun_tipi) || empty($marka)) {
                $_SESSION['mesaj'] = "Müşteri, Ürün Tipi ve Marka alanları zorunludur!";
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: duzenle.php?id=" . $urun_id_to_edit); // Düzenleme formuna geri dön
                exit();
            }

            $sql_edit_urun = "CALL sp_Urun_Guncelle($urun_id_to_edit, $musteri_id, '$urun_tipi', '$marka', '$model', '$seri_numarasi')";

            if (mysqli_query($conn, $sql_edit_urun)) {
                while(mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
                $_SESSION['mesaj'] = "Ürün (ID: " . $urun_id_to_edit . ") başarıyla güncellendi.";
                $_SESSION['mesaj_tur'] = "success";
                header("Location: index.php?highlight_id=" . $urun_id_to_edit);
                exit();
            } else {
                $_SESSION['mesaj'] = "Ürün güncellenirken bir hata oluştu (ID: " . $urun_id_to_edit . "): " . mysqli_error($conn);
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: duzenle.php?id=" . $urun_id_to_edit);
                exit();
            }
        }
        // ---------- DÜZENLEME BLOĞU SONU -----------
        else {
            $_SESSION['mesaj'] = "Geçersiz işlem isteği (POST action).";
            $_SESSION['mesaj_tur'] = "warning";
            header("Location: index.php");
            exit();
        }
    } else { // action parametresi yoksa
        $_SESSION['mesaj'] = "İşlem belirtilmedi (POST).";
        $_SESSION['mesaj_tur'] = "warning";
        header("Location: index.php");
        exit();
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    // ... (SİLME KODU OLDUĞU GİBİ KALIYOR) ...
    $urun_id_to_delete = intval($_GET['id']);
    if ($urun_id_to_delete > 0) {
        $sql_delete_urun = "CALL sp_Urun_Sil($urun_id_to_delete)";
        if (mysqli_query($conn, $sql_delete_urun)) {
            while(mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
            $_SESSION['mesaj'] = "Ürün (ID: ".$urun_id_to_delete.") başarıyla silindi.";
            $_SESSION['mesaj_tur'] = "success";
        } else {
            $_SESSION['mesaj'] = "Ürün silinirken bir hata oluştu (ID: ".$urun_id_to_delete."): " . mysqli_error($conn) . " (İlişkili servis kayıtları olabilir.)";
            $_SESSION['mesaj_tur'] = "danger";
        }
    } else {
        $_SESSION['mesaj'] = "Geçersiz Ürün ID.";
        $_SESSION['mesaj_tur'] = "danger";
    }
    header("Location: index.php");
    exit();
} else {
    $_SESSION['mesaj'] = "Bu sayfaya doğrudan erişilemez veya geçersiz istek.";
    $_SESSION['mesaj_tur'] = "danger";
    header("Location: index.php");
    exit();
}

if (isset($conn)) {
    mysqli_close($conn);
}
?>