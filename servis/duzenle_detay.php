<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
require_once '../includes/header.php';

$servis_kaydi = null;
$kullanilan_parcalar = [];
$tum_yedek_parcalar = [];
$servis_id_from_get = 0;
$odeme_durumlari = ['Beklemede', 'İşlemde', 'Tamamlandı', 'İptal Edildi']; // Enum değerleri


if (isset($_GET['id']) && !empty(trim($_GET['id'])) && is_numeric($_GET['id'])) {
    $servis_id_from_get = intval($_GET['id']);
    if ($servis_id_from_get > 0) {
        $sql_get_servis = "CALL sp_ServisKaydi_Getir_ByID_Detayli(" . $servis_id_from_get . ")";
        if ($result_servis = mysqli_query($conn, $sql_get_servis)) {
            if (mysqli_num_rows($result_servis) == 1) {
                $servis_kaydi = mysqli_fetch_assoc($result_servis);
            } else {
                $_SESSION['page_message'] = "Servis kaydı bulunamadı (ID: ".$servis_id_from_get.").";
                $_SESSION['page_message_type'] = "danger"; $servis_kaydi = false;
            }
            mysqli_free_result($result_servis);
            while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l = mysqli_store_result($conn)){ mysqli_free_result($l); } }
        } else { $_SESSION['page_message'] = "Servis kaydı bilgileri getirilirken hata: " . mysqli_error($conn); $_SESSION['page_message_type'] = "danger"; $servis_kaydi = false; }

        if ($servis_kaydi) {
            $sql_kullanilan_parcalar = "CALL sp_Servis_YedekParca_Getir_ByServisID(" . $servis_id_from_get . ")";
            if ($result_k_parcalar = mysqli_query($conn, $sql_kullanilan_parcalar)) {
                while ($row_k_parca = mysqli_fetch_assoc($result_k_parcalar)) { $kullanilan_parcalar[] = $row_k_parca; }
                mysqli_free_result($result_k_parcalar);
                while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l = mysqli_store_result($conn)){ mysqli_free_result($l); } }
            }
            $sql_tum_parcalar = "CALL sp_YedekParca_Getir_Tum()";
            if ($result_tum_parcalar = mysqli_query($conn, $sql_tum_parcalar)) {
                while ($row_tum_parca = mysqli_fetch_assoc($result_tum_parcalar)) { $tum_yedek_parcalar[] = $row_tum_parca; }
                mysqli_free_result($result_tum_parcalar);
                while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l = mysqli_store_result($conn)){ mysqli_free_result($l); } }
            }
        }
    } else { $_SESSION['page_message'] = "Geçersiz Servis ID."; $_SESSION['page_message_type'] = "danger"; $servis_kaydi = false; }
} else { $_SESSION['page_message'] = "Servis ID belirtilmedi."; $_SESSION['page_message_type'] = "danger"; $servis_kaydi = false; }

if ($servis_kaydi === false && !isset($_SESSION['page_message'])) {
    $_SESSION['page_message'] = "İstenen servis kaydı yüklenemedi veya mevcut değil.";
    $_SESSION['page_message_type'] = "warning";
}
?>

<div class="page-content-wrapper">
    <div class="page-header-flex">
        <h2>Servis Detayları ve Yönetimi <?php if ($servis_kaydi && isset($servis_kaydi['ServisID'])) echo "<small class='text-muted'>(Kayıt No: " . htmlspecialchars($servis_kaydi['ServisID']) . ")</small>"; ?></h2>
        <a href="index.php" class="btn" style="background-color:#e69500; border-color:#e69500; color:#000;"><i class="fas fa-list-ul"></i> Servis Listesine Dön</a>
</a>
    </div>

    <?php if ($servis_kaydi && isset($servis_kaydi['ServisID'])): ?>
    <div class="row mt-4">
        <div class="col-lg-7 mb-4"> <?php // Sol Sütun: Genel Bilgiler, Ücret/Durum ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-receipt"></i> Servis Özeti</h4>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Müşteri:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($servis_kaydi['MusteriAdiSoyadi']); ?> <small class="text-muted">(Tel: <?php echo htmlspecialchars($servis_kaydi['MusteriTel']); ?>)</small></dd>
                        <dt class="col-sm-4">Ürün:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($servis_kaydi['UrunBilgisi']); ?> <small class="text-muted">(Tip: <?php echo htmlspecialchars($servis_kaydi['UrunTipi']); ?>)</small></dd>
                        <dt class="col-sm-4">Atanan Personel:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($servis_kaydi['PersonelAdiSoyadi']); ?></dd>
                        <dt class="col-sm-4">Planlanan Servis Tarihi:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars(date('d F Y, l', strtotime($servis_kaydi['ServisTarihi']))); ?></dd>
                        <dt class="col-sm-4">Kayıt Tarihi:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($servis_kaydi['KayitTarihi']))); ?></dd>
                        <dt class="col-sm-12 mt-2">Sorun Tanımı / Talep:</dt>
                        <dd class="col-sm-12"><div class="p-2 bg-light border rounded"><?php echo nl2br(htmlspecialchars($servis_kaydi['SorunTanimi'])); ?></div></dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light py-3">
                     <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-edit"></i> Ücret ve Durum Güncelleme</h4>
                </div>
                <div class="card-body">
                    <form action="action_servis.php" method="POST">
                        <input type="hidden" name="action" value="update_ucret_durum">
                        <input type="hidden" name="servis_id" value="<?php echo htmlspecialchars($servis_kaydi['ServisID']); ?>">
                        
                        <div class="form-group">
                            <label for="iscilik_ucreti"><i class="fas fa-hand-holding-usd"></i> İşçilik Ücreti (TL):</label>
                            <input type="number" name="iscilik_ucreti" id="iscilik_ucreti" class="form-control form-control-lg" step="0.01" min="0" value="<?php echo htmlspecialchars(number_format($servis_kaydi['IscilikUcreti'], 2, '.', '')); ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-tools"></i> Parça Ücreti (TL):</label>
                                    <p class="form-control-static form-control-lg bg-light border rounded px-3 py-2 mb-0"><?php echo htmlspecialchars(number_format($servis_kaydi['ParcaUcreti'], 2, ',', '.')); ?> TL</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-cash-register"></i> Toplam Ücret (TL):</label>
                                    <p class="form-control-static form-control-lg bg-light border rounded px-3 py-2 mb-0"><strong><?php echo htmlspecialchars(number_format($servis_kaydi['ToplamUcret'], 2, ',', '.')); ?> TL</strong></p>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <label for="durum"><i class="fas fa-clipboard-list"></i> Servis Durumu:</label>
                            <select name="durum" id="durum" class="form-control form-control-lg">
                                <?php foreach ($odeme_durumlari as $durum_secenek): ?>
                                    <option value="<?php echo $durum_secenek; ?>" <?php if ($servis_kaydi['Durum'] == $durum_secenek) echo 'selected'; ?>>
                                        <?php echo $durum_secenek; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg mt-3"><i class="fas fa-sync-alt"></i> Bilgileri Kaydet</button>
                        <?php 
                        // Fatura oluşturma butonu (Eğer servis tamamlandıysa ve henüz faturası kesilmemişse göster)
                        $fatura_var_mi = false;
                        if ($conn && $servis_kaydi['Durum'] == 'Tamamlandı') {
                            $res_fatura_check = mysqli_query($conn, "SELECT 1 FROM Fatura WHERE ServisID = " . $servis_kaydi['ServisID']);
                            if ($res_fatura_check && mysqli_num_rows($res_fatura_check) > 0) {
                                $fatura_var_mi = true;
                            }
                            if($res_fatura_check) mysqli_free_result($res_fatura_check);
                            while(mysqli_more_results($conn) && mysqli_next_result($conn)) { if($l = mysqli_store_result($conn)){ mysqli_free_result($l); } }
                        }
                        if ($servis_kaydi['Durum'] == 'Tamamlandı' && !$fatura_var_mi):
                        ?>
                            <a href="../fatura/action_fatura.php?action=create_fatura_from_servis&servis_id=<?php echo $servis_kaydi['ServisID']; ?>" class="btn btn-success btn-lg mt-3" style="margin-left: 10px;" onclick="return confirm('Bu servis için fatura oluşturulacak. Onaylıyor musunuz?');"><i class="fas fa-file-invoice-dollar"></i> Fatura Oluştur</a>
                        <?php elseif ($fatura_var_mi): ?>
                            <a href="../fatura/duzenle_odeme.php?id=<?php echo mysqli_query($conn, "SELECT FaturaID FROM Fatura WHERE ServisID = ".$servis_kaydi['ServisID'])->fetch_assoc()['FaturaID']; ?>" class="btn btn-outline-info btn-lg mt-3" style="margin-left: 10px;"><i class="fas fa-eye"></i> Faturayı Gör/Düzenle</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5"> <?php // Sağ Sütun: Kullanılan Parçalar ve Parça Ekleme ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 font-weight-bold text-primary"><i class="fas fa-archive"></i> Kullanılan Yedek Parçalar</h5>
                </div>
                <div class="card-body">
                    <?php if (count($kullanilan_parcalar) > 0): ?>
                        <ul class="list-group list-group-flush">
                        <?php foreach($kullanilan_parcalar as $k_parca): ?>
                            <li class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="d-block"><?php echo htmlspecialchars($k_parca['ParcaAdi']); ?></strong>
                                    <small class="text-muted"><?php echo htmlspecialchars($k_parca['ParcaMarka']); ?> | Adet: <?php echo htmlspecialchars($k_parca['KullanilanAdet']); ?></small>
                                    <small class="d-block text-info">Birim: <?php echo htmlspecialchars(number_format($k_parca['BirimFiyat'], 2, ',', '.')); ?> TL | Toplam: <strong><?php echo htmlspecialchars(number_format($k_parca['AraToplam'], 2, ',', '.')); ?> TL</strong></small>
                                </div>
                                <a href="action_servis.php?action=remove_parca&servis_id=<?php echo $servis_id_from_get; ?>&yedekparca_id=<?php echo $k_parca['YedekParcaID']; ?>&adet=<?php echo $k_parca['KullanilanAdet']; ?>" 
                                   class="btn btn-outline-danger btn-sm" title="Parçanın Tamamını Çıkar"
                                   onclick="return confirm('<?php echo htmlspecialchars(addslashes($k_parca['ParcaAdi'])); ?> parçasının tamamını (<?php echo $k_parca['KullanilanAdet']; ?> adet) servis kaydından çıkarmak istediğinizden emin misiniz? Stok güncellenecektir.');">
                                   <i class="fas fa-trash-alt"></i>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">Bu servis kaydında henüz yedek parça kullanılmamış.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light py-3">
                     <h5 class="mb-0 font-weight-bold text-primary"><i class="fas fa-cart-plus"></i> Servise Yedek Parça Ekle</h5>
                </div>
                <div class="card-body">
                    <form action="action_servis.php" method="POST">
                        <input type="hidden" name="action" value="add_parca_to_servis">
                        <input type="hidden" name="servis_id" value="<?php echo htmlspecialchars($servis_kaydi['ServisID']); ?>">
                        <div class="form-group">
                            <label for="yedekparca_id_ekle">Yedek Parça Seçiniz:</label> <?php // ID'yi değiştirdim, yukarıdakiyle karışmasın ?>
                            <select name="yedekparca_id" id="yedekparca_id_ekle" class="form-control" required>
                                <option value="">-- Seçim Yapınız --</option>
                                <?php foreach ($tum_yedek_parcalar as $yp): ?>
                                    <option value="<?php echo htmlspecialchars($yp['YedekParcaID']); ?>" data-stok="<?php echo htmlspecialchars($yp['StokAdedi']); ?>">
                                        <?php echo htmlspecialchars($yp['ParcaAdi'] . ' (' . $yp['Marka'] . ') - Stok: ' . $yp['StokAdedi']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="kullanilan_adet_ekle">Eklenecek Adet:</label> <?php // ID'yi değiştirdim ?>
                            <input type="number" name="kullanilan_adet" id="kullanilan_adet_ekle" class="form-control" min="1" value="1" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-plus-circle"></i> Parçayı Servise Ekle</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php elseif (!isset($_SESSION['page_message'])): ?>
        <div class="alert alert-warning mt-3">İstenen servis kaydı bilgileri yüklenemedi veya sistemde kayıtlı değil. Lütfen geçerli bir ID ile tekrar deneyin.</div>
    <?php endif; ?>
</div>

<?php
if(isset($conn)) { mysqli_close($conn); }
require_once '../includes/footer.php';
?>