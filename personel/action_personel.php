<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

// POST İSTEKLERİ (EKLEME VE DÜZENLEME)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        // EKLEME
        if ($_POST['action'] == 'add') {
            $ad = isset($_POST['ad']) ? mysqli_real_escape_string($conn, trim($_POST['ad'])) : null;
            $soyad = isset($_POST['soyad']) ? mysqli_real_escape_string($conn, trim($_POST['soyad'])) : null;
            $vardiya = isset($_POST['vardiya']) ? mysqli_real_escape_string($conn, trim($_POST['vardiya'])) : null;
            $telno = isset($_POST['telno']) ? mysqli_real_escape_string($conn, trim($_POST['telno'])) : null;

            if (empty($ad) || empty($soyad)) {
                $_SESSION['mesaj'] = "Ad ve Soyad alanları zorunludur!";
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: ekle.php");
                exit();
            }

            $sql_add = "CALL sp_TeknikPersonel_Ekle('$ad', '$soyad', '$vardiya', '$telno')";
            if (mysqli_query($conn, $sql_add)) {
                $yeniPersonelID = null;
                if ($result_id = mysqli_store_result($conn)) {
                    if ($row_id = mysqli_fetch_assoc($result_id)) {
                        $$yeniPersonelID = $row_id['YeniTeknikPersonelID'];
                    }
                    mysqli_free_result($result_id);
                }
                while(mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
                $_SESSION['mesaj'] = "Yeni teknik personel başarıyla eklendi.";
                $_SESSION['mesaj_tur'] = "success";
                if (!empty($yeniPersonelID)) {
                    header("Location: index.php?highlight_id=" . $yeniPersonelID);
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $_SESSION['mesaj'] = "Teknik personel eklenirken hata: " . mysqli_error($conn);
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: index.php");
                exit();
            }
        }
        // DÜZENLEME
        elseif ($_POST['action'] == 'edit') {
            $personel_id = isset($_POST['personel_id']) ? intval($_POST['personel_id']) : 0;
            $ad = isset($_POST['ad']) ? mysqli_real_escape_string($conn, trim($_POST['ad'])) : null;
            $soyad = isset($_POST['soyad']) ? mysqli_real_escape_string($conn, trim($_POST['soyad'])) : null;
            $vardiya = isset($_POST['vardiya']) ? mysqli_real_escape_string($conn, trim($_POST['vardiya'])) : null;
            $telno = isset($_POST['telno']) ? mysqli_real_escape_string($conn, trim($_POST['telno'])) : null;

            if ($personel_id <= 0 || empty($ad) || empty($soyad)) {
                $_SESSION['mesaj'] = "Personel ID, Ad ve Soyad alanları zorunludur!";
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: duzenle.php?id=" . $personel_id);
                exit();
            }

            $sql_edit = "CALL sp_TeknikPersonel_Guncelle($personel_id, '$ad', '$soyad', '$vardiya', '$telno')";
            if (mysqli_query($conn, $sql_edit)) {
                while(mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
                $_SESSION['mesaj'] = "Teknik personel (ID: ".$personel_id.") başarıyla güncellendi.";
                $_SESSION['mesaj_tur'] = "success";
                header("Location: index.php?highlight_id=" . $personel_id);
                exit();
            } else {
                $_SESSION['mesaj'] = "Teknik personel güncellenirken hata (ID: ".$personel_id."): " . mysqli_error($conn);
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: duzenle.php?id=" . $personel_id);
                exit();
            }
        } else {
            $_SESSION['mesaj'] = "Geçersiz işlem (POST).";
            $_SESSION['mesaj_tur'] = "warning";
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['mesaj'] = "İşlem belirtilmedi (POST).";
        $_SESSION['mesaj_tur'] = "warning";
        header("Location: index.php");
        exit();
    }
}
// GET İSTEKLERİ (SİLME)
elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $personel_id_to_delete = intval($_GET['id']);
    if ($personel_id_to_delete > 0) {
        $sql_delete = "CALL sp_TeknikPersonel_Sil($personel_id_to_delete)";
        if (mysqli_query($conn, $sql_delete)) {
            while(mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
            $_SESSION['mesaj'] = "Teknik personel (ID: ".$personel_id_to_delete.") başarıyla silindi.";
            $_SESSION['mesaj_tur'] = "success";
        } else {
            $_SESSION['mesaj'] = "Teknik personel silinirken hata (ID: ".$personel_id_to_delete."): " . mysqli_error($conn) . " (İlişkili servis kayıtları olabilir.)";
            $_SESSION['mesaj_tur'] = "danger";
        }
    } else {
        $_SESSION['mesaj'] = "Geçersiz Personel ID.";
        $_SESSION['mesaj_tur'] = "danger";
    }
    header("Location: index.php");
    exit();
}
// GEÇERSİZ İSTEK
else {
    $_SESSION['mesaj'] = "Bu sayfaya doğrudan erişim veya geçersiz istek.";
    $_SESSION['mesaj_tur'] = "danger";
    header("Location: index.php");
    exit();
}

if (isset($conn)) {
    mysqli_close($conn);
}
?>