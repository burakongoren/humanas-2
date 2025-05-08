import { useState, useEffect } from 'react'
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom'
import './App.css'
import Header from './components/Header'
import UserList from './components/UserList'
import Footer from './components/Footer'
import UserPrediction from './pages/UserPrediction'

function HomePage() {
  const [loading, setLoading] = useState(false);
  const [users, setUsers] = useState([]);
  const [lastRefresh, setLastRefresh] = useState('');
  
  // API verilerini çek
  const fetchData = async () => {
    setLoading(true);
    try {
      // Her seferinde yeni verileri almak için timestamp ekle
      const timestamp = new Date().getTime();
      const response = await fetch(`/.netlify/functions/api/login_prediction_app.php?_t=${timestamp}`, {
        cache: 'no-store'
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      const data = await response.json();
      
      if (data && data.users) {
        setUsers(data.users);
        setLastRefresh(new Date().toLocaleTimeString('tr-TR'));
      } else {
        console.error("API response doesn't contain users data:", data);
      }
    } catch (error) {
      console.error('Veri çekme hatası:', error);
    } finally {
      setLoading(false);
    }
  };

  // Sayfayı manuel olarak yenile
  const handleRefresh = () => {
    fetchData();
  };

  // Sayfa yüklendiğinde verileri çek
  useEffect(() => {
    fetchData();
    
    // Sayfayı yeniden yüklerken (hard refresh) de çalışsın
    window.onload = fetchData;
    
    return () => {
      window.onload = null;
    };
  }, []);

  return (
    <div className="app-container">
      <Header onRefresh={handleRefresh} lastRefresh={lastRefresh} />

      <main>
        {loading ? (
          <div className="loading">Yükleniyor...</div>
        ) : (
          <UserList users={users} />
        )}
      </main>

      <Footer />
    </div>
  )
}

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<HomePage />} />
        <Route path="/user/:userId" element={<UserPrediction />} />
      </Routes>
    </Router>
  )
}

export default App
