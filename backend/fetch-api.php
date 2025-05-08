<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Add CORS headers at the start of the file
// Allow requests from your Netlify domain
header("Access-Control-Allow-Origin: https://humanas-case.netlify.app");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// For preflight OPTIONS requests
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

// Attempt to fetch the data from the API
$apiSuccess = false;
$response = '';

try {
    $response = @file_get_contents($apiUrl, false, $context);
    
    if ($response === false) {
        $errorMessage = ["error" => "Failed to fetch data from API"];
        if ($error = error_get_last()) {
            $errorMessage["details"] = $error;
        }
        echo json_encode($errorMessage);
        exit;
    } else {
        $apiSuccess = true;
    }
} catch (Exception $e) {
    echo json_encode([
        "error" => "Exception caught", 
        "message" => $e->getMessage()
    ]);
    exit;
}

// If API request failed, use the known API response structure
if (!$apiSuccess) {
    $response = '{"status":0,"message":"Success","data":{"rows":[{"id":"user_1","name":"Ahmet","logins":["2025-04-01T04:47:00Z","2025-04-06T08:37:00Z","2025-04-08T14:13:00Z","2025-04-10T12:42:00Z","2025-04-13T06:24:00Z","2025-04-14T11:04:00Z","2025-04-14T18:09:00Z","2025-04-21T16:13:00Z","2025-04-22T00:04:00Z","2025-04-27T15:29:00Z","2025-04-30T07:31:00Z","2025-05-01T07:26:00Z"]},{"id":"user_2","name":"Ayşe","logins":["2025-04-03T19:36:00Z","2025-04-13T10:42:00Z","2025-04-18T04:18:00Z","2025-04-18T10:52:00Z","2025-04-19T08:07:00Z","2025-04-19T21:14:00Z","2025-04-25T02:03:00Z","2025-04-26T04:22:00Z","2025-04-26T08:29:00Z","2025-04-26T15:13:00Z","2025-04-27T16:36:00Z","2025-04-28T04:02:00Z","2025-04-30T08:00:00Z","2025-04-30T18:18:00Z","2025-05-01T00:31:00Z","2025-05-01T02:32:00Z","2025-05-01T04:59:00Z","2025-05-02T20:18:00Z","2025-05-03T14:38:00Z","2025-05-05T04:33:00Z"]},{"id":"user_3","name":"Mehmet","logins":["2025-04-06T01:22:00Z","2025-04-07T01:31:00Z","2025-04-08T04:26:00Z","2025-04-09T14:36:00Z","2025-04-13T19:24:00Z","2025-04-18T16:28:00Z","2025-04-20T23:18:00Z","2025-04-23T08:08:00Z","2025-04-24T19:56:00Z","2025-04-25T02:48:00Z","2025-04-25T23:21:00Z","2025-04-27T15:48:00Z","2025-04-29T16:11:00Z","2025-04-30T20:11:00Z","2025-05-01T05:13:00Z","2025-05-05T18:15:00Z"]},{"id":"user_4","name":"Fatma","logins":["2025-04-02T10:30:00Z","2025-04-06T08:25:00Z","2025-04-17T09:03:00Z","2025-04-23T23:44:00Z","2025-05-04T14:53:00Z","2025-05-04T18:24:00Z"]},{"id":"user_5","name":"Ali","logins":["2025-04-09T09:50:00Z","2025-04-09T18:52:00Z","2025-04-11T07:46:00Z","2025-04-11T23:15:00Z","2025-04-12T23:20:00Z","2025-04-13T21:42:00Z","2025-04-14T08:08:00Z","2025-04-17T18:10:00Z","2025-04-17T23:59:00Z","2025-04-23T11:38:00Z","2025-04-26T15:33:00Z","2025-04-30T15:53:00Z","2025-05-03T00:24:00Z","2025-05-03T07:18:00Z","2025-05-04T08:22:00Z","2025-05-05T10:10:00Z"]},{"id":"user_6","name":"Zeynep","logins":["2025-04-01T22:52:00Z","2025-04-03T03:07:00Z","2025-04-04T13:43:00Z","2025-04-05T02:51:00Z","2025-04-06T02:07:00Z","2025-04-10T01:30:00Z","2025-04-10T05:11:00Z","2025-04-11T05:42:00Z","2025-04-14T17:08:00Z","2025-04-14T18:05:00Z","2025-04-22T12:48:00Z","2025-04-22T14:30:00Z","2025-04-24T08:11:00Z","2025-04-25T16:47:00Z","2025-04-28T22:06:00Z","2025-04-28T23:17:00Z","2025-04-29T06:08:00Z","2025-04-29T12:00:00Z","2025-04-29T18:28:00Z"]},{"id":"user_7","name":"Hasan","logins":["2025-04-02T20:37:00Z","2025-04-06T21:09:00Z","2025-04-07T17:44:00Z","2025-04-07T20:34:00Z","2025-04-08T15:54:00Z","2025-04-10T15:30:00Z","2025-04-15T01:43:00Z","2025-04-18T04:06:00Z","2025-04-21T05:36:00Z","2025-04-21T06:42:00Z","2025-04-22T21:16:00Z","2025-04-27T07:37:00Z","2025-04-27T19:56:00Z","2025-04-29T00:18:00Z","2025-05-01T01:18:00Z","2025-05-04T05:26:00Z"]},{"id":"user_8","name":"Elif","logins":["2025-04-01T16:38:00Z","2025-04-04T00:59:00Z","2025-04-04T11:35:00Z","2025-04-05T08:42:00Z","2025-04-06T19:42:00Z","2025-04-13T20:54:00Z","2025-04-21T04:38:00Z","2025-04-25T04:08:00Z","2025-04-26T06:20:00Z","2025-04-26T14:46:00Z","2025-05-04T02:14:00Z","2025-05-05T16:33:00Z"]},{"id":"user_9","name":"Mert","logins":["2025-04-13T17:11:00Z","2025-04-14T04:48:00Z","2025-04-14T13:35:00Z","2025-04-14T18:51:00Z","2025-04-15T08:04:00Z","2025-04-15T18:07:00Z","2025-04-16T22:35:00Z","2025-04-20T17:33:00Z","2025-04-21T12:38:00Z","2025-04-27T12:15:00Z","2025-04-27T16:23:00Z","2025-04-28T11:55:00Z","2025-04-28T13:18:00Z","2025-04-29T02:41:00Z","2025-04-29T08:16:00Z","2025-04-30T17:59:00Z","2025-05-01T03:40:00Z","2025-05-02T08:27:00Z","2025-05-03T01:07:00Z","2025-05-05T11:46:00Z"]},{"id":"user_10","name":"Derya","logins":["2025-04-02T11:35:00Z","2025-04-04T02:34:00Z","2025-04-04T05:35:00Z","2025-04-06T18:54:00Z","2025-04-10T09:22:00Z","2025-04-12T05:01:00Z","2025-04-17T23:50:00Z","2025-04-26T20:54:00Z","2025-05-02T08:55:00Z"]}]}}';
}

// Her seferinde dosyanın güncel timestamp ile güncellenmesi için
// Dosyaya timestamp yorumu eklemeyin, geçerli JSON verisini yazın
$timestamp = date('Y-m-d H:i:s');
// Yorum eklemek yerine JSON nesnesine timestamp ekleyelim
$responseObj = json_decode($response, true);
$responseObj['timestamp'] = $timestamp;
$response = json_encode($responseObj);

// Write the response to a JSON file
file_put_contents($jsonFile, $response);

// İstemciye JSON yanıtı gönder
echo $response; 