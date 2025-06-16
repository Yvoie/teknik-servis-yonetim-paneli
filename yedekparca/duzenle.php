<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
require_once '../includes/header.php';

$yedekparca = null;
$yedekparca_id_from_get = 0;

if (isset($_GET['id']) && !empty(trim($_GET['id'])) && is_numeric($_GET['id'])) {
    $yedekparca_id_from_get = intval($_GET['id']);
    if ($yedekparca_id_from_get > 0) {
        $sql_get_yedekparca = "CALL sp_YedekParca_Getir_ByID(" . $yedekparca_id_from_get . ")";
        if ($result_yedekparca = mysqli_query($conn, $sql_get_yedekparca)) {
            if (mysqli_num_rows($result_yedekparca) == 1) {
                $yedekparca = mysqli_fetch_assoc($result_yedekparca);
            } else {
                $_SESSION['page_message'] = "Düzenlenecek yedek parça bulunamadı (ID: ".$yedekparca_id_from_get.").";
                $_SESSION['page_message_type'] = "danger"; $yedekparca = false;
            }
            mysqli_free_result($result_yedekparca);
            while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
        } else { $_SESSION['page_message'] = "Yedek parça bilgileri getirilirken hata: " . mysqli_error($conn); $_SESSION['page_message_type'] = "danger"; $yedekparca = false;}
    } else { $_SESSION['page_message'] = "Geçersiz Yedek Parça ID."; $_SESSION['page_message_type'] = "danger"; $yedekparca = false;}
} else { $_SESSION['page_message'] = "Düzenlenecek Yedek Parça ID belirtilmedi."; $_SESSION['page_message_type'] = "danger"; $yedekparca = false;}

if ($yedekparca === false && !isset($_SESSION['page_message'])) {
    $_SESSION['page_message'] = "İstenen yedek parça kaydı yüklenemedi.";
    $_SESSION['page_message_type'] = "warning";
}
?>

<div class="page-content-wrapper">
    <div class="page-header-flex">
        <h2>Yedek Parça Bilgilerini Düzenle <?php if ($yedekparca && isset($yedekparca['YedekParcaID'])) echo "<small class='text-muted'>(ID: " . htmlspecialchars($yedekparca['YedekParcaID']) . ")</small>"; ?></h2>
        <a href="index.php" class="btn" style="background-color:#e69500; border-color:#e69500; color:#000;"><i class="fas fa-list-ul"></i> Yedek Parça Listesine Dön</a>
    </div>

    <?php if ($yedekparca && isset($yedekparca['YedekParcaID'])): ?>
    <form action="action_yedekparca.php" method="POST" style="margin-top: 20px;">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="yedekparca_id" value="<?php echo htmlspecialchars($yedekparca['YedekParcaID']); ?>">

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-edit"></i> Parça Detayları</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="parca_adi"><i class="fas fa-tag"></i> Parça Adı:</label>
                            <input type="text" name="parca_adi" id="parca_adi" class="form-control form-control-lg" required value="<?php echo htmlspecialchars($yedekparca['ParcaAdi']); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="marka"><i class="fas fa-copyright"></i> Marka:</label>
                            <input type="text" name="marka" id="marka" class="form-control form-control-lg" value="<?php echo htmlspecialchars($yedekparca['Marka']); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="parca_ucreti"><i class="fas fa-lira-sign"></i> Parça Birim Ücreti (TL):</label>
                            <input type="number" name="parca_ucreti" id="parca_ucreti" class="form-control form-control-lg" step="0.01" min="0" required value="<?php echo htmlspecialchars(number_format($yedekparca['ParcaUcreti'], 2, '.', '')); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="stok_adedi"><i class="fas fa-cubes"></i> Stok Adedi:</label>
                            <input type="number" name="stok_adedi" id="stok_adedi" class="form-control form-control-lg" min="0" required value="<?php echo htmlspecialchars($yedekparca['StokAdedi']); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="garanti_suresi"><i class="fas fa-shield-alt"></i> Garanti Süresi (Ay):</label>
                            <input type="number" name="garanti_suresi" id="garanti_suresi" class="form-control form-control-lg" min="0" value="<?php echo htmlspecialchars($yedekparca['GarantiSuresi']); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mt-4 text-center">
            <button type="submit" class="btn btn-primary btn-lg" style="margin-right:10px;"><i class="fas fa-sync-alt"></i> Bilgileri Güncelle</button>
            <a href="index.php" class="btn btn-lg" style="background-color:#FF4C4C; color:#fff;"> <i class="fas fa-times"></i> İptal</a>
        </div>
    </form>
    <?php elseif (!isset($_SESSION['page_message'])): ?>
        <div class="alert alert-warning mt-3">Düzenlenecek yedek parça bilgileri yüklenemedi veya kayıt mevcut değil.</div>
    <?php endif; ?>
</div>

<?php
if(isset($conn)) { mysqli_close($conn); }
require_once '../includes/footer.php';
?>