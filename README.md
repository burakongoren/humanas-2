# Login Tahmin Sistemi

## Proje Hakkında

Bu uygulama, kullanıcıların geçmiş login zamanlarını analiz ederek bir sonraki login zamanlarını tahmin eden bir web uygulamasıdır. Üç farklı tahmin algoritması kullanarak, kullanıcı davranışlarını modelleyip gelecekteki login zamanları için öngörüler sunar. Kullanıcı dostu arayüzü ile hem basit hem de etkili bir deneyim sağlar.

## Özellikler

- Kullanıcı listesi görüntüleme ve arama yapabilme
- Kullanıcı detaylarını görüntüleme
- Kullanıcıların geçmiş login zamanlarını görüntüleme
- Üç farklı algoritma ile gelecekteki login zamanlarını tahmin etme:
  - Basit Ortalama Aralık Yöntemi
  - Gün-Saat Pattern Analizi
  - Gaussian Mixture Models (GMM)
- Her algoritma için doğruluk skorları ve ek bilgiler
- Humanas kurumsal kimliğine uygun modern arayüz
- Mobil uyumlu responsive tasarım

## Gerekli Yazılımlar

- XAMPP (v7.4 veya üzeri) - Apache ve PHP için
- Node.js (v14 veya üzeri) - Frontend geliştirme ortamı için
- npm (v6 veya üzeri) - Node.js ile birlikte gelir
- Git - Proje repolarını klonlamak için
- Modern bir web tarayıcı (Chrome, Firefox, Edge, vs.)

## Kurulum Adımları

### 1. XAMPP Kurulumu ve Başlatma
1. XAMPP'i [resmi sitesinden](https://www.apachefriends.org/index.html) indirin ve kurun.
2. XAMPP Kontrol Panelini açın.
3. Apache servisini başlatın.

### 2. Projeyi Klonlama
1. Komut satırını (PowerShell veya CMD) yönetici olarak açın.
2. XAMPP'in htdocs klasörüne giderek projeyi klonlayın:
```bash
cd C:\xampp\htdocs
git clone https://github.com/burakongoren/humanas.git
```

### 3. Backend Kurulumu
Backend dosyaları otomatik olarak hazırdır ve XAMPP ile çalışacaktır. Apache servisi çalışır durumdaysa backend API'si kullanıma hazırdır.

### 4. Frontend Kurulumu
1. Komut satırında frontend klasörüne gidin:
```bash
cd C:\xampp\htdocs\humanas\frontend
```

2. Gerekli npm paketlerini yükleyin:
```bash
npm install
```

3. Geliştirme sunucusunu başlatın:
```bash
npm run dev
```

4. (İsteğe bağlı) Production build oluşturmak için:
```bash
npm run build
```

### 5. Uygulamaya Erişim
- Frontend (geliştirme modu): `http://localhost:5173`
- Backend API: `http://localhost/humanas/backend/login_prediction_app.php`

## Teknolojiler

### Frontend
- React.js (v18)
- React Router DOM (v7) - Sayfa yönlendirmeleri için
- Modern JavaScript (ES6+)
- Vite - Hızlı geliştirme ortamı ve build aracı
- CSS3 ile özel stilizasyon

### Backend
- PHP (OOP prensiplerine uygun)
- RESTful API yapısı

### Sunucu
- XAMPP (Apache, PHP)

## Uygulama Mimarisi ve Çalışma Prensibi

### Ana Sayfa (HomePage)
- Kullanıcıların listelendiği ve arama yapılabilen ana sayfa
- Header, UserList ve Footer komponentlerinden oluşur
- Kullanıcı kartlarına tıklandığında detay sayfası aynı pencerede açılır

### Kullanıcı Detay Sayfası (UserPrediction)
- Seçilen kullanıcının login geçmişi ve tahmin sonuçlarını gösterir
- Header, PredictionResults ve Footer komponentlerinden oluşur
- Ana sayfaya dönüş butonu içerir

### Veri Akışı
1. Kullanıcı, uygulamaya eriştiğinde tüm kullanıcılar listelenir
2. Kullanıcı, bir kullanıcı seçtiğinde (tıkladığında) yeni bir sekmede detay sayfası açılır
3. Detay sayfasında, kullanıcının login geçmişi ve tahmin algoritmaları gösterilir
4. Her algoritma, kullanıcının geçmiş verilerine dayanarak tahmin yapar ve doğruluk puanı hesaplanır

### Dosya Yapısı
```
humanas/
│
├── backend/                  # PHP Backend
│   ├── api_data.json         # Kullanıcı ve login verileri
│   ├── login_prediction_app.php  # Ana backend API
│   ├── LoginPredictor.php    # Temel tahmin sınıfı (soyut)
│   ├── AverageIntervalPredictor.php  # Ortalama aralık algoritması
│   ├── PatternAnalysisPredictor.php  # Gün-saat pattern analizi
│   └── GaussianMixtureModelPredictor.php  # GMM algoritması
│
└── frontend/                # React Frontend
    ├── public/              # Statik dosyalar
    └── src/                 # Kaynak kodlar
        ├── App.jsx          # Ana uygulama ve yönlendirme
        ├── App.css          # Ana stil dosyası
        ├── pages/           # Sayfa komponentleri
        │   └── UserPrediction.jsx  # Kullanıcı detay sayfası
        └── components/      # Yeniden kullanılabilir komponentler
            ├── Header.jsx   # Sayfa başlığı
            ├── UserList.jsx # Kullanıcı listesi ve arama
            ├── PredictionResults.jsx # Tahmin sonuçları
            └── Footer.jsx   # Sayfa altlığı
```

## Tahmin Algoritmaları

### 1. Basit Ortalama Aralık Yöntemi (Average Interval)
Bu algoritma, kullanıcının login'leri arasındaki ortalama süreyi hesaplar ve son login zamanına bu süreyi ekleyerek bir sonraki tahmini login zamanını belirler.

### 2. Gün-Saat Pattern Analizi (Pattern Analysis)
Bu algoritma, kullanıcının hangi gün ve saatlerde login olma eğiliminde olduğunu analiz eder. Haftanın günlerini ve günün saatlerini inceleyerek en yüksek olasılığa sahip zamanı tahmin eder.

### 3. Gaussian Mixture Models (GMM)
Bu algoritma, istatistiksel yaklaşımla login zamanlarını analiz eder. Kullanıcının login davranışlarını çoklu normal dağılımlar (Gaussian) kullanarak modelleyip bir sonraki login için en olası zamanı belirler.

## Doğruluk Skorları

Her algoritma için, son login zamanını hariç bırakarak bir tahmin yapılır ve bu tahmin gerçek son login zamanıyla karşılaştırılarak 0-100 arası bir doğruluk skoru hesaplanır:

- **90-100**: Mükemmel (1 saatten az fark)
- **80-90**: Çok İyi (1-3 saat fark)
- **70-80**: İyi (3-6 saat fark)
- **60-70**: Ortalamanın Üzerinde (6-12 saat fark)
- **50-60**: Ortalama (12-24 saat fark)
- **30-50**: Ortalamanın Altında (24-48 saat fark)
- **10-30**: Zayıf (48 saatten fazla fark)

## Sorun Giderme

- **Backend Error 500**: Apache ve PHP hizmetlerinin çalıştığından emin olun. PHP hata günlüklerini kontrol edin.
- **Frontend Connection Error**: Backend URL'sinin doğru olduğundan emin olun. CORS hatası için backend HTTP başlıklarını kontrol edin.
- **Predictor Class Errors**: Sınıf include hatası için dosya yollarını kontrol edin.

## Geliştirici

- Burak ÖNGÖREN

## Deployment Bilgileri

### Hosting Konfigürasyonu
- **Frontend:** Netlify üzerinde barındırılmaktadır (https://your-netlify-site.netlify.app)
- **Backend:** InfinityFree üzerinde barındırılmaktadır (http://your-infinityfree-domain.infinityfreeapp.com)

### Backend Kurulumu (InfinityFree)
1. InfinityFree hesabınıza giriş yapın
2. `htdocs` klasörü içine `backend` klasörünü yükleyin
3. Backend dosyalarının doğru yetkilere sahip olduğundan emin olun

### Frontend Kurulumu (Netlify)
1. Frontend kodlarını derleyin: `cd frontend && npm run build`
2. Netlify'a yükleyin veya Netlify CLI kullanın: `netlify deploy --prod`
3. Netlify yapılandırma dosyaları (`_redirects`) istekleri InfinityFree'de barındırılan backend'e yönlendirecektir
