<?php
// includes/sidebar.php
// Sidebar navigation for dashboard
$currentUser = getCurrentUser();
?>

<div class="sidebar bg-darker">
    <div class="sidebar-header">
        <div class="logo-container">
            <a href="/" class="sidebar-logo">
                <img src="/assets/images/logo.png" alt="WaveHost Logo" class="img-fluid">
            </a>
            <button class="btn sidebar-toggle d-md-none" type="button">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
    
    <div class="sidebar-user">
        <div class="d-flex align-items-center p-3">
            <div class="avatar me-3">
                <?php if ($currentUser && !empty($currentUser['profile_image'])): ?>
                    <img src="<?php echo $currentUser['profile_image']; ?>" alt="<?php echo $currentUser['username']; ?>" class="rounded-circle">
                <?php else: ?>
                    <img src="<?php echo generateAvatar($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>" alt="<?php echo $currentUser['username']; ?>" class="rounded-circle">
                <?php endif; ?>
            </div>
            <div class="user-info">
                <h6 class="mb-0"><?php echo $currentUser['first_name'] . ' ' . $currentUser['last_name']; ?></h6>
                <small class="text-muted"><?php echo $currentUser['email']; ?></small>
            </div>
        </div>
    </div>
    
    <div class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo getActiveClass('/dash'); ?>" href="/dash">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo getActiveClass('/dash/services'); ?>" href="/dash/services">
                    <i class="fas fa-server me-2"></i> Services
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo getActiveClass('/dash/invoices'); ?>" href="/dash/invoices">
                    <i class="fas fa-file-invoice-dollar me-2"></i> Invoices
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo getActiveClass('/dash/tickets'); ?>" href="/dash/tickets">
                    <i class="fas fa-ticket-alt me-2"></i> Support Tickets
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo getActiveClass('/dash/affiliate'); ?>" href="/dash/affiliate">
                    <i class="fas fa-users me-2"></i> Affiliate Program
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo getActiveClass('/dash/account'); ?>" href="/dash/account">
                    <i class="fas fa-user-cog me-2"></i> Account Settings
                </a>
            </li>
        </ul>
    </div>
    
    <?php if (isAdmin() || isStaff()): ?>
    <div class="sidebar-section">
        <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
            <span>Staff Area</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo getActiveClass('/admin'); ?>" href="/admin">
                    <i class="fas fa-user-shield me-2"></i> Admin Panel
                </a>
            </li>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="sidebar-section mt-auto">
        <div class="px-3 py-3">
            <a href="/auth/logout" class="btn btn-outline-danger w-100">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (sidebar.classList.contains('open') && !sidebar.contains(event.target) && event.target !== sidebarToggle) {
            sidebar.classList.remove('open');
        }
    });
});
</script>