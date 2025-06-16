<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
require_once '../includes/header.php';
?>

<div class="page-content-wrapper">
    <div class="page-header-flex">
        <h2>Ürün Yönetimi</h2>
        <p style="margin-bottom:0;"><a href="ekle.php" class="btn btn-success"><i class="fas fa-plus-circle"></i> Yeni Ürün Ekle</a></p>
    </div>

    <?php
    $sql = "CALL sp_Urun_Getir_Tum()";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            echo "<div class='table-wrapper'>";
            echo "<table class='table table-hover table-striped table-bordered'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th style='width: 5%; text-align:center;'>ID</th>";
            echo "<th style='width: 15%;'>Ürün Tipi</th>";
            echo "<th style='width: 15%;'>Marka</th>";
            echo "<th style='width: 15%;'>Model</th>";
            echo "<th style='width: 20%;'>Seri Numarası</th>";
            echo "<th style='width: 20%;'>Sahibi (Müşteri)</th>";
            echo "<th style='width: 10%; text-align:center;'>İşlemler</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            $highlight_id_urun = isset($_GET['highlight_id']) ? intval($_GET['highlight_id']) : 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $row_class_urun = ($highlight_id_urun == $row['UrunID']) ? 'table-success' : '';
                echo "<tr class='" . $row_class_urun . "'>";
                echo "<td style='text-align:center;'>" . htmlspecialchars($row['UrunID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['UrunTipi']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Marka']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Model']) . "</td>";
                echo "<td>" . htmlspecialchars($row['SeriNumarasi']) . "</td>";
                echo "<td>" . htmlspecialchars($row['MusteriAd'] . ' ' . $row['MusteriSoyad']) . " <small>(ID: " . htmlspecialchars($row['MusteriID']) . ")</small></td>";
                echo "<td style='text-align:center; white-space: nowrap;'>"; // white-space: nowrap eklendi
                echo "<a href='duzenle.php?id=" . $row['UrunID'] . "' class='btn btn-warning btn-sm' style='margin-right: 5px;' title='Düzenle'><i class='fas fa-edit'></i> Düzenle</a>"; // " Düzenle" eklendi
                echo "<a href='action_urun.php?action=delete&id=" . $row['UrunID'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Bu ürünü silmek istediğinizden emin misiniz? İlişkili servis kayıtları varsa sorun olabilir.\");' title='Sil'><i class='fas fa-trash-alt'></i> Sil</a>"; // " Sil" eklendi
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>"; 
            mysqli_free_result($result);
        } else {
            echo "<div class='alert alert-info mt-3'>Kayıtlı ürün bulunamadı. <a href='ekle.php'>İlk ürününüzü ekleyin.</a></div>";
        }
        while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
    } else {
        echo "<div class='alert alert-danger mt-3'>Ürünler getirilirken bir hata oluştu: " . mysqli_error($conn) . "</div>";
    }

    mysqli_close($conn);
    ?>
</div> 

<?php
require_once '../includes/footer.php';
?>