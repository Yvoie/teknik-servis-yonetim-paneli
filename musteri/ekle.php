<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// require_once '../config/db.php'; // Bu sayfada doğrudan DB işlemi yok
require_once '../includes/header.php';
?>

<div class="page-content-wrapper">
    <div class="page-header-flex">
        <h2>Yeni Müşteri Ekle</h2>
        <a href="index.php" class="btn" style="background-color:#e69500; border-color:#e69500; color:#000;"><i class="fas fa-list-ul"></i> Müşteri Listesine Dön</a>
    </div>

    <form action="action_musteri.php" method="POST" style="margin-top: 20px;">
        <input type="hidden" name="action" value="add">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="ad"><i class="fas fa-user"></i> Adınız:</label>
                    <input type="text" name="ad" id="ad" class="form-control" required placeholder="Müşterinin adı">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="soyad"><i class="fas fa-user"></i> Soyadınız:</label>
                    <input type="text" name="soyad" id="soyad" class="form-control" required placeholder="Müşterinin soyadı">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tckn"><i class="fas fa-id-card"></i> TC Kimlik No:</label>
                    <input type="text" name="tckn" id="tckn" class="form-control" maxlength="11" placeholder="TC Kimlik Numarası (isteğe bağlı)">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="telno"><i class="fas fa-phone"></i> Telefon Numarası:</label>
                    <input type="tel" name="telno" id="telno" class="form-control" placeholder="Örn: 5XX XXX XX XX">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="eposta"><i class="fas fa-envelope"></i> E-posta Adresi:</label>
            <input type="email" name="eposta" id="eposta" class="form-control" placeholder="ornek@example.com (isteğe bağlı)">
        </div>

        <div class="form-group">
            <label for="adres"><i class="fas fa-map-marker-alt"></i> Adres:</label>
            <textarea name="adres" id="adres" class="form-control" rows="3" placeholder="Müşterinin tam adresi"></textarea>
        </div>

        <div class="form-group mt-4 text-center"> <?php // Butonları ortala ve üst boşluk ver ?>
            <button type="submit" class="btn btn-primary btn-lg" style="margin-right:10px;"><i class="fas fa-save"></i> Kaydet</button>
            <a href="index.php" class="btn btn-lg" style="background-color:#FF4C4C; color:#fff;"> <i class="fas fa-times"></i> İptal</a>
        </div>
    </form>
</div>

<?php
require_once '../includes/footer.php';
?>