import React from 'react';

const LoginHistory = ({ loginData }) => {
  return (
    <div className="login-history">
      <h3>Login Geçmişi</h3>
      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>Tarih</th>
            <th>Gün</th>
            <th>Saat</th>
          </tr>
        </thead>
        <tbody>
          {loginData.map((login, index) => {
            const date = new Date(login);
            return (
              <tr key={index}>
                <td>{index + 1}</td>
                <td>{date.toLocaleString('tr-TR')}</td>
                <td>{date.toLocaleDateString('tr-TR', { weekday: 'long' })}</td>
                <td>{date.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })}</td>
              </tr>
            );
          })}
        </tbody>
      </table>
    </div>
  );
};

export default LoginHistory; 