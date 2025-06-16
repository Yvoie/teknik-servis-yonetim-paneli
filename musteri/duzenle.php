<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
require_once '../includes/header.php';

$musteri = null;
$musteri_id_from_get = 0;

if (isset($_GET['id']) && !empty(trim($_GET['id'])) && is_numeric($_GET['id'])) {
    $musteri_id_from_get = intval($_GET['id']);
    if ($musteri_id_from_get > 0) {
        $sql_get_musteri = "CALL sp_Musteri_Getir_ByID(" . $musteri_id_from_get . ")";
        if ($result_musteri = mysqli_query($conn, $sql_get_musteri)) {
            if (mysqli_num_rows($result_musteri) == 1) {
                $musteri = mysqli_fetch_assoc($result_musteri);
            } else {
                $_SESSION['page_message'] = "Düzenlenecek müşteri bulunamadı (ID: ".$musteri_id_from_get.").";
                $_SESSION['page_message_type'] = "danger"; $musteri = false;
            }
            mysqli_free_result($result_musteri);
            while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l_result = mysqli_store_result($conn)){ mysqli_free_result($l_result); } }
        } else {
            $_SESSION['page_message'] = "Müşteri bilgileri getirilirken hata: " . mysqli_error($conn);
            $_SESSION['page_message_type'] = "danger"; $musteri = false;
        }
    } else {
        $_SESSION['page_message'] = "Geçersiz Müşteri ID."; $_SESSION['page_message_type'] = "danger"; $musteri = false;
    }
} else {
    $_SESSION['page_message'] = "Düzenlenecek Müşteri ID belirtilmedi."; $_SESSION['page_message_type'] = "danger"; $musteri = false;
}

// Mesajları header.php zaten gösteriyor, bu yüzden burada echo yapmaya gerek yok, sadece yönlendirme veya form göstermeme kararı
if ($musteri === false) { // Eğer $musteri false ise (ID geçersiz veya müşteri bulunamadı)
    // header("Location: index.php"); // Direkt listeye yönlendirilebilir
    // exit();
    // Veya hata mesajını header zaten göstereceği için, bu sayfada ek bir şey yapmaya gerek yok,
    // aşağıdaki if ($musteri && isset($musteri['MusteriID'])) bloğu çalışmayacak.
}
?>

<div class="page-content-wrapper">
    <div class="page-header-flex">
        <h2>Müşteri Bilgilerini Düzenle <?php if ($musteri && isset($musteri['MusteriID'])) echo "(ID: " . htmlspecialchars($musteri['MusteriID']) . ")"; ?></h2>
        <a href="index.php" class="btn" style="background-color:#e69500; border-color:#e69500; color:#000;"><i class="fas fa-list-ul"></i> Müşteri Listesine Dön</a>
    </div>

    <?php if ($musteri && isset($musteri['MusteriID'])): ?>
    <form action="action_musteri.php" method="POST" style="margin-top: 20px;">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="musteri_id" value="<?php echo htmlspecialchars($musteri['MusteriID']); ?>">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="ad"><i class="fas fa-user"></i> Adınız:</label>
                    <input type="text" name="ad" id="ad" class="form-control" required value="<?php echo htmlspecialchars($musteri['Ad']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="soyad"><i class="fas fa-user"></i> Soyadınız:</label>
                    <input type="text" name="soyad" id="soyad" class="form-control" required value="<?php echo htmlspecialchars($musteri['Soyad']); ?>">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tckn"><i class="fas fa-id-card"></i> TC Kimlik No:</label>
                    <input type="text" name="tckn" id="tckn" class="form-control" maxlength="11" value="<?php echo htmlspecialchars($musteri['TCKN']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="telno"><i class="fas fa-phone"></i> Telefon Numarası:</label>
                    <input type="tel" name="telno" id="telno" class="form-control" value="<?php echo htmlspecialchars($musteri['TelNo']); ?>">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="eposta"><i class="fas fa-envelope"></i> E-posta Adresi:</label>
            <input type="email" name="eposta" id="eposta" class="form-control" value="<?php echo htmlspecialchars($musteri['Eposta']); ?>">
        </div>

        <div class="form-group">
            <label for="adres"><i class="fas fa-map-marker-alt"></i> Adres:</label>
            <textarea name="adres" id="adres" class="form-control" rows="3"><?php echo htmlspecialchars($musteri['Adres']); ?></textarea>
        </div>

        <div class="form-group mt-4 text-center">
            <button type="submit" class="btn btn-primary btn-lg" style="margin-right:10px;"><i class="fas fa-sync-alt"></i> Güncelle</button>
            <a href="index.php" class="btn btn-lg" style="background-color:#FF4C4C; color:#fff;"> <i class="fas fa-times"></i> İptal</a>
        </div>
    </form>
    <?php elseif (!isset($_SESSION['page_message'])): // Eğer yukarida bir session mesajı set edilmediyse ve $musteri yoksa genel hata ?>
        <div class="alert alert-warning">İstenen müşteri kaydı yüklenemedi veya mevcut değil.</div>
    <?php endif; ?>
</div>

<?php
if(isset($conn)) { mysqli_close($conn); }
require_once '../includes/footer.php';
?>