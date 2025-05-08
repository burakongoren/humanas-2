const https = require('https');
const http = require('http');

exports.handler = async function(event) {
  // Backend path from the URL
  const path = event.path.replace('/.netlify/functions/api', '');
  const apiUrl = `http://humanas-backend.infinityfreeapp.com/backend${path}`;
  
  // Add querystring parameters
  const url = new URL(apiUrl);
  const params = new URLSearchParams(event.queryStringParameters);
  url.search = params.toString();

  console.log(`Proxying request to: ${url.toString()}`);

  try {
    // Create and return promise for the HTTP request
    const response = await new Promise((resolve, reject) => {
      const req = http.get(url.toString(), (res) => {
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
      
      req.on('error', (e) => {
        reject({ 
          statusCode: 500, 
          body: JSON.stringify({ error: `Request failed: ${e.message}` }) 
        });
      });
    });

    // Add CORS headers to response
    return {
      statusCode: response.statusCode,
      headers: {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': 'Content-Type, Authorization',
        'Access-Control-Allow-Methods': 'GET, POST, OPTIONS',
        'Content-Type': 'application/json'
      },
      body: response.body
    };
  } catch (error) {
    console.log('Error:', error);
    return {
      statusCode: error.statusCode || 500,
      body: error.body || JSON.stringify({ error: 'Internal Server Error' })
    };
  }
}; 