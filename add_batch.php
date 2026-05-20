<?php
require_once __DIR__ . '/api/db.php';

$pageTitle = 'Add Eggs';
$activePage = 'batches';
$errors = [];
$activeBatches = get_active_batches_by_tray($pdo);
$selectedTray = isset($_GET['tray']) ? (int) $_GET['tray'] : 1;
if (!valid_tray_number($selectedTray)) {
    $selectedTray = 1;
}

$form = [
    'tray_number' => $selectedTray,
    'batch_name' => '',
    'egg_type' => '',
    'quantity' => '',
    'start_date' => date('Y-m-d'),
    'incubation_days' => 21,
    'notes' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validation = validate_batch_form($pdo, $_POST);
    $errors = $validation['errors'];
    $form = $validation['data'];

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO egg_batches (batch_name, tray_number, egg_type, quantity, start_date, incubation_days, expected_hatch_date, status, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $form['batch_name'],
            $form['tray_number'],
            $form['egg_type'],
            $form['quantity'],
            $form['start_date'],
            $form['incubation_days'],
            $form['expected_hatch_date'],
            'Incubating',
            $form['notes'],
        ]);

        redirect_to('trays.php?message=batch_added');
    }
}

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
                        <?php $occupiedText = $activeBatches[$trayNumber] ? ' - occupied' : ''; ?>
                        <option value="<?= e((string) $trayNumber) ?>" <?= (int) $form['tray_number'] === $trayNumber ? 'selected' : '' ?>>
                            <?= e(tray_name($trayNumber) . $occupiedText) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="batch_name">Batch name</label>
                <input class="form-control form-control-lg" type="text" id="batch_name" name="batch_name" value="<?= e($form['batch_name']) ?>" maxlength="100" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="egg_type">Egg type</label>
                <input class="form-control form-control-lg" type="text" id="egg_type" name="egg_type" value="<?= e($form['egg_type']) ?>" maxlength="50" placeholder="Chicken, duck, quail">
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
            <div class="col-12">
                <label class="form-label" for="notes">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="4"><?= e($form['notes']) ?></textarea>
            </div>
            <div class="col-12 action-row">
                <button class="btn btn-success btn-lg" type="submit">Save Batch</button>
                <a class="btn btn-outline-secondary btn-lg" href="trays.php">Cancel</a>
            </div>
        </form>
    </article>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
