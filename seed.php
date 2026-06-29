<?php
/**
 * Demo data seeder (re-runnable).
 *
 * Creates:
 *   - a demo household user  (demo@ecms.local / demo123)  with a realistic
 *     appliance set and 5 months of bill history + a past alert
 *   - two extra users so the admin panel has something to show
 *
 * Run from the command line:   php electricity-consumption-monitor/seed.php
 * Safe to run multiple times — it removes any previous demo accounts first.
 *
 * Delete this file before any real deployment.
 */
require __DIR__ . '/config/db.php';
require __DIR__ . '/config/tariff.php';

/** Insert a user, returning its new id. */
function make_user($conn, $name, $email, $password, $limit, $role)
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare(
        $conn,
        'INSERT INTO users (name, email, password, bill_limit, role) VALUES (?, ?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param($stmt, 'sssds', $name, $email, $hash, $limit, $role);
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $id;
}

// ---- Clean up previous demo accounts (cascade clears their data) ----
$demo_emails = ['demo@ecms.local', 'ayesha@ecms.local', 'bilal@ecms.local'];
$in = "'" . implode("','", array_map(fn($e) => mysqli_real_escape_string($conn, $e), $demo_emails)) . "'";
mysqli_query($conn, "DELETE FROM users WHERE email IN ($in)");

// ---- Main demo user ----
$demo_id = make_user($conn, 'Demo Household', 'demo@ecms.local', 'demo123', 18000, 'user');

// ---- Appliances (name, wattage, hours/day) ----
$appliances = [
    ['Air Conditioner', 1500, 6],
    ['Refrigerator',     200, 24],
    ['Ceiling Fans (x4)', 300, 12],
    ['LED Lights',         60, 8],
    ['Washing Machine',   500, 1],
    ['Water Motor Pump',  750, 2],
    ['LED Television',    100, 5],
];
$stmt = mysqli_prepare(
    $conn,
    'INSERT INTO appliances (user_id, name, wattage, hours_per_day) VALUES (?, ?, ?, ?)'
);
foreach ($appliances as $a) {
    mysqli_stmt_bind_param($stmt, 'isdd', $demo_id, $a[0], $a[1], $a[2]);
    mysqli_stmt_execute($stmt);
}
mysqli_stmt_close($stmt);

// ---- Bill history: Jan–May 2026 with varying consumption ----
$history_units = [1 => 412.5, 2 => 458.0, 3 => 521.4, 4 => 487.9, 5 => 553.2];
$stmt = mysqli_prepare(
    $conn,
    'INSERT INTO monthly_bills (user_id, month, year, total_units, total_bill) VALUES (?, ?, ?, ?, ?)'
);
foreach ($history_units as $month => $units) {
    $bill = calculate_bill($units);
    $year = 2026;
    mysqli_stmt_bind_param($stmt, 'iiidd', $demo_id, $month, $year, $units, $bill);
    mysqli_stmt_execute($stmt);
}
mysqli_stmt_close($stmt);

// ---- A past budget alert ----
$msg = 'Your estimated bill for May 2026 (PKR ' . number_format(calculate_bill(553.2), 2)
     . ') has exceeded your budget limit of PKR 18,000.00.';
$stmt = mysqli_prepare($conn, 'INSERT INTO alerts (user_id, message, is_read) VALUES (?, ?, 1)');
mysqli_stmt_bind_param($stmt, 'is', $demo_id, $msg);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// ---- Two extra users for the admin panel ----
make_user($conn, 'Ayesha Khan', 'ayesha@ecms.local', 'ayesha123', 12000, 'user');
make_user($conn, 'Bilal Ahmed', 'bilal@ecms.local', 'bilal123', 25000, 'user');

// ---- Report ----
$total_now = 0;
foreach ($appliances as $a) {
    $total_now += ($a[1] * $a[2] * 30) / 1000;
}
echo "Seed complete.\n";
echo "  Demo user: demo@ecms.local / demo123\n";
echo "  Appliances: " . count($appliances) . " (current ~" . round($total_now, 1) . " kWh/month, est. bill PKR " . number_format(calculate_bill($total_now), 2) . ")\n";
echo "  History: " . count($history_units) . " months (Jan–May 2026)\n";
echo "  Extra users: ayesha@ecms.local, bilal@ecms.local\n";
