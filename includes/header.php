<?php
/**
 * Shared page header: opens the HTML document, loads Bootstrap 5 + Chart.js,
 * and renders the top navigation bar for logged-in users.
 *
 * Set $page_title before including this file to set the browser title.
 */
if (!isset($page_title)) {
    $page_title = 'ECMS';
}
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> &middot; ECMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
<?php if (is_logged_in()): ?>
<nav class="navbar navbar-expand-lg navbar-dark ecms-navbar sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/dashboard.php">
            <i class="bi bi-lightning-charge-fill"></i> ECMS
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/appliances/index.php"><i class="bi bi-plug"></i> Appliances</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/bills/index.php"><i class="bi bi-receipt"></i> Bills</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/alerts/index.php"><i class="bi bi-bell"></i> Alerts</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/profile.php"><i class="bi bi-person-gear"></i> Profile</a></li>
                <?php if (is_admin()): ?>
                <li class="nav-item"><a class="nav-link text-warning" href="<?= BASE_URL ?>/admin/index.php"><i class="bi bi-shield-lock"></i> Admin</a></li>
                <li class="nav-item"><a class="nav-link text-warning" href="<?= BASE_URL ?>/admin/tariff.php"><i class="bi bi-cash-coin"></i> Tariff</a></li>
                <?php endif; ?>
            </ul>
            <span class="navbar-text text-white me-3">Hi, <?= e(current_user_name()) ?></span>
            <a class="btn btn-sm btn-outline-light" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>
</nav>
<?php endif; ?>
<main class="container py-4">
<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
