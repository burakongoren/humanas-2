import React, { useState } from 'react';
import { Link } from 'react-router-dom';

const UserList = ({ users }) => {
  const [searchTerm, setSearchTerm] = useState('');
  
  // Filter users based on search term
  const filteredUsers = users.filter(user => 
    user.name.toLowerCase().includes(searchTerm.toLowerCase())
  );
  
  // Clear search handler
  const handleClearSearch = () => {
    setSearchTerm('');
  };

  return (
    <div className="user-selection">
      <div className="section-header">
        <h2>Kullanıcılar</h2>
        <span className="user-count">{filteredUsers.length} kullanıcı</span>
      </div>
      
      <div className="search-container">
        <div className="search-input-group">
          <span className="search-icon">🔍</span>
          <input
            type="text"
            placeholder="Ara"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="search-input"
          />
          {searchTerm && (
            <button 
              className="clear-search" 
              onClick={handleClearSearch}
              title="Aramayı temizle"
            >
              ✕
            </button>
          )}
        </div>
        {filteredUsers.length === 0 && searchTerm && (
          <div className="no-results">
            "{searchTerm}" ile eşleşen kullanıcı bulunamadı
          </div>
        )}
      </div>
      
      {filteredUsers.length > 0 ? (
        <div className="user-list">
          {filteredUsers.map(user => (
            <Link 
              key={user.id}
              to={`/user/${user.id}`}
              className="user-card-link"
            >
              <div className="user-card">
                <div className="user-avatar">{user.name.charAt(0)}</div>
                <div className="user-info">
                  <h3>{user.name}</h3>
                  <p className="user-id">ID: {user.id}</p>
                  <div className="login-counter">
                    <span className="login-icon">📊</span>
                    <span className="login-count">{user.loginCount || 0} login</span>
                  </div>
                </div>
              </div>
            </Link>
          ))}
        </div>
      ) : null}
    </div>
  );
};

export default UserList; 