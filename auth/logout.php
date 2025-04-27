<?php
// auth/logout.php - Logout script
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Log the user out
$logoutResult = logoutUser();

// Set success message
setFlashMessage('success', 'You have been successfully logged out.');

// Redirect to login page
redirect('/auth/login');
?>