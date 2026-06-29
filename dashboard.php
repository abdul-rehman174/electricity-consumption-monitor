<?php
/**
 * Dashboard — the main screen after login.
 *   FR-08/FR-09 : compute total kWh and estimated bill (progressive WAPDA slabs).
 *   FR-11       : auto-save the current month's bill record.
 *   FR-12       : summary cards + top energy-consuming appliances.
 *   FR-13       : monthly consumption trend chart (Chart.js).
 *   FR-14       : per-appliance breakdown chart.
 *   FR-16       : raise a budget alert when the estimate exceeds the user's limit.
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$user_id = current_user_id();

// ---- Load the user's budget limit ----
$stmt = mysqli_prepare($conn, 'SELECT name, bill_limit FROM users WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res  = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);
$bill_limit = (float) $user['bill_limit'];

// ---- Load appliances and compute consumption ----
$stmt = mysqli_prepare($conn, 'SELECT name, wattage, hours_per_day FROM appliances WHERE user_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$appliances  = [];
$total_units = 0.0;
while ($row = mysqli_fetch_assoc($res)) {
    $kwh = monthly_kwh($row['wattage'], $row['hours_per_day']);
    $total_units += $kwh;
    $appliances[] = ['name' => $row['name'], 'kwh' => round($kwh, 2)];
}
mysqli_stmt_close($stmt);
$total_units = round($total_units, 2);

$estimated_bill = calculate_bill($total_units);
$appliance_count = count($appliances);

// Top energy-consuming appliances (FR-12).
usort($appliances, fn($a, $b) => $b['kwh'] <=> $a['kwh']);
$top_appliances = array_slice($appliances, 0, 5);

// ---- FR-11: save / update this month's bill record ----
$cur_month = (int) date('n');
$cur_year  = (int) date('Y');
if ($appliance_count > 0) {
    $stmt = mysqli_prepare(
        $conn,
        'INSERT INTO monthly_bills (user_id, month, year, total_units, total_bill)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE total_units = VALUES(total_units), total_bill = VALUES(total_bill)'
    );
    mysqli_stmt_bind_param($stmt, 'iiidd', $user_id, $cur_month, $cur_year, $total_units, $estimated_bill);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ---- FR-16: budget alert (one per month, only when exceeded) ----
$over_budget = $bill_limit > 0 && $estimated_bill > $bill_limit;
if ($over_budget) {
    $period = date('F Y', mktime(0, 0, 0, $cur_month, 1, $cur_year));
    $like   = '%' . $period . '%';
    $stmt = mysqli_prepare(
        $conn,
        'SELECT id FROM alerts WHERE user_id = ? AND message LIKE ?'
    );
    mysqli_stmt_bind_param($stmt, 'is', $user_id, $like);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $already = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if (!$already) {
        $message = sprintf(
            'Your estimated bill for %s (PKR %s) has exceeded your budget limit of PKR %s.',
            $period,
            number_format($estimated_bill, 2),
            number_format($bill_limit, 2)
        );
        $stmt = mysqli_prepare($conn, 'INSERT INTO alerts (user_id, message) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'is', $user_id, $message);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// ---- FR-13: last 6 months of history for the trend chart ----
$stmt = mysqli_prepare(
    $conn,
    'SELECT month, year, total_units, total_bill FROM monthly_bills
     WHERE user_id = ? ORDER BY year DESC, month DESC LIMIT 6'
);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$history = [];
while ($row = mysqli_fetch_assoc($res)) {
    $history[] = $row;
}
mysqli_stmt_close($stmt);
$history = array_reverse($history); // chronological order for the chart

$trend_labels = [];
$trend_units  = [];
foreach ($history as $h) {
    $trend_labels[] = date('M Y', mktime(0, 0, 0, (int) $h['month'], 1, (int) $h['year']));
    $trend_units[]  = (float) $h['total_units'];
}

// Breakdown chart data (FR-14).
$break_labels = array_map(fn($a) => $a['name'], $top_appliances);
$break_values = array_map(fn($a) => $a['kwh'], $top_appliances);

$page_title = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<h3 class="mb-3"><i class="bi bi-speedometer2 text-success"></i> Dashboard</h3>

<?php if ($over_budget): ?>
    <div class="alert alert-danger d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>Your estimated bill of <strong>PKR <?= e(number_format($estimated_bill, 2)) ?></strong>
        has exceeded your budget limit of <strong>PKR <?= e(number_format($bill_limit, 2)) ?></strong>.</div>
    </div>
<?php endif; ?>

<!-- FR-12: summary cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card"><div class="card-body d-flex justify-content-between align-items-center">
            <div><div class="text-muted small">Total Units</div><div class="stat-value"><?= e(number_format($total_units, 2)) ?></div><div class="text-muted small">kWh / month</div></div>
            <i class="bi bi-lightning-charge stat-icon text-success"></i>
        </div></div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card"><div class="card-body d-flex justify-content-between align-items-center">
            <div><div class="text-muted small">Estimated Bill</div><div class="stat-value">₨ <?= e(number_format($estimated_bill, 0)) ?></div><div class="text-muted small">this month</div></div>
            <i class="bi bi-receipt stat-icon text-success"></i>
        </div></div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card"><div class="card-body d-flex justify-content-between align-items-center">
            <div><div class="text-muted small">Appliances</div><div class="stat-value"><?= e($appliance_count) ?></div><div class="text-muted small">registered</div></div>
            <i class="bi bi-plug stat-icon text-success"></i>
        </div></div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card"><div class="card-body d-flex justify-content-between align-items-center">
            <div><div class="text-muted small">Budget Limit</div><div class="stat-value">₨ <?= e(number_format($bill_limit, 0)) ?></div><div class="text-muted small">monthly</div></div>
            <i class="bi bi-wallet2 stat-icon text-success"></i>
        </div></div>
    </div>
</div>

<?php if ($appliance_count === 0): ?>
    <div class="card"><div class="card-body text-center text-muted py-5">
        <i class="bi bi-plug fs-1"></i>
        <p class="mt-2">No appliances yet. Add some to see your consumption and estimated bill.</p>
        <a href="<?= BASE_URL ?>/appliances/add.php" class="btn btn-ecms">Add your first appliance</a>
    </div></div>
<?php else: ?>
<div class="row g-3">
    <!-- FR-13: trend chart -->
    <div class="col-lg-7">
        <div class="card h-100"><div class="card-body">
            <h6 class="card-title">Monthly Consumption Trend</h6>
            <canvas id="trendChart" height="160"></canvas>
        </div></div>
    </div>
    <!-- FR-14: appliance breakdown -->
    <div class="col-lg-5">
        <div class="card h-100"><div class="card-body">
            <h6 class="card-title">Appliance Breakdown (kWh)</h6>
            <canvas id="breakdownChart" height="200"></canvas>
        </div></div>
    </div>
</div>

<!-- Top consumers (FR-12) -->
<div class="card mt-3"><div class="card-body">
    <h6 class="card-title">Top Energy-Consuming Appliances</h6>
    <table class="table table-sm mb-0">
        <thead><tr><th>#</th><th>Appliance</th><th class="text-end">Monthly kWh</th><th class="text-end">Share</th></tr></thead>
        <tbody>
        <?php foreach ($top_appliances as $i => $a):
            $share = $total_units > 0 ? ($a['kwh'] / $total_units) * 100 : 0; ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= e($a['name']) ?></td>
                <td class="text-end"><?= e(number_format($a['kwh'], 2)) ?></td>
                <td class="text-end"><?= e(number_format($share, 1)) ?>%</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div></div>

<script>
// Data passed from PHP to Chart.js
const trendLabels = <?= json_encode($trend_labels) ?>;
const trendUnits  = <?= json_encode($trend_units) ?>;
const breakLabels = <?= json_encode($break_labels) ?>;
const breakValues = <?= json_encode($break_values) ?>;

// FR-13: monthly consumption trend
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: trendLabels,
        datasets: [{
            label: 'Units (kWh)',
            data: trendUnits,
            borderColor: '#1B5E20',
            backgroundColor: 'rgba(46,125,50,0.15)',
            fill: true,
            tension: 0.3,
            pointRadius: 4
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'kWh' } },
            x: { title: { display: true, text: 'Month' } }
        },
        plugins: { legend: { display: true } }
    }
});

// FR-14: appliance breakdown pie
new Chart(document.getElementById('breakdownChart'), {
    type: 'pie',
    data: {
        labels: breakLabels,
        datasets: [{
            data: breakValues,
            backgroundColor: ['#1B5E20','#2e7d32','#43a047','#66bb6a','#a5d6a7']
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});
</script>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
