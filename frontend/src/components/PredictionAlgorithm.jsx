import React from 'react';

const PredictionAlgorithm = ({ title, description, prediction }) => {
  // Prediction artık bir nesne olarak geliyor (nextLogin ve accuracy bilgisi içeriyor)
  const nextLoginDate = prediction?.nextLogin || "Hesaplanamadı";
  const accuracy = prediction?.accuracy || null;

  // Doğruluk skoru sınıfını belirle
  const getScoreClass = (score) => {
    if (score >= 90) return 'score-excellent';
    if (score >= 80) return 'score-very-good';
    if (score >= 70) return 'score-good';
    if (score >= 60) return 'score-above-average';
    if (score >= 50) return 'score-average';
    if (score >= 40) return 'score-below-average';
    if (score >= 30) return 'score-poor';
    if (score >= 20) return 'score-very-poor';
    return 'score-bad';
  };
  
  // Tarih formatını daha okunaklı hale getir
  const formatDateTime = (dateTimeStr) => {
    if (!dateTimeStr) return '';
    
    // Tarih formatını oluştur, geçersiz ise orijinali göster
    try {
      const date = new Date(dateTimeStr);
      if (isNaN(date.getTime())) return dateTimeStr;
      
      return date.toLocaleString('tr-TR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      });
    } catch {
      return dateTimeStr;
    }
  };

  return (
    <div className="algorithm">
      <h4>{title}</h4>
      <p>{description}</p>
      <div className="prediction">
        <p>Tahmini bir sonraki login: <strong>{formatDateTime(nextLoginDate)}</strong></p>
        
        {/* Doğruluk skoru bilgisi */}
        {accuracy && (
          <div className="accuracy-info">
            <h5>Doğruluk Analizi</h5>
            {accuracy.score !== 'N/A' ? (
              <>
                <p>
                  <span className={`accuracy-score-badge ${getScoreClass(accuracy.score)}`}>
                    {accuracy.score}%
                  </span>
                  <strong>Doğruluk Skoru</strong>
                </p>
                <p>Son login'e kıyasla saat farkı: <strong>{accuracy.hourDifference} saat</strong></p>
                <p>Tahmin edilen: <strong>{formatDateTime(accuracy.predicted)}</strong></p>
                <p>Gerçek son login: <strong>{formatDateTime(accuracy.actual)}</strong></p>
              </>
            ) : (
              <p>{accuracy.message || 'Doğruluk hesaplanamadı.'}</p>
            )}
          </div>
        )}
      </div>
    </div>
  );
};

export default PredictionAlgorithm; 