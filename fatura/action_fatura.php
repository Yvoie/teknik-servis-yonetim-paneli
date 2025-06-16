<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

// Sadece POST isteklerini kabul et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {

        // -------------------- FATURA ÖDEME DURUMUNU GÜNCELLEME --------------------
        if ($_POST['action'] == 'update_odeme_durumu') {
            $fatura_id = isset($_POST['fatura_id']) ? intval($_POST['fatura_id']) : 0;
            $yeni_odeme_durumu = isset($_POST['odeme_durumu']) ? mysqli_real_escape_string($conn, $_POST['odeme_durumu']) : null;

            // Geçerli ödeme durumları (ENUM listesiyle eşleşmeli)
            $gecerli_durumlar = ['Ödenmedi', 'Ödendi', 'Kısmi Ödendi'];

            if ($fatura_id <= 0 || empty($yeni_odeme_durumu) || !in_array($yeni_odeme_durumu, $gecerli_durumlar)) {
                $_SESSION['mesaj'] = "Geçersiz veri. Fatura ID ve geçerli bir Ödeme Durumu gereklidir.";
                $_SESSION['mesaj_tur'] = "danger";
                // Hata durumunda düzenleme formuna ID ile geri dön, eğer ID geçerliyse
                $redirect_url = ($fatura_id > 0) ? "duzenle_odeme.php?id=" . $fatura_id : "index.php";
                header("Location: " . $redirect_url);
                exit();
            }

            $sql_update_odeme = "CALL sp_Fatura_OdemeDurumu_Guncelle($fatura_id, '$yeni_odeme_durumu')";

            if (mysqli_query($conn, $sql_update_odeme)) {
                // SP bir sonuç seti döndürebilir (ROW_COUNT() gibi), onu temizleyelim
                while(mysqli_more_results($conn) && mysqli_next_result($conn)) {
                    if($l_result = mysqli_store_result($conn)){
                        mysqli_free_result($l_result);
                    }
                }
                $_SESSION['mesaj'] = "Fatura (ID: ".$fatura_id.") ödeme durumu başarıyla güncellendi.";
                $_SESSION['mesaj_tur'] = "success";
                header("Location: index.php?highlight_id=" . $fatura_id); // Listede faturayı vurgula
                exit();
            } else {
                $_SESSION['mesaj'] = "Fatura ödeme durumu güncellenirken hata oluştu (ID: ".$fatura_id."): " . mysqli_error($conn);
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: duzenle_odeme.php?id=" . $fatura_id);
                exit();
            }
        }
        // -------------------- YENİ FATURA OLUŞTURMA (GEREKİRSE EKLENECEK ACTION) --------------------
        /*
        elseif ($_POST['action'] == 'create_fatura') {
            // Bu genellikle bir ServisID üzerinden tetiklenir.
            // $servis_id_for_fatura = isset($_POST['servis_id']) ? intval($_POST['servis_id']) : 0;
            // if ($servis_id_for_fatura > 0) {
            //     $sql_create_fatura = "CALL sp_Fatura_Ekle($servis_id_for_fatura)";
            //     // ... (Benzer başarı/hata ve yönlendirme mantığı) ...
            // } else {
            //     // Hata mesajı ve yönlendirme
            // }
        }
        */
        else {
            $_SESSION['mesaj'] = "Geçersiz işlem isteği (POST action).";
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
} else {
    // POST isteği değilse
    $_SESSION['mesaj'] = "Bu sayfaya doğrudan erişilemez veya geçersiz bir istek yapıldı.";
    $_SESSION['mesaj_tur'] = "danger";
    header("Location: index.php"); // Ana fatura listesine yönlendir
    exit();
}

if (isset($conn)) {
    mysqli_close($conn);
}
?>