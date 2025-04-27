<?php
// dash/index.php - Dashboard Home
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
    setFlashMessage('info', 'Please login to access your dashboard.');
    redirect('/auth/login');
}

// Get current user
$currentUser = getCurrentUser();

// Get services count
$db = db();
$servicesCount = $db->selectOne(
    "SELECT COUNT(*) as count FROM services WHERE user_id = ? AND status = 'active'",
    [$currentUser['id']]
)['count'];

// Get unpaid invoices count
$unpaidInvoicesCount = $db->selectOne(
    "SELECT COUNT(*) as count FROM invoices WHERE user_id = ? AND status = 'unpaid'",
    [$currentUser['id']]
)['count'];

// Get open tickets count
$openTicketsCount = $db->selectOne(
    "SELECT COUNT(*) as count FROM tickets WHERE user_id = ? AND status = 'open'",
    [$currentUser['id']]
)['count'];

// Get billing status
$billingStatus = "All invoices are paid";
if ($unpaidInvoicesCount > 0) {
    $billingStatus = "You have {$unpaidInvoicesCount} unpaid invoice(s)";
}

// Get support status
$supportStatus = "No active support tickets";
if ($openTicketsCount > 0) {
    $supportStatus = "You have {$openTicketsCount} open ticket(s)";
}

// Set page title
$pageTitle = 'Dashboard';

// Include header
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/loader.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="fw-bold mb-0">Welcome back, <span class="text-primary"><?php echo $currentUser['username']; ?></span></h1>
            <p class="text-muted">Here's what's happening with your account today</p>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="d-flex flex-column align-items-md-end">
                <div class="d-flex align-items-center mb-2">
                    <div class="rounded-circle overflow-hidden me-2" style="width: 50px; height: 50px;">
                        <img src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($currentUser['email']))); ?>?s=100&d=mp" alt="Profile" class="w-100 h-100 object-fit-cover">
                    </div>
                    <div>
                        <div class="fw-bold"><?php echo $currentUser['first_name'] . ' ' . $currentUser['last_name']; ?></div>
                        <div class="small text-muted"><?php echo $currentUser['email']; ?></div>
                    </div>
                </div>
                <a href="/dash/account" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-cog me-1"></i> Settings
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <!-- Billing Status Card -->
        <div class="col-md-4 mb-4">
            <div class="card bg-dark h-100">
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="icon-box rounded bg-primary-subtle me-3">
                            <i class="fas fa-file-invoice text-primary"></i>
                        </div>
                        <div class="mt-2">
                            <h2 class="h5 mb-0"><?php echo $unpaidInvoicesCount; ?> Invoices</h2>
                        </div>
                    </div>
                    <h4 class="card-title h5 mb-2">Billing Status</h4>
                    <p class="card-text text-muted mb-3"><?php echo $billingStatus; ?></p>
                    <a href="/dash/invoices" class="btn btn-outline-primary w-100">View History</a>
                </div>
            </div>
        </div>

        <!-- Active Services Card -->
        <div class="col-md-4 mb-4">
            <div class="card bg-dark h-100">
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="icon-box rounded bg-info-subtle me-3">
                            <i class="fas fa-server text-info"></i>
                        </div>
                        <div class="mt-2">
                            <h2 class="h5 mb-0"><?php echo $servicesCount; ?> Services</h2>
                        </div>
                    </div>
                    <h4 class="card-title h5 mb-2">Active Services</h4>
                    <p class="card-text text-muted mb-3">Manage your active services</p>
                    <a href="/dash/services" class="btn btn-primary w-100">Manage Services</a>
                </div>
            </div>
        </div>

        <!-- Support Status Card -->
        <div class="col-md-4 mb-4">
            <div class="card bg-dark h-100">
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="icon-box rounded bg-success-subtle me-3">
                            <i class="fas fa-ticket-alt text-success"></i>
                        </div>
                        <div class="mt-2">
                            <h2 class="h5 mb-0"><?php echo $openTicketsCount; ?> Tickets</h2>
                        </div>
                    </div>
                    <h4 class="card-title h5 mb-2">Support Status</h4>
                    <p class="card-text text-muted mb-3"><?php echo $supportStatus; ?></p>
                    <div class="d-flex">
                        <a href="/dash/ticket/new" class="btn btn-primary me-2 flex-grow-1">New Ticket</a>
                        <a href="/dash/tickets" class="btn btn-outline-primary flex-grow-1">View Tickets</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="fw-bold mb-4">Quick Actions</h2>
    <div class="row">
        <!-- Quick Action Cards -->
        <div class="col-md-4 mb-4">
            <div class="card bg-dark h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box rounded bg-info-subtle me-3">
                            <i class="fas fa-globe text-info"></i>
                        </div>
                        <h4 class="card-title h5 mb-0">Domains</h4>
                    </div>
                    <p class="card-text text-muted mb-3">Manage domain names and DNS settings</p>
                    <a href="/dash/domains" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card bg-dark h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box rounded bg-primary-subtle me-3">
                            <i class="fas fa-user-friends text-primary"></i>
                        </div>
                        <h4 class="card-title h5 mb-0">Affiliate Program</h4>
                    </div>
                    <p class="card-text text-muted mb-3">Earn commissions through referrals</p>
                    <a href="/dash/affiliate" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card bg-dark h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box rounded bg-success-subtle me-3">
                            <i class="fas fa-wallet text-success"></i>
                        </div>
                        <h4 class="card-title h5 mb-0">Account Credit</h4>
                    </div>
                    <p class="card-text text-muted mb-3">Add or manage account balance</p>
                    <a href="/dash/credit" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card bg-dark h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box rounded bg-primary-subtle me-3">
                            <i class="fas fa-shield-alt text-primary"></i>
                        </div>
                        <h4 class="card-title h5 mb-0">Shield Panel <span class="badge bg-info rounded-pill ms-2 small">Beta</span></h4>
                    </div>
                    <p class="card-text text-muted mb-3">DDoS protection and filtering</p>
                    <a href="/dash/shield" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card bg-dark h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box rounded bg-info-subtle me-3">
                            <i class="fas fa-users-cog text-info"></i>
                        </div>
                        <h4 class="card-title h5 mb-0">Access Control</h4>
                    </div>
                    <p class="card-text text-muted mb-3">Manage user permissions</p>
                    <a href="/dash/access" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card bg-dark h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box rounded bg-secondary-subtle me-3">
                            <i class="fas fa-network-wired text-secondary"></i>
                        </div>
                        <h4 class="card-title h5 mb-0">Transit Manager <span class="badge bg-secondary rounded-pill ms-2 small">Soon</span></h4>
                    </div>
                    <p class="card-text text-muted mb-3">Manage your network traffic</p>
                    <a href="/dash/transit" class="stretched-link"></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>