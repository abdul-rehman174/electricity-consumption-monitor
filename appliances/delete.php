<?php
/** FR-06: Delete Appliance — removes it from all future calculations. */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user_id = current_user_id();
$id      = (int) ($_GET['id'] ?? 0);

// Delete only if the appliance belongs to the current user.
$stmt = mysqli_prepare($conn, 'DELETE FROM appliances WHERE id = ? AND user_id = ?');
mysqli_stmt_bind_param($stmt, 'ii', $id, $user_id);
mysqli_stmt_execute($stmt);
$deleted = mysqli_stmt_affected_rows($stmt) > 0;
mysqli_stmt_close($stmt);

set_flash($deleted ? 'success' : 'warning', $deleted ? 'Appliance deleted.' : 'Appliance not found.');
redirect('appliances/index.php');
