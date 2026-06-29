<?php
/** FR-04: Add Appliance — name, rated wattage (W) and daily usage (hours). */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user_id = current_user_id();
$errors  = [];
$name = $wattage = $hours = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $wattage = trim($_POST['wattage'] ?? '');
    $hours   = trim($_POST['hours_per_day'] ?? '');

    if ($name === '') {
        $errors[] = 'Appliance name is required.';
    }
    if (!is_numeric($wattage) || $wattage <= 0) {
        $errors[] = 'Wattage must be a number greater than 0.';
    }
    if (!is_numeric($hours) || $hours < 0 || $hours > 24) {
        $errors[] = 'Daily usage must be between 0 and 24 hours.';
    }

    if (!$errors) {
        $w = (float) $wattage;
        $h = (float) $hours;
        $stmt = mysqli_prepare(
            $conn,
            'INSERT INTO appliances (user_id, name, wattage, hours_per_day) VALUES (?, ?, ?, ?)'
        );
        mysqli_stmt_bind_param($stmt, 'isdd', $user_id, $name, $w, $h);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            set_flash('success', 'Appliance added.');
            redirect('appliances/index.php');
        } else {
            $errors[] = 'Could not save the appliance. Please try again.';
            mysqli_stmt_close($stmt);
        }
    }
}

$page_title = 'Add Appliance';
require __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <h3 class="mb-3"><i class="bi bi-plus-circle text-success"></i> Add Appliance</h3>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="card"><div class="card-body p-4">
            <form method="post" novalidate>
                <div class="mb-3">
                    <label class="form-label">Appliance Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Air Conditioner" value="<?= e($name) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Rated Wattage (Watts)</label>
                    <input type="number" step="0.01" min="0.01" name="wattage" class="form-control" placeholder="e.g. 1500" value="<?= e($wattage) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Average Daily Usage (hours)</label>
                    <input type="number" step="0.01" min="0" max="24" name="hours_per_day" class="form-control" placeholder="e.g. 6" value="<?= e($hours) ?>" required>
                </div>
                <button type="submit" class="btn btn-ecms"><i class="bi bi-check-lg"></i> Save</button>
                <a href="<?= BASE_URL ?>/appliances/index.php" class="btn btn-link">Cancel</a>
            </form>
        </div></div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
