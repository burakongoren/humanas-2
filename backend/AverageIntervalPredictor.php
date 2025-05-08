<?php
require_once 'LoginPredictor.php';

/**
 * AverageIntervalPredictor
 * 
 * ÇALIŞMA PRENSİBİ:
 * ---------------------------
 * Bu algoritma, kullanıcının önceki tüm login zamanları arasındaki 
 * süreleri (interval) hesaplayarak, bu sürelerin ortalamasını alır.
 * Ardından son login zamanına bu ortalama süreyi ekleyerek bir sonraki
 * muhtemel login zamanını tahmin eder.
 * 
 * Örnek:
 * - Kullanıcı zamanları: 10:00, 14:00, 19:00 (aynı gün)
 * - Aralıklar: 4 saat, 5 saat
 * - Ortalama aralık: 4.5 saat
 * - Son login: 19:00
 * - Tahmin: 19:00 + 4.5 saat = 23:30
 * 
 * AVANTAJLAR:
 * ---------------------------
 * 1. Basit ve anlaşılır bir yöntem
 * 2. Düzenli login alışkanlıkları olan kullanıcılar için oldukça doğru tahminler
 * 3. Hesaplama yükü çok düşük
 * 
 * DEZAVANTAJLAR:
 * ---------------------------
 * 1. Kullanıcının farklı günlerdeki davranışlarını ayırt edemez
 * 2. Hafta içi/hafta sonu farklarını dikkate almaz
 * 3. Düzensiz login alışkanlıkları olan kullanıcılarda yanılma payı yüksek
 */
class AverageIntervalPredictor extends LoginPredictor {
    private $averageInterval;
    
    public function __construct(array $loginDates) {
        parent::__construct($loginDates);
        $this->calculateAverageInterval();
    }
    
    /**
     * ADIMLAR:
     * 1. Her ardışık login zamanı arasındaki farkı saniye cinsinden hesapla
     * 2. Bu farkları saat birimine çevir
     * 3. Tüm aralıkların ortalamasını al
     * 4. Eğer kullanıcının hiç login geçmişi yoksa, varsayılan olarak 24 saat kullan
     */
    private function calculateAverageInterval() {
        $intervals = [];
        
        for ($i = 1; $i < count($this->loginDates); $i++) {
            $interval = $this->loginDates[$i]->getTimestamp() - $this->loginDates[$i-1]->getTimestamp();
            $intervals[] = $interval / 3600; // Saniyeyi saate çevir
        }
        
        $this->averageInterval = count($intervals) > 0 
            ? array_sum($intervals) / count($intervals) 
            : 24; // Varsayılan olarak 24 saat
    }
    
    /**
     * Bir sonraki login zamanını tahmin et
     * 
     * ADIMLAR:
     * 1. Son login zamanını al
     * 2. Son login zamanına ortalama aralığı ekle
     * 3. Elde edilen zamanı bir sonraki tahmini login zamanı olarak döndür
     * 
     * @return DateTime Tahmini bir sonraki login zamanı
     */
    public function predictNextLogin(): DateTime {
        $lastLogin = $this->getLastLogin();
        $nextLoginTimestamp = $lastLogin->getTimestamp() + (int)($this->averageInterval * 3600);
        
        $nextLoginDate = new DateTime();
        $nextLoginDate->setTimestamp($nextLoginTimestamp);
        
        return $nextLoginDate;
    }
    
    public function getName(): string {
        return "Basit Ortalama Aralık Yöntemi";
    }
    
    public function getDescription(): string {
        return "Bu algoritma, kullanıcının login'leri arasındaki ortalama süreyi (saat cinsinden) hesaplar " .
               "ve son login zamanına bu süreyi ekleyerek bir sonraki tahmini login zamanını belirler. " .
               "Basit ve anlaşılır yapısıyla düzenli login alışkanlıkları olan kullanıcılar için doğru " .
               "tahminler üretebilir. Ancak hafta içi/hafta sonu farklarını dikkate almaz ve düzensiz " .
               "login davranışlarında yanılma payı yüksektir.";
    }

    public function getAdditionalInfo(): string {
        $lastLogin = $this->getLastLogin();
        
        return "<p>Ortalama login aralığı: " . round($this->averageInterval, 2) . " saat</p>" .
               "<p>Son login: " . $lastLogin->format('Y-m-d H:i:s') . "</p>";
    }
} 