<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php'; // Müşteri listesi için DB bağlantısı gerekli
require_once '../includes/header.php';

$musteriler = [];
$sql_musteriler = "CALL sp_Musteri_Getir_Tum()";
if ($result_musteriler = mysqli_query($conn, $sql_musteriler)) {
    if (mysqli_num_rows($result_musteriler) > 0) {
        while ($row_musteri = mysqli_fetch_assoc($result_musteriler)) {
            $musteriler[] = $row_musteri;
        }
    }
    mysqli_free_result($result_musteriler);
    while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l = mysqli_store_result($conn)){ mysqli_free_result($l); } }
} else {
     // Hata durumunu logla veya kullanıcıya bildir
}
?>

<div class="page-content-wrapper">
    <div class="page-header-flex">
        <h2>Yeni Ürün Ekle</h2>
        <a href="index.php" class="btn" style="background-color:#e67e22; color:black; padding:0.375rem 0.75rem; border:none;"><i class="fas fa-list-ul"></i> Ürün Listesine Dön</a>
    </div>

    <form action="action_urun.php" method="POST" style="margin-top: 20px;">
        <input type="hidden" name="action" value="add">

        <div class="form-group">
            <label for="musteri_id"><i class="fas fa-user-tie"></i> Ürünün Sahibi (Müşteri):</label>
            <select name="musteri_id" id="musteri_id" class="form-control" required>
                <option value="">-- Müşteri Seçiniz --</option>
                <?php foreach ($musteriler as $musteri): ?>
                    <option value="<?php echo htmlspecialchars($musteri['MusteriID']); ?>">
                        <?php echo htmlspecialchars($musteri['Ad'] . ' ' . $musteri['Soyad']) . " (TCKN: " . htmlspecialchars($musteri['TCKN']) . ")"; ?>
                    </option>
                <?php endforeach; ?>
                <?php if(empty($musteriler)): ?>
                    <option value="" disabled>Müşteri bulunamadı. Lütfen önce müşteri ekleyin.</option>
                <?php endif; ?>
            </select>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="urun_tipi"><i class="fas fa-tag"></i> Ürün Tipi:</label>
                    <input type="text" name="urun_tipi" id="urun_tipi" class="form-control" required placeholder="Örn: Buzdolabı, Klima">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="marka"><i class="fas fa-copyright"></i> Marka:</label>
                    <input type="text" name="marka" id="marka" class="form-control" required placeholder="Örn: Arçelik, Bosch">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="model"><i class="fas fa-barcode"></i> Model:</label>
                    <input type="text" name="model" id="model" class="form-control" placeholder="Ürünün tam modeli">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="seri_numarasi"><i class="fas fa-hashtag"></i> Seri Numarası:</label>
                    <input type="text" name="seri_numarasi" id="seri_numarasi" class="form-control" placeholder="Benzersiz seri no (etikette yazar)">
                </div>
            </div>
        </div>

        <div class="form-group mt-4 text-center">
            <button type="submit" class="btn btn-primary btn-lg" style="margin-right:10px;"><i class="fas fa-plus-circle"></i> Ürünü Kaydet</button>
            <a href="index.php" class="btn btn-lg" style="background-color:#e74c3c; color:white; border:none;"><i class="fas fa-times"></i> İptal</a>
        </div>
    </form>
</div>

<?php
if(isset($conn)) { mysqli_close($conn); }
require_once '../includes/footer.php';
?>