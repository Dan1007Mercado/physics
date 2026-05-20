<?php
require_once __DIR__ . '/api/db.php';

$pageTitle = 'Egg Batches';
$activePage = 'batches';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = process_batch_status_action($pdo, $_POST);
    if ($result['success']) {
        redirect_to('egg_batches.php?message=status_updated');
    }
    $errors[] = $result['message'];
}

$successMessage = message_text($_GET['message'] ?? null);
$stmt = $pdo->query('SELECT * FROM egg_batches ORDER BY created_at DESC, id DESC');
$batches = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="page-section">
    <div class="hero-panel mb-3">
        <div>
            <span class="eyebrow">Batch records</span>
            <h2>Review all recorded egg batches.</h2>
            <p>Open a batch to view details, edit information, or update its current status.</p>
        </div>
        <div class="hero-actions">
            <a class="btn btn-success btn-lg" href="add_batch.php">Add Eggs</a>
            <a class="btn btn-outline-primary btn-lg" href="trays.php">View Trays</a>
        </div>
    </div>

    <?php if ($successMessage): ?>
        <div class="alert alert-success alert-readable"><?= e($successMessage) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger alert-readable">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <article class="table-card">
        <div class="card-heading">
            <div>
                <span class="eyebrow">Newest first</span>
                <h2>All Egg Batches</h2>
            </div>
            <span class="count-pill"><?= e((string) count($batches)) ?> records</span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle data-table">
                <thead>
                <tr>
                    <th>Batch</th>
                    <th>Tray</th>
                    <th>Egg Type</th>
                    <th>Quantity</th>
                    <th>Start Date</th>
                    <th>Expected Hatch</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($batches): ?>
                    <?php foreach ($batches as $batch): ?>
                        <?php $remaining = days_remaining_info($batch['expected_hatch_date']); ?>
                        <tr>
                            <td>
                                <strong><?= e($batch['batch_name']) ?></strong>
                                <small class="table-subtext d-block">Created <?= e(format_datetime_display($batch['created_at'])) ?></small>
                            </td>
                            <td><?= e(tray_name((int) $batch['tray_number'])) ?></td>
                            <td><?= e($batch['egg_type'] ?: 'Not specified') ?></td>
                            <td><strong><?= e((string) $batch['quantity']) ?></strong> eggs</td>
                            <td><?= e(format_date_display($batch['start_date'])) ?></td>
                            <td>
                                <?= e(format_date_display($batch['expected_hatch_date'])) ?>
                                <small class="table-subtext d-block days-<?= e($remaining['state']) ?>"><?= e($remaining['label']) ?></small>
                            </td>
                            <td><span class="badge badge-soft-<?= e(batch_status_class($batch['status'])) ?>"><?= e($batch['status']) ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn-sm btn-primary" href="view_batch.php?id=<?= e((string) $batch['id']) ?>">View</a>
                                    <a class="btn btn-sm btn-outline-primary" href="edit_batch.php?id=<?= e((string) $batch['id']) ?>">Edit</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="empty-table-message">No egg batches recorded yet.</div>
                            <a class="btn btn-success btn-lg mt-2" href="add_batch.php">Add First Batch</a>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
