<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
require_once '../includes/header.php';

$urun = null;
$urun_id_from_get = 0;
$musteriler = [];

// Müşterileri dropdown için getirelim
$sql_musteriler_edit = "CALL sp_Musteri_Getir_Tum()";
if ($result_musteriler_edit = mysqli_query($conn, $sql_musteriler_edit)) {
    if (mysqli_num_rows($result_musteriler_edit) > 0) {
        while ($row_musteri_edit = mysqli_fetch_assoc($result_musteriler_edit)) {
            $musteriler[] = $row_musteri_edit;
        }
    }
    mysqli_free_result($result_musteriler_edit);
    while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l = mysqli_store_result($conn)){ mysqli_free_result($l); } }
}

if (isset($_GET['id']) && !empty(trim($_GET['id'])) && is_numeric($_GET['id'])) {
    $urun_id_from_get = intval($_GET['id']);
    if ($urun_id_from_get > 0) {
        $sql_get_urun = "CALL sp_Urun_Getir_ByID(" . $urun_id_from_get . ")";
        if ($result_urun = mysqli_query($conn, $sql_get_urun)) {
            if (mysqli_num_rows($result_urun) == 1) {
                $urun = mysqli_fetch_assoc($result_urun);
            } else {
                $_SESSION['page_message'] = "Düzenlenecek ürün bulunamadı (ID: ".$urun_id_from_get.").";
                $_SESSION['page_message_type'] = "danger"; $urun = false;
            }
            mysqli_free_result($result_urun);
            while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l = mysqli_store_result($conn)){ mysqli_free_result($l); } }
        } else {
            $_SESSION['page_message'] = "Ürün bilgileri getirilirken hata: " . mysqli_error($conn);
            $_SESSION['page_message_type'] = "danger"; $urun = false;
        }
    } else {
        $_SESSION['page_message'] = "Geçersiz Ürün ID."; $_SESSION['page_message_type'] = "danger"; $urun = false;
    }
} else {
    $_SESSION['page_message'] = "Düzenlenecek Ürün ID belirtilmedi."; $_SESSION['page_message_type'] = "danger"; $urun = false;
}

if ($urun === false && isset($_SESSION['page_message'])) {
    // Mesaj header.php'de gösterilecek, burada sadece formun yüklenmesini engelliyoruz.
}
?>

<div class="page-content-wrapper">
    <div class="page-header-flex">
        <h2>Ürün Bilgilerini Düzenle <?php if ($urun && isset($urun['UrunID'])) echo "<small>(ID: " . htmlspecialchars($urun['UrunID']) . ")</small>"; ?></h2>
        <a href="index.php" class="btn" style="background-color:#e67e22; color:black; padding:0.375rem 0.75rem; border:none;"><i class="fas fa-list-ul"></i> Ürün Listesine Dön</a>
    </div>

    <?php if ($urun && isset($urun['UrunID'])): ?>
    <form action="action_urun.php" method="POST" style="margin-top: 20px;">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="urun_id" value="<?php echo htmlspecialchars($urun['UrunID']); ?>">

        <div class="form-group">
            <label for="musteri_id"><i class="fas fa-user-tie"></i> Ürünün Sahibi (Müşteri):</label>
            <select name="musteri_id" id="musteri_id" class="form-control" required>
                <option value="">-- Müşteri Seçiniz --</option>
                <?php foreach ($musteriler as $musteri_item): ?>
                    <option value="<?php echo htmlspecialchars($musteri_item['MusteriID']); ?>" <?php if (isset($urun['MusteriID']) && $urun['MusteriID'] == $musteri_item['MusteriID']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($musteri_item['Ad'] . ' ' . $musteri_item['Soyad']) . " (TCKN: " . htmlspecialchars($musteri_item['TCKN']) . ")"; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="urun_tipi"><i class="fas fa-tag"></i> Ürün Tipi:</label>
                    <input type="text" name="urun_tipi" id="urun_tipi" class="form-control" required value="<?php echo htmlspecialchars($urun['UrunTipi']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="marka"><i class="fas fa-copyright"></i> Marka:</label>
                    <input type="text" name="marka" id="marka" class="form-control" required value="<?php echo htmlspecialchars($urun['Marka']); ?>">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="model"><i class="fas fa-barcode"></i> Model:</label>
                    <input type="text" name="model" id="model" class="form-control" value="<?php echo htmlspecialchars($urun['Model']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="seri_numarasi"><i class="fas fa-hashtag"></i> Seri Numarası:</label>
                    <input type="text" name="seri_numarasi" id="seri_numarasi" class="form-control" value="<?php echo htmlspecialchars($urun['SeriNumarasi']); ?>">
                </div>
            </div>
        </div>

        <div class="form-group mt-4 text-center">
            <button type="submit" class="btn btn-primary btn-lg" style="margin-right:10px;"><i class="fas fa-sync-alt"></i> Bilgileri Güncelle</button>
            <a href="index.php" class="btn btn-lg" style="background-color:#e74c3c; color:white; border:none;"><i class="fas fa-times"></i> İptal</a>
        </div>
    </form>
    <?php elseif (!isset($_SESSION['page_message'])): // Eğer session'da özel bir mesaj yoksa ve ürün yüklenemediyse ?>
        <div class="alert alert-warning mt-3">Düzenlenecek ürün bilgileri yüklenemedi veya ürün mevcut değil.</div>
    <?php endif; ?>
</div>

<?php
if(isset($conn)) { mysqli_close($conn); }
require_once '../includes/footer.php';
?>