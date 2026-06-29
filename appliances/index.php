<?php
/**
 * FR-07: View Appliance List — shows every appliance the user has added
 * along with its estimated monthly consumption in kWh (FR-08).
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user_id = current_user_id();

$stmt = mysqli_prepare(
    $conn,
    'SELECT id, name, wattage, hours_per_day FROM appliances WHERE user_id = ? ORDER BY name'
);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$appliances = [];
$total_units = 0.0;
while ($row = mysqli_fetch_assoc($res)) {
    $row['kwh'] = round(monthly_kwh($row['wattage'], $row['hours_per_day']), 2);
    $total_units += $row['kwh'];
    $appliances[] = $row;
}
mysqli_stmt_close($stmt);

$page_title = 'Appliances';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-plug text-success"></i> My Appliances</h3>
    <a href="<?= BASE_URL ?>/appliances/add.php" class="btn btn-ecms"><i class="bi bi-plus-lg"></i> Add Appliance</a>
</div>

<?php if (!$appliances): ?>
    <div class="card"><div class="card-body text-center text-muted py-5">
        <i class="bi bi-inbox fs-1"></i>
        <p class="mt-2 mb-0">No appliances yet. Add your first appliance to start estimating your bill.</p>
    </div></div>
<?php else: ?>
    <div class="card"><div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Appliance</th>
                    <th class="text-end">Wattage (W)</th>
                    <th class="text-end">Hours / day</th>
                    <th class="text-end">Monthly kWh</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appliances as $a): ?>
                    <tr>
                        <td><?= e($a['name']) ?></td>
                        <td class="text-end"><?= e(number_format($a['wattage'], 2)) ?></td>
                        <td class="text-end"><?= e(number_format($a['hours_per_day'], 2)) ?></td>
                        <td class="text-end fw-semibold"><?= e(number_format($a['kwh'], 2)) ?></td>
                        <td class="text-end">
                            <a href="<?= BASE_URL ?>/appliances/edit.php?id=<?= (int) $a['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <a href="<?= BASE_URL ?>/appliances/delete.php?id=<?= (int) $a['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete this appliance? It will be removed from future calculations.');"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="3" class="text-end">Total estimated monthly consumption</th>
                    <th class="text-end"><?= e(number_format($total_units, 2)) ?> kWh</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div></div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
