import React from 'react';

const Header = ({ onRefresh, lastRefresh }) => {
  return (
    <header>
      <div className="header-content">
        <div className="logo-container">
          <img src="src/assets/favicon.ico" alt="Humanas Logo" className="logo" />
          <h1>Humanas</h1>
        </div>
        <h2 className="subtitle">Kullanıcı Login Tahmin Sistemi</h2>
        <p>Gelişmiş algoritma teknolojisiyle kullanıcılarınızın login davranışlarını analiz edin ve gelecekteki login eğilimlerini yüksek doğrulukla tahmin edin.</p>
        <div className="refresh-container">
          <button onClick={onRefresh} className="refresh-button">
            <span className="refresh-icon">⟳</span> Verileri Yenile
          </button>
          {lastRefresh && <span className="last-refresh">Son yenileme: {lastRefresh}</span>}
        </div>
      </div>
    </header>
  );
};

export default Header; 