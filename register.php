<?php
/**
 * FR-01: User Registration.
 * Collects name, email, password and a monthly bill budget limit.
 * Password is hashed with password_hash() before storage (NFR-02).
 */
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$name = $email = '';
$bill_limit = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm'] ?? '';
    $bill_limit = trim($_POST['bill_limit'] ?? '');

    // ---- Server-side validation (NFR-03) ----
    if ($name === '') {
        $errors[] = 'Full name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if ($bill_limit === '' || !is_numeric($bill_limit) || $bill_limit < 0) {
        $errors[] = 'Monthly budget limit must be a non-negative number.';
    }

    // ---- Email uniqueness check ----
    if (!$errors) {
        $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ?');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'An account with this email already exists.';
        }
        mysqli_stmt_close($stmt);
    }

    // ---- Create the account ----
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $limit = (float) $bill_limit;
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO users (name, email, password, bill_limit, role)
             VALUES (?, ?, ?, ?, 'user')"
        );
        mysqli_stmt_bind_param($stmt, 'sssd', $name, $email, $hash, $limit);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            set_flash('success', 'Account created successfully. Please log in.');
            redirect('login.php');
        } else {
            $errors[] = 'Registration failed. Please try again.';
            mysqli_stmt_close($stmt);
        }
    }
}

$page_title = 'Register';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register &middot; ECMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="card auth-card">
        <div class="card-body p-4">
            <div class="text-center mb-3">
                <i class="bi bi-lightning-charge-fill text-success fs-1"></i>
                <h4 class="mt-2 mb-0">Create your ECMS account</h4>
                <p class="text-muted small">Monitor your electricity, estimate your bill</p>
            </div>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= e($name) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($email) ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Monthly Budget Limit (PKR)</label>
                    <input type="number" step="0.01" min="0" name="bill_limit" class="form-control" value="<?= e($bill_limit) ?>" required>
                    <div class="form-text">You'll be alerted when your estimated bill exceeds this.</div>
                </div>
                <button type="submit" class="btn btn-ecms w-100">Register</button>
            </form>
            <p class="text-center mt-3 mb-0">
                Already have an account? <a href="<?= BASE_URL ?>/login.php">Log in</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
