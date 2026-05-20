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

<section class="page-section">
    <article class="form-panel">
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
                <button class="btn btn-primary btn-lg" type="submit">Filter</button>
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
                <?php if ($readings): ?>
                    <?php foreach ($readings as $reading): ?>
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
                        <td colspan="4" class="text-center text-muted py-4">No readings found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
