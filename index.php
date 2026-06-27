<?php
/** Entry point: send users to their dashboard or the login page. */
require_once __DIR__ . '/includes/functions.php';
redirect(is_logged_in() ? 'dashboard.php' : 'login.php');
