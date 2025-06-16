<?php
// Session'ı başlat (mesajları kullanabilmek için)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı bağlantısını dahil et
require_once '../config/db.php';

// --- POST İSTEKLERİNİ İŞLE (EKLEME VE DÜZENLEME) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) { // 'action' parametresi var mı?

        // -------------------- EKLEME İŞLEMİ --------------------
         if ($_POST['action'] == 'add') {
            $tckn = isset($_POST['tckn']) ? mysqli_real_escape_string($conn, trim($_POST['tckn'])) : null;
            $ad = isset($_POST['ad']) ? mysqli_real_escape_string($conn, trim($_POST['ad'])) : null;
            $soyad = isset($_POST['soyad']) ? mysqli_real_escape_string($conn, trim($_POST['soyad'])) : null;
            $adres = isset($_POST['adres']) ? mysqli_real_escape_string($conn, trim($_POST['adres'])) : null;
            $telno = isset($_POST['telno']) ? mysqli_real_escape_string($conn, trim($_POST['telno'])) : null;
            $eposta = isset($_POST['eposta']) ? mysqli_real_escape_string($conn, trim($_POST['eposta'])) : null;

            if (empty($ad) || empty($soyad)) {
                $_SESSION['mesaj'] = "Ad ve Soyad alanları zorunludur!";
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: ekle.php");
                exit();
            }

            $sql_add = "CALL sp_Musteri_Ekle('$tckn', '$ad', '$soyad', '$adres', '$telno', '$eposta')";

            // ******************** BURADAN İTİBAREN DEĞİŞİYOR ********************
            if (mysqli_query($conn, $sql_add)) { // SP çağrıldı
                $yeniMusteriID = null;

                // Doğrudan sonuç setini almayı deneyelim
                if ($result_id = mysqli_store_result($conn)) {
                    if ($row_id = mysqli_fetch_assoc($result_id)) {
                        $yeniMusteriID = $row_id['YeniMusteriID']; // SP'deki AS takma adıyla eşleşmeli
                    }
                    mysqli_free_result($result_id);
                }

                // SP'den sonra başka potansiyel sonuç setleri varsa onları temizle
                while(mysqli_more_results($conn) && mysqli_next_result($conn)) {
                    if($l_result = mysqli_store_result($conn)){
                        mysqli_free_result($l_result);
                    }
                }

                $_SESSION['mesaj'] = "Yeni müşteri başarıyla eklendi.";
                $_SESSION['mesaj_tur'] = "success";
                
                if (!empty($yeniMusteriID)) {
                    header("Location: index.php?highlight_id=" . $yeniMusteriID);
                } else {
                    $_SESSION['mesaj'] .= " (ID alınamadı, vurgulama yapılamadı. Lütfen SP'yi kontrol edin.)";
                    header("Location: index.php");
                }
                exit();
            } else {
                $_SESSION['mesaj'] = "Müşteri eklenirken bir hata oluştu: " . mysqli_error($conn);
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: index.php");
                exit();
            }
        }
        // -------------------- DÜZENLEME İŞLEMİ (Değişiklik Yok) --------------------
        elseif ($_POST['action'] == 'edit') {
            // ... (Mevcut düzenleme kodunuz burada) ...
            $musteri_id_to_edit = isset($_POST['musteri_id']) ? intval($_POST['musteri_id']) : 0;
            $tckn = isset($_POST['tckn']) ? mysqli_real_escape_string($conn, trim($_POST['tckn'])) : null;
            $ad = isset($_POST['ad']) ? mysqli_real_escape_string($conn, trim($_POST['ad'])) : null;
            $soyad = isset($_POST['soyad']) ? mysqli_real_escape_string($conn, trim($_POST['soyad'])) : null;
            $adres = isset($_POST['adres']) ? mysqli_real_escape_string($conn, trim($_POST['adres'])) : null;
            $telno = isset($_POST['telno']) ? mysqli_real_escape_string($conn, trim($_POST['telno'])) : null;
            $eposta = isset($_POST['eposta']) ? mysqli_real_escape_string($conn, trim($_POST['eposta'])) : null;

            if ($musteri_id_to_edit <= 0) {
                $_SESSION['mesaj'] = "Düzenleme için geçersiz Müşteri ID!";
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: index.php");
                exit();
            }
            if (empty($ad) || empty($soyad)) {
                $_SESSION['mesaj'] = "Ad ve Soyad alanları zorunludur!";
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: duzenle.php?id=" . $musteri_id_to_edit);
                exit();
            }

            $sql_edit = "CALL sp_Musteri_Guncelle($musteri_id_to_edit, '$tckn', '$ad', '$soyad', '$adres', '$telno', '$eposta')";

            if (mysqli_query($conn, $sql_edit)) {
                while(mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
                $_SESSION['mesaj'] = "Müşteri (ID: " . $musteri_id_to_edit . ") bilgileri başarıyla güncellendi.";
                $_SESSION['mesaj_tur'] = "success";
                header("Location: index.php?highlight_id=" . $musteri_id_to_edit);
                exit();
            } else {
                $_SESSION['mesaj'] = "Müşteri güncellenirken bir hata oluştu (ID: " . $musteri_id_to_edit . "): " . mysqli_error($conn);
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: duzenle.php?id=" . $musteri_id_to_edit);
                exit();
            }
        }
        // ... (Kalan kod aynı)
        else {
            $_SESSION['mesaj'] = "Geçersiz işlem türü (POST action).";
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
elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    // ... (Silme kodunuz burada) ...
    $musteri_id_to_delete = intval($_GET['id']);

    if ($musteri_id_to_delete > 0) {
        $sql_delete = "CALL sp_Musteri_Sil($musteri_id_to_delete)";
        if (mysqli_query($conn, $sql_delete)) {
            while(mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
            $_SESSION['mesaj'] = "Müşteri (ID: " . $musteri_id_to_delete . ") başarıyla silindi.";
            $_SESSION['mesaj_tur'] = "success";
        } else {
            $_SESSION['mesaj'] = "Müşteri silinirken hata oluştu (ID: " . $musteri_id_to_delete . "): " . mysqli_error($conn) . " (İlişkili servis kayıtları olabilir.)";
            $_SESSION['mesaj_tur'] = "danger";
        }
    } else {
        $_SESSION['mesaj'] = "Silme işlemi için geçersiz Müşteri ID.";
        $_SESSION['mesaj_tur'] = "danger";
    }
    header("Location: index.php");
    exit();
}
else {
    $_SESSION['mesaj'] = "Bu sayfaya doğrudan erişilemez veya geçersiz bir istek yapıldı.";
    $_SESSION['mesaj_tur'] = "danger";
    header("Location: index.php");
    exit();
}

if (isset($conn)) {
    mysqli_close($conn);
}
?>