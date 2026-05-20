<?php
require_once __DIR__ . '/api/db.php';

$pageTitle = 'Dashboard';
$activePage = 'dashboard';

$settings = get_settings($pdo);
$latestStmt = $pdo->query('SELECT * FROM readings ORDER BY created_at DESC, id DESC LIMIT 1');
$latestReading = $latestStmt->fetch() ?: null;
$condition = overall_condition($latestReading, $settings);
$activeBatches = get_active_batches_by_tray($pdo);

$recentStmt = $pdo->query('SELECT * FROM readings ORDER BY created_at DESC, id DESC LIMIT 8');
$recentReadings = $recentStmt->fetchAll();

$activeTrayCount = 0;
foreach ($activeBatches as $batch) {
    if ($batch) {
        $activeTrayCount++;
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-section dashboard-page" data-dashboard="true">
    <div class="hero-panel mb-3">
        <div>
            <span class="eyebrow">Live incubator overview</span>
            <h2>Check the incubator before opening the cabinet.</h2>
            <p>Temperature and humidity readings are received from the ESP32. Hardware control remains with the HX-W3001.</p>
        </div>
        <div class="hero-actions">
            <a class="btn btn-success btn-lg" href="add_batch.php">Add Egg Batch</a>
            <a class="btn btn-outline-primary btn-lg" href="settings.php">Edit Safe Range</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-3">
            <article class="stat-card temp-card">
                <span class="stat-label">Current Temperature</span>
                <strong id="current-temperature" class="metric-value">
                    <?= $latestReading ? e(number_display($latestReading['temperature'])) . ' C' : '--' ?>
                </strong>
                <small>Safe range: <?= e(number_display($settings['min_temp'])) ?> C to <?= e(number_display($settings['max_temp'])) ?> C</small>
            </article>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <article class="stat-card humidity-card">
                <span class="stat-label">Current Humidity</span>
                <strong id="current-humidity" class="metric-value">
                    <?= $latestReading ? e(number_display($latestReading['humidity'])) . '%' : '--' ?>
                </strong>
                <small>Safe range: <?= e(number_display($settings['min_humidity'])) ?>% to <?= e(number_display($settings['max_humidity'])) ?>%</small>
            </article>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <article class="stat-card update-card">
                <span class="stat-label">Latest Update</span>
                <strong id="latest-update" class="metric-value small-value">
                    <?= $latestReading ? e(format_datetime_display($latestReading['created_at'])) : '--' ?>
                </strong>
                <small>Dashboard refreshes every 10 seconds.</small>
            </article>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <article class="stat-card status-card">
                <span class="stat-label">Incubator Status</span>
                <strong id="incubator-status" class="status-message status-<?= e($condition['level']) ?>">
                    <?= e($condition['message']) ?>
                </strong>
                <small><?= e((string) $activeTrayCount) ?> of 2 trays currently active.</small>
            </article>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-4">
            <article class="alert-panel h-100">
                <div class="card-heading">
                    <div>
                        <span class="eyebrow">Needs attention</span>
                        <h2>Alerts</h2>
                    </div>
                    <span class="status-dot status-dot-<?= e($condition['level']) ?>"></span>
                </div>

                <ul id="alerts-list" class="alert-list">
                    <?php if ($condition['alerts']): ?>
                        <?php foreach ($condition['alerts'] as $alert): ?>
                            <li><?= e($alert) ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="safe">Condition is normal. No action needed right now.</li>
                    <?php endif; ?>
                </ul>
            </article>
        </div>

        <div class="col-12 col-xl-8">
            <div class="row g-3">
                <?php foreach ([1, 2] as $trayNumber): ?>
                    <?php
                    $batch = $activeBatches[$trayNumber];
                    $remaining = $batch ? days_remaining_info($batch['expected_hatch_date']) : null;
                    ?>
                    <div class="col-12 col-lg-6">
                        <article class="tray-card h-100">
                            <div class="card-heading">
                                <div>
                                    <span class="eyebrow">Egg tray</span>
                                    <h2><?= e(tray_name($trayNumber)) ?></h2>
                                </div>
                                <span class="tray-number"><?= e((string) $trayNumber) ?></span>
                            </div>

                            <?php if ($batch): ?>
                                <div class="batch-highlight">
                                    <div>
                                        <span class="eyebrow">Active batch</span>
                                        <h3><?= e($batch['batch_name']) ?></h3>
                                    </div>
                                    <span class="badge badge-soft-<?= e(batch_status_class($batch['status'])) ?>"><?= e($batch['status']) ?></span>
                                </div>

                                <dl class="details-grid">
                                    <div>
                                        <dt>Egg Type</dt>
                                        <dd><?= e($batch['egg_type'] ?: 'Not specified') ?></dd>
                                    </div>
                                    <div>
                                        <dt>Quantity</dt>
                                        <dd><?= e((string) $batch['quantity']) ?> eggs</dd>
                                    </div>
                                    <div>
                                        <dt>Expected Hatch</dt>
                                        <dd><?= e(format_date_display($batch['expected_hatch_date'])) ?></dd>
                                    </div>
                                    <div>
                                        <dt>Remaining</dt>
                                        <dd class="days-<?= e($remaining['state']) ?>"><?= e($remaining['label']) ?></dd>
                                    </div>
                                </dl>

                                <a class="btn btn-primary btn-lg w-100" href="view_batch.php?id=<?= e((string) $batch['id']) ?>">View Batch Details</a>
                            <?php else: ?>
                                <div class="empty-state">
                                    <h3>Tray is available</h3>
                                    <p>Add a new batch when this tray is cleaned and ready.</p>
                                    <a class="btn btn-success btn-lg" href="add_batch.php?tray=<?= e((string) $trayNumber) ?>">Add Eggs to Tray <?= e((string) $trayNumber) ?></a>
                                </div>
                            <?php endif; ?>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <article class="table-card mt-3">
        <div class="card-heading">
            <div>
                <span class="eyebrow">Recent sensor data</span>
                <h2>Recent Readings</h2>
            </div>
            <a class="btn btn-outline-primary btn-lg" href="readings.php">View All Readings</a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Date and Time</th>
                    <th>Temperature</th>
                    <th>Humidity</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($recentReadings): ?>
                    <?php foreach ($recentReadings as $reading): ?>
                        <tr>
                            <td><?= e(format_datetime_display($reading['created_at'])) ?></td>
                            <td><strong><?= e(number_display($reading['temperature'])) ?> C</strong></td>
                            <td><strong><?= e(number_display($reading['humidity'])) ?>%</strong></td>
                            <td>
                                <?php foreach (reading_badges($reading, $settings) as $badge): ?>
                                    <span class="badge badge-soft-<?= e($badge['class']) ?>"><?= e($badge['label']) ?></span>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-4">
                            <div class="empty-table-message">No sensor readings saved yet.</div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
