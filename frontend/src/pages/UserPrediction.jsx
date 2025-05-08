import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import Header from '../components/Header';
import Footer from '../components/Footer';
import PredictionResults from '../components/PredictionResults';

const UserPrediction = () => {
  const { userId } = useParams();
  const [selectedUser, setSelectedUser] = useState(null);
  const [predictions, setPredictions] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [lastRefresh, setLastRefresh] = useState(null);

  useEffect(() => {
    fetchUserData();
  }, [userId]);

  const fetchUserData = async () => {
    try {
      setLoading(true);
      const timestamp = new Date().getTime();
      const response = await fetch(`/backend/login_prediction_app.php?userId=${userId}&_t=${timestamp}`, {
        cache: 'no-store'
      });
      
      if (!response.ok) {
        throw new Error('Kullanıcı verileri alınamadı');
      }
      
      const data = await response.json();
      
      if (data.error) {
        setError(data.error);
      } else {
        // API yanıtını mevcut bileşenlere uyacak şekilde düzenle
        setSelectedUser(data.user);
        setPredictions(data.predictions);
        setLastRefresh(new Date().toLocaleTimeString());
      }
      setLoading(false);
    } catch (err) {
      setError(err.message);
      setLoading(false);
    }
  };

  const handleRefresh = () => {
    fetchUserData();
  };

  if (loading) {
    return <div className="loading">Yükleniyor...</div>;
  }

  if (error) {
    return <div className="error-message">{error}</div>;
  }

  return (
    <div className="app-container">
      <Header onRefresh={handleRefresh} lastRefresh={lastRefresh} />

      <main>
        <div className="back-button-container">
          <Link to="/" className="back-button">
            <span className="back-icon">←</span> Kullanıcı Listesine Dön
          </Link>
        </div>
        
        <PredictionResults 
          predictions={predictions} 
          selectedUser={selectedUser} 
        />
      </main>

      <Footer />
    </div>
  );
};

export default UserPrediction; 