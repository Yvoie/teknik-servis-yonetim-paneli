<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/header.php';
// require_once '../config/db.php'; // Bu sayfada doğrudan DB işlemi yok
?>

<div class="page-content-wrapper">
    <div class="page-header-flex">
        <h2>Yeni Yedek Parça Ekle</h2>
        <a href="index.php" class="btn" style="background-color:#e69500; border-color:#e69500; color:#000;"><i class="fas fa-list-ul"></i> Yedek Parça Listesine Dön</a>
    </div>

    <form action="action_yedekparca.php" method="POST" style="margin-top: 20px;">
        <input type="hidden" name="action" value="add">

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-tools"></i> Parça Bilgileri</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="parca_adi"><i class="fas fa-tag"></i> Parça Adı:</label>
                            <input type="text" name="parca_adi" id="parca_adi" class="form-control form-control-lg" required placeholder="Yedek Parçanın Tam Adı (Örn: Buzdolabı Kompresörü Model X)">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="marka"><i class="fas fa-copyright"></i> Marka:</label>
                            <input type="text" name="marka" id="marka" class="form-control form-control-lg" placeholder="Parçanın Markası (varsa)">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="parca_ucreti"><i class="fas fa-lira-sign"></i> Parça Birim Ücreti (TL):</label>
                            <input type="number" name="parca_ucreti" id="parca_ucreti" class="form-control form-control-lg" step="0.01" min="0" required placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="stok_adedi"><i class="fas fa-cubes"></i> Stok Adedi:</label>
                            <input type="number" name="stok_adedi" id="stok_adedi" class="form-control form-control-lg" min="0" required placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="garanti_suresi"><i class="fas fa-shield-alt"></i> Garanti Süresi (Ay):</label>
                            <input type="number" name="garanti_suresi" id="garanti_suresi" class="form-control form-control-lg" min="0" placeholder="0 (Garantisiz ise)">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mt-4 text-center">
            <button type="submit" class="btn btn-primary btn-lg" style="margin-right:10px;"><i class="fas fa-save"></i> Yedek Parçayı Kaydet</button>
            <a href="index.php" class="btn btn-lg" style="background-color:#FF4C4C; color:#fff;"> <i class="fas fa-times"></i> İptal</a>
        </div>
    </form>
</div>

<?php
require_once '../includes/footer.php';
?>