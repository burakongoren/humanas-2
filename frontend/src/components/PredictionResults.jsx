import React from 'react';
import LoginHistory from './LoginHistory';
import PredictionAlgorithm from './PredictionAlgorithm';

const PredictionResults = ({ predictions, selectedUser }) => {
  if (!predictions || !selectedUser) return null;

  return (
    <div className="prediction-results">
      <h2>Tahmin Sonuçları: {selectedUser.name}</h2>
      
      <LoginHistory loginData={selectedUser.logins} />

      <div className="algorithm-list">
        <h3>Tahmin Algoritmaları</h3>
        
        {predictions.error ? (
          <div className="error-message">
            <p>Tahmin oluşturulurken bir hata oluştu: {predictions.error}</p>
          </div>
        ) : (
          <>
            <PredictionAlgorithm 
              title="Basit Ortalama Aralık Yöntemi"
              description="Bu algoritma, kullanıcının login'leri arasındaki ortalama süreyi (saat cinsinden) hesaplar ve son login zamanına bu süreyi ekleyerek bir sonraki tahmini login zamanını belirler. Basit ve anlaşılır yapısıyla düzenli login alışkanlıkları olan kullanıcılar için doğru tahminler üretebilir. Ancak hafta içi/hafta sonu farklarını dikkate almaz ve düzensiz login davranışlarında yanılma payı yüksektir."
              prediction={predictions.averageInterval}
            />

            <PredictionAlgorithm 
              title="Gün-Saat Pattern Analizi"
              description="Bu algoritma, kullanıcının hangi gün ve saatlerde login olma eğiliminde olduğunu analiz eder. Her gün ve saat dilimi için login sıklığını hesaplayarak en yoğun kullanım zamanlarını belirler ve bu bilgiyi kullanarak bir sonraki login zamanını tahmin eder. Kullanıcının düzenli davranış örüntülerini tespit edebilir ve hafta içi/hafta sonu farklarını dikkate alır. Ancak yeterli login verisi olmadan doğru tahminler yapılamaz ve kullanıcı davranışları değişirse tahminler hatalı olabilir."
              prediction={predictions.patternAnalysis}
            />

            <PredictionAlgorithm 
              title="Gaussian Mixture Models (GMM)"
              description="Bu algoritma, login zamanlarının olasılık dağılımını modelleyerek tahmin yapar. Login zamanlarını hafta içi/hafta sonu ve sabah/öğle/akşam olarak kümelere ayırır, her küme için istatistiksel analiz yapar ve Gaussian dağılım kullanarak sonraki login zamanını olasılıksal olarak tahmin eder. Karmaşık login davranış örüntülerini modelleyebilir ve olasılıksal yaklaşımı sayesinde daha gerçekçi tahminler üretir. Ancak algoritmanın anlaşılması ve uygulanması zordur, ayrıca yeterli veri olmadığında doğru tahminler yapamayabilir."
              prediction={predictions.gaussianMixture}
            />
          </>
        )}
      </div>
    </div>
  );
};

export default PredictionResults; 