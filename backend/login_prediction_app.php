<?php
// Çıktı tamponlamasını başlat - HTML çıktısı olmadığından emin olmak için 
ob_start();

// Allow requests from your Netlify domain - Netlify site URL'ini kullan
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// For preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enable error reporting - Ama çıktıya değil, loglara yaz
error_reporting(E_ALL);
ini_set('display_errors', 0); // çıktıya hata çıkarmayı devre dışı bırak
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1); // hataları loga yaz

// Set the default timezone
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

// Önce API verilerini güncelleyelim
updateAPIData();

// JSON dosyasından verileri yükle
$jsonFile = __DIR__ . '/api_data.json';
$jsonData = file_get_contents($jsonFile);

if ($jsonData === false) {
    http_response_code(500);
    echo json_encode(['error' => "Could not read the file $jsonFile"]);
    exit;
}

$data = json_decode($jsonData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['error' => "Error parsing JSON: " . json_last_error_msg()]);
    exit;
}

// Kullanıcı verilerini al
$users = [];
if (isset($data['data']['rows'])) {
    $users = $data['data']['rows'];
} else {
    http_response_code(500);
    echo json_encode(['error' => "Invalid data structure in JSON file"]);
    exit;
}

// Türkçe gün isimleri
$dayNames = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];

// İngilizce gün isimlerini Türkçe'ye çeviren fonksiyon
function translateDayName($englishDay) {
    $days = [
        'Monday' => 'Pazartesi',
        'Tuesday' => 'Salı',
        'Wednesday' => 'Çarşamba',
        'Thursday' => 'Perşembe',
        'Friday' => 'Cuma',
        'Saturday' => 'Cumartesi',
        'Sunday' => 'Pazar'
    ];
    
    return $days[$englishDay] ?? $englishDay;
}

// Doğruluk skorunu hesaplamak için yardımcı fonksiyon
function calculateAccuracyScore($predictedDate, $actualDate) {
    $predicted = new DateTime($predictedDate);
    $actual = new DateTime($actualDate);
    
    // Saat farkını hesapla (mutlak değer olarak)
    $intervalInSeconds = abs($predicted->getTimestamp() - $actual->getTimestamp());
    $intervalInHours = $intervalInSeconds / 3600;
    
    // Daha hassas bir skor hesaplama (1-100 arası, her 1 saat fark için daha hassas değişim)
    if ($intervalInHours < 1) {
        // 1 saatten az fark: 90-100 arası
        $score = 100 - (int)($intervalInHours * 10);
    } elseif ($intervalInHours < 3) {
        // 1-3 saat arası: 80-90 arası
        $score = 90 - (int)(($intervalInHours - 1) * 5);
    } elseif ($intervalInHours < 6) {
        // 3-6 saat arası: 70-80 arası
        $score = 80 - (int)(($intervalInHours - 3) * (10/3));
    } elseif ($intervalInHours < 12) {
        // 6-12 saat arası: 60-70 arası
        $score = 70 - (int)(($intervalInHours - 6) * (10/6));
    } elseif ($intervalInHours < 24) {
        // 12-24 saat arası: 50-60 arası
        $score = 60 - (int)(($intervalInHours - 12) * (10/12));
    } elseif ($intervalInHours < 48) {
        // 24-48 saat arası: 30-50 arası
        $score = 50 - (int)(($intervalInHours - 24) * (20/24));
    } elseif ($intervalInHours < 72) {
        // 48-72 saat arası: 20-30 arası
        $score = 30 - (int)(($intervalInHours - 48) * (10/24));
    } elseif ($intervalInHours < 96) {
        // 72-96 saat arası: 10-20 arası
        $score = 20 - (int)(($intervalInHours - 72) * (10/24));
    } else {
        // 96 saatten fazla: 10 puan
        $score = 10;
    }
    
    // Sınırları garantile
    $score = max(10, min(100, $score));
    
    return [
        'score' => $score,
        'hourDifference' => round($intervalInHours, 2),
        'predicted' => $predictedDate,
        'actual' => $actualDate
    ];
}

// Belirli bir kullanıcı için tahmin isteniyorsa
$userId = isset($_GET['userId']) ? $_GET['userId'] : null;
$debug = isset($_GET['debug']) && $_GET['debug'] === '1';

// Debug modu CORS ve içerik tipi ayarları
if ($debug) {
    header("Content-Type: text/html; charset=UTF-8");
}

/**
 * API verilerini güncelleyen fonksiyon
 */
function updateAPIData() {
    $apiUrl = 'https://case-test-api.humanas.io/';
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
    
    // Attempt to fetch the data from the API
    $apiSuccess = false;
    $response = '';
    
    try {
        $response = @file_get_contents($apiUrl, false, $context);
        
        if ($response === false) {
            return false;
        } else {
            $apiSuccess = true;
        }
    } catch (Exception $e) {
        return false;
    }
    
    // If API request failed, use the known API response structure
    if (!$apiSuccess) {
        $response = '{"status":0,"message":"Success","data":{"rows":[{"id":"user_1","name":"Ahmet","logins":["2025-04-01T04:47:00Z","2025-04-06T08:37:00Z","2025-04-08T14:13:00Z","2025-04-10T12:42:00Z","2025-04-13T06:24:00Z","2025-04-14T11:04:00Z","2025-04-14T18:09:00Z","2025-04-21T16:13:00Z","2025-04-22T00:04:00Z","2025-04-27T15:29:00Z","2025-04-30T07:31:00Z","2025-05-01T07:26:00Z"]},{"id":"user_2","name":"Ayşe","logins":["2025-04-03T19:36:00Z","2025-04-13T10:42:00Z","2025-04-18T04:18:00Z","2025-04-18T10:52:00Z","2025-04-19T08:07:00Z","2025-04-19T21:14:00Z","2025-04-25T02:03:00Z","2025-04-26T04:22:00Z","2025-04-26T08:29:00Z","2025-04-26T15:13:00Z","2025-04-27T16:36:00Z","2025-04-28T04:02:00Z","2025-04-30T08:00:00Z","2025-04-30T18:18:00Z","2025-05-01T00:31:00Z","2025-05-01T02:32:00Z","2025-05-01T04:59:00Z","2025-05-02T20:18:00Z","2025-05-03T14:38:00Z","2025-05-05T04:33:00Z"]},{"id":"user_3","name":"Mehmet","logins":["2025-04-06T01:22:00Z","2025-04-07T01:31:00Z","2025-04-08T04:26:00Z","2025-04-09T14:36:00Z","2025-04-13T19:24:00Z","2025-04-18T16:28:00Z","2025-04-20T23:18:00Z","2025-04-23T08:08:00Z","2025-04-24T19:56:00Z","2025-04-25T02:48:00Z","2025-04-25T23:21:00Z","2025-04-27T15:48:00Z","2025-04-29T16:11:00Z","2025-04-30T20:11:00Z","2025-05-01T05:13:00Z","2025-05-05T18:15:00Z"]},{"id":"user_4","name":"Fatma","logins":["2025-04-02T10:30:00Z","2025-04-06T08:25:00Z","2025-04-17T09:03:00Z","2025-04-23T23:44:00Z","2025-05-04T14:53:00Z","2025-05-04T18:24:00Z"]},{"id":"user_5","name":"Ali","logins":["2025-04-09T09:50:00Z","2025-04-09T18:52:00Z","2025-04-11T07:46:00Z","2025-04-11T23:15:00Z","2025-04-12T23:20:00Z","2025-04-13T21:42:00Z","2025-04-14T08:08:00Z","2025-04-17T18:10:00Z","2025-04-17T23:59:00Z","2025-04-23T11:38:00Z","2025-04-26T15:33:00Z","2025-04-30T15:53:00Z","2025-05-03T00:24:00Z","2025-05-03T07:18:00Z","2025-05-04T08:22:00Z","2025-05-05T10:10:00Z"]},{"id":"user_6","name":"Zeynep","logins":["2025-04-01T22:52:00Z","2025-04-03T03:07:00Z","2025-04-04T13:43:00Z","2025-04-05T02:51:00Z","2025-04-06T02:07:00Z","2025-04-10T01:30:00Z","2025-04-10T05:11:00Z","2025-04-11T05:42:00Z","2025-04-14T17:08:00Z","2025-04-14T18:05:00Z","2025-04-22T12:48:00Z","2025-04-22T14:30:00Z","2025-04-24T08:11:00Z","2025-04-25T16:47:00Z","2025-04-28T22:06:00Z","2025-04-28T23:17:00Z","2025-04-29T06:08:00Z","2025-04-29T12:00:00Z","2025-04-29T18:28:00Z"]},{"id":"user_7","name":"Hasan","logins":["2025-04-02T20:37:00Z","2025-04-06T21:09:00Z","2025-04-07T17:44:00Z","2025-04-07T20:34:00Z","2025-04-08T15:54:00Z","2025-04-10T15:30:00Z","2025-04-15T01:43:00Z","2025-04-18T04:06:00Z","2025-04-21T05:36:00Z","2025-04-21T06:42:00Z","2025-04-22T21:16:00Z","2025-04-27T07:37:00Z","2025-04-27T19:56:00Z","2025-04-29T00:18:00Z","2025-05-01T01:18:00Z","2025-05-04T05:26:00Z"]},{"id":"user_8","name":"Elif","logins":["2025-04-01T16:38:00Z","2025-04-04T00:59:00Z","2025-04-04T11:35:00Z","2025-04-05T08:42:00Z","2025-04-06T19:42:00Z","2025-04-13T20:54:00Z","2025-04-21T04:38:00Z","2025-04-25T04:08:00Z","2025-04-26T06:20:00Z","2025-04-26T14:46:00Z","2025-05-04T02:14:00Z","2025-05-05T16:33:00Z"]},{"id":"user_9","name":"Mert","logins":["2025-04-13T17:11:00Z","2025-04-14T04:48:00Z","2025-04-14T13:35:00Z","2025-04-14T18:51:00Z","2025-04-15T08:04:00Z","2025-04-15T18:07:00Z","2025-04-16T22:35:00Z","2025-04-20T17:33:00Z","2025-04-21T12:38:00Z","2025-04-27T12:15:00Z","2025-04-27T16:23:00Z","2025-04-28T11:55:00Z","2025-04-28T13:18:00Z","2025-04-29T02:41:00Z","2025-04-29T08:16:00Z","2025-04-30T17:59:00Z","2025-05-01T03:40:00Z","2025-05-02T08:27:00Z","2025-05-03T01:07:00Z","2025-05-05T11:46:00Z"]},{"id":"user_10","name":"Derya","logins":["2025-04-02T11:35:00Z","2025-04-04T02:34:00Z","2025-04-04T05:35:00Z","2025-04-06T18:54:00Z","2025-04-10T09:22:00Z","2025-04-12T05:01:00Z","2025-04-17T23:50:00Z","2025-04-26T20:54:00Z","2025-05-02T08:55:00Z"]}]}}';
    }
    
    // Her seferinde dosyanın güncel timestamp ile güncellenmesi için
    $timestamp = date('Y-m-d H:i:s');
    // JSON nesnesine timestamp ekleyelim
    $responseObj = json_decode($response, true);
    $responseObj['timestamp'] = $timestamp;
    $response = json_encode($responseObj);

    // Write the response to a JSON file
    file_put_contents($jsonFile, $response);
    
    return true;
}

/**
 * Kullanıcı login tahminlerini yap
 * 
 * @param array $user Kullanıcı verisi
 * @param array $dayNames Gün isimleri
 * @return array Tahmin sonuçları
 */
function makePredictions($user, $dayNames) {
    $userId = $user['id'];
    $userName = $user['name'];
    $logins = $user['logins'];
    
    // Debug için login verilerini yazdır
    error_log("User ID: " . $userId . ", Name: " . $userName . ", Login count: " . count($logins));
    
    // Loginleri DateTime nesnelerine dönüştür
    $loginDates = [];
    foreach ($logins as $login) {
        try {
            $loginDates[] = new DateTime($login);
        } catch (Exception $e) {
            error_log("DateTime conversion error for user $userId: " . $e->getMessage() . " - Value: $login");
        }
    }
    
    // Kullanıcının login sayısını kontrol et
    if (count($loginDates) < 2) {
        error_log("Not enough logins for user $userId: " . count($loginDates) . " logins");
        return ['error' => 'Not enough login data for prediction'];
    }
    
    // Son login zamanını kaydet
    $lastLogin = end($logins);
    
    // Tüm loginler için normal tahmin yap
    $predictions = calculatePrediction($loginDates);
    
    // Doğruluk skorunu hesaplamak için son login hariç tüm loginlerle tahmin yap
    if (count($loginDates) > 2) { // En az 3 login varsa (2 login tahmin için, 1 login doğrulama için)
        // Son login hariç diğer loginleri al
        $loginDatesExceptLast = array_slice($loginDates, 0, -1);
        
        // Son login hariç tahmin yap (ayrı bir tahmin hesaplaması yap)
        $predictionsForAccuracy = calculatePrediction($loginDatesExceptLast);
        
        // Her algoritma için doğruluk skorunu hesapla
        foreach (['averageInterval', 'patternAnalysis', 'gaussianMixture'] as $algorithm) {
            if (isset($predictions[$algorithm]) && isset($predictionsForAccuracy[$algorithm])) {
                $predictions[$algorithm]['accuracy'] = calculateAccuracyScore(
                    $predictionsForAccuracy[$algorithm]['nextLogin'],
                    $lastLogin
                );
            }
        }
    }
    
    // Sonuçları hazırla
    return [
        'averageInterval' => $predictions['averageInterval'] ?? null,
        'patternAnalysis' => $predictions['patternAnalysis'] ?? null,
        'gaussianMixture' => $predictions['gaussianMixture'] ?? null,
    ];
}

// Tahmin hesaplamaları
function calculatePrediction($loginDates) {
    // Her üç algoritmayı kullanarak tahmin yap
    $predictions = [];
    
    try {
        // Ortalama Aralık Tahmini
        $averageIntervalPredictor = new AverageIntervalPredictor($loginDates);
        $predictions['averageInterval'] = [
            'nextLogin' => $averageIntervalPredictor->predictNextLogin()->format('Y-m-d H:i:s')
        ];
        
        // Pattern Analizi
        $patternAnalysisPredictor = new PatternAnalysisPredictor($loginDates);
        $predictions['patternAnalysis'] = [
            'nextLogin' => $patternAnalysisPredictor->predictNextLogin()->format('Y-m-d H:i:s')
        ];
        
        // Gaussian Mixture Model
        $gaussianMixturePredictor = new GaussianMixtureModelPredictor($loginDates);
        $predictions['gaussianMixture'] = [
            'nextLogin' => $gaussianMixturePredictor->predictNextLogin()->format('Y-m-d H:i:s')
        ];
    } catch (Exception $e) {
        $predictions['error'] = $e->getMessage();
    }
    
    return $predictions;
}

// Tüm kullanıcılar için tahmin yapacak mıyız yoksa belirli bir kullanıcı için mi?
if ($userId) {
    // Belirli bir kullanıcı için tahmin yap
    $targetUser = null;
    foreach ($users as $user) {
        if ($user['id'] == $userId) {
            $targetUser = $user;
            break;
        }
    }
    
    if (!$targetUser) {
        http_response_code(404);
        echo json_encode(['error' => "User not found with ID: $userId"]);
        exit;
    }
    
    $predictions = makePredictions($targetUser, $dayNames);
    
    // Debug modu etkinse detaylı bilgi göster
    if ($debug) {
        echo "<h1>Debug Mode: Prediction Details for User: {$targetUser['name']} (ID: {$targetUser['id']})</h1>";
        echo "<h2>Login Data:</h2>";
        echo "<pre>";
        print_r($targetUser['logins']);
        echo "</pre>";
        
        echo "<h2>Prediction Results:</h2>";
        echo "<pre>";
        print_r($predictions);
        echo "</pre>";
        exit;
    }
    
    // Kullanıcı bilgileri ve tahminleri birleştir
    $result = [
        'user' => [
            'id' => $targetUser['id'],
            'name' => $targetUser['name'],
            'logins' => $targetUser['logins']
        ],
        'predictions' => $predictions
    ];
    
    echo json_encode($result, JSON_PRETTY_PRINT);
} else {
    // Tüm kullanıcıları listele
    $usersList = [];
    foreach ($users as $user) {
        $usersList[] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'loginCount' => count($user['logins'])
        ];
    }
    echo json_encode(['users' => $usersList], JSON_PRETTY_PRINT);
} 