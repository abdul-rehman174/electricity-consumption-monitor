<?php
/**
 * Alerts page (FR-17).
 * Lists all budget alerts with their date/time, newest first.
 * Viewing this page marks any unread alerts as read.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user_id = current_user_id();

// Mark unread alerts as read (FR-17).
$stmt = mysqli_prepare($conn, 'UPDATE alerts SET is_read = 1 WHERE user_id = ? AND is_read = 0');
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Fetch all alerts.
$stmt = mysqli_prepare(
    $conn,
    'SELECT message, created_at FROM alerts WHERE user_id = ? ORDER BY created_at DESC'
);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$alerts = [];
while ($row = mysqli_fetch_assoc($res)) {
    $alerts[] = $row;
}
mysqli_stmt_close($stmt);

$page_title = 'Alerts';
require __DIR__ . '/../includes/header.php';
?>
<h3 class="mb-3"><i class="bi bi-bell text-success"></i> Budget Alerts</h3>

<?php if (!$alerts): ?>
    <div class="card"><div class="card-body text-center text-muted py-5">
        <i class="bi bi-bell-slash fs-1"></i>
        <p class="mt-2 mb-0">No alerts. You're within your budget limit.</p>
    </div></div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($alerts as $a): ?>
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <span><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i><?= e($a['message']) ?></span>
                    <small class="text-muted text-nowrap ms-3"><?= e(date('d M Y, H:i', strtotime($a['created_at']))) ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
