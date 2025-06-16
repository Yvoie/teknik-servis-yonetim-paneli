<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
require_once '../includes/header.php';

$fatura = null;
$fatura_id_from_get = 0;

$odeme_durumlari = ['Ödenmedi', 'Ödendi', 'Kısmi Ödendi']; // Seçenekler için

if (isset($_GET['id']) && !empty(trim($_GET['id'])) && is_numeric($_GET['id'])) {
    $fatura_id_from_get = intval($_GET['id']);
    if ($fatura_id_from_get > 0) {
        // ID'ye göre fatura detaylarını getiren SP'yi çağır (Müşteri ve Servis bilgileriyle)
        $sql_get_fatura = "CALL sp_Fatura_Getir_ByID_Detayli(" . $fatura_id_from_get . ")";
        if ($result_fatura = mysqli_query($conn, $sql_get_fatura)) {
            if (mysqli_num_rows($result_fatura) == 1) {
                $fatura = mysqli_fetch_assoc($result_fatura);
            } else {
                $_SESSION['mesaj'] = "Fatura bulunamadı (ID: ".$fatura_id_from_get.").";
                $_SESSION['mesaj_tur'] = "danger"; $fatura = false;
            }
            mysqli_free_result($result_fatura);
            while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
        } else {
            $_SESSION['mesaj'] = "Fatura bilgileri getirilirken hata: " . mysqli_error($conn);
            $_SESSION['mesaj_tur'] = "danger"; $fatura = false;
        }
    } else {
        $_SESSION['mesaj'] = "Geçersiz Fatura ID."; $_SESSION['mesaj_tur'] = "danger"; $fatura = false;
    }
} else {
    $_SESSION['mesaj'] = "Düzenlenecek Fatura ID belirtilmedi."; $_SESSION['mesaj_tur'] = "danger"; $fatura = false;
}

if (!$fatura && isset($_SESSION['mesaj'])) {
     echo "<div class='alert alert-" . htmlspecialchars($_SESSION['mesaj_tur']) . "'>" . htmlspecialchars($_SESSION['mesaj']) . " <a href='index.php' class='btn btn-warning btn-sm'>Fatura Listesine Dön</a></div>";
    unset($_SESSION['mesaj']); unset($_SESSION['mesaj_tur']);
}

if ($fatura && isset($fatura['FaturaID'])):
?>

<div class="page-content-wrapper">
    <div class="page-header-flex">
        <h2>Fatura Ödeme Durumu Güncelle (Fatura ID: <?php echo htmlspecialchars($fatura['FaturaID']); ?>)</h2>
        <p><a href="index.php" class="btn btn-warning btn-sm">Fatura Listesine Dön</a></p>
    </div>

    <table class="table table-bordered" style="margin-bottom: 20px;">
        <tr><th style="width:25%;">Müşteri:</th><td><?php echo htmlspecialchars($fatura['MusteriAdiSoyadi']); ?></td></tr>
        <tr><th>Servis ID:</th><td><a href="../servis/duzenle_detay.php?id=<?php echo htmlspecialchars($fatura['ServisID']); ?>"><?php echo htmlspecialchars($fatura['ServisID']); ?></a></td></tr>
        <tr><th>Fatura Tarihi:</th><td><?php echo htmlspecialchars(date('d.m.Y', strtotime($fatura['FaturaTarihi']))); ?></td></tr>
        <tr><th>Toplam Ücret:</th><td><strong><?php echo htmlspecialchars(number_format($fatura['ToplamUcret'], 2, ',', '.')); ?> TL</strong></td></tr>
        <tr><th>Mevcut Ödeme Durumu:</th><td><?php echo htmlspecialchars($fatura['OdemeDurumu']); ?></td></tr>
    </table>

    <form action="action_fatura.php" method="POST" class="form-horizontal">
        <input type="hidden" name="action" value="update_odeme_durumu">
        <input type="hidden" name="fatura_id" value="<?php echo htmlspecialchars($fatura['FaturaID']); ?>">

        <div class="form-group row">
            <label for="odeme_durumu" class="col-sm-3 col-form-label">Yeni Ödeme Durumu:</label>
            <div class="col-sm-9">
                <select name="odeme_durumu" id="odeme_durumu" class="form-control" required>
                    <?php foreach ($odeme_durumlari as $durum): ?>
                        <option value="<?php echo $durum; ?>" <?php if ($fatura['OdemeDurumu'] == $durum) echo 'selected'; ?>>
                            <?php echo $durum; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-9 offset-sm-3">
                <button type="submit" class="btn btn-primary">Ödeme Durumunu Güncelle</button>
                <a href="index.php" class="btn btn-default" style="margin-left:10px; background-color: #E74C3C; border-color: #C0392B; color: white;">İptal</a>
            </div>
        </div>
    </form>
</div>

<?php
endif;

if(isset($conn)) { mysqli_close($conn); }
require_once '../includes/footer.php';
?>