<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php'; // Müşteri ve Personel listesi için DB bağlantısı
require_once '../includes/header.php';

$musteriler = [];
$teknik_personeller = [];

$sql_musteriler = "CALL sp_Musteri_Getir_Tum()";
if ($result_musteriler = mysqli_query($conn, $sql_musteriler)) {
    if (mysqli_num_rows($result_musteriler) > 0) {
        while ($row_musteri = mysqli_fetch_assoc($result_musteriler)) { $musteriler[] = $row_musteri; }
    }
    mysqli_free_result($result_musteriler);
    while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l = mysqli_store_result($conn)){ mysqli_free_result($l); } }
}

$sql_personeller = "CALL sp_TeknikPersonel_Getir_Tum()";
if ($result_personeller = mysqli_query($conn, $sql_personeller)) {
    if (mysqli_num_rows($result_personeller) > 0) {
        while ($row_personel = mysqli_fetch_assoc($result_personeller)) { $teknik_personeller[] = $row_personel; }
    }
    mysqli_free_result($result_personeller);
    while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l = mysqli_store_result($conn)){ mysqli_free_result($l); } }
}
?>

<div class="page-content-wrapper">
    <div class="page-header-flex">
        <h2>Yeni Servis Kaydı Oluştur</h2>
        <a href="index.php" class="btn" style="background-color:#e69500; border-color:#e69500; color:#000;"><i class="fas fa-list-ul"></i> Servis Listesine Dön</a>
</a>
    </div>

    <form action="action_servis.php" method="POST" style="margin-top: 30px;" id="formServisKaydi">
        <input type="hidden" name="action" value="add">

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-user-friends"></i> Müşteri ve Ürün Bilgileri</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="musteri_id">Müşteri Seçiniz:</label>
                            <select name="musteri_id" id="musteri_id" class="form-control" required>
                                <option value="">-- Müşteri Seçiniz --</option>
                                <?php foreach ($musteriler as $musteri): ?>
                                    <option value="<?php echo htmlspecialchars($musteri['MusteriID']); ?>">
                                        <?php echo htmlspecialchars($musteri['Ad'] . ' ' . $musteri['Soyad']) . " (TCKN: " . htmlspecialchars($musteri['TCKN']) . ")"; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="urun_id">Müşteriye Ait Ürün:</label>
                            <select name="urun_id" id="urun_id" class="form-control" required disabled>
                                <option value="">-- Önce Müşteri Seçiniz --</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Servis Detayları</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="personel_id">Atanacak Teknik Personel:</label>
                            <select name="personel_id" id="personel_id" class="form-control" required>
                                <option value="">-- Personel Seçiniz --</option>
                                <?php foreach ($teknik_personeller as $personel): ?>
                                    <option value="<?php echo htmlspecialchars($personel['TeknikPersonelID']); ?>">
                                        <?php echo htmlspecialchars($personel['Ad'] . ' ' . $personel['Soyad']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="servis_tarihi">Planlanan Servis Tarihi:</label>
                            <input type="date" name="servis_tarihi" id="servis_tarihi" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="sorun_tanimi">Sorun Tanımı / Talep:</label>
                    <textarea name="sorun_tanimi" id="sorun_tanimi" class="form-control" rows="5" required placeholder="Müşterinin bildirdiği arıza, sorun detayları veya bakım talebini buraya girin..."></textarea>
                </div>
                <div class="form-group">
                    <label for="iscilik_ucreti">Tahmini İşçilik Ücreti (TL):</label>
                    <input type="number" name="iscilik_ucreti" id="iscilik_ucreti" class="form-control" step="0.01" min="0" value="0.00" placeholder="0.00 (Gerekirse sonradan güncellenebilir)">
                </div>
            </div>
        </div>

        <div class="form-group mt-4 text-center">
            <button type="submit" class="btn btn-primary btn-lg" style="margin-right:10px;"><i class="fas fa-save"></i> Servis Kaydını Oluştur</button>
            <a href="index.php" class="btn btn-lg" style="background-color:#FF4C4C; color:#fff;"> <i class="fas fa-times"></i> İptal</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const musteriSelect = document.getElementById('musteri_id');
    const urunSelect = document.getElementById('urun_id');
    const projectRoot = "<?php echo $project_root_url; ?>";

    musteriSelect.addEventListener('change', function() {
        const secilenMusteriID = this.value;
        urunSelect.innerHTML = '<option value="">Yükleniyor...</option>';
        urunSelect.disabled = true;

        if (secilenMusteriID) {
            fetch(projectRoot + '/urun/ajax_get_urunler.php?musteri_id=' + secilenMusteriID)
                .then(response => {
                    if (!response.ok) { throw new Error('Network response was not ok'); }
                    return response.json();
                })
                .then(data => {
                    urunSelect.innerHTML = '<option value="">-- Ürün Seçiniz --</option>';
                    if (data.length > 0) {
                        data.forEach(function(urun) {
                            const option = document.createElement('option');
                            option.value = urun.UrunID;
                            option.textContent = urun.UrunTipi + ' - ' + urun.Marka + ' ' + urun.Model + ' (SN: ' + urun.SeriNumarasi + ')';
                            urunSelect.appendChild(option);
                        });
                        urunSelect.disabled = false;
                    } else {
                        urunSelect.innerHTML = '<option value="">Bu müşteriye ait ürün bulunamadı.</option>';
                    }
                })
                .catch(error => {
                    console.error('Ürünler getirilirken hata:', error);
                    urunSelect.innerHTML = '<option value="">Ürünler yüklenemedi.</option>';
                });
        } else {
            urunSelect.innerHTML = '<option value="">-- Önce Müşteri Seçiniz --</option>';
            urunSelect.disabled = true;
        }
    });
});
</script>

<?php
if(isset($conn)) { mysqli_close($conn); }
require_once '../includes/footer.php';
?>