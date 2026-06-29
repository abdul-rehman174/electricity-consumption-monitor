<?php
/**
 * One-time setup: creates the default admin account.
 * Visit http://localhost/electricity-consumption-monitor/setup.php once after importing ecms.sql.
 *
 * Default admin credentials (change the password after logging in):
 *   email:    admin@ecms.local
 *   password: admin123
 */
require_once __DIR__ . '/includes/functions.php';

$admin_email = 'admin@ecms.local';
$admin_pass  = 'admin123';

$stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ?');
mysqli_stmt_bind_param($stmt, 's', $admin_email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
$exists = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if ($exists) {
    $message = 'Admin account already exists. You can log in with ' . $admin_email . '.';
} else {
    $hash = password_hash($admin_pass, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO users (name, email, password, bill_limit, role)
         VALUES ('System Admin', ?, ?, 0, 'admin')"
    );
    mysqli_stmt_bind_param($stmt, 'ss', $admin_email, $hash);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $message = $ok
        ? 'Admin account created. Log in with ' . $admin_email . ' / ' . $admin_pass
        : 'Could not create admin account: ' . mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ECMS Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 560px;">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h4 class="mb-3">ECMS Setup</h4>
            <div class="alert alert-info"><?= e($message) ?></div>
            <p class="text-muted small mb-3">
                For security, delete <code>setup.php</code> after the admin account exists.
            </p>
            <a href="<?= BASE_URL ?>/login.php" class="btn btn-success">Go to Login</a>
        </div>
    </div>
</div>
</body>
</html>
