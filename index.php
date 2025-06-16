<?php
require_once 'includes/header.php';
require_once 'config/db.php';

$stats = [
    'toplam_musteri' => 0,
    'toplam_urun' => 0,
    'aktif_servis_sayisi' => 0,
    'tamamlanan_servis_bu_ay' => 0,
    'odenmemis_fatura_sayisi' => 0,
    'toplam_teknik_personel' => 0,
    'kritik_stok_yedekparca' => 0,
    'bugun_acilan_servis' => 0
];

if ($conn) {
    function get_count($db_conn, $query) {
        $count = 0;
        if ($result = mysqli_query($db_conn, $query)) {
            if (mysqli_num_rows($result) > 0) {
                $count = mysqli_fetch_assoc($result)['total'];
            }
            mysqli_free_result($result);
            // Saklı yordam değilse ve birden fazla sonuç seti beklenmiyorsa bu döngüye gerek olmayabilir
            // Ama CALL komutları için veya bazı durumlarda gerekebilir, güvenli olması için bırakıyorum
            while(mysqli_more_results($db_conn) && mysqli_next_result($db_conn)) { 
                if($l_result = mysqli_store_result($db_conn)){ mysqli_free_result($l_result); } 
            }
        } else {
            // Geliştirme aşamasında hatayı loglayabilir veya gösterebilirsiniz
            // error_log("Sorgu hatası: " . mysqli_error($db_conn) . " Sorgu: " . $query);
        }
        return $count;
    }

    $stats['toplam_musteri'] = get_count($conn, "SELECT COUNT(*) as total FROM Musteri");
    $stats['toplam_urun'] = get_count($conn, "SELECT COUNT(*) as total FROM Urun");
    $stats['aktif_servis_sayisi'] = get_count($conn, "SELECT COUNT(*) as total FROM ServisKaydi WHERE Durum = 'Beklemede' OR Durum = 'İşlemde'");
    
    $bu_ay_ilk_gun = date('Y-m-01');
    $bu_ay_son_gun = date('Y-m-t');
    $stats['tamamlanan_servis_bu_ay'] = get_count($conn, "SELECT COUNT(*) as total FROM ServisKaydi WHERE Durum = 'Tamamlandı' AND ServisTarihi BETWEEN '$bu_ay_ilk_gun' AND '$bu_ay_son_gun'");
    
    $stats['odenmemis_fatura_sayisi'] = get_count($conn, "SELECT COUNT(*) as total FROM Fatura WHERE OdemeDurumu = 'Ödenmedi' OR OdemeDurumu = 'Kısmi Ödendi'");
    $stats['toplam_teknik_personel'] = get_count($conn, "SELECT COUNT(*) as total FROM TeknikPersonel");
    $stats['kritik_stok_yedekparca'] = get_count($conn, "SELECT COUNT(*) as total FROM YedekParca WHERE StokAdedi < 5");
    
    $bugun_tarih = date('Y-m-d');
    $stats['bugun_acilan_servis'] = get_count($conn, "SELECT COUNT(*) as total FROM ServisKaydi WHERE DATE(KayitTarihi) = '$bugun_tarih'");
    
    mysqli_close($conn);
}
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800&display=swap');
    /* style.css'den gelen body stilleri geçerli olacak, burası ek veya override */
    
    .admin-page-content { /* .page-content-wrapper için yeni bir isim, header.php'deki class ile karışmaması için */
        max-width: 1350px; /* İçeriğin maksimum genişliği biraz daha arttı */
        margin: 30px auto; /* Üst ve alt boşluk */
        padding: 20px;    /* Kenar boşlukları */
    }

    .hero-banner {
        background: linear-gradient(135deg, #6f86d6 0%, #48c6ef 100%); /* Daha yumuşak bir mavi gradyan */
        color: white;
        padding: 45px 35px;
        border-radius: 16px;
        margin-bottom: 35px;
        text-align: center;
        box-shadow: 0 12px 35px -12px rgba(72, 198, 239, 0.5);
        animation: fadeInAndSlideUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    }
    .hero-banner h2 {
        margin-top: 0;
        margin-bottom: 12px;
        font-size: 2.6em;
        font-weight: 700;
        color: white;
        border-bottom: none;
    }
    .hero-banner p {
        font-size: 1.2em;
        margin-bottom: 28px;
        opacity: 0.9;
        max-width: 650px;
        margin-left: auto;
        margin-right: auto;
    }
    .hero-banner .btn-hero-action {
        padding: 14px 35px;
        font-size: 1.15em;
        border-radius: 50px;
        background-color: #fff;
        color: #5c77ce; /* Buton rengi gradyanla uyumlu */
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        display: inline-block; /* Butonun düzgün görünmesi için */
    }
    .hero-banner .btn-hero-action:hover {
        background-color: #f4f6f8;
        transform: translateY(-3px) scale(1.03);
        box-shadow: 0 7px 20px rgba(0,0,0,0.18);
    }
    .hero-banner .btn-hero-action i { margin-right: 10px; }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 28px;
    }

    .stat-item-card {
        background-color: #fff;
        border-radius: 12px;
        padding: 28px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
        display: flex;
        align-items: center; /* İkon ve yazıyı dikeyde ortala */
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        animation: cardPopIn 0.5s ease-out forwards;
        /* opacity:0; // Animasyon başlangıcında gizli olması için, ama JS ile yönetmek daha iyi olabilir */
        position: relative;
        overflow: hidden;
    }
     .stat-item-card:hover {
        transform: translateY(-8px) scale(1.01);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
    }
    
    .stat-item-card .icon-area {
        font-size: 2.4em;
        padding: 18px;
        border-radius: 10px; /* Hafif yuvarlak köşe */
        margin-right: 20px;
        color: #fff; /* İkon rengi beyaz */
        width: 65px;
        height: 65px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .stat-item-card .info-area h3 {
        margin: 0 0 6px 0;
        font-size: 0.95em; /* Başlık biraz daha küçük */
        color: #6c757d; /* Soluk gri */
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-item-card .info-area .number-display {
        font-size: 2.3em;
        font-weight: 700;
        line-height: 1;
    }
     .stat-item-card .info-area small {
        font-size: 0.8em;
        color: #7f8c8d;
        display: block; /* Alt satıra geçmesi için */
        margin-top: 3px;
    }

    /* Kart renkleri ve ikon arka planları */
    .stat-item-card.musteri .icon-area { background-color: #3498db; }
    .stat-item-card.musteri .info-area .number-display { color: #3498db; }

    .stat-item-card.urun .icon-area { background-color: #9b59b6; }
    .stat-item-card.urun .info-area .number-display { color: #9b59b6; }
    
    .stat-item-card.aktif-servis .icon-area { background-color: #f39c12; }
    .stat-item-card.aktif-servis .info-area .number-display { color: #f39c12; }

    .stat-item-card.tamamlanan-servis .icon-area { background-color: #2ecc71; }
    .stat-item-card.tamamlanan-servis .info-area .number-display { color: #2ecc71; }

    .stat-item-card.bekleyen-fatura .icon-area { background-color: #e74c3c; }
    .stat-item-card.bekleyen-fatura .info-area .number-display { color: #e74c3c; }

    .stat-item-card.personel .icon-area { background-color: #1abc9c; }
    .stat-item-card.personel .info-area .number-display { color: #1abc9c; }

    .stat-item-card.kritik-stok .icon-area { background-color: #d35400; }
    .stat-item-card.kritik-stok .info-area .number-display { color: #d35400; }

    .stat-item-card.bugun-servis .icon-area { background-color: #34495e; }
    .stat-item-card.bugun-servis .info-area .number-display { color: #34495e; }

    /* Animasyonlar */
    @keyframes fadeInHero {
        from { opacity: 0.5; transform: translateY(-15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes cardPopIn {
        0% { opacity: 0; transform: translateY(30px) scale(0.9); }
        100% { opacity: 1; transform: translateY(0) scale(1); }
    }
    /* Kartların tek tek belirmesi için PHP döngüsüyle animasyon gecikmesi */
    <?php for ($k_idx = 0; $k_idx < 8; $k_idx++): ?>
    .stats-grid > .stat-item-card:nth-child(<?php echo $k_idx + 1; ?>) {
        animation-delay: <?php echo $k_idx * 0.1 + 0.4; ?>s; /* Hero'dan sonra ve kademeli */
    }
    <?php endfor; ?>
</style>
<!-- Font Awesome header.php'de olmalı -->

<div class="admin-page-content"> <?php // .page-content-wrapper yerine daha spesifik ?>
    <div class="hero-banner">
        <h2>Servis Kontrol Merkezi</h2>
        <p>Hoşgeldiniz, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>! İşletmenizin genel durumunu anlık olarak takip edin ve tüm operasyonel süreçlerinizi verimli bir şekilde yönetin.</p>
        <a href="<?php echo $project_root_url; ?>/servis/ekle.php" class="btn-hero-action"><i class="fas fa-concierge-bell"></i> Yeni Servis Talebi Oluştur</a>
    </div>

    <div class="stats-grid">
        <div class="stat-item-card musteri">
            <div class="icon-area"><i class="fas fa-users"></i></div>
            <div class="info-area">
                <h3>Toplam Müşteri</h3>
                <p class="number-display"><?php echo $stats['toplam_musteri']; ?></p>
            </div>
        </div>
        <div class="stat-item-card urun">
            <div class="icon-area"><i class="fas fa-boxes-stacked"></i></div>
            <div class="info-area">
                <h3>Kayıtlı Ürün</h3>
                <p class="number-display"><?php echo $stats['toplam_urun']; ?></p>
            </div>
        </div>
        <div class="stat-item-card aktif-servis">
            <div class="icon-area"><i class="fas fa-tools"></i></div>
            <div class="info-area">
                <h3>Aktif Servisler</h3>
                <p class="number-display"><?php echo $stats['aktif_servis_sayisi']; ?></p>
                <small>(Beklemede/İşlemde)</small>
            </div>
        </div>
        <div class="stat-item-card tamamlanan-servis">
            <div class="icon-area"><i class="fas fa-calendar-check"></i></div>
            <div class="info-area">
                <h3>Bu Ay Tamamlanan</h3>
                <p class="number-display"><?php echo $stats['tamamlanan_servis_bu_ay']; ?></p>
            </div>
        </div>
        <div class="stat-item-card bekleyen-fatura">
            <div class="icon-area"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="info-area">
                <h3>Ödenmemiş Fatura</h3>
                <p class="number-display"><?php echo $stats['odenmemis_fatura_sayisi']; ?></p>
            </div>
        </div>
        <div class="stat-item-card personel">
            <div class="icon-area"><i class="fas fa-user-cog"></i></div>
            <div class="info-area">
                <h3>Teknik Personel</h3>
                <p class="number-display"><?php echo $stats['toplam_teknik_personel']; ?></p>
            </div>
        </div>
        <div class="stat-item-card kritik-stok">
            <div class="icon-area"><i class="fas fa-archive"></i></div> <!-- İkon değişti -->
            <div class="info-area">
                <h3>Kritik Stoklu Parça</h3>
                <p class="number-display"><?php echo $stats['kritik_stok_yedekparca']; ?></p>
                <small>(Stok < 5)</small>
            </div>
        </div>
        <div class="stat-item-card bugun-servis">
            <div class="icon-area"><i class="fas fa-business-time"></i></div> <!-- İkon değişti -->
            <div class="info-area">
                <h3>Bugün Açılan Servis</h3>
                <p class="number-display"><?php echo $stats['bugun_acilan_servis']; ?></p>
            </div>
        </div>
    </div>
    <?php // Hızlı işlem linkleri Hero Banner'a taşındı, istersen buraya başka içerikler eklenebilir. ?>
</div>

<?php
require_once 'includes/footer.php';
?>