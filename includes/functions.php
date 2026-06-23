<?php
/**
 * Core helpers, session handling, and authentication guards.
 * Every page includes this file first.
 */

// Base URL the app is served from. Change this if you rename the project folder.
// e.g. http://localhost/electricity-consumption-monitor  ->  '/electricity-consumption-monitor'
if (!defined('BASE_URL')) {
    define('BASE_URL', '/electricity-consumption-monitor');
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/tariff.php';

// FR-03: maintain a login session for the whole browser session.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escape a value for safe HTML output (NFR-02: prevents XSS).
 */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect helper. $path is relative to BASE_URL, e.g. 'dashboard.php'.
 */
function redirect($path)
{
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function is_admin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function current_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

function current_user_name()
{
    return $_SESSION['user_name'] ?? '';
}

/**
 * Guard for pages that require a logged-in user. Sends guests to the login page.
 */
function require_login()
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

/**
 * NFR-02 / FR-18: server-side role check run on every admin request.
 */
function require_admin()
{
    require_login();
    if (!is_admin()) {
        redirect('dashboard.php');
    }
}

/**
 * FR-08: monthly consumption for one appliance.
 * Monthly kWh = (Wattage x Hours per day x 30) / 1000.
 */
function monthly_kwh($wattage, $hours_per_day)
{
    return ((float) $wattage * (float) $hours_per_day * 30) / 1000;
}

/**
 * Simple one-shot flash message stored in the session.
 */
function set_flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash()
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Total monthly units (kWh) across all of a user's appliances.
 */
function user_total_units($conn, $user_id)
{
    $total = 0.0;
    $stmt  = mysqli_prepare($conn, 'SELECT wattage, hours_per_day FROM appliances WHERE user_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $total += monthly_kwh($row['wattage'], $row['hours_per_day']);
    }
    mysqli_stmt_close($stmt);
    return round($total, 2);
}
