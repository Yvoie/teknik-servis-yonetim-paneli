<?php
require_once '../config/db.php'; // Veritabanı bağlantısı

header('Content-Type: application/json'); // Dönen verinin JSON olduğunu belirt

$musteri_id = 0;
if (isset($_GET['musteri_id']) && is_numeric($_GET['musteri_id'])) {
    $musteri_id = intval($_GET['musteri_id']);
}

$urunler = [];

if ($musteri_id > 0) {
    // Belirli bir müşterinin ürünlerini getiren saklı yordamı çağır
    $sql = "CALL sp_Urun_Getir_ByMusteriID(" . $musteri_id . ")";
    
    if ($result = mysqli_query($conn, $sql)) {
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                // Sadece gerekli alanları alalım (JavaScript tarafında kullanılacaklar)
                $urunler[] = [
                    'UrunID' => $row['UrunID'],
                    'Marka' => $row['Marka'],
                    'Model' => $row['Model'],
                    'SeriNumarasi' => $row['SeriNumarasi'],
                    'UrunTipi' => $row['UrunTipi'] // İsteğe bağlı olarak eklenebilir
                ];
            }
        }
        mysqli_free_result($result);
        // Saklı yordamdan sonra kalan tüm sonuç setlerini temizle
        while(mysqli_more_results($conn) && mysqli_next_result($conn)) {
            if($l_result = mysqli_store_result($conn)){
                mysqli_free_result($l_result);
            }
        }
    } else {
        // Sorgu hatası durumunda boş dizi veya hata mesajı döndürülebilir
        // Şimdilik boş dizi döndürüyoruz, JavaScript tarafı bunu handle edebilir.
        // echo json_encode(['error' => 'Veritabanı sorgu hatası: ' . mysqli_error($conn)]);
        // exit;
    }
}

mysqli_close($conn); // Veritabanı bağlantısını kapat
echo json_encode($urunler); // Ürünleri JSON formatında döndür
exit(); // Script'i sonlandır
?>