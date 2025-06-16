<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
require_once '../includes/header.php';
?>

<div class="page-content-wrapper">
    <div class="page-header-flex">
        <h2>Teknik Personel Yönetimi</h2>
        <p style="margin-bottom:0;"><a href="ekle.php" class="btn btn-success"><i class="fas fa-user-plus"></i> Yeni Personel Ekle</a></p>
    </div>

    <?php
    $sql = "CALL sp_TeknikPersonel_Getir_Tum()"; // TeknikPersonelID kullandığımız SP
    $result = mysqli_query($conn, $sql);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            echo "<div class='table-wrapper'>";
            echo "<table class='table table-hover table-striped table-bordered' style='min-width: 800px;'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th style='width: 5%; text-align:center;'>ID</th>";
            echo "<th style='width: 25%;'>Ad</th>";
            echo "<th style='width: 25%;'>Soyad</th>";
            echo "<th style='width: 20%;'>Vardiya</th>";
            echo "<th style='width: 15%;'>Telefon No</th>";
            echo "<th style='width: 10%; text-align:center;'>İşlemler</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            $highlight_id_personel = isset($_GET['highlight_id']) ? intval($_GET['highlight_id']) : 0;
            while ($row = mysqli_fetch_assoc($result)) {
                // Saklı yordamdan dönen ID kolon adının 'TeknikPersonelID' olduğunu varsayıyoruz.
                $row_class_personel = ($highlight_id_personel == $row['TeknikPersonelID']) ? 'table-success' : '';
                echo "<tr class='" . $row_class_personel . "'>";
                echo "<td style='text-align:center;'>" . htmlspecialchars($row['TeknikPersonelID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Ad']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Soyad']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Vardiya']) . "</td>";
                echo "<td>" . htmlspecialchars($row['TelNo']) . "</td>";
                echo "<td style='text-align:center; white-space: nowrap;'>";
                echo "<a href='duzenle.php?id=" . $row['TeknikPersonelID'] . "' class='btn btn-warning btn-sm' style='margin-right: 5px;' title='Düzenle'><i class='fas fa-edit'></i> Düzenle</a>";
                echo "<a href='action_personel.php?action=delete&id=" . $row['TeknikPersonelID'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Bu teknik personeli silmek istediğinizden emin misiniz? Atanmış servis kayıtları varsa sorun olabilir.\");' title='Sil'><i class='fas fa-trash-alt'></i> Sil</a>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>";
            mysqli_free_result($result);
        } else {
            echo "<div class='alert alert-info mt-3'>Kayıtlı teknik personel bulunamadı. <a href='ekle.php'>İlk personelinizi ekleyin.</a></div>";
        }
        while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
    } else {
        echo "<div class='alert alert-danger mt-3'>Teknik personel getirilirken hata oluştu: " . mysqli_error($conn) . "</div>";
    }

    mysqli_close($conn);
    ?>
</div>

<?php
require_once '../includes/footer.php';
?>