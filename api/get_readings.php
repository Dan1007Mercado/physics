<?php
require_once __DIR__ . '/db.php';

$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int) $_GET['limit'] : 50;
$limit = max(1, min($limit, 200));

$stmt = $pdo->prepare('SELECT * FROM readings ORDER BY created_at DESC, id DESC LIMIT ?');
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->execute();
$readings = $stmt->fetchAll();
$settings = get_settings($pdo);

foreach ($readings as &$reading) {
    $reading['badges'] = reading_badges($reading, $settings);
}

send_json([
    'success' => true,
    'readings' => $readings,
]);
