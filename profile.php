<?php
/**
 * Profile page.
 *   FR-15 : set or update the monthly bill budget limit (PKR).
 * Also lets the user update their display name.
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$user_id = current_user_id();
$errors  = [];

// Load current profile.
$stmt = mysqli_prepare($conn, 'SELECT name, email, bill_limit FROM users WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res  = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

$name       = $user['name'];
$bill_limit = $user['bill_limit'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $bill_limit = trim($_POST['bill_limit'] ?? '');

    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if ($bill_limit === '' || !is_numeric($bill_limit) || $bill_limit < 0) {
        $errors[] = 'Budget limit must be a non-negative number.';
    }

    if (!$errors) {
        $limit = (float) $bill_limit;
        $stmt = mysqli_prepare($conn, 'UPDATE users SET name = ?, bill_limit = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'sdi', $name, $limit, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['user_name'] = $name; // keep the navbar in sync
            set_flash('success', 'Profile updated.');
        } else {
            $errors[] = 'Could not update profile. Please try again.';
        }
        mysqli_stmt_close($stmt);
    }
}

$page_title = 'Profile';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <h3 class="mb-3"><i class="bi bi-person-gear text-success"></i> Profile & Budget</h3>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="card"><div class="card-body p-4">
            <form method="post" novalidate>
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= e($name) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                    <div class="form-text">Email cannot be changed.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Monthly Budget Limit (PKR)</label>
                    <input type="number" step="0.01" min="0" name="bill_limit" class="form-control" value="<?= e($bill_limit) ?>" required>
                    <div class="form-text">You'll be alerted when your estimated bill exceeds this amount.</div>
                </div>
                <button type="submit" class="btn btn-ecms"><i class="bi bi-check-lg"></i> Save Changes</button>
            </form>
        </div></div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
