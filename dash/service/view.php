
<?php
// dash/service/view.php - View service details
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
    setFlashMessage('info', 'Please login to view your services.');
    redirect('/auth/login');
}

// Get current user
$currentUser = getCurrentUser();

// Get service ID from URL
$serviceId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if service exists and belongs to user
$db = db();
$service = $db->selectOne(
    "SELECT * FROM services WHERE id = ? AND user_id = ?",
    [$serviceId, $currentUser['id']]
);

if (!$service) {
    setFlashMessage('error', 'Service not found or does not belong to you.');
    redirect('/dash/services');
}

// Get service details based on type
switch ($service['service_type']) {
    case 'game_server':
        $details = $db->selectOne(
            "SELECT * FROM game_servers WHERE service_id = ?",
            [$service['id']]
        );
        $serviceTypeLabel = 'Game Server';
        break;
        
    case 'web_hosting':
        $details = $db->selectOne(
            "SELECT * FROM web_hosting WHERE service_id = ?",
            [$service['id']]
        );
        $serviceTypeLabel = 'Web Hosting';
        break;
        
    case 'vps':
        $details = $db->selectOne(
            "SELECT * FROM vps_hosting WHERE service_id = ?",
            [$service['id']]
        );
        $serviceTypeLabel = 'Virtual Private Server';
        break;
        
    default:
        $details = null;
        $serviceTypeLabel = ucfirst($service['service_type']);
}

// Process service management forms
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cancel service
    if (isset($_POST['cancel_service'])) {
        if ($service['status'] === 'active' || $service['status'] === 'suspended') {
            // Update service status
            $db->update(
                'services',
                [
                    'status' => 'cancelled',
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$service['id']]
            );
            
            // Log activity
            logActivity($currentUser['id'], 'service_cancelled', ['service_id' => $service['id']]);
            
            // Success message
            setFlashMessage('success', 'Service has been cancelled successfully.');
            redirect('/dash/services');
        } else {
            setFlashMessage('error', 'This service cannot be cancelled in its current state.');
        }
    }
    
    // Other service management actions would be added here
}

// Set page title
$pageTitle = $serviceTypeLabel;

// Include header
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/loader.php';
?>

<div class="container py-5">
    <div>
        <h1 class="fw-bold mb-0"><?php echo $serviceTypeLabel; ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/dash" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/dash/services" class="text-decoration-none">Services</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $serviceTypeLabel; ?></li>
            </ol>
        </nav>
    </div>

    <div class="mt-5 mb-4">
        <h2 class="fw-bold mb-0">
            <?php echo $serviceTypeLabel; ?> (#<?php echo $service['id']; ?>)
            <span class="badge bg-<?php echo getStatusBadgeColor($service['status']); ?> ms-2">
                <?php echo ucfirst($service['status']); ?>
            </span>
        </h2>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <!-- Product Information Card -->
            <div class="card bg-dark">
                <div class="card-header bg-darker">
                    <h4 class="card-title mb-0">Product Information</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="text-muted">Product ID:</div>
                        <div class="fw-bold text-end">#<?php echo $service['id']; ?></div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="text-muted">Billing Cycle:</div>
                        <div class="fw-bold text-end"><?php echo ucfirst($service['billing_cycle']); ?></div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="text-muted">Price (excl. VAT):</div>
                        <div class="fw-bold text-end">€<?php echo number_format($service['amount'], 2); ?></div>
                    </div>
                    <?php if ($service['service_type'] === 'vps'): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <div class="text-muted">Hostname:</div>
                            <div class="fw-bold text-end"><?php echo $details['hostname']; ?></div>
                        </div>
                    <?php elseif ($service['service_type'] === 'web_hosting'): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <div class="text-muted">Domain:</div>
                            <div class="fw-bold text-end"><?php echo $details['domain']; ?></div>
                        </div>
                    <?php elseif ($service['service_type'] === 'game_server'): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <div class="text-muted">Game:</div>
                            <div class="fw-bold text-end"><?php echo ucfirst($details['game_type']); ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="text-muted">Due Date:</div>
                        <div class="fw-bold text-end"><?php echo formatDate($service['due_date'], 'n/j/Y'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="row">
                <!-- Manage Service Card -->
                <div class="col-md-6 mb-4">
                    <div class="card bg-dark h-100">
                        <div class="card-body p-4">
                            <h3 class="card-title mb-3">Manage Service</h3>
                            <p class="mb-4">Manage your <?php echo strtolower($serviceTypeLabel); ?></p>
                            <a href="<?php echo getManagementLink($service, $details); ?>" class="btn btn-primary w-100" <?php echo ($service['status'] !== 'active') ? 'disabled' : ''; ?>>
                                <i class="fas fa-cogs me-2"></i> Manage Service
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Renew Service Card -->
                <div class="col-md-6 mb-4">
                    <div class="card bg-dark h-100">
                        <div class="card-body p-4">
                            <h3 class="card-title mb-3">Renew Service</h3>
                            <p class="mb-4">Renew your <?php echo strtolower($serviceTypeLabel); ?></p>
                            <a href="/dash/service/renew/<?php echo $service['id']; ?>" class="btn btn-primary w-100" <?php echo ($service['status'] !== 'active' && $service['status'] !== 'suspended') ? 'disabled' : ''; ?>>
                                <i class="fas fa-sync-alt me-2"></i> Renew Service
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Upgrade Service Card -->
                <div class="col-md-6 mb-4">
                    <div class="card bg-dark h-100">
                        <div class="card-body p-4">
                            <h3 class="card-title mb-3">Upgrade Service</h3>
                            <p class="mb-4">Upgrade your <?php echo strtolower($serviceTypeLabel); ?></p>
                            <a href="/dash/service/upgrade/<?php echo $service['id']; ?>" class="btn btn-primary w-100" <?php echo ($service['status'] !== 'active') ? 'disabled' : ''; ?>>
                                <i class="fas fa-arrow-up me-2"></i> Upgrade Service
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cancel Service Card -->
                <div class="col-md-6 mb-4">
                    <div class="card bg-dark h-100">
                        <div class="card-body p-4">
                            <h3 class="card-title mb-3">Cancel Service</h3>
                            <p class="mb-4">Cancel your <?php echo strtolower($serviceTypeLabel); ?></p>
                            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelServiceModal" <?php echo ($service['status'] !== 'active' && $service['status'] !== 'suspended') ? 'disabled' : ''; ?>>
                                <i class="fas fa-times-circle me-2"></i> Cancel Service
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Details Section -->
    <?php if ($service['status'] === 'active'): ?>
    <div class="card bg-dark mt-4">
        <div class="card-header bg-darker">
            <h4 class="card-title mb-0">Service Details</h4>
        </div>
        <div class="card-body">
            <?php if ($service['service_type'] === 'vps'): ?>
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">Location</div>
                        <div class="fw-bold"><?php echo ucfirst($details['location']); ?></div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">Operating System</div>
                        <div class="fw-bold"><?php echo ucfirst($details['operating_system']); ?></div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">CPU Cores</div>
                        <div class="fw-bold"><?php echo $details['cpu_cores']; ?></div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">Memory</div>
                        <div class="fw-bold"><?php echo $details['ram']; ?> GB</div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">Storage</div>
                        <div class="fw-bold"><?php echo $details['storage']; ?> GB</div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">Bandwidth</div>
                        <div class="fw-bold"><?php echo $details['bandwidth']; ?></div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">IPv4 Addresses</div>
                        <div class="fw-bold">
                            <?php 
                            $ipv4 = json_decode($details['ip_addresses'], true);
                            echo count($ipv4['ipv4'] ?? [1]); 
                            ?>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">IPv6 Addresses</div>
                        <div class="fw-bold">
                            <?php 
                            $ipv6 = json_decode($details['ip_addresses'], true);
                            echo count($ipv6['ipv6'] ?? [1]); 
                            ?>
                        </div>
                    </div>
                </div>
            <?php elseif ($service['service_type'] === 'web_hosting'): ?>
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">Domain</div>
                        <div class="fw-bold"><?php echo $details['domain']; ?></div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">Disk Space</div>
                        <div class="fw-bold"><?php echo $details['disk_space']; ?> GB</div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">Bandwidth</div>
                        <div class="fw-bold"><?php echo $details['bandwidth']; ?></div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">Databases</div>
                        <div class="fw-bold"><?php echo $details['db_count']; ?></div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">Email Accounts</div>
                        <div class="fw-bold"><?php echo $details['email_accounts']; ?></div>
                    </div>
                </div>
            <?php elseif ($service['service_type'] === 'game_server'): ?>
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">Game Type</div>
                        <div class="fw-bold"><?php echo ucfirst($details['game_type']); ?></div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">Location</div>
                        <div class="fw-bold"><?php echo ucfirst($details['location']); ?></div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">Slots</div>
                        <div class="fw-bold"><?php echo $details['slots']; ?></div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">RAM</div>
                        <div class="fw-bold"><?php echo $details['ram']; ?> GB</div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">CPU Limit</div>
                        <div class="fw-bold"><?php echo $details['cpu_limit']; ?>%</div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="small text-muted mb-1">Disk Space</div>
                        <div class="fw-bold"><?php echo $details['disk_space']; ?> GB</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions for Active Services -->
    <?php if ($service['status'] === 'active'): ?>
    <div class="card bg-dark mt-4">
        <div class="card-header bg-darker">
            <h4 class="card-title mb-0">Quick Actions</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <?php if ($service['service_type'] === 'vps'): ?>
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-desktop me-2"></i> VNC Console
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-chart-line me-2"></i> Usage Statistics
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-sync-alt me-2"></i> Reset Password
                        </a>
                    </div>
                <?php elseif ($service['service_type'] === 'web_hosting'): ?>
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-server me-2"></i> cPanel Login
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-database me-2"></i> Database Manager
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-envelope me-2"></i> Email Accounts
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-file me-2"></i> File Manager
                        </a>
                    </div>
                <?php elseif ($service['service_type'] === 'game_server'): ?>
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo PTERODACTYL_URL; ?>/server/<?php echo $service['server_id']; ?>" target="_blank" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-gamepad me-2"></i> Game Panel
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-terminal me-2"></i> Console
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-download me-2"></i> Backups
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-cog me-2"></i> Server Settings
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Invoices Section -->
    <div class="card bg-dark mt-4">
        <div class="card-header bg-darker d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Recent Invoices</h4>
            <?php if ($service['status'] === 'active'): ?>
                <a href="/dash/invoices?service_id=<?php echo $service['id']; ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-external-link-alt me-2"></i> View All Invoices
                </a>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get recent invoices for this service
                    $invoices = $db->select(
                        "SELECT * FROM invoices 
                         WHERE service_id = ? 
                         ORDER BY created_at DESC LIMIT 5",
                        [$service['id']]
                    );
                    
                    if (empty($invoices)):
                    ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">No invoices found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td>
                                    <a href="/invoice/view/<?php echo $invoice['id']; ?>" class="text-primary text-decoration-none">
                                        #<?php echo str_pad($invoice['id'], 5, '0', STR_PAD_LEFT); ?>
                                    </a>
                                </td>
                                <td><?php echo formatDate($invoice['created_at'], 'd/m/Y'); ?></td>
                                <td><?php echo formatDate($invoice['due_date'], 'd/m/Y'); ?></td>
                                <td><?php echo formatCurrency($invoice['amount']); ?></td>
                                <td>
                                    <?php
                                    switch ($invoice['status']) {
                                        case 'paid':
                                            echo '<span class="badge bg-success">Paid</span>';
                                            break;
                                        case 'unpaid':
                                            echo '<span class="badge bg-warning text-dark">Unpaid</span>';
                                            break;
                                        case 'overdue':
                                            echo '<span class="badge bg-danger">Overdue</span>';
                                            break;
                                        case 'cancelled':
                                            echo '<span class="badge bg-secondary">Cancelled</span>';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($invoice['status'] === 'unpaid' || $invoice['status'] === 'overdue'): ?>
                                        <a href="/payment/gateway/<?php echo $invoice['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-credit-card me-1"></i> Pay Now
                                        </a>
                                    <?php endif; ?>
                                    <a href="/invoice/view/<?php echo $invoice['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Related Tickets Section -->
    <div class="card bg-dark mt-4">
        <div class="card-header bg-darker d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Support Tickets</h4>
            <a href="/dash/ticket/new?service_id=<?php echo $service['id']; ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-2"></i> Open New Ticket
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Subject</th>
                        <th>Last Updated</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get tickets for this service
                    $tickets = $db->select(
                        "SELECT * FROM tickets 
                         WHERE service_id = ? 
                         ORDER BY updated_at DESC LIMIT 5",
                        [$service['id']]
                    );
                    
                    if (empty($tickets)):
                    ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">No tickets found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td>
                                    <a href="/dash/ticket/view/<?php echo $ticket['id']; ?>" class="text-primary text-decoration-none">
                                        #<?php echo substr(md5($ticket['id']), 0, 8); ?>
                                    </a>
                                </td>
                                <td><?php echo clean($ticket['subject']); ?></td>
                                <td><?php echo formatDate($ticket['updated_at'], 'd/m/Y g:i A'); ?></td>
                                <td>
                                    <?php
                                    switch ($ticket['status']) {
                                        case 'open':
                                            echo '<span class="badge bg-success">Open</span>';
                                            break;
                                        case 'answered':
                                            echo '<span class="badge bg-primary">Answered</span>';
                                            break;
                                        case 'closed':
                                            echo '<span class="badge bg-danger">Closed</span>';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td class="text-end">
                                    <a href="/dash/ticket/view/<?php echo $ticket['id']; ?>" class="btn btn-primary btn-sm">
                                        View Ticket
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Cancel Service Modal -->
<div class="modal fade" id="cancelServiceModal" tabindex="-1" aria-labelledby="cancelServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelServiceModalLabel">Confirm Cancellation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel the following service?</p>
                <div class="card bg-darker mb-3">
                    <div class="card-body">
                        <div class="fw-bold"><?php echo $service['name']; ?></div>
                        <div class="small text-muted"><?php echo $serviceTypeLabel; ?></div>
                    </div>
                </div>
                <div class="alert alert-danger">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Warning</div>
                            <div>Cancelling this service will immediately revoke access. This action cannot be undone.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <form method="post" action="">
                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                    <button type="submit" name="cancel_service" class="btn btn-danger">Cancel Service</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function to generate the correct management link
function getManagementLink($service, $details) {
    global $db;
    
    switch ($service['service_type']) {
        case 'game_server':
            return PTERODACTYL_URL . '/server/' . $service['server_id'];
        case 'web_hosting':
            return 'https://cpanel.' . $details['domain'];
        case 'vps':
            return '/dash/service/vps/' . $service['id'];
        default:
            return '/dash/service/view/' . $service['id'];
    }
}

// Helper function to determine badge color
function getStatusBadgeColor($status) {
    switch ($status) {
        case 'active':
            return 'success';
        case 'pending':
            return 'warning';
        case 'suspended':
            return 'danger';
        case 'cancelled':
            return 'secondary';
        case 'terminated':
            return 'dark';
        default:
            return 'primary';
    }
}

require_once __DIR__ . '/../../includes/footer.php';