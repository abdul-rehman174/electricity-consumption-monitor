<?php
/** FR-05: Edit Appliance — update name, wattage or daily usage. */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user_id = current_user_id();
$id      = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$errors  = [];

// Load the appliance, scoped to the current user (prevents editing others' data).
$stmt = mysqli_prepare(
    $conn,
    'SELECT id, name, wattage, hours_per_day FROM appliances WHERE id = ? AND user_id = ?'
);
mysqli_stmt_bind_param($stmt, 'ii', $id, $user_id);
mysqli_stmt_execute($stmt);
$res        = mysqli_stmt_get_result($stmt);
$appliance  = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$appliance) {
    set_flash('warning', 'Appliance not found.');
    redirect('appliances/index.php');
}

$name    = $appliance['name'];
$wattage = $appliance['wattage'];
$hours   = $appliance['hours_per_day'];

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
            'UPDATE appliances SET name = ?, wattage = ?, hours_per_day = ? WHERE id = ? AND user_id = ?'
        );
        mysqli_stmt_bind_param($stmt, 'sddii', $name, $w, $h, $id, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            set_flash('success', 'Appliance updated.');
            redirect('appliances/index.php');
        } else {
            $errors[] = 'Could not update the appliance. Please try again.';
            mysqli_stmt_close($stmt);
        }
    }
}

$page_title = 'Edit Appliance';
require __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <h3 class="mb-3"><i class="bi bi-pencil-square text-success"></i> Edit Appliance</h3>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="card"><div class="card-body p-4">
            <form method="post" novalidate>
                <input type="hidden" name="id" value="<?= (int) $id ?>">
                <div class="mb-3">
                    <label class="form-label">Appliance Name</label>
                    <input type="text" name="name" class="form-control" value="<?= e($name) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Rated Wattage (Watts)</label>
                    <input type="number" step="0.01" min="0.01" name="wattage" class="form-control" value="<?= e($wattage) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Average Daily Usage (hours)</label>
                    <input type="number" step="0.01" min="0" max="24" name="hours_per_day" class="form-control" value="<?= e($hours) ?>" required>
                </div>
                <button type="submit" class="btn btn-ecms"><i class="bi bi-check-lg"></i> Update</button>
                <a href="<?= BASE_URL ?>/appliances/index.php" class="btn btn-link">Cancel</a>
            </form>
        </div></div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
