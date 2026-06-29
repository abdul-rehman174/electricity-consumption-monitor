<?php
/**
 * FR-20: Delete a user account. Foreign keys use ON DELETE CASCADE, so the
 * user's appliances, bills and alerts are removed automatically.
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);

// An admin must not delete their own account from here.
if ($id === (int) current_user_id()) {
    set_flash('warning', 'You cannot delete your own admin account.');
    redirect('admin/index.php');
}

$stmt = mysqli_prepare($conn, 'DELETE FROM users WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$deleted = mysqli_stmt_affected_rows($stmt) > 0;
mysqli_stmt_close($stmt);

set_flash($deleted ? 'success' : 'warning', $deleted ? 'User account deleted.' : 'User not found.');
redirect('admin/index.php');
