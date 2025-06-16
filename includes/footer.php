    </main> <?php // .container div'ini kapatır ?>
    <footer>
        <p>© <?php echo date("Y"); ?> Teknik Servis Yönetim Sistemi. Tüm hakları saklıdır.</p>
    </footer>

    <?php
    // Gerekirse JS dosyalarını buraya ekleyebilirsin.
    // Örnek: echo '<script src="/teknik_servis/js/script.js"></script>';
    // YUKARIDAKİ SATIR YORUM İÇİNDE OLMALI VEYA SİLİNMELİ EĞER KULLANILMIYORSA
    ?>

    <?php
    // Yeni eklenen müşteriyi vurgulamak için JavaScript
    if (isset($_GET['highlight_id']) && !empty(trim($_GET['highlight_id']))):
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const highlightedRow = document.querySelector('tr.table-success');
            if (highlightedRow) {
                // console.log('Vurgulanan satır bulundu:', highlightedRow); // Test için
                setTimeout(function() {
                    highlightedRow.classList.remove('table-success');
                    // console.log('Vurgu kaldırıldı.'); // Test için

                    if (window.history.replaceState) {
                        const cleanURL = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.search.replace(/[?&]highlight_id=\d+/g, '').replace(/^&/, '?');
                        window.history.replaceState({path: cleanURL}, '', cleanURL);
                        // console.log('URL temizlendi.'); // Test için
                    }
                }, 3000); // 3 saniye
            } else {
                // console.log('Vurgulanacak satır bulunamadı (table-success class yok).'); // Test için
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>