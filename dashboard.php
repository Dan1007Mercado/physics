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

include __DIR__ . '/includes/header.php';
?>

<section data-dashboard="true">
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
                <small>Dashboard refreshes automatically.</small>
            </article>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <article class="stat-card status-card">
                <span class="stat-label">Incubator Status</span>
                <strong id="incubator-status" class="status-message status-<?= e($condition['level']) ?>">
                    <?= e($condition['message']) ?>
                </strong>
                <small>Warnings are based on your saved settings.</small>
            </article>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <?php foreach ([1, 2] as $trayNumber): ?>
            <?php
            $batch = $activeBatches[$trayNumber];
            $remaining = $batch ? days_remaining_info($batch['expected_hatch_date']) : null;
            ?>
            <div class="col-12 col-lg-6">
                <article class="tray-card">
                    <div class="card-heading">
                        <div>
                            <span class="eyebrow">Egg tray</span>
                            <h2><?= e(tray_name($trayNumber)) ?></h2>
                        </div>
                        <span class="tray-number"><?= e((string) $trayNumber) ?></span>
                    </div>

                    <?php if ($batch): ?>
                        <div class="batch-highlight">
                            <h3><?= e($batch['batch_name']) ?></h3>
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
                                <dt>Expected Hatch Date</dt>
                                <dd><?= e(format_date_display($batch['expected_hatch_date'])) ?></dd>
                            </div>
                            <div>
                                <dt>Days Remaining</dt>
                                <dd class="days-<?= e($remaining['state']) ?>"><?= e($remaining['label']) ?></dd>
                            </div>
                        </dl>
                        <a class="btn btn-primary btn-lg w-100" href="view_batch.php?id=<?= e((string) $batch['id']) ?>">View Details</a>
                    <?php else: ?>
                        <div class="empty-state">
                            <h3>No active batch in this tray</h3>
                            <p>Add eggs when this tray is ready for a new batch.</p>
                            <a class="btn btn-success btn-lg" href="add_batch.php?tray=<?= e((string) $trayNumber) ?>">Add Eggs to Tray</a>
                        </div>
                    <?php endif; ?>
                </article>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-4">
            <article class="alert-panel">
                <div class="card-heading">
                    <div>
                        <span class="eyebrow">Needs attention</span>
                        <h2>Alerts</h2>
                    </div>
                </div>
                <ul id="alerts-list" class="alert-list">
                    <?php if ($condition['alerts']): ?>
                        <?php foreach ($condition['alerts'] as $alert): ?>
                            <li><?= e($alert) ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No action needed right now.</li>
                    <?php endif; ?>
                </ul>
            </article>
        </div>
        <div class="col-12 col-xl-8">
            <article class="table-card">
                <div class="card-heading">
                    <div>
                        <span class="eyebrow">Recent sensor data</span>
                        <h2>Recent Readings</h2>
                    </div>
                    <a class="btn btn-outline-primary" href="readings.php">View All</a>
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
                                    <td><?= e(number_display($reading['temperature'])) ?> C</td>
                                    <td><?= e(number_display($reading['humidity'])) ?>%</td>
                                    <td>
                                        <?php foreach (reading_badges($reading, $settings) as $badge): ?>
                                            <span class="badge badge-soft-<?= e($badge['class']) ?>"><?= e($badge['label']) ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No sensor readings saved yet.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
