<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
require_once '../includes/header.php';

$personel = null;
$teknik_personel_id_from_get = 0;

if (isset($_GET['id']) && !empty(trim($_GET['id'])) && is_numeric($_GET['id'])) {
    $teknik_personel_id_from_get = intval($_GET['id']);
    if ($teknik_personel_id_from_get > 0) {
        // Saklı yordamın p_TeknikPersonelID parametresi aldığını ve TeknikPersonelID döndürdüğünü varsayıyoruz
        $sql_get_personel = "CALL sp_TeknikPersonel_Getir_ByID(" . $teknik_personel_id_from_get . ")";
        if ($result_personel = mysqli_query($conn, $sql_get_personel)) {
            if (mysqli_num_rows($result_personel) == 1) {
                $personel = mysqli_fetch_assoc($result_personel);
            } else {
                $_SESSION['page_message'] = "Düzenlenecek teknik personel bulunamadı (ID: ".$teknik_personel_id_from_get.").";
                $_SESSION['page_message_type'] = "danger"; $personel = false;
            }
            mysqli_free_result($result_personel);
            while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
        } else { $_SESSION['page_message'] = "Personel bilgileri getirilirken hata: " . mysqli_error($conn); $_SESSION['page_message_type'] = "danger"; $personel = false;}
    } else { $_SESSION['page_message'] = "Geçersiz Teknik Personel ID."; $_SESSION['page_message_type'] = "danger"; $personel = false;}
} else { $_SESSION['page_message'] = "Düzenlenecek Teknik Personel ID belirtilmedi."; $_SESSION['page_message_type'] = "danger"; $personel = false;}

if ($personel === false && !isset($_SESSION['page_message'])) {
    $_SESSION['page_message'] = "İstenen personel kaydı yüklenemedi.";
    $_SESSION['page_message_type'] = "warning";
}
?>

<div class="page-content-wrapper">
    <div class="page-header-flex">
        <h2>Teknik Personel Bilgilerini Düzenle <?php if ($personel && isset($personel['TeknikPersonelID'])) echo "<small class='text-muted'>(ID: " . htmlspecialchars($personel['TeknikPersonelID']) . ")</small>"; ?></h2>
        <a href="index.php" class="btn" style="background-color:#e69500; border-color:#e69500; color:#000;"><i class="fas fa-list-ul"></i> Personel Listesine Dön</a>
    </div>

    <?php if ($personel && isset($personel['TeknikPersonelID'])): ?>
    <form action="action_personel.php" method="POST" style="margin-top: 20px;">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="teknik_personel_id" value="<?php echo htmlspecialchars($personel['TeknikPersonelID']); ?>">

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-edit"></i> Personel Detayları</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ad"><i class="fas fa-user"></i> Adı:</label>
                            <input type="text" name="ad" id="ad" class="form-control form-control-lg" required value="<?php echo htmlspecialchars($personel['Ad']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="soyad"><i class="fas fa-user"></i> Soyadı:</label>
                            <input type="text" name="soyad" id="soyad" class="form-control form-control-lg" required value="<?php echo htmlspecialchars($personel['Soyad']); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vardiya"><i class="fas fa-clock"></i> Vardiyası:</label>
                            <input type="text" name="vardiya" id="vardiya" class="form-control form-control-lg" value="<?php echo htmlspecialchars($personel['Vardiya']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telno"><i class="fas fa-phone-alt"></i> Telefon Numarası:</label>
                            <input type="tel" name="telno" id="telno" class="form-control form-control-lg" value="<?php echo htmlspecialchars($personel['TelNo']); ?>">
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
        <div class="alert alert-warning mt-3">Düzenlenecek personel bilgileri yüklenemedi veya kayıt mevcut değil.</div>
    <?php endif; ?>
</div>

<?php
if(isset($conn)) { mysqli_close($conn); }
require_once '../includes/footer.php';
?>