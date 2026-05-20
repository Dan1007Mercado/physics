<?php
require_once __DIR__ . '/api/db.php';

$pageTitle = 'Batch Details';
$activePage = 'batches';
$batchId = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['batch_id']) ? (int) $_POST['batch_id'] : 0);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = process_batch_status_action($pdo, $_POST);
    if ($result['success']) {
        redirect_to('view_batch.php?id=' . (int) $result['batch_id'] . '&message=status_updated');
    }
    $errors[] = $result['message'];
}

$batch = $batchId > 0 ? get_batch_by_id($pdo, $batchId) : null;
$successMessage = message_text($_GET['message'] ?? null);

include __DIR__ . '/includes/header.php';
?>

<section class="page-section">
    <?php if (!$batch): ?>
        <div class="alert alert-danger">Batch not found.</div>
        <a class="btn btn-primary btn-lg" href="egg_batches.php">Back to Egg Batches</a>
    <?php else: ?>
        <?php $remaining = days_remaining_info($batch['expected_hatch_date']); ?>

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

        <article class="detail-panel">
            <div class="card-heading">
                <div>
                    <span class="eyebrow"><?= e(tray_name((int) $batch['tray_number'])) ?></span>
                    <h2><?= e($batch['batch_name']) ?></h2>
                </div>
                <span class="badge badge-soft-<?= e(batch_status_class($batch['status'])) ?>"><?= e($batch['status']) ?></span>
            </div>

            <dl class="details-grid details-grid-large">
                <div>
                    <dt>Egg Type</dt>
                    <dd><?= e($batch['egg_type'] ?: 'Not specified') ?></dd>
                </div>
                <div>
                    <dt>Quantity</dt>
                    <dd><?= e((string) $batch['quantity']) ?> eggs</dd>
                </div>
                <div>
                    <dt>Start Date</dt>
                    <dd><?= e(format_date_display($batch['start_date'])) ?></dd>
                </div>
                <div>
                    <dt>Incubation Days</dt>
                    <dd><?= e((string) $batch['incubation_days']) ?> days</dd>
                </div>
                <div>
                    <dt>Expected Hatch Date</dt>
                    <dd><?= e(format_date_display($batch['expected_hatch_date'])) ?></dd>
                </div>
                <div>
                    <dt>Days Remaining</dt>
                    <dd class="days-<?= e($remaining['state']) ?>"><?= e($remaining['label']) ?></dd>
                </div>
                <div>
                    <dt>Created</dt>
                    <dd><?= e(format_datetime_display($batch['created_at'])) ?></dd>
                </div>
                <div>
                    <dt>Last Updated</dt>
                    <dd><?= e(format_datetime_display($batch['updated_at'])) ?></dd>
                </div>
            </dl>

            <div class="notes-box">
                <h3>Notes</h3>
                <p><?= $batch['notes'] ? nl2br(e($batch['notes'])) : 'No notes recorded.' ?></p>
            </div>

            <div class="action-row">
                <a class="btn btn-primary btn-lg" href="edit_batch.php?id=<?= e((string) $batch['id']) ?>">Edit Batch</a>
                <?php foreach (status_action_map() as $action => $status): ?>
                    <?php if ($batch['status'] !== $status): ?>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="batch_id" value="<?= e((string) $batch['id']) ?>">
                            <input type="hidden" name="action" value="<?= e($action) ?>">
                            <button class="btn btn-lg <?= $action === 'failed' ? 'btn-outline-danger' : 'btn-outline-secondary' ?>" type="submit">
                                <?= e(status_action_label($action)) ?>
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endforeach; ?>
                <a class="btn btn-outline-secondary btn-lg" href="egg_batches.php">Back</a>
            </div>
        </article>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
