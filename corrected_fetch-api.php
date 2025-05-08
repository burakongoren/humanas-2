<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Çıktıya hata göstermeyi devre dışı bırak
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1); // Hataları loga yaz

// CORS başlıkları - Netlify sitesine izin veriyoruz
header("Access-Control-Allow-Origin: https://humanas-case.netlify.app");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Preflight OPTIONS istekleri için
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Force output flushing
ob_implicit_flush(true);
if (ob_get_level()) ob_end_flush();

// API URL
$apiUrl = 'https://case-test-api.humanas.io/';

// Dosya yolu
$jsonFile = __DIR__ . '/api_data.json';

// Create stream context
$opts = [
    'http' => [
        'method' => 'GET',
        'header' => "Accept: application/json\r\n",
        'ignore_errors' => true,
        'timeout' => 30,
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
];

$context = stream_context_create($opts); 