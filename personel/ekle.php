<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/header.php';
?>

<div class="page-content-wrapper">
    <div class="page-header-flex">
        <h2>Yeni Teknik Personel Ekle</h2>
        <a href="index.php" class="btn" style="background-color:#e69500; border-color:#e69500; color:#000;"><i class="fas fa-list-ul"></i> Personel Listesine Dön</a>
    </div>

    <form action="action_personel.php" method="POST" style="margin-top: 20px;">
        <input type="hidden" name="action" value="add">

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-user-cog"></i> Personel Bilgileri</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ad"><i class="fas fa-user"></i> Adı:</label>
                            <input type="text" name="ad" id="ad" class="form-control form-control-lg" required placeholder="Personelin adı">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="soyad"><i class="fas fa-user"></i> Soyadı:</label>
                            <input type="text" name="soyad" id="soyad" class="form-control form-control-lg" required placeholder="Personelin soyadı">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vardiya"><i class="fas fa-clock"></i> Vardiyası:</label>
                            <input type="text" name="vardiya" id="vardiya" class="form-control form-control-lg" placeholder="Örn: Gündüz, Akşam, Gece">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telno"><i class="fas fa-phone-alt"></i> Telefon Numarası:</label>
                            <input type="tel" name="telno" id="telno" class="form-control form-control-lg" placeholder="Örn: 5XX XXX XX XX">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mt-4 text-center">
            <button type="submit" class="btn btn-primary btn-lg" style="margin-right:10px;"><i class="fas fa-save"></i> Personeli Kaydet</button>
            <a href="index.php" class="btn btn-lg" style="background-color:#FF4C4C; color:#fff;"> <i class="fas fa-times"></i> İptal</a>
        </div>
    </form>
</div>

<?php
require_once '../includes/footer.php';
?>