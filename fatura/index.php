<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
require_once '../includes/header.php'; // header.php içinde main.container açılıyor
?>

<style>
/* Bu stiller style.css dosyanıza da taşınabilir */
.page-header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.page-header-flex h2 {
    margin: 0;
    border-bottom: none;
    font-size: 1.8em;
    color: #2c3e50;
}
.table-wrapper {
    width: 100%;
    overflow-x: auto;
}
.badge {
    display: inline-block;
    padding: .35em .65em;
    font-size: .75em;
    font-weight: 700;
    line-height: 1;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: .25rem;
}
.badge-success { background-color: #28a745; } /* Ödendi */
.badge-danger { background-color: #dc3545; } /* Ödenmedi */
.badge-warning { background-color: #ffc107; color: #212529;} /* Kısmi Ödendi */
.badge-secondary { background-color: #6c757d; } /* Diğer durumlar için */
</style>

<div class="page-content-wrapper"> <?php // Tüm sayfa içeriği için sarmalayıcı ?>

    <div class="page-header-flex">
        <h2>Fatura Listesi</h2>
        <p style="margin-bottom:0;">
            <a href="../servis/index.php" class="btn btn-warning">Servis Kayıtlarına Git</a> <?php // Buton rengi güncellendi ?>
        </p>
    </div>

    <?php
    $sql = "CALL sp_Fatura_Getir_Tum_Detayli()";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            echo "<div class='table-wrapper'>";
            echo "<table class='table table-bordered table-striped' style='min-width: 1100px;'>"; // min-width ayarlandı
            echo "<thead>";
            echo "<tr>";
            echo "<th style='min-width: 80px; text-align:center;'>Fatura ID</th>";
            echo "<th style='min-width: 80px; text-align:center;'>Servis ID</th>";
            echo "<th style='min-width: 200px;'>Müşteri</th>";
            echo "<th style='min-width: 120px;'>Fatura Tarihi</th>";
            echo "<th style='min-width: 150px; text-align:right;'>Toplam Ücret</th>";
            echo "<th style='min-width: 120px;'>Ödeme Durumu</th>";
            echo "<th style='min-width: 200px; text-align:center;'>İşlemler</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            $highlight_id_fatura = isset($_GET['highlight_id']) ? intval($_GET['highlight_id']) : 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $row_class_fatura = ($highlight_id_fatura == $row['FaturaID']) ? 'table-success' : '';
                echo "<tr class='" . $row_class_fatura . "'>";
                echo "<td style='text-align:center;'>" . htmlspecialchars($row['FaturaID']) . "</td>";
                echo "<td style='text-align:center;'><a href='../servis/duzenle_detay.php?id=" . htmlspecialchars($row['ServisID']) . "'>" . htmlspecialchars($row['ServisID']) . "</a></td>";
                echo "<td>" . htmlspecialchars($row['MusteriAdiSoyadi']) . "<br><small>(ID: " . htmlspecialchars($row['MusteriID']) . ")</small></td>";
                echo "<td>" . htmlspecialchars(date('d.m.Y', strtotime($row['FaturaTarihi']))) . "</td>";
                echo "<td style='text-align:right; font-weight:bold;'>" . htmlspecialchars(number_format($row['ToplamUcret'], 2, ',', '.')) . " TL</td>";
                
                $odeme_durumu_text = htmlspecialchars($row['OdemeDurumu']);
                $badge_class = 'badge-secondary'; // Varsayılan
                if ($odeme_durumu_text == 'Ödendi') $badge_class = 'badge-success';
                elseif ($odeme_durumu_text == 'Ödenmedi') $badge_class = 'badge-danger';
                elseif ($odeme_durumu_text == 'Kısmi Ödendi') $badge_class = 'badge-warning';

                echo "<td><span class='badge " . $badge_class . "'>" . $odeme_durumu_text . "</span></td>";
                echo "<td style='text-align:center;'>";
                echo "<a href='duzenle_odeme.php?id=" . $row['FaturaID'] . "' class='btn btn-info btn-sm'>Ödeme Durumunu Güncelle</a>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>"; // .table-wrapper sonu
            mysqli_free_result($result);
        } else {
            echo "<div class='alert alert-info'>Kayıtlı fatura bulunamadı.</div>";
        }
        while(mysqli_more_results($conn) && mysqli_next_result($conn)) {
            if($l_result = mysqli_store_result($conn)){
                mysqli_free_result($l_result);
            }
        }
    } else {
        echo "<div class='alert alert-danger'>Faturalar getirilirken hata oluştu: " . mysqli_error($conn) . "</div>";
    }

    mysqli_close($conn);
    ?>
</div> <?php // .page-content-wrapper sonu ?>
<?php
require_once '../includes/footer.php'; // footer.php main.container'ı kapatıyor
?>