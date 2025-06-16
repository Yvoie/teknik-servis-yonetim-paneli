<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
require_once '../includes/header.php';
?>

<div class="page-content-wrapper">
    <div class="page-header-flex">
        <h2>Yedek Parça Yönetimi</h2>
        <p style="margin-bottom:0;"><a href="ekle.php" class="btn btn-success"><i class="fas fa-plus-circle"></i> Yeni Yedek Parça Ekle</a></p>
    </div>

    <?php
    $sql = "CALL sp_YedekParca_Getir_Tum()";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            echo "<div class='table-wrapper'>";
            echo "<table class='table table-hover table-striped table-bordered' style='min-width: 900px;'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th style='width: 5%; text-align:center;'>ID</th>";
            echo "<th style='width: 30%;'>Parça Adı</th>";
            echo "<th style='width: 20%;'>Marka</th>";
            echo "<th style='width: 15%; text-align:right;'>Ücreti</th>";
            echo "<th style='width: 10%; text-align:center;'>Garanti (Ay)</th>";
            echo "<th style='width: 10%; text-align:center;'>Stok</th>";
            echo "<th style='width: 10%; text-align:center;'>İşlemler</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            $highlight_id_yedekparca = isset($_GET['highlight_id']) ? intval($_GET['highlight_id']) : 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $row_class_yedekparca = ($highlight_id_yedekparca == $row['YedekParcaID']) ? 'table-success' : '';
                echo "<tr class='" . $row_class_yedekparca . "'>";
                echo "<td style='text-align:center;'>" . htmlspecialchars($row['YedekParcaID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['ParcaAdi']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Marka']) . "</td>";
                echo "<td style='text-align:right;'>" . htmlspecialchars(number_format($row['ParcaUcreti'], 2, ',', '.')) . " TL</td>";
                echo "<td style='text-align:center;'>" . htmlspecialchars($row['GarantiSuresi']) . "</td>";
                echo "<td style='text-align:center;'>" . htmlspecialchars($row['StokAdedi']) . "</td>";
                echo "<td style='text-align:center; white-space: nowrap;'>";
                echo "<a href='duzenle.php?id=" . $row['YedekParcaID'] . "' class='btn btn-warning btn-sm' style='margin-right: 5px;' title='Düzenle'><i class='fas fa-edit'></i> Düzenle</a>";
                echo "<a href='action_yedekparca.php?action=delete&id=" . $row['YedekParcaID'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Bu yedek parçayı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.\");' title='Sil'><i class='fas fa-trash-alt'></i> Sil</a>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>";
            mysqli_free_result($result);
        } else {
            echo "<div class='alert alert-info mt-3'>Kayıtlı yedek parça bulunamadı. <a href='ekle.php'>İlk yedek parçanızı ekleyin.</a></div>";
        }
        while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
    } else {
        echo "<div class='alert alert-danger mt-3'>Yedek parçalar getirilirken hata oluştu: " . mysqli_error($conn) . "</div>";
    }

    mysqli_close($conn);
    ?>
</div>

<?php
require_once '../includes/footer.php';
?>