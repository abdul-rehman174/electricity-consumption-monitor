<?php
/**
 * FR-02: User Login (shared by regular users and admins — FR-18).
 * Authenticates by email + password, then redirects to the dashboard.
 */
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Please enter both email and password.';
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT id, name, password, role FROM users WHERE email = ?');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $res  = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password'])) {
            // Establish the session (FR-03).
            session_regenerate_id(true);
            $_SESSION['user_id']   = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role']      = $user['role'];
            redirect('dashboard.php');
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &middot; ECMS</title>
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
                <h4 class="mt-2 mb-0">Welcome back</h4>
                <p class="text-muted small">Log in to your ECMS account</p>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $err): ?>
                        <div><?= e($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($email) ?>" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-ecms w-100">Log In</button>
            </form>
            <p class="text-center mt-3 mb-0">
                New here? <a href="<?= BASE_URL ?>/register.php">Create an account</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
