<?php
/**
 * Database connection (MySQLi).
 *
 * Edit these four constants to match your XAMPP setup.
 * Default XAMPP credentials are user "root" with an empty password.
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'ecms');     // dedicated app user (created during setup)
define('DB_PASS', 'ecms123');  // change this in production
define('DB_NAME', 'ecms');

// Open a single shared connection used by the whole application.
$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// NFR-04: show a friendly message instead of crashing on DB failure.
if (!$conn) {
    die('Database connection failed. Make sure MySQL is running in XAMPP and that you have imported database/ecms.sql.');
}

mysqli_set_charset($conn, 'utf8mb4');
