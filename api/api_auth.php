<?php
$valid_api_key = 'your_secret_api_key';

$provided_api_key = $_SERVER['HTTP_X_API_KEY'] ?? '';

if (!hash_equals($valid_api_key, $provided_api_key)) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid API key',
    ]);
    exit;
}
