import React from 'react';

const Footer = () => {
  return (
    <footer>
      <div className="footer-content">
        <div className="footer-logo">
          <img src="src/assets/favicon.ico" alt="Humanas Logo" className="logo" />
          <span className="logo-text">Humanas</span>
        </div>
        <div className="footer-links">
          <a href="https://humanas.io/privacy-policy" className="footer-link">Gizlilik Politikası</a>
          <a href="https://humanas.io/privacy-policy" className="footer-link">Kullanım Şartları</a>
          <a href="https://humanas.io/contact-us" className="footer-link">İletişim</a>
        </div>
        <p className="copyright">&copy; {new Date().getFullYear()} Humanas AI Analytics • Tüm Hakları Saklıdır</p>
      </div>
    </footer>
  );
};

export default Footer; 