<?php
require_once __DIR__ . '/api/db.php';

$pageTitle = 'Egg Trays';
$activePage = 'trays';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = process_batch_status_action($pdo, $_POST);
    if ($result['success']) {
        redirect_to('trays.php?message=status_updated');
    }
    $errors[] = $result['message'];
}

$successMessage = message_text($_GET['message'] ?? null);
$activeBatches = get_active_batches_by_tray($pdo);

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

    <div class="row g-3">
        <?php foreach ([1, 2] as $trayNumber): ?>
            <?php
            $batch = $activeBatches[$trayNumber];
            $remaining = $batch ? days_remaining_info($batch['expected_hatch_date']) : null;
            ?>
            <div class="col-12 col-xl-6">
                <article class="tray-card tray-card-large">
                    <div class="card-heading">
                        <div>
                            <span class="eyebrow">Incubator tray</span>
                            <h2><?= e(tray_name($trayNumber)) ?></h2>
                        </div>
                        <span class="tray-number"><?= e((string) $trayNumber) ?></span>
                    </div>

                    <?php if ($batch): ?>
                        <div class="batch-highlight">
                            <h3><?= e($batch['batch_name']) ?></h3>
                            <span class="badge badge-soft-warning">Incubating</span>
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
                                <dt>Start Date</dt>
                                <dd><?= e(format_date_display($batch['start_date'])) ?></dd>
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
                                <dt>Status</dt>
                                <dd><?= e($batch['status']) ?></dd>
                            </div>
                        </dl>

                        <div class="notes-box compact">
                            <h3>Notes</h3>
                            <p><?= $batch['notes'] ? nl2br(e($batch['notes'])) : 'No notes recorded.' ?></p>
                        </div>

                        <div class="action-row">
                            <a class="btn btn-primary btn-lg" href="view_batch.php?id=<?= e((string) $batch['id']) ?>">View Details</a>
                            <a class="btn btn-outline-primary btn-lg" href="edit_batch.php?id=<?= e((string) $batch['id']) ?>">Edit Batch</a>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="batch_id" value="<?= e((string) $batch['id']) ?>">
                                <input type="hidden" name="action" value="hatched">
                                <button class="btn btn-success btn-lg" type="submit">Mark as Hatched</button>
                            </form>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="batch_id" value="<?= e((string) $batch['id']) ?>">
                                <input type="hidden" name="action" value="removed">
                                <button class="btn btn-outline-secondary btn-lg" type="submit">Remove Batch</button>
                            </form>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="batch_id" value="<?= e((string) $batch['id']) ?>">
                                <input type="hidden" name="action" value="failed">
                                <button class="btn btn-outline-danger btn-lg" type="submit">Mark as Failed</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="empty-state tall-empty">
                            <h3>No active batch in this tray</h3>
                            <p>This tray is available for a new Incubating batch.</p>
                            <a class="btn btn-success btn-lg" href="add_batch.php?tray=<?= e((string) $trayNumber) ?>">Add Eggs to Tray</a>
                        </div>
                    <?php endif; ?>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
