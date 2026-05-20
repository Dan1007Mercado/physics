<?php
date_default_timezone_set('Asia/Manila');

$db_host = 'localhost';
$db_name = 'egg_incubator_db';
$db_user = 'root';
$db_pass = '';
$db_charset = 'utf8mb4';

$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";
$pdo_options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $pdo_options);
} catch (PDOException $e) {
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed. Check XAMPP MySQL and database settings.',
    ]);
    exit;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function send_json(array $payload, int $status_code = 200): void
{
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function get_settings(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM settings ORDER BY id ASC LIMIT 1');
    $settings = $stmt->fetch();

    if ($settings) {
        return $settings;
    }

    $pdo->exec('INSERT INTO settings (min_temp, max_temp, min_humidity, max_humidity) VALUES (35, 39, 50, 65)');
    $stmt = $pdo->query('SELECT * FROM settings ORDER BY id ASC LIMIT 1');
    return $stmt->fetch();
}

function is_valid_date(string $date): bool
{
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
}

function calculate_expected_hatch_date(string $start_date, int $incubation_days): string
{
    $date = DateTime::createFromFormat('Y-m-d', $start_date);
    $date->modify('+' . $incubation_days . ' days');
    return $date->format('Y-m-d');
}

function days_remaining_info(?string $expected_hatch_date): array
{
    if (!$expected_hatch_date || !is_valid_date($expected_hatch_date)) {
        return ['days' => null, 'label' => 'Not set', 'state' => 'muted'];
    }

    $today = new DateTime('today');
    $hatch_date = DateTime::createFromFormat('Y-m-d', $expected_hatch_date);
    $days = (int) $today->diff($hatch_date)->format('%r%a');

    if ($days > 1) {
        return ['days' => $days, 'label' => $days . ' days remaining', 'state' => $days <= 3 ? 'soon' : 'normal'];
    }

    if ($days === 1) {
        return ['days' => $days, 'label' => '1 day remaining', 'state' => 'soon'];
    }

    if ($days === 0) {
        return ['days' => $days, 'label' => 'Hatches today', 'state' => 'today'];
    }

    $overdue = abs($days);
    return [
        'days' => $days,
        'label' => $overdue === 1 ? '1 day overdue' : $overdue . ' days overdue',
        'state' => 'overdue',
    ];
}

function format_date_display(?string $date): string
{
    if (!$date || !is_valid_date($date)) {
        return '-';
    }

    return DateTime::createFromFormat('Y-m-d', $date)->format('M j, Y');
}

function format_datetime_display(?string $datetime): string
{
    if (!$datetime) {
        return '-';
    }

    $timestamp = strtotime($datetime);
    return $timestamp ? date('M j, Y g:i A', $timestamp) : '-';
}

function number_display($number, int $decimals = 1): string
{
    return number_format((float) $number, $decimals);
}

function reading_badges(array $reading, array $settings): array
{
    $badges = [];
    $temperature = (float) $reading['temperature'];
    $humidity = (float) $reading['humidity'];

    if ($temperature < (float) $settings['min_temp']) {
        $badges[] = ['label' => 'Temperature Low', 'class' => 'danger'];
    } elseif ($temperature > (float) $settings['max_temp']) {
        $badges[] = ['label' => 'Temperature High', 'class' => 'danger'];
    }

    if ($humidity < (float) $settings['min_humidity']) {
        $badges[] = ['label' => 'Humidity Low', 'class' => 'info'];
    } elseif ($humidity > (float) $settings['max_humidity']) {
        $badges[] = ['label' => 'Humidity High', 'class' => 'info'];
    }

    if (!$badges) {
        $badges[] = ['label' => 'Normal', 'class' => 'success'];
    }

    return $badges;
}

function overall_condition(?array $latest_reading, array $settings): array
{
    if (!$latest_reading) {
        return [
            'level' => 'secondary',
            'message' => 'No sensor reading available',
            'alerts' => ['No sensor reading available'],
            'is_stale' => false,
        ];
    }

    $alerts = [];
    $temperature = (float) $latest_reading['temperature'];
    $humidity = (float) $latest_reading['humidity'];
    $reading_time = strtotime($latest_reading['created_at']);
    $is_stale = !$reading_time || (time() - $reading_time) > 300;
    $has_temperature_alert = false;

    if ($is_stale) {
        $alerts[] = 'No recent sensor reading';
    }

    if ($temperature < (float) $settings['min_temp']) {
        $alerts[] = 'Temperature is too low';
        $has_temperature_alert = true;
    } elseif ($temperature > (float) $settings['max_temp']) {
        $alerts[] = 'Temperature is too high';
        $has_temperature_alert = true;
    }

    if ($humidity < (float) $settings['min_humidity']) {
        $alerts[] = 'Humidity is too low';
    } elseif ($humidity > (float) $settings['max_humidity']) {
        $alerts[] = 'Humidity is too high';
    }

    if (!$alerts) {
        return [
            'level' => 'success',
            'message' => 'Incubator condition is normal',
            'alerts' => [],
            'is_stale' => false,
        ];
    }

    return [
        'level' => $has_temperature_alert ? 'danger' : 'warning',
        'message' => $alerts[0],
        'alerts' => $alerts,
        'is_stale' => $is_stale,
    ];
}

function allowed_batch_statuses(): array
{
    return ['Incubating', 'Hatched', 'Removed', 'Failed'];
}

function batch_status_class(string $status): string
{
    switch ($status) {
        case 'Hatched':
            return 'success';
        case 'Failed':
            return 'danger';
        case 'Removed':
            return 'secondary';
        case 'Incubating':
        default:
            return 'warning';
    }
}

function get_active_batch_for_tray(PDO $pdo, int $tray_number): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM egg_batches WHERE tray_number = ? AND status = 'Incubating' ORDER BY start_date DESC, id DESC LIMIT 1");
    $stmt->execute([$tray_number]);
    $batch = $stmt->fetch();
    return $batch ?: null;
}

function get_active_batches_by_tray(PDO $pdo): array
{
    return [
        1 => get_active_batch_for_tray($pdo, 1),
        2 => get_active_batch_for_tray($pdo, 2),
    ];
}

function tray_is_occupied(PDO $pdo, int $tray_number, ?int $exclude_batch_id = null): bool
{
    $sql = "SELECT COUNT(*) FROM egg_batches WHERE tray_number = ? AND status = 'Incubating'";
    $params = [$tray_number];

    if ($exclude_batch_id !== null) {
        $sql .= ' AND id <> ?';
        $params[] = $exclude_batch_id;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn() > 0;
}

function get_batch_by_id(PDO $pdo, int $batch_id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM egg_batches WHERE id = ?');
    $stmt->execute([$batch_id]);
    $batch = $stmt->fetch();
    return $batch ?: null;
}

function update_batch_status(PDO $pdo, int $batch_id, string $status): bool
{
    if (!in_array($status, allowed_batch_statuses(), true)) {
        return false;
    }

    $stmt = $pdo->prepare('UPDATE egg_batches SET status = ? WHERE id = ?');
    return $stmt->execute([$status, $batch_id]);
}

function process_batch_status_action(PDO $pdo, array $input): array
{
    $actionMap = status_action_map();
    $action = clean_text($input['action'] ?? '', 20);
    $batchId = isset($input['batch_id']) ? (int) $input['batch_id'] : 0;

    if ($batchId < 1 || !isset($actionMap[$action])) {
        return [
            'success' => false,
            'message' => 'Choose a valid batch action.',
        ];
    }

    $batch = get_batch_by_id($pdo, $batchId);
    if (!$batch) {
        return [
            'success' => false,
            'message' => 'Batch not found.',
        ];
    }

    update_batch_status($pdo, $batchId, $actionMap[$action]);

    return [
        'success' => true,
        'message' => 'Batch status updated.',
        'batch_id' => $batchId,
        'new_status' => $actionMap[$action],
    ];
}

function valid_tray_number(int $tray_number): bool
{
    return in_array($tray_number, [1, 2], true);
}

function tray_name(int $tray_number): string
{
    return 'Egg Tray ' . $tray_number;
}

function active_nav_class(string $activePage, string $page): string
{
    return $activePage === $page ? 'active' : '';
}

function status_action_map(): array
{
    return [
        'hatched' => 'Hatched',
        'removed' => 'Removed',
        'failed' => 'Failed',
    ];
}

function status_action_label(string $action): string
{
    switch ($action) {
        case 'hatched':
            return 'Mark as Hatched';
        case 'removed':
            return 'Remove Batch';
        case 'failed':
            return 'Mark as Failed';
        default:
            return 'Update Status';
    }
}

function clean_text($value, int $max_length): string
{
    $text = trim((string) $value);
    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $max_length);
    }

    return substr($text, 0, $max_length);
}

function validate_batch_form(PDO $pdo, array $input, ?int $exclude_batch_id = null, bool $allow_status = false): array
{
    $errors = [];

    $tray_number = isset($input['tray_number']) ? (int) $input['tray_number'] : 0;
    $batch_name = clean_text($input['batch_name'] ?? '', 100);
    $egg_type = clean_text($input['egg_type'] ?? '', 50);
    $quantity = isset($input['quantity']) ? (int) $input['quantity'] : 0;
    $start_date = clean_text($input['start_date'] ?? '', 10);
    $incubation_days = isset($input['incubation_days']) ? (int) $input['incubation_days'] : 0;
    $notes = trim((string) ($input['notes'] ?? ''));
    $status = $allow_status ? clean_text($input['status'] ?? 'Incubating', 30) : 'Incubating';

    if (!valid_tray_number($tray_number)) {
        $errors[] = 'Choose Egg Tray 1 or Egg Tray 2.';
    }

    if ($batch_name === '') {
        $errors[] = 'Enter a batch name.';
    }

    if ($quantity < 1) {
        $errors[] = 'Enter the number of eggs.';
    }

    if (!is_valid_date($start_date)) {
        $errors[] = 'Choose a valid start date.';
    }

    if ($incubation_days < 1) {
        $errors[] = 'Enter the number of incubation days.';
    }

    if (!in_array($status, allowed_batch_statuses(), true)) {
        $errors[] = 'Choose a valid batch status.';
    }

    if (!$errors && $status === 'Incubating' && tray_is_occupied($pdo, $tray_number, $exclude_batch_id)) {
        $errors[] = tray_name($tray_number) . ' already has an active Incubating batch.';
    }

    $expected_hatch_date = (!$errors && is_valid_date($start_date) && $incubation_days > 0)
        ? calculate_expected_hatch_date($start_date, $incubation_days)
        : '';

    return [
        'errors' => $errors,
        'data' => [
            'tray_number' => $tray_number,
            'batch_name' => $batch_name,
            'egg_type' => $egg_type,
            'quantity' => $quantity,
            'start_date' => $start_date,
            'incubation_days' => $incubation_days,
            'expected_hatch_date' => $expected_hatch_date,
            'status' => $status,
            'notes' => $notes,
        ],
    ];
}

function message_text(?string $message_key): string
{
    switch ($message_key) {
        case 'batch_added':
            return 'Egg batch saved.';
        case 'batch_updated':
            return 'Egg batch updated.';
        case 'status_updated':
            return 'Batch status updated.';
        case 'settings_saved':
            return 'Warning settings saved.';
        default:
            return '';
    }
}

function redirect_to(string $url): void
{
    header('Location: ' . $url);
    exit;
}
