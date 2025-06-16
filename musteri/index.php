<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
require_once '../includes/header.php'; // Header'ı dahil et (main.container açılır)
?>

<div class="page-content-wrapper"> <?php // Ana içerik sarmalayıcısı ?>
    <div class="page-header-flex">
        <h2>Müşteri Yönetimi</h2>
        <p style="margin-bottom:0;"><a href="ekle.php" class="btn btn-success"><i class="fas fa-user-plus"></i> Yeni Müşteri Ekle</a></p>
    </div>

    <?php
    // Mesajları header.php zaten gösteriyor, burada tekrar göstermeye gerek yok
    // if (isset($_SESSION['mesaj'])) { ... }
    ?>

    <?php
    $sql = "CALL sp_Musteri_Getir_Tum()"; // Varsayılan sıralama Ad, Soyad
    // ID'ye göre en yeniden eskiye sıralamak için SP'yi değiştirmeniz gerekir:
    // $sql = "CALL sp_Musteri_Getir_Tum_Sirali('MusteriID', 'DESC')"; 
    $result = mysqli_query($conn, $sql);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            echo "<div class='table-wrapper'>"; // Tablo için kaydırma sarmalayıcısı
            echo "<table class='table table-hover table-striped table-bordered'>"; // table-hover eklendi
            echo "<thead>";
            echo "<tr>";
            echo "<th style='width: 5%; text-align:center;'>ID</th>";
            echo "<th style='width: 15%;'>TCKN</th>";
            echo "<th style='width: 20%;'>Ad Soyad</th>";
            echo "<th style='width: 15%;'>Telefon</th>";
            echo "<th style='width: 25%;'>E-posta</th>";
            echo "<th style='width: 20%; text-align:center;'>İşlemler</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            $highlight_id_musteri = isset($_GET['highlight_id']) ? intval($_GET['highlight_id']) : 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $row_class_musteri = ($highlight_id_musteri == $row['MusteriID']) ? 'table-success' : ''; // Bootstrap vurgu class'ı
                echo "<tr class='" . $row_class_musteri . "'>";
                echo "<td style='text-align:center;'>" . htmlspecialchars($row['MusteriID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['TCKN']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Ad'] . ' ' . $row['Soyad']) . "</td>";
                echo "<td>" . htmlspecialchars($row['TelNo']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Eposta']) . "</td>";
                echo "<td style='text-align:center;'>";
                echo "<a href='duzenle.php?id=" . $row['MusteriID'] . "' class='btn btn-warning btn-sm' style='margin-right: 8px;' title='Düzenle'><i class='fas fa-edit'></i> Düzenle</a>";
                echo "<a href='action_musteri.php?action=delete&id=" . $row['MusteriID'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Bu müşteriyi silmek istediğinizden emin misiniz?\");' title='Sil'><i class='fas fa-trash-alt'></i> Sil</a>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>"; // .table-wrapper sonu
            mysqli_free_result($result);
        } else {
            echo "<div class='alert alert-info mt-3'>Kayıtlı müşteri bulunamadı. <a href='ekle.php'>İlk müşterinizi ekleyin.</a></div>";
        }
        while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
    } else {
        echo "<div class='alert alert-danger mt-3'>Müşteriler getirilirken bir hata oluştu: " . mysqli_error($conn) . "</div>";
    }

    mysqli_close($conn);
    ?>
</div> <?php // .page-content-wrapper sonu ?>

<?php
require_once '../includes/footer.php'; // Footer'ı dahil et (main.container kapanır)
?>