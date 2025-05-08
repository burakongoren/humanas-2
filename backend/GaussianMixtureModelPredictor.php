<?php
require_once 'LoginPredictor.php';

/**
 * GaussianMixtureModelPredictor
 * 
 * ÇALIŞMA PRENSİBİ:
 * ---------------------------
 * Bu algoritma, kullanıcının login davranışını istatistiksel olarak modellemek için
 * Gaussian Karışım Modeli (Gaussian Mixture Model - GMM) yaklaşımını kullanır.
 * Temel olarak şu adımları izler:
 * 
 * 1. KÜMELEME (CLUSTERING):
 *    - Login zamanlarını anlamlı kümelere ayırır
 *    - Hafta içi (weekday) ve hafta sonu (weekend) olarak ayırma
 *    - Günün zaman dilimlerine göre ayırma: sabah, öğle, akşam
 *    - Toplam 6 küme oluşturur: Hafta içi sabah, Hafta içi öğle, Hafta içi akşam,
 *      Hafta sonu sabah, Hafta sonu öğle, Hafta sonu akşam
 * 
 * 2. İSTATİSTİKSEL ANALİZ:
 *    - Her küme için login zamanları arasındaki ortalama süre hesaplanır
 *    - Standart sapma değerleri hesaplanır (varyans değişkenliği)
 *    - Bu değerler bir Gaussian (normal) dağılım modellemesi için kullanılır
 * 
 * 3. GEÇİŞ OLASILIKLARI:
 *    - Bir kümeden diğerine geçiş olasılıkları tanımlanır
 *    - Örneğin, "hafta içi sabah" kümesinden sonra en yüksek olasılıkla 
 *      "hafta içi öğle" kümesine geçiş olur
 * 
 * 4. TAHMİN:
 *    - Son login'in hangi kümeye ait olduğu belirlenir
 *    - Geçiş olasılıklarına göre bir sonraki küme tahmin edilir
 *    - Gaussian dağılımdan bir zaman aralığı seçilir (Box-Muller dönüşümü kullanılarak)
 *    - Sonraki tahmini login zamanı, seçilen kümenin özelliklerine göre ayarlanır
 * 
 * AVANTAJLAR:
 * ---------------------------
 * 1. Karmaşık login davranış örüntülerini modelleyebilir
 * 2. Hafta içi/hafta sonu ve günün farklı zamanlarını dikkate alır
 * 3. Olasılıksal bir yaklaşım kullanarak daha gerçekçi tahminler üretir
 * 4. Standart sapmayı kullanarak değişkenliği hesaba katar
 * 
 * DEZAVANTAJLAR:
 * ---------------------------
 * 1. Karmaşık bir algoritma, anlaşılması ve uygulanması zor olabilir
 * 2. Yeterli veri yoksa kümeleme işlemi sağlıklı olmayabilir
 * 3. Hesaplama yükü diğer algoritmalara göre daha yüksektir
 * 
 * MATEMATİKSEL TEMELLER:
 * ---------------------------
 * 1. Gaussian Dağılım (Normal Dağılım):
 *    f(x) = (1 / (σ√(2π))) * e^(-(x-μ)²/(2σ²))
 *    Burada:
 *    - μ: Ortalama (beklenen değer)
 *    - σ: Standart sapma
 *    - e: Euler sayısı (≈ 2.71828)
 *    - π: Pi sayısı (≈ 3.14159)
 * 
 * 2. Box-Muller Dönüşümü: 
 *    Normal dağılımdan rastgele sayı üretmek için kullanılan bir yöntem
 *    z = sqrt(-2 * ln(u1)) * cos(2π * u2)
 *    Burada:
 *    - u1 ve u2: [0,1] aralığında düzgün dağılımlı rastgele sayılar
 *    - ln: Doğal logaritma
 *    - z: Standart normal dağılımdan (μ=0, σ=1) bir rastgele değişken
 */
class GaussianMixtureModelPredictor extends LoginPredictor {
    /**
     * Login zamanlarını kümelere ayırma
     */
    private $clusters = [];
    
    /**
     * Her küme için istatistikler (ortalama, standart sapma, vb.)
     */
    private $clusterStats = [];
    
    /**
     * Son login'in ait olduğu küme
     */
    private $lastLoginCluster;
    
    /**
     * Bir sonraki tahmini küme
     */
    private $nextCluster;
    
    /**
     * Kümeler arasındaki geçiş olasılıkları
     */
    private $transitions = [];
    
    /**
     * Küme etiketleri
     */
    private $clusterLabels = [];
    
    /**
     * Türkçe gün isimleri
     */
    private $dayNames = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
    
    /**
     * Constructor
     * 
     * @param array $loginDates DateTime nesnelerinden oluşan dizi
     */
    public function __construct(array $loginDates) {
        parent::__construct($loginDates);
        $this->initializeModel();
    }
    
    /**
     * Modeli başlat
     * 
     * ADIMLAR:
     * 1. Küme etiketlerini tanımla
     * 2. Kümeler arası geçiş olasılıklarını tanımla
     * 3. Login zamanlarını kümelere ayır
     * 4. Her küme için istatistikleri hesapla
     * 5. Son login'in küme bilgisini belirle
     * 6. Sonraki muhtemel kümeyi belirle
     */
    private function initializeModel() {
        // Küme etiketlerini tanımla
        $this->clusterLabels = [
            'weekday_morning' => 'Hafta içi sabah (05-11)',
            'weekday_afternoon' => 'Hafta içi öğle (12-17)',
            'weekday_evening' => 'Hafta içi akşam (18-04)',
            'weekend_morning' => 'Hafta sonu sabah (05-11)',
            'weekend_afternoon' => 'Hafta sonu öğle (12-17)',
            'weekend_evening' => 'Hafta sonu akşam (18-04)',
        ];
        
        // Kümeler arasındaki geçiş olasılıklarını tanımla
        $this->transitions = [
            'weekday_morning' => ['weekday_afternoon' => 0.6, 'weekday_evening' => 0.3, 'weekday_morning' => 0.1],
            'weekday_afternoon' => ['weekday_evening' => 0.7, 'weekday_morning' => 0.2, 'weekday_afternoon' => 0.1],
            'weekday_evening' => ['weekday_morning' => 0.6, 'weekday_afternoon' => 0.3, 'weekday_evening' => 0.1],
            'weekend_morning' => ['weekend_afternoon' => 0.6, 'weekend_evening' => 0.3, 'weekend_morning' => 0.1],
            'weekend_afternoon' => ['weekend_evening' => 0.7, 'weekend_morning' => 0.2, 'weekend_afternoon' => 0.1],
            'weekend_evening' => ['weekend_morning' => 0.6, 'weekend_afternoon' => 0.3, 'weekend_evening' => 0.1],
        ];
        
        // Login zamanlarını kümelere ayır
        $this->clusterLoginDates();
        
        // Her küme için istatistikleri hesapla
        $this->calculateClusterStats();
        
        // Son login'in hangi kümeye ait olduğunu belirle
        $this->determineLastLoginCluster();
        
        // Sonraki muhtemel kümeyi belirle
        $this->determineNextCluster();
    }
    
    /**
     * Login zamanlarını kümelere ayır
     * 
     * ADIMLAR:
     * 1. Her küme için boş dizi oluştur
     * 2. Her login zamanı için:
     *    a. Saat bilgisini belirle (0-23)
     *    b. Gün bilgisini belirle (hafta içi / hafta sonu)
     *    c. Günün hangi dilimine denk geldiğini belirle (sabah/öğle/akşam)
     *    d. İlgili kümeye ekle
     * 
     * ZAMAN DİLİMLERİ:
     * - Sabah: 05:00-11:59
     * - Öğle: 12:00-17:59
     * - Akşam: 18:00-04:59
     */
    private function clusterLoginDates() {
        // Kümeleri başlat
        $this->clusters = [
            'weekday_morning' => [],   // Hafta içi sabah (5-11)
            'weekday_afternoon' => [], // Hafta içi öğle (12-17)
            'weekday_evening' => [],   // Hafta içi akşam (18-23, 0-4)
            'weekend_morning' => [],   // Hafta sonu sabah (5-11)
            'weekend_afternoon' => [], // Hafta sonu öğle (12-17)
            'weekend_evening' => [],   // Hafta sonu akşam (18-23, 0-4)
        ];
        
        // Her login tarihini uygun kümeye ekle
        foreach ($this->loginDates as $date) {
            $hour = (int)$date->format('G');
            $dayOfWeek = (int)$date->format('w');
            $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
            
            $timeOfDay = '';
            if ($hour >= 5 && $hour < 12) {
                $timeOfDay = 'morning';
            } elseif ($hour >= 12 && $hour < 18) {
                $timeOfDay = 'afternoon';
            } else {
                $timeOfDay = 'evening';
            }
            
            $clusterKey = ($isWeekend ? 'weekend_' : 'weekday_') . $timeOfDay;
            $this->clusters[$clusterKey][] = $date;
        }
    }
    
    /**
     * Her küme için istatistikleri hesapla
     * 
     * ADIMLAR:
     * 1. Her küme için:
     *    a. Kümedeki ardışık loginler arasındaki aralıkları hesapla
     *    b. Aralıkların ortalamasını hesapla
     *    c. Aralıkların standart sapmasını hesapla
     *    d. Küme istatistiklerini kaydet
     * 
     * Standart sapma, verilerin ortalamadan ne kadar saptığını gösterir.
     * Düşük standart sapma: Veriler ortalamaya yakın (düzenli davranış)
     * Yüksek standart sapma: Veriler ortalamadan uzak (düzensiz davranış)
     */
    private function calculateClusterStats() {
        foreach ($this->clusters as $clusterName => $clusterDates) {
            if (count($clusterDates) > 1) {
                // Ortalama login aralığını hesapla
                $intervals = [];
                for ($j = 1; $j < count($clusterDates); $j++) {
                    $interval = $clusterDates[$j]->getTimestamp() - $clusterDates[$j-1]->getTimestamp();
                    $intervals[] = $interval / 3600; // Saniyeyi saate çevir
                }
                
                $mean = array_sum($intervals) / count($intervals);
                
                // Standart sapmayı hesapla
                $variance = 0;
                foreach ($intervals as $interval) {
                    $variance += pow($interval - $mean, 2);
                }
                $variance /= count($intervals);
                $stdDev = sqrt($variance);
                
                $this->clusterStats[$clusterName] = [
                    'mean' => $mean,
                    'stdDev' => $stdDev,
                    'count' => count($clusterDates)
                ];
            }
        }
    }
    
    /**
     * Son login'in hangi kümeye ait olduğunu belirle
     * 
     * ADIMLAR:
     * 1. Son login zamanının saat bilgisini al
     * 2. Son login zamanının gün bilgisini al (hafta içi / hafta sonu)
     * 3. Zaman dilimine göre sabah/öğle/akşam olarak sınıflandır
     * 4. Son login'in ait olduğu kümeyi belirle
     */
    private function determineLastLoginCluster() {
        $lastLogin = $this->getLastLogin();
        $hour = (int)$lastLogin->format('G');
        $dayOfWeek = (int)$lastLogin->format('w');
        $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
        
        $timeOfDay = '';
        if ($hour >= 5 && $hour < 12) {
            $timeOfDay = 'morning';
        } elseif ($hour >= 12 && $hour < 18) {
            $timeOfDay = 'afternoon';
        } else {
            $timeOfDay = 'evening';
        }
        
        $this->lastLoginCluster = ($isWeekend ? 'weekend_' : 'weekday_') . $timeOfDay;
    }
    
    /**
     * Sonraki muhtemel kümeyi belirle
     * 
     * ADIMLAR:
     * 1. Son login'in ait olduğu kümeden başla
     * 2. Bu kümeden diğer kümelere geçiş olasılıklarını al
     * 3. Olasılık dağılımına göre rastgele bir küme seç
     * 
     * ÖRNEK:
     * - Son küme: hafta içi sabah
     * - Geçiş olasılıkları: 
     *   * hafta içi öğle: %60
     *   * hafta içi akşam: %30
     *   * hafta içi sabah: %10
     * - Rastgele seçim sonucu: hafta içi öğle
     */
    private function determineNextCluster() {
        $this->nextCluster = $this->lastLoginCluster; // Varsayılan olarak aynı küme
        
        if (isset($this->transitions[$this->lastLoginCluster])) {
            // Geçiş olasılıklarına göre sonraki kümeyi belirle
            $rand = mt_rand() / mt_getrandmax();
            $cumulativeProbability = 0;
            
            foreach ($this->transitions[$this->lastLoginCluster] as $cluster => $probability) {
                $cumulativeProbability += $probability;
                if ($rand <= $cumulativeProbability) {
                    $this->nextCluster = $cluster;
                    break;
                }
            }
        }
    }
    
    /**
     * Bir sonraki login zamanını tahmin et
     * 
     * ADIMLAR:
     * 1. Son login zamanını al
     * 2. Tahmin edilen bir sonraki küme için istatistikleri al (ortalama, standart sapma)
     * 3. Box-Muller dönüşümü ile normal dağılımdan bir zaman aralığı seç
     * 4. Son login zamanına bu aralığı ekle
     * 5. Elde edilen zamanı, seçilen kümenin özelliklerine göre düzelt:
     *    a. Doğru saat dilimine getir
     *    b. Doğru gün türüne getir (hafta içi/hafta sonu)
     * 
     * Box-Muller Yöntemi, rastgele sayılardan normal dağılıma uygun
     * sayılar üretmek için kullanılan matematiksel bir dönüşümdür.
     * 
     * @return DateTime Tahmini bir sonraki login zamanı
     */
    public function predictNextLogin(): DateTime {
        $lastLogin = $this->getLastLogin();
        $nextLoginDate = clone $lastLogin;
        
        if (isset($this->clusterStats[$this->nextCluster])) {
            // Gaussian dağılımdan bir interval seç (Box-Muller dönüşümü ile)
            $mean = $this->clusterStats[$this->nextCluster]['mean'];
            $stdDev = $this->clusterStats[$this->nextCluster]['stdDev'];
            
            $u1 = mt_rand() / mt_getrandmax();
            $u2 = mt_rand() / mt_getrandmax();
            
            $z = sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);
            $interval = $mean + $z * $stdDev;
            
            // Negatif intervalleri engelle
            $interval = max(1, $interval); // En az 1 saat
            
            $nextLoginTimestamp = $lastLogin->getTimestamp() + (int)($interval * 3600);
            $nextLoginDate->setTimestamp($nextLoginTimestamp);
            
            // Kümeye göre saat dilimini ayarla
            $hour = (int)$nextLoginDate->format('G');
            
            if (strpos($this->nextCluster, 'morning') !== false && ($hour < 5 || $hour >= 12)) {
                // Sabah kümesi için saati 5-11 arasına ayarla
                $nextLoginDate->setTime(mt_rand(5, 11), mt_rand(0, 59));
            } elseif (strpos($this->nextCluster, 'afternoon') !== false && ($hour < 12 || $hour >= 18)) {
                // Öğle kümesi için saati 12-17 arasına ayarla
                $nextLoginDate->setTime(mt_rand(12, 17), mt_rand(0, 59));
            } elseif (strpos($this->nextCluster, 'evening') !== false && ($hour >= 5 && $hour < 18)) {
                // Akşam kümesi için saati 18-23 veya 0-4 arasına ayarla
                $nextLoginDate->setTime(mt_rand(0, 1) ? mt_rand(18, 23) : mt_rand(0, 4), mt_rand(0, 59));
            }
            
            // Hafta içi/sonu ayarlaması
            $dayOfWeek = (int)$nextLoginDate->format('w');
            $isWeekendNow = ($dayOfWeek === 0 || $dayOfWeek === 6);
            
            if (strpos($this->nextCluster, 'weekend') !== false && !$isWeekendNow) {
                // Hafta sonuna ayarla
                $daysToWeekend = ($dayOfWeek == 0) ? 6 : 6 - $dayOfWeek;
                $nextLoginDate->modify("+{$daysToWeekend} days");
            } elseif (strpos($this->nextCluster, 'weekday') !== false && $isWeekendNow) {
                // Hafta içine ayarla
                $daysToWeekday = ($dayOfWeek == 0) ? 1 : 2;
                $nextLoginDate->modify("+{$daysToWeekday} days");
            }
        }
        
        return $nextLoginDate;
    }
    
    /**
     * Sınıfın adını döndür
     * 
     * @return string Algoritma adı
     */
    public function getName(): string {
        return "Gaussian Mixture Models (GMM)";
    }
    
    /**
     * Algoritmanın nasıl çalıştığını açıklayan açıklamayı döndür
     * 
     * @return string Algoritma açıklaması
     */
    public function getDescription(): string {
        return "Bu algoritma, login zamanlarının olasılık dağılımını modelleyerek tahmin yapar. " .
               "Login zamanlarını hafta içi/hafta sonu ve sabah/öğle/akşam olarak kümelere ayırır, " .
               "her küme için istatistiksel analiz yapar ve Gaussian dağılım kullanarak sonraki " .
               "login zamanını olasılıksal olarak tahmin eder. Karmaşık login davranış örüntülerini " .
               "modelleyebilir ve olasılıksal yaklaşımı sayesinde daha gerçekçi tahminler üretir. " .
               "Ancak algoritmanın anlaşılması ve uygulanması zordur, ayrıca yeterli veri olmadığında " .
               "doğru tahminler yapamayabilir.";
    }
    
    /**
     * Tahmin ile ilgili ek bilgileri döndür (HTML biçiminde)
     * 
     * @return string Ek bilgiler (HTML)
     */
    public function getAdditionalInfo(): string {
        $html = "<p>Küme istatistikleri:</p>";
        $html .= "<table>";
        $html .= "<tr><th>Küme</th><th>Login Sayısı</th><th>Ortalama Aralık (saat)</th><th>Standart Sapma</th></tr>";
        
        foreach ($this->clusterStats as $clusterName => $stats) {
            $html .= "<tr>";
            $html .= "<td>" . $this->clusterLabels[$clusterName] . "</td>";
            $html .= "<td>" . $stats['count'] . "</td>";
            $html .= "<td>" . round($stats['mean'], 2) . "</td>";
            $html .= "<td>" . round($stats['stdDev'], 2) . "</td>";
            $html .= "</tr>";
        }
        $html .= "</table>";
        
        $html .= "<p>Son login kümesi: " . $this->clusterLabels[$this->lastLoginCluster] . "</p>";
        $html .= "<p>Sonraki tahmini küme: " . $this->clusterLabels[$this->nextCluster] . "</p>";
        
        return $html;
    }
} 