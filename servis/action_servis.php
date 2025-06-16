<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

// POST İSTEKLERİNİ İŞLE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        // -------------------- YENİ SERVİS KAYDI EKLEME --------------------
        if ($_POST['action'] == 'add') {
            // ... (Mevcut ekleme kodunuz burada) ...
            $musteri_id = isset($_POST['musteri_id']) ? intval($_POST['musteri_id']) : 0;
            $urun_id = isset($_POST['urun_id']) ? intval($_POST['urun_id']) : 0;
            $personel_id = isset($_POST['personel_id']) ? intval($_POST['personel_id']) : 0;
            $servis_tarihi = isset($_POST['servis_tarihi']) ? mysqli_real_escape_string($conn, trim($_POST['servis_tarihi'])) : null;
            $sorun_tanimi = isset($_POST['sorun_tanimi']) ? mysqli_real_escape_string($conn, trim($_POST['sorun_tanimi'])) : null;
            $iscilik_ucreti = isset($_POST['iscilik_ucreti']) ? floatval($_POST['iscilik_ucreti']) : 0.00;

            if ($musteri_id <= 0 || $urun_id <= 0 || $personel_id <= 0 || empty($servis_tarihi) || empty($sorun_tanimi)) {
                $_SESSION['mesaj'] = "Müşteri, Ürün, Personel, Servis Tarihi ve Sorun Tanımı alanları zorunludur!";
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: ekle.php");
                exit();
            }

            $sql_add_servis = "CALL sp_ServisKaydi_Ekle($musteri_id, $urun_id, $personel_id, '$servis_tarihi', '$sorun_tanimi', $iscilik_ucreti)";
            if (mysqli_query($conn, $sql_add_servis)) {
                $yeniServisID = null;
                if ($result_id = mysqli_store_result($conn)) {
                    if ($row_id = mysqli_fetch_assoc($result_id)) {
                        $yeniServisID = $row_id['YeniServisID'];
                    }
                    mysqli_free_result($result_id);
                }
                while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l = mysqli_store_result($conn)){ mysqli_free_result($l); } }
                $_SESSION['mesaj'] = "Yeni servis kaydı başarıyla oluşturuldu.";
                $_SESSION['mesaj_tur'] = "success";
                if (!empty($yeniServisID)) {
                    header("Location: duzenle_detay.php?id=" . $yeniServisID . "&highlight=true"); // Detay sayfasına yönlendir
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $_SESSION['mesaj'] = "Servis kaydı oluşturulurken bir hata oluştu: " . mysqli_error($conn);
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: ekle.php");
                exit();
            }
        }
        // -------------------- SERVİS DURUM VE İŞÇİLİK ÜCRETİ GÜNCELLEME --------------------
        elseif ($_POST['action'] == 'update_ucret_durum') {
            $servis_id = isset($_POST['servis_id']) ? intval($_POST['servis_id']) : 0;
            $iscilik_ucreti = isset($_POST['iscilik_ucreti']) ? floatval($_POST['iscilik_ucreti']) : null; // Null olabilir, SP'de kontrol edilebilir
            $durum = isset($_POST['durum']) ? mysqli_real_escape_string($conn, $_POST['durum']) : null;

            if ($servis_id <= 0 || $durum === null || $iscilik_ucreti === null || $iscilik_ucreti < 0) {
                $_SESSION['mesaj'] = "Geçersiz veri. Servis ID, Durum ve geçerli bir İşçilik Ücreti gereklidir.";
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: duzenle_detay.php?id=" . $servis_id);
                exit();
            }

            // sp_ServisKaydi_Guncelle_Temel yordamını kullanabiliriz, ancak o diğer alanları da istiyor.
            // Sadece durum ve işçilik için ayrı bir SP daha iyi olabilir veya mevcut SP'yi NULL kabul edecek şekilde düzenleyebiliriz.
            // Şimdilik mevcut sp_ServisKaydi_Guncelle_Temel'i kullanmak için eksik verileri DB'den çekmemiz gerekir.
            // Daha basit bir yol: Sadece bu iki alanı güncelleyen ayrı bir SP veya direkt UPDATE sorgusu.
            // Ödev kapsamında SP kullanmamız gerektiği için, bu iki alanı güncelleyen bir SP daha iyi olur.
            // Varsayalım sp_ServisKaydi_Update_DurumVeIscilik(servisID, yeniDurum, yeniIscilik) adında bir SP'miz var.
            // Şimdilik sp_ServisKaydi_Guncelle_Temel'i kullanmak için tüm bilgileri tekrar alalım (çok ideal değil)

            $sql_get_current = "CALL sp_ServisKaydi_Getir_ByID_Detayli(" . $servis_id . ")";
            $current_data = null;
            if($res_curr = mysqli_query($conn, $sql_get_current)){
                if(mysqli_num_rows($res_curr) == 1){
                    $current_data = mysqli_fetch_assoc($res_curr);
                }
                mysqli_free_result($res_curr);
                while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l = mysqli_store_result($conn)){ mysqli_free_result($l); } }
            }

            if($current_data){
                $sql_update = "CALL sp_ServisKaydi_Guncelle_Temel(
                    $servis_id, 
                    {$current_data['MusteriID']}, 
                    {$current_data['UrunID']}, 
                    {$current_data['PersonelID']}, 
                    '{$current_data['ServisTarihi']}', 
                    '" . mysqli_real_escape_string($conn, $current_data['SorunTanimi']) . "', 
                    $iscilik_ucreti, 
                    '$durum'
                )";

                if (mysqli_query($conn, $sql_update)) {
                    while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l = mysqli_store_result($conn)){ mysqli_free_result($l); } }
                    // Ücretler değiştiği için ana ücret güncelleme SP'sini de çağıralım.
                    // sp_ServisKaydi_Guncelle_Temel zaten ToplamUcret'i Iscilik+MevcutParcaUcreti olarak ayarlıyor.
                    // Eğer parça ücreti de bu işlemle güncelleniyorsa sp_ServisKaydi_UcretleriGuncelle çağrılmalı.
                    // Şimdilik sadece durum ve işçilik güncellendi.
                    $_SESSION['mesaj'] = "Servis kaydı (ID: ".$servis_id.") durumu ve/veya işçilik ücreti başarıyla güncellendi.";
                    $_SESSION['mesaj_tur'] = "success";
                } else {
                    $_SESSION['mesaj'] = "Servis durumu/işçilik ücreti güncellenirken hata: " . mysqli_error($conn);
                    $_SESSION['mesaj_tur'] = "danger";
                }
            } else {
                 $_SESSION['mesaj'] = "Güncellenecek servis kaydı verileri alınamadı.";
                 $_SESSION['mesaj_tur'] = "danger";
            }
            header("Location: duzenle_detay.php?id=" . $servis_id . "&highlight=true");
            exit();
        }
        // -------------------- SERVİSE YEDEK PARÇA EKLEME --------------------
        elseif ($_POST['action'] == 'add_parca_to_servis') {
            $servis_id = isset($_POST['servis_id']) ? intval($_POST['servis_id']) : 0;
            $yedekparca_id = isset($_POST['yedekparca_id']) ? intval($_POST['yedekparca_id']) : 0;
            $kullanilan_adet = isset($_POST['kullanilan_adet']) ? intval($_POST['kullanilan_adet']) : 0;

            if ($servis_id <= 0 || $yedekparca_id <= 0 || $kullanilan_adet <= 0) {
                $_SESSION['mesaj'] = "Geçersiz veri. Servis ID, Yedek Parça ID ve geçerli bir Adet gereklidir.";
                $_SESSION['mesaj_tur'] = "danger";
                header("Location: duzenle_detay.php?id=" . $servis_id);
                exit();
            }

            $sql_add_parca = "CALL sp_Servis_YedekParca_Ekle($servis_id, $yedekparca_id, $kullanilan_adet)";
            if (mysqli_query($conn, $sql_add_parca)) {
                // SP içinde SELECT 'Mesaj' vardı, onu da temizleyelim
                while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l = mysqli_store_result($conn)){ mysqli_free_result($l); } }
                $_SESSION['mesaj'] = "Yedek parça servis kaydına başarıyla eklendi.";
                $_SESSION['mesaj_tur'] = "success";
            } else {
                $_SESSION['mesaj'] = "Yedek parça eklenirken hata: " . mysqli_error($conn);
                $_SESSION['mesaj_tur'] = "danger";
            }
            header("Location: duzenle_detay.php?id=" . $servis_id . "&highlight_parca=" . $yedekparca_id); // Parça listesini vurgulamak için
            exit();
        }
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
}
// GET İSTEKLERİNİ İŞLE (SERVİSTEN PARÇA ÇIKARMA)
elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action'])) {
    if ($_GET['action'] == 'remove_parca' && isset($_GET['servis_id']) && isset($_GET['yedekparca_id']) && isset($_GET['adet'])) {
        $servis_id = intval($_GET['servis_id']);
        $yedekparca_id = intval($_GET['yedekparca_id']);
        $adet_cikar = intval($_GET['adet']); // Kaç adet çıkarılacağı (şimdi sadece 1 adet için link verdik)

        if ($servis_id > 0 && $yedekparca_id > 0 && $adet_cikar > 0) {
            $sql_remove_parca = "CALL sp_Servis_YedekParca_Sil($servis_id, $yedekparca_id, $adet_cikar)";
             if (mysqli_query($conn, $sql_remove_parca)) {
                while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l = mysqli_store_result($conn)){ mysqli_free_result($l); } }
                $_SESSION['mesaj'] = $adet_cikar . " adet yedek parça servis kaydından çıkarıldı.";
                $_SESSION['mesaj_tur'] = "success";
            } else {
                $_SESSION['mesaj'] = "Yedek parça çıkarılırken hata: " . mysqli_error($conn);
                $_SESSION['mesaj_tur'] = "danger";
            }
        } else {
            $_SESSION['mesaj'] = "Parça çıkarma için geçersiz parametreler.";
            $_SESSION['mesaj_tur'] = "danger";
        }
        header("Location: duzenle_detay.php?id=" . $servis_id);
        exit();
    }
    // ----- SERVİS KAYDI SİLME İŞLEMİ (GET ile) -----
    /*
    elseif ($_GET['action'] == 'delete' && isset($_GET['id'])) {
        // ... (Mevcut silme kodunuz buraya gelebilir veya POST'a taşınabilir) ...
    }
    */
    else {
        $_SESSION['mesaj'] = "Geçersiz işlem isteği (GET action).";
        $_SESSION['mesaj_tur'] = "warning";
        header("Location: index.php");
        exit();
    }
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