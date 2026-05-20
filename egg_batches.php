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

    <article class="table-card">
        <div class="card-heading">
            <div>
                <span class="eyebrow">Batch records</span>
                <h2>All Egg Batches</h2>
            </div>
            <a class="btn btn-success btn-lg" href="add_batch.php">Add Eggs</a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Batch Name</th>
                    <th>Tray</th>
                    <th>Egg Type</th>
                    <th>Quantity</th>
                    <th>Start Date</th>
                    <th>Expected Hatch Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($batches): ?>
                    <?php foreach ($batches as $batch): ?>
                        <tr>
                            <td><strong><?= e($batch['batch_name']) ?></strong></td>
                            <td><?= e(tray_name((int) $batch['tray_number'])) ?></td>
                            <td><?= e($batch['egg_type'] ?: 'Not specified') ?></td>
                            <td><?= e((string) $batch['quantity']) ?></td>
                            <td><?= e(format_date_display($batch['start_date'])) ?></td>
                            <td><?= e(format_date_display($batch['expected_hatch_date'])) ?></td>
                            <td><span class="badge badge-soft-<?= e(batch_status_class($batch['status'])) ?>"><?= e($batch['status']) ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn-sm btn-primary" href="view_batch.php?id=<?= e((string) $batch['id']) ?>">View Details</a>
                                    <a class="btn btn-sm btn-outline-primary" href="edit_batch.php?id=<?= e((string) $batch['id']) ?>">Edit Batch</a>
                                    <?php if ($batch['status'] === 'Incubating'): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="batch_id" value="<?= e((string) $batch['id']) ?>">
                                            <input type="hidden" name="action" value="hatched">
                                            <button class="btn btn-sm btn-outline-success" type="submit">Mark as Hatched</button>
                                        </form>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="batch_id" value="<?= e((string) $batch['id']) ?>">
                                            <input type="hidden" name="action" value="removed">
                                            <button class="btn btn-sm btn-outline-secondary" type="submit">Remove Batch</button>
                                        </form>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="batch_id" value="<?= e((string) $batch['id']) ?>">
                                            <input type="hidden" name="action" value="failed">
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Mark as Failed</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No egg batches recorded yet.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
