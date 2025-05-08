<?php
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

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Çıktıya hata göstermeyi devre dışı bırak
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1); // Hataları loga yaz

// Default timezone
date_default_timezone_set('UTC');

// Cache'i devre dışı bırak
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Sınıfları dahil et
require_once 'LoginPredictor.php';
require_once 'AverageIntervalPredictor.php';
require_once 'PatternAnalysisPredictor.php';
require_once 'GaussianMixtureModelPredictor.php';

/**
 * Login Prediction System API
 * 
 * Bu script, kullanıcıların geçmiş login verilerini analiz ederek
 * gelecekteki login zamanlarını üç farklı algoritma ile tahmin eder
 * ve JSON formatında döndürür.
 */ 