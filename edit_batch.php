<?php
require_once __DIR__ . '/api/db.php';

$pageTitle = 'Edit Batch';
$activePage = 'batches';
$batchId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$batch = $batchId > 0 ? get_batch_by_id($pdo, $batchId) : null;
$errors = [];

if (!$batch) {
    include __DIR__ . '/includes/header.php';
    ?>
    <section class="page-section">
        <div class="alert alert-danger">Batch not found.</div>
        <a class="btn btn-primary btn-lg" href="egg_batches.php">Back to Egg Batches</a>
    </section>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$form = [
    'tray_number' => (int) $batch['tray_number'],
    'batch_name' => $batch['batch_name'],
    'egg_type' => $batch['egg_type'],
    'quantity' => (int) $batch['quantity'],
    'start_date' => $batch['start_date'],
    'incubation_days' => (int) $batch['incubation_days'],
    'expected_hatch_date' => $batch['expected_hatch_date'],
    'status' => $batch['status'],
    'notes' => $batch['notes'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validation = validate_batch_form($pdo, $_POST, $batchId, true);
    $errors = $validation['errors'];
    $form = $validation['data'];

    if (!$errors) {
        $stmt = $pdo->prepare(
            'UPDATE egg_batches
             SET batch_name = ?, tray_number = ?, egg_type = ?, quantity = ?, start_date = ?, incubation_days = ?, expected_hatch_date = ?, status = ?, notes = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $form['batch_name'],
            $form['tray_number'],
            $form['egg_type'],
            $form['quantity'],
            $form['start_date'],
            $form['incubation_days'],
            $form['expected_hatch_date'],
            $form['status'],
            $form['notes'],
            $batchId,
        ]);

        redirect_to('view_batch.php?id=' . $batchId . '&message=batch_updated');
    }
}

$activeBatches = get_active_batches_by_tray($pdo);

include __DIR__ . '/includes/header.php';
?>

<section class="page-section narrow-section">
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <article class="form-panel">
        <form method="post" class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label" for="tray_number">Tray number</label>
                <select class="form-select form-select-lg" id="tray_number" name="tray_number" required>
                    <?php foreach ([1, 2] as $trayNumber): ?>
                        <?php
                        $activeBatch = $activeBatches[$trayNumber];
                        $occupiedText = ($activeBatch && (int) $activeBatch['id'] !== $batchId) ? ' - occupied' : '';
                        ?>
                        <option value="<?= e((string) $trayNumber) ?>" <?= (int) $form['tray_number'] === $trayNumber ? 'selected' : '' ?>>
                            <?= e(tray_name($trayNumber) . $occupiedText) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="status">Status</label>
                <select class="form-select form-select-lg" id="status" name="status" required>
                    <?php foreach (allowed_batch_statuses() as $status): ?>
                        <option value="<?= e($status) ?>" <?= $form['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="batch_name">Batch name</label>
                <input class="form-control form-control-lg" type="text" id="batch_name" name="batch_name" value="<?= e($form['batch_name']) ?>" maxlength="100" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="egg_type">Egg type</label>
                <input class="form-control form-control-lg" type="text" id="egg_type" name="egg_type" value="<?= e($form['egg_type']) ?>" maxlength="50">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="quantity">Quantity</label>
                <input class="form-control form-control-lg" type="number" id="quantity" name="quantity" value="<?= e((string) $form['quantity']) ?>" min="1" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="start_date">Start date</label>
                <input class="form-control form-control-lg" type="date" id="start_date" name="start_date" value="<?= e($form['start_date']) ?>" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="incubation_days">Incubation days</label>
                <input class="form-control form-control-lg" type="number" id="incubation_days" name="incubation_days" value="<?= e((string) $form['incubation_days']) ?>" min="1" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label">Expected hatch date</label>
                <div class="readonly-box"><?= e(format_date_display($form['expected_hatch_date'])) ?></div>
            </div>
            <div class="col-12">
                <label class="form-label" for="notes">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="4"><?= e($form['notes']) ?></textarea>
            </div>
            <div class="col-12 action-row">
                <button class="btn btn-success btn-lg" type="submit">Save Batch</button>
                <a class="btn btn-outline-secondary btn-lg" href="view_batch.php?id=<?= e((string) $batchId) ?>">Cancel</a>
            </div>
        </form>
    </article>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
