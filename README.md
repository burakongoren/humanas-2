# Humanas Login Tahmin Sistemi

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
- Çevrimiçi/çevrimdışı çalışma desteği (backend bağlantısı olmadığında yerel verilere düşer)

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

### Deployment
- Frontend: Netlify üzerinde barındırılmaktadır (https://humanas-case.netlify.app)
- Backend: InfinityFree üzerinde barındırılmaktadır (https://humanas-backend.infinityfreeapp.com)

## Yerel Kurulum Adımları

### Gerekli Yazılımlar
- XAMPP (v7.4 veya üzeri) - Apache ve PHP için
- Node.js (v14 veya üzeri) - Frontend geliştirme ortamı için
- npm (v6 veya üzeri) - Node.js ile birlikte gelir
- Git - Proje repolarını klonlamak için
- Modern bir web tarayıcı (Chrome, Firefox, Edge, vs.)

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
1. Backend dosyalarını XAMPP'in htdocs klasörüne kopyalayın.
2. Apache servisi çalışır durumdaysa backend API'si kullanıma hazırdır.

### 4. Frontend Kurulumu
1. Komut satırında frontend klasörüne gidin:
```bash
cd frontend
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

## Deployment Bilgileri

### Backend Deployment (InfinityFree)
1. InfinityFree hesabınıza giriş yapın
2. FTP bilgilerinizi kullanarak FileZilla gibi bir FTP istemcisi ile bağlanın:
   - FTP Sunucusu: ftpupload.net
   - FTP Kullanıcı Adı: if0_38928145
   - FTP Port: 21
3. Backend klasörünü htdocs klasörü içine yükleyin
4. Backend API'sine şu adresten erişebilirsiniz: https://humanas-backend.infinityfreeapp.com/backend/login_prediction_app.php

### Frontend Deployment (Netlify)
1. Frontend kodlarını derleyin:
```bash
cd frontend && npm run build
```

2. Netlify deployment seçenekleri:
   - **Manuel Deploy**: Netlify kontrol panelinde "Add new site" > "Deploy manually" seçeneğine tıklayın ve `frontend/dist` klasörünü sürükleyip bırakın.
   - **Git Entegrasyonu**: GitHub reponuzu Netlify'a bağlayın ve otomatik deployment yapılandırın.

3. Site ayarları:
   - **Base directory**: frontend
   - **Build command**: npm install && npm run build
   - **Publish directory**: dist

## Veri Akışı ve Çalışma Prensibi

1. Uygulama yüklendiğinde önce backend API'sine bağlanmayı dener
2. Backend API'sine erişilemezse veya bir sorun olursa, yerel JSON verilerini kullanır
3. Her sayfa yenilendiğinde backend'den güncel verileri çekmeye çalışır
4. Kullanıcı detaylarına tıkladığınızda detay sayfasında tahmin algoritmaları çalışır
5. Algoritmaların sonuçlarını karşılaştırmalı olarak görüntüleyebilirsiniz

### Backend Veri Güncellemesi
- Backend verileri API'den çekilir ve `api_data.json` dosyasında saklanır
- Frontend her sayfa yenilemesinde bu güncel verilere erişmeye çalışır
- Aynı zamanda frontend, yerel bir kopya da tutar (çevrimdışı çalışma için)

> **ÖNEMLİ NOT:** Şu anki uygulamada, sayfa her yenilendiğinde backend API'sinden veri çekilir, ancak bu veri **yerel JSON dosyasını otomatik olarak güncellemez**. Frontend her zaman güncel veriyi göstermeye çalışır (bağlantı varsa), fakat Netlify'da barındırılan `/data/api_data.json` dosyası yalnızca manuel olarak güncellendiğinde değişir. Eğer frontend ve backend verileri farklıysa, backend verileri öncelikli olarak kullanılır.

## Sorun Giderme

- **CORS Hatası**: Backend'in CORS başlıklarını kontrol edin. Şu anda tüm kaynaklardan erişime izin verilmiştir (*).
- **Backend Bağlantı Sorunları**: Tarayıcı konsolunda network trafiğini inceleyin. Timeout hatası varsa, timeout süresini arttırın.
- **Veriler Gösterilmiyor**: Tarayıcı konsolunda hata mesajlarını kontrol edin. JSON formatında bir sorun olabilir.
- **Build Hatası**: Node.js ve npm sürümlerinizin güncel olduğundan emin olun.

## Lisans

Bu proje Humanas firması için özel olarak geliştirilmiştir. Tüm hakları saklıdır.

## İletişim

Proje geliştirici: Burak ÖNGÖREN
