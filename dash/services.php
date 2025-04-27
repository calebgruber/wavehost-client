<?php
// dash/services.php - Services page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
    setFlashMessage('info', 'Please login to access your services.');
    redirect('/auth/login');
}

// Get current user
$currentUser = getCurrentUser();

// Get services
$db = db();
$services = $db->select(
    "SELECT * FROM services WHERE user_id = ? ORDER BY created_at DESC",
    [$currentUser['id']]
);

// Prepare service data for display
foreach ($services as &$service) {
    // Get service details based on type
    switch ($service['service_type']) {
        case 'game_server':
            $details = $db->selectOne(
                "SELECT * FROM game_servers WHERE service_id = ?",
                [$service['id']]
            );
            $service['details'] = $details;
            $service['identifier'] = $details ? $details['game_type'] : 'Unknown';
            break;
            
        case 'web_hosting':
            $details = $db->selectOne(
                "SELECT * FROM web_hosting WHERE service_id = ?",
                [$service['id']]
            );
            $service['details'] = $details;
            $service['identifier'] = $details ? $details['domain'] : 'Unknown';
            break;
            
        case 'vps':
            $details = $db->selectOne(
                "SELECT * FROM vps_hosting WHERE service_id = ?",
                [$service['id']]
            );
            $service['details'] = $details;
            $service['identifier'] = $details ? $details['hostname'] : 'Unknown';
            break;
    }
    
    // Get next invoice
    $nextInvoice = $db->selectOne(
        "SELECT * FROM invoices 
         WHERE service_id = ? AND status = 'unpaid' 
         ORDER BY due_date ASC LIMIT 1",
        [$service['id']]
    );
    
    $service['next_invoice'] = $nextInvoice;
}

// Handle cancel request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_service'])) {
    $serviceId = $_POST['service_id'] ?? 0;
    
    // Check if service exists and belongs to user
    $service = $db->selectOne(
        "SELECT * FROM services WHERE id = ? AND user_id = ?",
        [$serviceId, $currentUser['id']]
    );
    
    if ($service) {
        // Check if service can be canceled
        if ($service['status'] === 'active' || $service['status'] === 'suspended') {
            // Update service status
            $db->update(
                'services',
                [
                    'status' => 'cancelled',
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$serviceId]
            );
            
            // Log activity
            logActivity($currentUser['id'], 'service_cancelled', ['service_id' => $serviceId]);
            
            // Success message
            setFlashMessage('success', 'Service has been cancelled successfully.');
        } else {
            setFlashMessage('error', 'This service cannot be cancelled in its current state.');
        }
    } else {
        setFlashMessage('error', 'Service not found or does not belong to you.');
    }
    
    redirect('/dash/services');
}

// Set page title
$pageTitle = 'Services';

// Include header
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/loader.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-0">Services</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dash" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Services</li>
                </ol>
            </nav>
        </div>
        <div>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="orderNewService" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-plus me-2"></i> Order New Service
                </button>
                <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="orderNewService">
                    <li><a class="dropdown-item" href="/game-server">Game Server</a></li>
                    <li><a class="dropdown-item" href="/web-hosting">Web Hosting</a></li>
                    <li><a class="dropdown-item" href="/vps-hosting">VPS Hosting</a></li>
                </ul>
            </div>
        </div>
    </div>

    <h2 class="fw-bold mb-4">View all Services</h2>
    
    <div class="card bg-dark mb-4">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0" id="services-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Due Date</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">No services found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td>
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <?php if ($service['service_type'] === 'vps'): ?>
                                                <div class="icon-box rounded bg-primary-subtle">
                                                    <i class="fas fa-server text-primary"></i>
                                                </div>
                                            <?php elseif ($service['service_type'] === 'web_hosting'): ?>
                                                <div class="icon-box rounded bg-success-subtle">
                                                    <i class="fas fa-globe text-success"></i>
                                                </div>
                                            <?php elseif ($service['service_type'] === 'game_server'): ?>
                                                <div class="icon-box rounded bg-danger-subtle">
                                                    <i class="fas fa-gamepad text-danger"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="/dash/service/view/<?php echo $service['id']; ?>" class="text-primary text-decoration-none">
                                                <?php echo getServiceTypeLabel($service['service_type']); ?>
                                            </a>
                                            <div class="small text-muted"><?php echo $service['identifier']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo formatDate($service['due_date'], 'n/j/Y'); ?></td>
                                <td>You</td>
                                <td>
                                    <?php echo getStatusBadge($service['status']); ?>
                                </td>
                                <td class="text-end">
                                    <a href="/dash/service/view/<?php echo $service['id']; ?>" class="btn btn-primary btn-sm">View Service</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Active Services -->
    <?php
    $activeServices = array_filter($services, function($s) {
        return $s['status'] === 'active';
    });
    
    if (!empty($activeServices)):
    ?>
    <h2 class="fw-bold mb-4">Active Services</h2>
    <div class="row">
        <?php foreach ($activeServices as $service): ?>
            <div class="col-md-6 mb-4">
                <div class="card bg-dark">
                    <div class="card-header bg-darker d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><?php echo $service['name']; ?></h5>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="text-muted">Due Date:</div>
                            <div class="fw-bold"><?php echo formatDate($service['due_date'], 'F j, Y'); ?></div>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <div class="text-muted">Billing Cycle:</div>
                            <div class="fw-bold"><?php echo ucfirst($service['billing_cycle']); ?></div>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <div class="text-muted">Price:</div>
                            <div class="fw-bold"><?php echo formatCurrency($service['amount']); ?> / <?php echo getBillingCycleLabel($service['billing_cycle']); ?></div>
                        </div>
                        
                        <hr class="border-secondary my-3">
                        
                        <?php if ($service['next_invoice']): ?>
                            <div class="alert alert-warning mb-3">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Payment Due</div>
                                        <div>Next payment of <?php echo formatCurrency($service['next_invoice']['amount']); ?> is due on <?php echo formatDate($service['next_invoice']['due_date'], 'F j, Y'); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-grid">
                            <a href="/dash/service/view/<?php echo $service['id']; ?>" class="btn btn-primary">
                                Manage Service
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php
// Helper function to get service type label
function getServiceTypeLabel($type) {
    switch ($type) {
        case 'game_server':
            return 'Game Server';
        case 'web_hosting':
            return 'Web Hosting';
        case 'vps':
            return 'Virtual Private Server';
        default:
            return ucfirst($type);
    }
}

// Helper function to get billing cycle label
function getBillingCycleLabel($cycle) {
    switch ($cycle) {
        case 'monthly':
            return 'Month';
        case 'quarterly':
            return 'Quarter';
        case 'semiannually':
            return '6 Months';
        case 'annually':
            return 'Year';
        case 'biennially':
            return '2 Years';
        case 'triennially':
            return '3 Years';
        default:
            return ucfirst($cycle);
    }
}

// Helper function to get status badge
function getStatusBadge($status) {
    switch ($status) {
        case 'active':
            return '<span class="badge bg-success">Active</span>';
        case 'pending':
            return '<span class="badge bg-warning text-dark">Pending</span>';
        case 'suspended':
            return '<span class="badge bg-danger">Suspended</span>';
        case 'cancelled':
            return '<span class="badge bg-secondary">Cancelled</span>';
        case 'terminated':
            return '<span class="badge bg-dark">Terminated</span>';
        default:
            return '<span class="badge bg-primary">'.ucfirst($status).'</span>';
    }
}

require_once __DIR__ . '/../includes/footer.php';
?>