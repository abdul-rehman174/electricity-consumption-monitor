<?php
/** FR-03: terminate the session and return to the login page. */
require_once __DIR__ . '/includes/functions.php';

$_SESSION = [];
session_destroy();

redirect('login.php');
