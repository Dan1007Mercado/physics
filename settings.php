<?php
require_once __DIR__ . '/api/db.php';

$pageTitle = 'Warning Settings';
$activePage = 'settings';
$settings = get_settings($pdo);
$errors = [];
$successMessage = message_text($_GET['message'] ?? null);

$form = [
    'min_temp' => (string) $settings['min_temp'],
    'max_temp' => (string) $settings['max_temp'],
    'min_humidity' => (string) $settings['min_humidity'],
    'max_humidity' => (string) $settings['max_humidity'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'min_temp' => trim((string) ($_POST['min_temp'] ?? '')),
        'max_temp' => trim((string) ($_POST['max_temp'] ?? '')),
        'min_humidity' => trim((string) ($_POST['min_humidity'] ?? '')),
        'max_humidity' => trim((string) ($_POST['max_humidity'] ?? '')),
    ];

    foreach ($form as $field => $value) {
        if ($value === '' || !is_numeric($value)) {
            $errors[] = 'All warning values must be numbers.';
            break;
        }
    }

    if (!$errors) {
        $minTemp = (float) $form['min_temp'];
        $maxTemp = (float) $form['max_temp'];
        $minHumidity = (float) $form['min_humidity'];
        $maxHumidity = (float) $form['max_humidity'];

        if ($minTemp >= $maxTemp) {
            $errors[] = 'Lowest safe temperature must be lower than highest safe temperature.';
        }

        if ($minHumidity >= $maxHumidity) {
            $errors[] = 'Lowest safe humidity must be lower than highest safe humidity.';
        }

        if ($minHumidity < 0 || $maxHumidity > 100) {
            $errors[] = 'Humidity values must stay between 0 and 100.';
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE settings SET min_temp = ?, max_temp = ?, min_humidity = ?, max_humidity = ? WHERE id = ?');
        $stmt->execute([
            (float) $form['min_temp'],
            (float) $form['max_temp'],
            (float) $form['min_humidity'],
            (float) $form['max_humidity'],
            (int) $settings['id'],
        ]);

        redirect_to('settings.php?message=settings_saved');
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-section">
    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?= e($successMessage) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <article class="form-panel">
        <p class="helper-note">
            These values are only used for dashboard warnings. They do not control the incubator hardware.
        </p>

        <form method="post" class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label" for="min_temp">Lowest safe temperature</label>
                <div class="input-group input-group-lg">
                    <input class="form-control" type="number" step="0.1" id="min_temp" name="min_temp" value="<?= e($form['min_temp']) ?>" required>
                    <span class="input-group-text">C</span>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="max_temp">Highest safe temperature</label>
                <div class="input-group input-group-lg">
                    <input class="form-control" type="number" step="0.1" id="max_temp" name="max_temp" value="<?= e($form['max_temp']) ?>" required>
                    <span class="input-group-text">C</span>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="min_humidity">Lowest safe humidity</label>
                <div class="input-group input-group-lg">
                    <input class="form-control" type="number" step="0.1" min="0" max="100" id="min_humidity" name="min_humidity" value="<?= e($form['min_humidity']) ?>" required>
                    <span class="input-group-text">%</span>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="max_humidity">Highest safe humidity</label>
                <div class="input-group input-group-lg">
                    <input class="form-control" type="number" step="0.1" min="0" max="100" id="max_humidity" name="max_humidity" value="<?= e($form['max_humidity']) ?>" required>
                    <span class="input-group-text">%</span>
                </div>
            </div>
            <div class="col-12">
                <button class="btn btn-success btn-lg" type="submit">Save Settings</button>
            </div>
        </form>
    </article>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
