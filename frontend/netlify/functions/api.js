const http = require('http');

exports.handler = async function(event) {
  // Parse the path from the URL
  const path = event.path.replace('/.netlify/functions/api', '');
  
  // Full URL to the backend API
  const apiUrl = `http://humanas-backend.infinityfreeapp.com/backend${path}`;
  
  // Get query parameters from the event
  const queryString = Object.keys(event.queryStringParameters || {})
    .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(event.queryStringParameters[key])}`)
    .join('&');
  
  // Complete URL with query parameters
  const fullUrl = queryString ? `${apiUrl}?${queryString}` : apiUrl;
  
  console.log(`Proxying request to: ${fullUrl}`);

  try {
    // Make the HTTP request to the backend
    const response = await new Promise((resolve, reject) => {
      const req = http.get(fullUrl, (res) => {
        let body = '';
        res.on('data', (chunk) => (body += chunk));
        res.on('end', () => {
          resolve({
            statusCode: res.statusCode,
            body: body,
            headers: res.headers
          });
        });
      });
      
      req.on('error', (error) => {
        console.error('Error making request:', error);
        reject({
          statusCode: 500,
          body: JSON.stringify({ error: `Failed to fetch from backend: ${error.message}` })
        });
      });
      
      // Set a timeout to prevent hanging requests
      req.setTimeout(10000, () => {
        req.abort();
        reject({
          statusCode: 504,
          body: JSON.stringify({ error: 'Request to backend timed out' })
        });
      });
    });

    return {
      statusCode: response.statusCode,
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': 'Content-Type, Authorization',
        'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE'
      },
      body: response.body
    };
  } catch (error) {
    console.log('Error in Netlify Function:', error);
    return {
      statusCode: error.statusCode || 500,
      body: error.body || JSON.stringify({ error: 'Internal Server Error' })
    };
  }
}; 