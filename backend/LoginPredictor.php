<?php
/**
 * GENEL MİMARİ:
 * --------------------------
 * Bu abstract sınıf, farklı tahmin algoritmaları için bir temel oluşturur.
 * LoginPredictor, bir "Strategy Pattern" örneğidir - farklı algoritma 
 * stratejilerini aynı arayüz altında toplayarak değiştirilebilir kılar.
 * 
 * Sınıf, kullanıcının geçmiş login zamanlarını (DateTime nesneleri olarak) alır
 * ve bunları tarih sırasına göre sıralar. Alt sınıflar bu verileri kullanarak
 * kendi tahmin yöntemlerini uygular.
 * 
 * ALGORİTMALAR:
 * --------------------------
 * Bu sistemde şu tahmin algoritmaları bulunmaktadır:
 * 
 * 1. AverageIntervalPredictor: Login'ler arasındaki ortalama süreyi kullanır.
 *    Basit ancak düzenli kullanıcılar için yeterli olabilir.
 * 
 * 2. PatternAnalysisPredictor: Kullanıcının gün ve saat bazındaki login 
 *    örüntülerini analiz eder. "Kullanıcı genellikle Pazartesileri saat 9'da
 *    login oluyor" gibi örüntüleri tespit edebilir.
 * 
 * 3. GaussianMixtureModelPredictor: En gelişmiş tahmin yöntemidir. Kullanıcının
 *    login davranışlarını istatistiksel olarak modeller ve olasılıksal
 *    tahminler üretir. Değişken davranış örüntülerini yakalayabilir.
 * 
 * KULLANIM:
 * --------------------------
 * $loginDates = [datetime1, datetime2, ...]; // Kullanıcının login zamanları
 * $predictor = new GaussianMixtureModelPredictor($loginDates);
 * $nextLogin = $predictor->predictNextLogin(); // Tahmini bir sonraki login zamanı
 */
abstract class LoginPredictor {
    protected $loginDates = [];
    
    public function __construct(array $loginDates) {
        // Login tarihlerini sıralı olduğundan emin ol
        $this->loginDates = $loginDates;
        usort($this->loginDates, function($a, $b) {
            return $a <=> $b;
        });
    }
    
    /**
     * Bu metot her tahmin algoritması tarafından ayrı ayrı uygulanmalıdır.
     * Her algoritma kendi yöntemini kullanarak bir sonraki olası login
     * zamanını hesaplar ve döndürür.
     */
    abstract public function predictNextLogin(): DateTime;
    
    /**
     * Algoritmanın kullanıcı dostu bir adını döndürür.
     * Bu ad kullanıcı arayüzünde görüntülenebilir.
     */
    abstract public function getName(): string;
    
    /**
     * Bu metot, algoritmanın çalışma prensibini açıklayan bir 
     * metin döndürür. Algoritmanın temel mantığını kullanıcıya
     * anlaşılır bir şekilde aktarır.
     */
    abstract public function getDescription(): string;
    
    /**
     * Bu metot, algoritmanın spesifik detaylarını ve tahmin
     * için kullandığı değerleri HTML formatında döndürür.
     * Bu sayede kullanıcı arayüzünde algoritmanın nasıl
     * çalıştığına dair detaylı bilgiler sunulabilir.
     */
    abstract public function getAdditionalInfo(): string;
    
    /**
     * Kullanıcının en son login olduğu zamanı döndürür.
     * Birçok tahmin algoritması son login zamanından hareket eder.
     */
    protected function getLastLogin(): DateTime {
        return end($this->loginDates);
    }
}