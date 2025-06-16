# Uludağ Beyaz Eşya Teknik Servis Yönetim Paneli

Bu proje, PHP, MySQL, HTML, CSS ve JavaScript kullanılarak geliştirilmiş, kapsamlı bir Beyaz Eşya Teknik Servis Yönetim Paneli uygulamasıdır. Uygulama, bir teknik servisin günlük operasyonlarını dijitalleştirmeyi ve verimliliği artırmayı hedefler.

## 📜 Proje Senaryosu ve Amacı

"Uludağ Teknik Servis" (veya kendi seçtiğiniz firma adı) adlı beyaz eşya teknik servis işletmesi için tasarlanan bu sistem, aşağıdaki temel işlevleri yerine getirir:
*   Müşteri bilgilerinin ve geçmişinin takibi.
*   Müşterilere ait ürünlerin (beyaz eşyaların) kaydedilmesi ve yönetimi.
*   Arıza ve bakım taleplerini içeren servis kayıtlarının oluşturulması, atanması ve süreç takibi.
*   Servislerde kullanılan yedek parçaların stok yönetimi, maliyet takibi ve servis kayıtlarına işlenmesi.
*   Teknik personel bilgilerinin yönetimi ve servislere atanması.
*   Tamamlanan servisler için fatura oluşturulması ve ödeme durumlarının takibi.
*   Sistemdeki önemli veri değişikliklerinin güvenilirlik ve denetim amacıyla loglanması.

Uygulama, müşteriden servis talebinin alınmasından, onarımın tamamlanıp faturalandırılmasına kadar olan tüm süreci kapsamaktadır.

## 🚀 Kullanılan Teknolojiler

*   **Sunucu Taraflı:** PHP (Native)
*   **Veritabanı:** MySQL (XAMPP paketi ile)
*   **İstemci Taraflı:** HTML5, CSS3, JavaScript (Temel DOM manipülasyonları ve AJAX için)
*   **Veritabanı Etkileşimi:** MySQLi (PHP eklentisi)
*   **Güvenlik ve Yapı:** Saklı Yordamlar (Stored Procedures) ve Tetikleyiciler (Triggers)
*   **Geliştirme Ortamı:** XAMPP, Visual Studio Code

## 📊 Veritabanı Yapısı ve Tasarımı

Veritabanı, ilişkisel bir model üzerine kurulmuş olup aşağıdaki ana varlıkları (tabloları) içermektedir:

*   **Musteri:** Müşteri iletişim ve adres bilgileri.
*   **Urun:** Müşterilere ait beyaz eşya bilgileri (tip, marka, model, seri no).
*   **TeknikPersonel:** Servis işlemlerini yapan teknik ekip üyeleri.
*   **YedekParca:** Tamir ve bakım için kullanılan parçaların stok ve fiyat bilgileri.
*   **ServisKaydi:** Müşteri talepleri, atanan personel, sorun tanımı ve işlem süreci.
*   **Servis_YedekParca:** Bir servis kaydında hangi yedek parçaların kullanıldığını gösteren ara tablo (N:N ilişki çözümü).
*   **Fatura:** Tamamlanan servisler için oluşturulan mali belgeler ve ödeme durumu.
*   **AuditLog:** Sistemdeki önemli veri değişikliklerini izlemek için kullanılan denetim tablosu.

Tablolar arası ilişkiler Yabancı Anahtarlar (Foreign Keys) ile, veri bütünlüğü ise Birincil Anahtarlar (Primary Keys), Benzersiz Kısıtlar (Unique Constraints) ve `NOT NULL` gibi kısıtlarla sağlanmıştır. `ON DELETE` ve `ON UPDATE` kuralları ilişkisel bütünlüğü korumak için dikkatlice tanımlanmıştır.


![image](https://github.com/user-attachments/assets/707bccef-dcd3-4209-893d-353d15978038)
![image](https://github.com/user-attachments/assets/29443441-338d-493b-9557-baad9de651ea)




## ✨ Önemli Özellikler ve İşlevler

*   **Admin Giriş Paneli:** Sisteme yetkili erişim için kullanıcı adı (`admin`) ve şifre (`123456`) ile giriş.
*   **CRUD Operasyonları:** Tüm ana modüller (Müşteri, Ürün, Teknik Personel, Yedek Parça, Servis Kaydı, Fatura) için Ekleme, Listeleme, Güncelleme ve Silme işlemleri.
*   **Dinamik Veri Yükleme:** Servis kaydı ekleme formunda, müşteri seçimine bağlı olarak o müşteriye ait ürünlerin AJAX ile dinamik olarak listelenmesi.
*   **Saklı Yordamlar (Stored Procedures):** Tüm veritabanı işlemleri (veri girişi, güncelleme, silme, sorgulama) güvenlik, performans ve modülerlik amacıyla MySQL saklı yordamları aracılığıyla gerçekleştirilmiştir. Her tablo için temel CRUD SP'leri ve bazı özel sorgulama SP'leri bulunmaktadır.
*   **Tetikleyiciler (Triggers):**
    *   `Musteri` tablosunda bir kayıt güncellendiğinde, yapılan değişikliği (`Ad`, `Soyad`, `Adres` vb.) `AuditLog` tablosuna otomatik olarak kaydeden bir `AFTER UPDATE` trigger'ı.
    *   `Servis_YedekParca` tablosuna yeni bir parça eklendiğinde, ilgili `ServisKaydi`'nın `ParcaUcreti` ve `ToplamUcret`'ini otomatik güncelleyen bir `AFTER INSERT` trigger'ı.
    *   `Servis_YedekParca` tablosundan bir parça silindiğinde veya adedi güncellendiğinde, ilgili `ServisKaydi` ücretlerini güncelleyen `AFTER DELETE` ve `AFTER UPDATE` trigger'ları.
*   **Kullanıcı Tanımlı Fonksiyon (User-Defined Function - UDF):**
    *   `fn_MusteriTamAdi(musteriID)`: Verilen müşteri ID'sine sahip müşterinin adını ve soyadını birleştirerek döndüren bir fonksiyon. (Örnek olarak listeleme sayfalarında kullanılabilir.)
*   **Görsel Arayüz:** Modern, kullanıcı dostu ve duyarlı (temel seviyede) bir arayüz. İstatistiksel verilerin ve hızlı işlem linklerinin bulunduğu bir ana sayfa.
*   **Session Yönetimi:** Kullanıcı girişi ve sayfa bazlı mesajlaşma (başarı/hata bildirimleri) için PHP sessionları kullanılmıştır.

## 🛠️ Kurulum ve Çalıştırma Adımları

1.  **Gereksinimler:**
    *   XAMPP (Apache, MySQL, PHP) veya benzeri bir yerel sunucu paketi.
    *   Bir web tarayıcısı.
    *   Bir metin editörü/IDE (örn: Visual Studio Code).

2.  **Kurulum:**
    *   Bu depoyu klonlayın veya ZIP olarak indirin.
        ```bash
        git clone https://github.com/SENIN_KULLANICI_ADIN/DEPO_ADIN.git
        ```
    *   İndirdiğiniz proje klasörünü (`teknik_servis`) XAMPP'ın `htdocs` dizinine kopyalayın.
    *   XAMPP Kontrol Paneli'nden Apache ve MySQL servislerini başlatın.
    *   Web tarayıcınızdan `http://localhost/phpmyadmin` adresine gidin.
    *   Yeni bir veritabanı oluşturun. Önerilen veritabanı adı: `teknik_servis` (UTF-8 karakter seti ile, örneğin `utf8mb4_unicode_ci`).
    *   Oluşturduğunuz `teknik_servis` veritabanını seçin ve "İçe Aktar" (Import) sekmesine gidin.
    *   Proje ana dizininde bulunan `database.sql` (veya `teknik_servis_dump.sql` olarak adlandırdığınız dosya) dosyasını seçip içe aktarın. Bu işlem tabloları, saklı yordamları, fonksiyonları ve tetikleyicileri oluşturacaktır.
    *   `config/db.php` dosyasını açın ve MySQL bağlantı bilgilerinizi (kullanıcı adı, şifre, veritabanı adı) kontrol edin/güncelleyin. Varsayılan XAMPP ayarları için genellikle `root` kullanıcısı ve boş şifre kullanılır.
        ```php
        define('DB_SERVER', 'localhost');
        define('DB_USERNAME', 'root'); // MySQL kullanıcı adınız
        define('DB_PASSWORD', '');     // MySQL şifreniz (boş olabilir)
        define('DB_NAME', 'teknik_servis'); // Oluşturduğunuz veritabanı adı
        ```

3.  **Çalıştırma:**
    *   Web tarayıcınızdan `http://localhost/teknik_servis/` adresine gidin.
    *   Login sayfasına yönlendirileceksiniz.
    *   **Admin Kullanıcı Adı:** `admin`
    *   **Admin Şifre:** `123456`
    *   Giriş yaptıktan sonra yönetim panelini kullanmaya başlayabilirsiniz.

## 🖼️ Ekran Görüntüleri

![image](https://github.com/user-attachments/assets/cfd85962-a5a1-415f-b525-e51d1002419b)
![image](https://github.com/user-attachments/assets/d65662e2-3ceb-4a26-9427-d0e27ad2c95f)



## 👨‍💻 Geliştirici

*   Yusuf MEYDAN
*   21010708006
*   Veritabanı Yönetim Sistemleri-II  / Final Proje Ödevi

---
