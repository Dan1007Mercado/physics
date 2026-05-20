<?php
require_once __DIR__ . '/api/db.php';

$pageTitle = 'Sensor Readings';
$activePage = 'readings';
$settings = get_settings($pdo);

$from = clean_text($_GET['from'] ?? '', 10);
$to = clean_text($_GET['to'] ?? '', 10);
$filters = [];
$params = [];

if ($from !== '' && is_valid_date($from)) {
    $filters[] = 'created_at >= ?';
    $params[] = $from . ' 00:00:00';
}

if ($to !== '' && is_valid_date($to)) {
    $filters[] = 'created_at <= ?';
    $params[] = $to . ' 23:59:59';
}

$sql = 'SELECT * FROM readings';
if ($filters) {
    $sql .= ' WHERE ' . implode(' AND ', $filters);
}
$sql .= ' ORDER BY created_at DESC, id DESC LIMIT 500';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$readings = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="page-section readings-page">
    <div class="hero-panel mb-3">
        <div>
            <span class="eyebrow">Sensor history</span>
            <h2>Review temperature and humidity records.</h2>
            <p>Use the date filter to check specific monitoring periods. The latest 500 matching records are shown.</p>
        </div>
        <div class="hero-actions">
            <a class="btn btn-outline-primary btn-lg" href="dashboard.php">Back to Dashboard</a>
        </div>
    </div>

    <article class="form-panel filter-panel">
        <div class="form-section-title">
            <span>🔎</span>
            <div>
                <h2>Filter readings</h2>
                <p>Leave dates empty to show the newest readings.</p>
            </div>
        </div>

        <form class="row g-3 align-items-end" method="get">
            <div class="col-12 col-md-4">
                <label class="form-label" for="from">From date</label>
                <input class="form-control form-control-lg" type="date" id="from" name="from" value="<?= e($from) ?>">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label" for="to">To date</label>
                <input class="form-control form-control-lg" type="date" id="to" name="to" value="<?= e($to) ?>">
            </div>

            <div class="col-12 col-md-4 d-grid d-md-flex gap-2">
                <button class="btn btn-primary btn-lg" type="submit">Apply Filter</button>
                <a class="btn btn-outline-secondary btn-lg" href="readings.php">Clear</a>
            </div>
        </form>
    </article>

    <article class="table-card mt-3">
        <div class="card-heading">
            <div>
                <span class="eyebrow">Newest first</span>
                <h2>All Sensor Readings</h2>
            </div>
            <span class="count-pill"><?= e((string) count($readings)) ?> records</span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle data-table">
                <thead>
                <tr>
                    <th>Date and Time</th>
                    <th>Temperature</th>
                    <th>Humidity</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($readings): ?>
                    <?php foreach ($readings as $reading): ?>
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
                            <div class="empty-table-message">No readings found for the selected date range.</div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
