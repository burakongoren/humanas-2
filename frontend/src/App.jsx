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
      
      // Netlify Functions üzerinden backend verisine erişim
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
        console.log("Backend verisi Netlify Functions üzerinden başarıyla alındı");
      } else {
        console.error("API response doesn't contain users data:", data);
        // Yerel JSON dosyasını kullan (fallback)
        await loadLocalData(timestamp);
      }
    } catch (error) {
      console.error('Netlify Functions ile veri çekme hatası:', error);
      // Yerel JSON dosyasını kullan (fallback)
      await loadLocalData(timestamp);
    } finally {
      setLoading(false);
    }
  };

  // Yerel JSON dosyasından veri yükleme (fallback için)
  const loadLocalData = async (timestamp) => {
    try {
      const localResponse = await fetch(`/data/api_data.json?_t=${timestamp}`, {
        cache: 'no-store'
      });
      
      if (!localResponse.ok) {
        throw new Error(`HTTP error! Status: ${localResponse.status}`);
      }
      
      const jsonData = await localResponse.json();
      
      // JSON formatı backend API'si ile aynı olduğundan, aynı şekilde işleyelim
      if (jsonData && jsonData.data && jsonData.data.rows) {
        const usersList = jsonData.data.rows.map(user => ({
          id: user.id,
          name: user.name,
          loginCount: user.logins.length
        }));
        
        setUsers(usersList);
        setLastRefresh(new Date().toLocaleTimeString('tr-TR'));
        console.log("Yerel JSON'dan veriler alındı (fallback)");
      } else {
        console.error("API response doesn't contain users data:", jsonData);
      }
    } catch (localError) {
      console.error('Yerel veri yükleme hatası:', localError);
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
