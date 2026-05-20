<?php
require_once __DIR__ . '/api_auth.php';
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json([
        'success' => false,
        'message' => 'Only POST requests are allowed',
    ], 405);
}

$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true);

if (!is_array($data) || !array_key_exists('temperature', $data) || !array_key_exists('humidity', $data)) {
    send_json([
        'success' => false,
        'message' => 'Missing temperature or humidity',
    ], 400);
}

if (!is_numeric($data['temperature']) || !is_numeric($data['humidity'])) {
    send_json([
        'success' => false,
        'message' => 'Temperature and humidity must be numbers',
    ], 400);
}

$temperature = (float) $data['temperature'];
$humidity = (float) $data['humidity'];

if ($humidity < 0 || $humidity > 100) {
    send_json([
        'success' => false,
        'message' => 'Humidity must be between 0 and 100',
    ], 400);
}

try {
    $stmt = $pdo->prepare('INSERT INTO readings (temperature, humidity) VALUES (?, ?)');
    $stmt->execute([$temperature, $humidity]);

    send_json([
        'success' => true,
        'message' => 'Reading saved successfully',
    ]);
} catch (PDOException $e) {
    send_json([
        'success' => false,
        'message' => 'Unable to save reading',
    ], 500);
}
