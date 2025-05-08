<?php
require_once 'LoginPredictor.php';

/**
 * PatternAnalysisPredictor
 * 
 * ÇALIŞMA PRENSİBİ:
 * ---------------------------
 * Bu algoritma, kullanıcının login davranışlarındaki haftalık ve günlük 
 * örüntüleri (pattern) tespit etmeye çalışır. Bunu iki aşamada yapar:
 * 
 * 1. HAFTALIK ÖRÜNTÜ ANALİZİ:
 *    - Kullanıcının haftanın hangi günlerinde (Pazartesi-Pazar) daha sık 
 *      giriş yaptığını hesaplar
 *    - Her gün için bir frekans değeri çıkarır
 *    - En yüksek frekansa sahip günü tespit eder
 * 
 * 2. GÜNLÜK ÖRÜNTÜ ANALİZİ:
 *    - Kullanıcının günün hangi saatlerinde (0-23) daha sık giriş 
 *      yaptığını hesaplar
 *    - Her saat için bir frekans değeri çıkarır
 *    - En yüksek frekansa sahip saati tespit eder
 * 
 * Sonuç olarak, "kullanıcı en çok Çarşamba günleri saat 14:00'te giriş yapıyor" 
 * gibi bir örüntü belirler ve bu bilgiye göre sonraki tahmini login zamanını üretir.
 * 
 * AVANTAJLAR:
 * ---------------------------
 * 1. Kullanıcının düzenli davranış örüntülerini tespit edebilir
 * 2. Hafta içi ve hafta sonu farklarını dikkate alır
 * 3. Saat bazında hassas tahminler sağlayabilir
 * 
 * DEZAVANTAJLAR:
 * ---------------------------
 * 1. Yeterli login verisi olmadan doğru tahminler yapmak zordur
 * 2. Kullanıcı davranışları değişirse tahminler hatalı olabilir
 * 3. Düzensiz login davranışı olan kullanıcılarda yanılma payı yüksektir
 */
class PatternAnalysisPredictor extends LoginPredictor {
    /**
     * Gün bazında login sıklığı (0=Pazar, 1=Pazartesi, ... 6=Cumartesi)
     */
    private $dayFrequency = [];
    
    /**
     * Saat bazında login sıklığı (0-23 saat)
     */
    private $hourFrequency = [];
    
    /**
     * En sık login olunan gün
     */
    private $mostFrequentDay;
    
    /**
     * En sık login olunan saat
     */
    private $mostFrequentHour;
    
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
        $this->analyzePatterns();
    }
    
    /**
     * Gün ve saat bazında login sıklıklarını analiz et
     * 
     * ADIMLAR:
     * 1. Gün ve saat frekans dizilerini başlat (sıfırla)
     * 2. Her login zamanı için:
     *    a. Haftanın hangi günü olduğunu belirle (0-6)
     *    b. Günün hangi saati olduğunu belirle (0-23)
     *    c. İlgili gün ve saat için frekans değerlerini artır
     * 3. En yüksek frekansa sahip gün ve saati bul
     * 
     * NOT: PHP'de date('w') fonksiyonu 0=Pazar, 1=Pazartesi, ... 6=Cumartesi şeklinde döner
     */
    private function analyzePatterns() {
        // Dizileri başlat
        $this->dayFrequency = array_fill(0, 7, 0);
        $this->hourFrequency = array_fill(0, 24, 0);
        
        // Her login için gün ve saat frekanslarını hesapla
        foreach ($this->loginDates as $date) {
            $dayOfWeek = (int)$date->format('w'); // 0=Pazar, 1=Pazartesi, ... 6=Cumartesi
            $hour = (int)$date->format('G'); // 0-23 saat
            
            $this->dayFrequency[$dayOfWeek]++;
            $this->hourFrequency[$hour]++;
        }
        
        // En sık login olunan gün ve saati bul
        $this->mostFrequentDay = array_search(max($this->dayFrequency), $this->dayFrequency);
        $this->mostFrequentHour = array_search(max($this->hourFrequency), $this->hourFrequency);
    }
    
    /**
     * Bir sonraki login zamanını tahmin et
     * 
     * ADIMLAR:
     * 1. Son login zamanını al
     * 2. Bu zamanın haftanın hangi günü olduğunu belirle
     * 3. En sık login olunan güne kaç gün kaldığını hesapla
     * 4. Eğer bugün en sık login olunan günse ve saat geçmişse, bir sonraki haftaya geç
     * 5. Tarihi en sık login olunan güne ayarla
     * 6. Saati en sık login olunan saate ayarla
     * 
     * ÖRNEK:
     * - Bugün: Salı, saat: 15:00
     * - En sık login: Perşembe, saat: 10:00
     * - Tahmin: Bu haftanın Perşembe günü, saat 10:00
     * 
     * @return DateTime Tahmini bir sonraki login zamanı
     */
    public function predictNextLogin(): DateTime {
        $lastLogin = $this->getLastLogin();
        $nextLoginDate = clone $lastLogin;
        
        $currentDayOfWeek = (int)$nextLoginDate->format('w');
        
        // Bir sonraki hedef güne git
        $daysToAdd = ($this->mostFrequentDay - $currentDayOfWeek + 7) % 7;
        if ($daysToAdd === 0) {
            // Eğer bugün en sık login olunan günse ve saat geçmişse, bir hafta ekle
            $currentHour = (int)$nextLoginDate->format('G');
            if ($currentHour >= $this->mostFrequentHour) {
                $daysToAdd = 7;
            }
        }
        
        $nextLoginDate->modify("+{$daysToAdd} days");
        $nextLoginDate->setTime($this->mostFrequentHour, 0);
        
        return $nextLoginDate;
    }
    
    /**
     * Sınıfın adını döndür
     * 
     * @return string Algoritma adı
     */
    public function getName(): string {
        return "Gün-Saat Pattern Analizi";
    }
    
    /**
     * Algoritmanın nasıl çalıştığını açıklayan açıklamayı döndür
     * 
     * @return string Algoritma açıklaması
     */
    public function getDescription(): string {
        return "Bu algoritma, kullanıcının hangi gün ve saatlerde login olma eğiliminde olduğunu analiz eder. " .
               "Her gün ve saat dilimi için login sıklığını hesaplayarak en yoğun kullanım zamanlarını belirler " .
               "ve bu bilgiyi kullanarak bir sonraki login zamanını tahmin eder. Kullanıcının düzenli davranış " .
               "örüntülerini tespit edebilir ve hafta içi/hafta sonu farklarını dikkate alır. Ancak yeterli " .
               "login verisi olmadan doğru tahminler yapılamaz ve kullanıcı davranışları değişirse tahminler " .
               "hatalı olabilir.";
    }
    
    /**
     * Tahmin ile ilgili ek bilgileri döndür (HTML biçiminde)
     * 
     * @return string Ek bilgiler (HTML)
     */
    public function getAdditionalInfo(): string {
        $html = "<p>Gün bazında login sıklığı:</p>";
        $html .= "<table style='width:50%'>";
        $html .= "<tr><th>Gün</th><th>Login Sayısı</th></tr>";
        
        for ($d = 0; $d < 7; $d++) {
            $html .= "<tr>";
            $html .= "<td>" . $this->dayNames[$d] . "</td>";
            $html .= "<td>" . $this->dayFrequency[$d] . "</td>";
            $html .= "</tr>";
        }
        $html .= "</table>";
        
        $html .= "<p>Saat bazında login sıklığı:</p>";
        $html .= "<table style='width:50%'>";
        $html .= "<tr><th>Saat</th><th>Login Sayısı</th></tr>";
        
        for ($h = 0; $h < 24; $h++) {
            if ($this->hourFrequency[$h] > 0) {
                $html .= "<tr>";
                $html .= "<td>" . sprintf("%02d:00", $h) . "</td>";
                $html .= "<td>" . $this->hourFrequency[$h] . "</td>";
                $html .= "</tr>";
            }
        }
        $html .= "</table>";
        
        $html .= "<p>En sık login olunan gün: " . $this->dayNames[$this->mostFrequentDay] . "</p>";
        $html .= "<p>En sık login olunan saat: " . sprintf("%02d:00", $this->mostFrequentHour) . "</p>";
        
        return $html;
    }
} 