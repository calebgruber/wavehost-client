<?php
// includes/header.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/cart.php'; // Add this line to fix getCartItemCount() error

// Rest of your header code

// Get current user if logged in
$currentUser = null;
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
}

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
if (strpos($_SERVER['REQUEST_URI'], '/dash/') !== false) {
    $currentPage = 'dashboard';
} elseif (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
    $currentPage = 'admin';
}

// Flash messages
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="/assets/images/favicon.ico">
    
    <!-- Bootstrap CSS (Material UI 3) -->
    <link href="/assets/css/material-dashboard.min.css" rel="stylesheet">
    
    <!-- Custom styles -->
    <link href="/assets/css/style.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <?php if (isset($extraCSS)) { echo $extraCSS; } ?>
</head>
<body class="<?php echo isset($bodyClass) ? $bodyClass : ''; ?>">
    <!-- Header/Navigation -->
    <?php if (strpos($_SERVER['REQUEST_URI'], '/auth/') === false) : ?>
    <header class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="/assets/images/logo.png" alt="WaveHost" height="40">
                WaveHost
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'game-server' || $currentPage === 'games' ? 'active' : ''; ?>" href="/games">Game Servers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'web-hosting' || $currentPage === 'web' ? 'active' : ''; ?>" href="/web">Web Hosting</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'vps-hosting' || $currentPage === 'vps' ? 'active' : ''; ?>" href="/vps">VPS Hosting</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'about' ? 'active' : ''; ?>" href="/about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'support' ? 'active' : ''; ?>" href="/support">Support</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'blog' ? 'active' : ''; ?>" href="/blog">Blog</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <?php if ($currentUser) : ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo $currentUser['username']; ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="/dash">Dashboard</a></li>
                                <li><a class="dropdown-item" href="/dash/services">My Services</a></li>
                                <li><a class="dropdown-item" href="/dash/invoices">Invoices</a></li>
                                <li><a class="dropdown-item" href="/dash/tickets">Support Tickets</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/dash/account">Account Settings</a></li>
                                <?php if ($currentUser['is_admin'] || $currentUser['is_staff']) : ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/admin">Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/auth/logout">Logout</a></li>
                            </ul>
                        </div>
                    <?php else : ?>
                        <a href="/auth/login" class="btn btn-outline-primary me-2">Login</a>
                        <a href="/auth/register" class="btn btn-primary">Register</a>
                    <?php endif; ?>
                </div>
                <!-- Cart and User Menu -->
<div class="d-flex align-items-center">
    <?php if ($currentUser) : ?>
        <!-- Cart icon with badge for logged in user -->
        <a href="/cart" class="position-relative me-3">
            <i class="fas fa-shopping-cart text-white fs-5"></i>
            <?php if (getCartItemCount() > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                    <?php echo getCartItemCount(); ?>
                    <span class="visually-hidden">items in cart</span>
                </span>
            <?php endif; ?>
        </a>
        
        <!-- User dropdown -->
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <?php echo $currentUser['username']; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="/dash">Dashboard</a></li>
                <li><a class="dropdown-item" href="/dash/services">My Services</a></li>
                <li><a class="dropdown-item" href="/dash/invoices">Invoices</a></li>
                <li><a class="dropdown-item" href="/dash/tickets">Support Tickets</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/dash/account">Account Settings</a></li>
                <?php if ($currentUser['is_admin'] || $currentUser['is_staff']) : ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/admin">Admin Panel</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/auth/logout">Logout</a></li>
            </ul>
        </div>
    <?php else : ?>
        <!-- Cart icon with badge for guest user -->
        <a href="/cart" class="position-relative me-3">
            <i class="fas fa-shopping-cart text-white fs-5"></i>
            <?php if (getCartItemCount() > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                    <?php echo getCartItemCount(); ?>
                    <span class="visually-hidden">items in cart</span>
                </span>
            <?php endif; ?>
        </a>
        <a href="/auth/login" class="btn btn-outline-primary me-2">Login</a>
        <a href="/auth/register" class="btn btn-primary">Register</a>
    <?php endif; ?>
</div>
            </div>
        </div>
    </header>
    <?php endif; ?>
    
    <!-- Flash Messages -->
    <?php if ($flashMessage) : ?>
    <div class="alert alert-<?php echo $flashMessage['type']; ?> alert-dismissible fade show">
        <?php echo $flashMessage['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="main-content">