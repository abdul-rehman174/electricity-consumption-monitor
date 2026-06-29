<?php
/**
 * Bills page.
 *   FR-10 : current estimated bill with a per-appliance breakdown (kWh + PKR).
 *   FR-11 : monthly bill history so users can compare across months.
 * Also shows the WAPDA slab breakdown so the calculation is transparent.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user_id = current_user_id();

// ---- Current appliances and per-appliance figures ----
$stmt = mysqli_prepare($conn, 'SELECT name, wattage, hours_per_day FROM appliances WHERE user_id = ? ORDER BY name');
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$rows = [];
$total_units = 0.0;
while ($row = mysqli_fetch_assoc($res)) {
    $kwh = round(monthly_kwh($row['wattage'], $row['hours_per_day']), 2);
    $total_units += $kwh;
    $rows[] = ['name' => $row['name'], 'kwh' => $kwh];
}
mysqli_stmt_close($stmt);
$total_units    = round($total_units, 2);
$estimated_bill = calculate_bill($total_units);
$slab_rows      = bill_breakdown($total_units);

// Per-appliance PKR share: proportional to its kWh contribution (FR-10).
foreach ($rows as &$r) {
    $r['pkr'] = $total_units > 0 ? round(($r['kwh'] / $total_units) * $estimated_bill, 2) : 0;
}
unset($r);

// ---- FR-11: bill history ----
$stmt = mysqli_prepare(
    $conn,
    'SELECT month, year, total_units, total_bill FROM monthly_bills
     WHERE user_id = ? ORDER BY year DESC, month DESC'
);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$history = [];
while ($row = mysqli_fetch_assoc($res)) {
    $history[] = $row;
}
mysqli_stmt_close($stmt);

$page_title = 'Bills';
require __DIR__ . '/../includes/header.php';
?>
<h3 class="mb-3"><i class="bi bi-receipt text-success"></i> Bill Summary</h3>

<div class="row g-3">
    <!-- Per-appliance breakdown -->
    <div class="col-lg-7">
        <div class="card"><div class="card-body">
            <h6 class="card-title">This Month — Per Appliance</h6>
            <?php if (!$rows): ?>
                <p class="text-muted mb-0">No appliances added yet.</p>
            <?php else: ?>
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light"><tr><th>Appliance</th><th class="text-end">kWh</th><th class="text-end">PKR</th></tr></thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= e($r['name']) ?></td>
                        <td class="text-end"><?= e(number_format($r['kwh'], 2)) ?></td>
                        <td class="text-end"><?= e(number_format($r['pkr'], 2)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light"><tr>
                    <th>Total</th>
                    <th class="text-end"><?= e(number_format($total_units, 2)) ?></th>
                    <th class="text-end"><?= e(number_format($estimated_bill, 2)) ?></th>
                </tr></tfoot>
            </table>
            <?php endif; ?>
        </div></div>
    </div>

    <!-- WAPDA slab breakdown -->
    <div class="col-lg-5">
        <div class="card"><div class="card-body">
            <h6 class="card-title">WAPDA Slab Breakdown</h6>
            <?php if (!$slab_rows): ?>
                <p class="text-muted mb-0">No consumption to bill.</p>
            <?php else: ?>
            <table class="table table-sm mb-2">
                <thead class="table-light"><tr><th>Units</th><th class="text-end">Rate</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                    <?php foreach ($slab_rows as $s): ?>
                    <tr>
                        <td><?= e($s['range']) ?> <span class="text-muted">(<?= e(number_format($s['units'], 1)) ?>)</span></td>
                        <td class="text-end"><?= e(number_format($s['rate'], 2)) ?></td>
                        <td class="text-end"><?= e(number_format($s['amount'], 2)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-between border-top pt-2">
                <strong>Estimated Bill</strong>
                <strong>PKR <?= e(number_format($estimated_bill, 2)) ?></strong>
            </div>
            <?php endif; ?>
        </div></div>
    </div>
</div>

<!-- FR-11: history -->
<div class="card mt-3"><div class="card-body">
    <h6 class="card-title">Monthly History</h6>
    <?php if (!$history): ?>
        <p class="text-muted mb-0">No saved months yet. Visit the dashboard to record this month.</p>
    <?php else: ?>
    <table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th>Month</th><th class="text-end">Units (kWh)</th><th class="text-end">Bill (PKR)</th></tr></thead>
        <tbody>
            <?php foreach ($history as $h):
                $label = date('F Y', mktime(0, 0, 0, (int) $h['month'], 1, (int) $h['year'])); ?>
            <tr>
                <td><?= e($label) ?></td>
                <td class="text-end"><?= e(number_format($h['total_units'], 2)) ?></td>
                <td class="text-end"><?= e(number_format($h['total_bill'], 2)) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
