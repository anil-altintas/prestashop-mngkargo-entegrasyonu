# MNG Kargo PrestaShop Modülü

Bu modül, PrestaShop 1.7 e-ticaret platformu için MNG Kargo entegrasyonu sağlar. Modül sayesinde siparişleriniz için otomatik olarak MNG Kargo gönderisi oluşturabilir, kargo takip numaralarını yönetebilir ve kargo etiketlerini yazdırabilirsiniz.

## 🚀 Özellikler

- MNG Kargo API entegrasyonu (Test ve Canlı ortamları desteklenir)
- Otomatik gönderi oluşturma ve yönetimi
- Kargo takip numarası sorgulama ve takip
- Kargo etiketi oluşturma ve yazdırma
- Sipariş durumu otomatik güncelleme
- Detaylı kargo log kayıtları
- Çoklu dil desteği
- Responsive tasarım

## 📋 Gereksinimler

- PrestaShop 1.7 veya üzeri
- PHP 7.2 veya üzeri
- cURL PHP eklentisi
- MNG Kargo API erişim bilgileri (Müşteri numarası, API anahtarı ve şifresi)

## 💾 Kurulum

1. Bu repository'yi klonlayın veya ZIP olarak indirin
2. İndirdiğiniz dosyaları PrestaShop'un `modules` dizinine yükleyin
3. PrestaShop yönetim panelinden "Modüller > Module Manager" bölümüne gidin
4. "MNG Kargo" modülünü bulun ve "Kur" butonuna tıklayın
5. Kurulum tamamlandıktan sonra "Yapılandır" butonuna tıklayarak API bilgilerinizi girin

## ⚙️ Yapılandırma

1. MNG Kargo API bilgilerinizi girin:
   - API Anahtarı
   - API Şifresi
   - Müşteri Numarası
2. Test modu veya canlı mod seçimini yapın
3. Varsayılan gönderi ayarlarını yapılandırın
4. Otomatik durum güncellemelerini aktifleştirin/devre dışı bırakın

## 📦 Kullanım

### Yeni Gönderi Oluşturma
1. Siparişler sayfasından ilgili siparişi seçin
2. "MNG Kargo Gönderi Oluştur" butonuna tıklayın
3. Gönderi bilgilerini kontrol edin ve onaylayın
4. Kargo etiketi otomatik olarak oluşturulacaktır

### Gönderi Takibi
1. Modül yönetim panelinden "Gönderiler" sekmesine gidin
2. İlgili gönderiyi bulun ve detaylarını görüntüleyin
3. Kargo durumunu sorgulayın ve etiketi yazdırın

## 🔧 Geliştirme

### Klasör Yapısı
```
mngkargo/
├── classes/
│   ├── MNGKargoAPI.php
│   └── MNGKargoShipment.php
├── controllers/
│   └── admin/
│       └── AdminMNGKargoController.php
├── views/
│   ├── templates/
│   │   ├── admin/
│   │   │   └── shipment_view.tpl
│   │   └── hook/
│   │       └── order_detail.tpl
├── translations/
├── mngkargo.php
├── config.xml
└── composer.json
```

### API Entegrasyonu
MNG Kargo API'si ile entegrasyon `MNGKargoAPI` sınıfı üzerinden yapılmaktadır. API endpoint'leri:

- Test: `https://test-api.mngkargo.com.tr/tswIntegration/services/tswIntegrationService`
- Production: `https://api.mngkargo.com.tr/tswIntegration/services/tswIntegrationService`

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun
3. Değişikliklerinizi commit edin
4. Branch'inizi push edin
5. Pull Request oluşturun

## 📝 Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına bakınız.

## 📞 İletişim

BarkodPOS - [info@barkodsatis.com](mailto:info@barkodsatis.com)

Proje Linki: [https://github.com/barkodpos/prestashop-mng-kargo](https://github.com/barkodpos/prestashop-mng-kargo) # prestashop-mngkargo-entegrasyonu
