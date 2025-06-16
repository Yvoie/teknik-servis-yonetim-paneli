<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php'; // Veritabanı bağlantısı bu dosyanın en başında olmalı

// POST İSTEKLERİ (EKLEME VE DÜZENLEME)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        // EKLEME
        if ($_POST['action'] == 'add') {
            $parca_adi = isset($_POST['parca_adi']) ? mysqli_real_escape_string($conn, trim($_POST['parca_adi'])) : null;
            $marka = isset($_POST['marka']) ? mysqli_real_escape_string($conn, trim($_POST['marka'])) : null;
            $parca_ucreti = isset($_POST['parca_ucreti']) ? floatval($_POST['parca_ucreti']) : 0.00;
            $garanti_suresi = isset($_POST['garanti_suresi']) ? intval($_POST['garanti_suresi']) : 0;
            $stok_adedi = isset($_POST['stok_adedi']) ? intval($_POST['stok_adedi']) : 0;

            if (empty($parca_adi) || $parca_ucreti < 0 || $stok_adedi < 0) {
                $_SESSION['mesaj'] = "Parça Adı, geçerli bir Parça Ücreti ve Stok Adedi alanları zorunludur!";
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: ekle.php");
                exit(); // Yönlendirmeden sonra script'i durdur
            }

            $sql_add = "CALL sp_YedekParca_Ekle('$parca_adi', '$marka', $parca_ucreti, $garanti_suresi, $stok_adedi)";
            if (mysqli_query($conn, $sql_add)) {
                $yeniYedekParcaID = null;
                if ($result_id = mysqli_store_result($conn)) {
                    if ($row_id = mysqli_fetch_assoc($result_id)) {
                        $yeniYedekParcaID = $row_id['YeniYedekParcaID']; // SP'deki takma adla eşleşmeli
                    }
                    mysqli_free_result($result_id);
                }
                while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
                
                $_SESSION['mesaj'] = "Yeni yedek parça başarıyla eklendi.";
                $_SESSION['mesaj_tur'] = "success";
                if (!empty($yeniYedekParcaID)) {
                    header("Location: index.php?highlight_id=" . $yeniYedekParcaID);
                } else {
                    $_SESSION['mesaj'] .= " (ID alınamadı, vurgulama yapılamadı.)";
                    header("Location: index.php");
                }
                exit(); // Yönlendirmeden sonra script'i durdur
            } else {
                $_SESSION['mesaj'] = "Yedek parça eklenirken hata: " . mysqli_error($conn);
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: index.php");
                exit(); // Yönlendirmeden sonra script'i durdur
            }
        }
        // DÜZENLEME
        elseif ($_POST['action'] == 'edit') {
            $yedekparca_id = isset($_POST['yedekparca_id']) ? intval($_POST['yedekparca_id']) : 0;
            $parca_adi = isset($_POST['parca_adi']) ? mysqli_real_escape_string($conn, trim($_POST['parca_adi'])) : null;
            $marka = isset($_POST['marka']) ? mysqli_real_escape_string($conn, trim($_POST['marka'])) : null;
            $parca_ucreti = isset($_POST['parca_ucreti']) ? floatval($_POST['parca_ucreti']) : 0.00;
            $garanti_suresi = isset($_POST['garanti_suresi']) ? intval($_POST['garanti_suresi']) : 0;
            $stok_adedi = isset($_POST['stok_adedi']) ? intval($_POST['stok_adedi']) : 0;

            if ($yedekparca_id <= 0 || empty($parca_adi) || $parca_ucreti < 0 || $stok_adedi < 0) {
                $_SESSION['mesaj'] = "Parça ID, Parça Adı, geçerli bir Parça Ücreti ve Stok Adedi alanları zorunludur!";
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: duzenle.php?id=" . $yedekparca_id);
                exit(); // Yönlendirmeden sonra script'i durdur
            }

            $sql_edit = "CALL sp_YedekParca_Guncelle($yedekparca_id, '$parca_adi', '$marka', $parca_ucreti, $garanti_suresi, $stok_adedi)";
            if (mysqli_query($conn, $sql_edit)) {
                while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
                $_SESSION['mesaj'] = "Yedek parça (ID: ".$yedekparca_id.") başarıyla güncellendi.";
                $_SESSION['mesaj_tur'] = "success";
                header("Location: index.php?highlight_id=" . $yedekparca_id);
                exit(); // Yönlendirmeden sonra script'i durdur
            } else {
                $_SESSION['mesaj'] = "Yedek parça güncellenirken hata (ID: ".$yedekparca_id."): " . mysqli_error($conn);
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: duzenle.php?id=" . $yedekparca_id);
                exit(); // Yönlendirmeden sonra script'i durdur
            }
        } else {
            $_SESSION['mesaj'] = "Geçersiz işlem (POST).";
            $_SESSION['mesaj_tur'] = "warning";
            header("Location: index.php");
            exit(); // Yönlendirmeden sonra script'i durdur
        }
    } else {
        $_SESSION['mesaj'] = "İşlem belirtilmedi (POST).";
        $_SESSION['mesaj_tur'] = "warning";
        header("Location: index.php");
        exit(); // Yönlendirmeden sonra script'i durdur
    }
}
// GET İSTEKLERİ (SİLME)
elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $yedekparca_id_to_delete = intval($_GET['id']);
    if ($yedekparca_id_to_delete > 0) {
        $sql_delete = "CALL sp_YedekParca_Sil($yedekparca_id_to_delete)";
        if (mysqli_query($conn, $sql_delete)) {
            while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
            $_SESSION['mesaj'] = "Yedek parça (ID: ".$yedekparca_id_to_delete.") başarıyla silindi.";
            $_SESSION['mesaj_tur'] = "success";
        } else {
            $_SESSION['mesaj'] = "Yedek parça silinirken hata (ID: ".$yedekparca_id_to_delete."): " . mysqli_error($conn) . " (Bu parça servis kayıtlarında kullanılıyor olabilir.)";
            $_SESSION['mesaj_tur'] = "danger";
        }
    } else {
        $_SESSION['mesaj'] = "Geçersiz Yedek Parça ID.";
        $_SESSION['mesaj_tur'] = "danger";
    }
    header("Location: index.php");
    exit(); // Yönlendirmeden sonra script'i durdur
}
// GEÇERSİZ İSTEK
else {
    $_SESSION['mesaj'] = "Bu sayfaya doğrudan erişim veya geçersiz istek.";
    $_SESSION['mesaj_tur'] = "danger";
    header("Location: index.php");
    exit(); // Yönlendirmeden sonra script'i durdur
}

// Bu noktaya gelinmemesi gerekir, ama her ihtimale karşı
if (isset($conn)) {
    mysqli_close($conn);
}
?>