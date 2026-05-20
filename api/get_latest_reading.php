<?php
require_once __DIR__ . '/db.php';

$stmt = $pdo->query('SELECT * FROM readings ORDER BY created_at DESC, id DESC LIMIT 1');
$latest = $stmt->fetch() ?: null;
$settings = get_settings($pdo);
$condition = overall_condition($latest, $settings);

send_json([
    'success' => true,
    'reading' => $latest,
    'settings' => $settings,
    'condition' => $condition,
]);
