<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
require_once '../includes/header.php'; // header.php içinde main.container açılıyor
?>

<style>
/* servis/index.php özel stilleri */
.table th.sorun-tanimi-col, .table td.sorun-tanimi-cell {
    max-width: 280px; /* Sorun tanımı için artırılmış genişlik */
    white-space: normal; 
    word-break: break-word; 
}
.table td.sorun-tanimi-cell small.kayit-tarihi { /* Kayıt tarihi için stil */
    display: block;
    margin-top: 5px;
    color: #6c757d;
    font-style: italic;
    font-size: 0.8em;
}
.servis-actions .btn { /* İşlem butonları için */
    margin-bottom: 5px;
}
.servis-actions .btn:last-child {
    margin-bottom: 0;
}
</style>

<div class="page-content-wrapper">
    <div class="page-header-flex">
        <h2>Servis Kayıt Yönetimi</h2>
        <p style="margin-bottom:0;"><a href="ekle.php" class="btn btn-success"><i class="fas fa-plus-circle"></i> Yeni Servis Kaydı</a></p>
    </div>

    <?php
    $sql = "CALL sp_ServisKaydi_Getir_Tum_Detayli()";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            echo "<div class='table-wrapper'>"; // Tablo için kaydırma sarmalayıcısı
            echo "<table class='table table-hover table-striped table-bordered' style='min-width: 1700px;'>"; // min-width artırıldı
            echo "<thead>";
            echo "<tr>";
            echo "<th style='width: 4%; text-align:center;'>ID</th>";
            echo "<th style='width: 13%;'>Müşteri</th>";
            echo "<th style='width: 18%;'>Ürün</th>";
            echo "<th style='width: 13%;'>Atanan Personel</th>";
            echo "<th style='width: 8%;'>Servis Tarihi</th>";
            echo "<th style='width: 20%;' class='sorun-tanimi-col'>Sorun Tanımı <small class='text-muted'>(Kayıt Tarihi)</small></th>";
            echo "<th style='width: 7%; text-align:right;'>İşçilik</th>";
            echo "<th style='width: 7%; text-align:right;'>Parça</th>";
            echo "<th style='width: 8%; text-align:right;'>Toplam</th>";
            echo "<th style='width: 8%; text-align:center;'>Durum</th>";
            echo "<th style='width: 10%; text-align:center;'>İşlemler</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            $highlight_id_servis = isset($_GET['highlight_id']) ? intval($_GET['highlight_id']) : 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $row_class_servis = ($highlight_id_servis == $row['ServisID']) ? 'table-success' : '';
                echo "<tr class='" . $row_class_servis . "'>";
                echo "<td style='text-align:center;'>" . htmlspecialchars($row['ServisID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['MusteriAdiSoyadi']) . "<br><small class='text-muted'>(ID: " . htmlspecialchars($row['MusteriID']) . ")</small></td>";
                echo "<td>" . htmlspecialchars($row['UrunBilgisi']) . "<br><small class='text-muted'>(Tip: " . htmlspecialchars($row['UrunTipi']) . ")</small></td>";
                echo "<td>" . htmlspecialchars($row['PersonelAdiSoyadi']) . "<br><small class='text-muted'>(ID: " . htmlspecialchars($row['PersonelID']) . ")</small></td>"; // PersonelID'nin SP'den geldiğini varsayıyoruz
                echo "<td>" . htmlspecialchars(date('d.m.Y', strtotime($row['ServisTarihi']))) . "</td>";
                
                $sorun_tanimi_kisa = mb_substr($row['SorunTanimi'], 0, 60, 'UTF-8');
                $sorun_tanimi_tam = $row['SorunTanimi'];
                echo "<td class='sorun-tanimi-cell' title='" . htmlspecialchars($sorun_tanimi_tam) . "'>" . nl2br(htmlspecialchars($sorun_tanimi_kisa)) . (mb_strlen($sorun_tanimi_tam, 'UTF-8') > 60 ? "..." : "") . "<small class='kayit-tarihi'>Kayıt: " . htmlspecialchars(date('d.m.Y H:i', strtotime($row['KayitTarihi']))) . "</small></td>";
                
                echo "<td style='text-align:right;'>" . htmlspecialchars(number_format($row['IscilikUcreti'], 2, ',', '.')) . " TL</td>";
                echo "<td style='text-align:right;'>" . htmlspecialchars(number_format($row['ParcaUcreti'], 2, ',', '.')) . " TL</td>";
                echo "<td style='text-align:right; font-weight:bold;'>" . htmlspecialchars(number_format($row['ToplamUcret'], 2, ',', '.')) . " TL</td>";
                
                $durum_text_servis = htmlspecialchars($row['Durum']);
                $durum_class_s = strtolower(str_replace(['i̇', 'ş', 'ç', 'ğ', 'ü', 'ö', ' '], ['i', 's', 'c', 'g', 'u', 'o', '-'], $durum_text_servis));
                echo "<td style='text-align:center;'><span class='badge badge-". $durum_class_s ."'>" . $durum_text_servis . "</span></td>";
                
                echo "<td class='servis-actions' style='text-align:center;'>";
                echo "<a href='duzenle_detay.php?id=" . $row['ServisID'] . "' class='btn btn-info btn-sm btn-block' title='Detayları Gör ve Düzenle'><i class='fas fa-search-plus'></i> Detay/Düzenle</a>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>"; // .table-wrapper sonu
            mysqli_free_result($result);
        } else {
            echo "<div class='alert alert-info mt-3'>Kayıtlı servis kaydı bulunamadı. <a href='ekle.php'>İlk servis kaydınızı oluşturun.</a></div>";
        }
        while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
    } else {
        echo "<div class='alert alert-danger mt-3'>Servis kayıtları getirilirken hata oluştu: " . mysqli_error($conn) . "</div>";
    }

    mysqli_close($conn);
    ?>
</div> <?php // .page-content-wrapper sonu ?>

<?php
require_once '../includes/footer.php';
?>