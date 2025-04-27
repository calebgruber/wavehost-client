<?php
// dash/tickets.php - Tickets page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
    setFlashMessage('info', 'Please login to access your tickets.');
    redirect('/auth/login');
}

// Get current user
$currentUser = getCurrentUser();

// Get tickets
$db = db();
$tickets = $db->select(
    "SELECT t.*, 
            (SELECT COUNT(*) FROM ticket_replies WHERE ticket_id = t.id) as reply_count 
     FROM tickets t 
     WHERE t.user_id = ? 
     ORDER BY t.updated_at DESC",
    [$currentUser['id']]
);

// Set page title
$pageTitle = 'Support Tickets';

// Include header
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/loader.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-0">Tickets</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dash" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tickets</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="/dash" class="btn btn-outline-primary me-2">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>
            <a href="/dash/ticket/new" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Open New Ticket
            </a>
        </div>
    </div>

    <!-- View all tickets -->
    <div class="card bg-dark mb-4">
        <div class="card-header bg-darker d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">View all Tickets</h4>
            <div>
                <div class="input-group">
                    <input type="text" class="form-control bg-dark border-secondary" placeholder="Search tickets..." id="ticket-search">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0" id="tickets-table">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Subject</th>
                        <th>Last Update</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
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
                                <td><?php echo formatDate($ticket['updated_at'], 'n/j/Y g:i:s A'); ?></td>
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
                                    <a href="/dash/ticket/view/<?php echo $ticket['id']; ?>" class="btn btn-primary btn-sm">View Ticket</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ticket Status Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card bg-dark text-center">
                <div class="card-body">
                    <div class="display-4 mb-2"><?php echo count(array_filter($tickets, function($t) { return $t['status'] === 'open'; })); ?></div>
                    <div class="text-success">Open Tickets</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card bg-dark text-center">
                <div class="card-body">
                    <div class="display-4 mb-2"><?php echo count(array_filter($tickets, function($t) { return $t['status'] === 'answered'; })); ?></div>
                    <div class="text-primary">Answered Tickets</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-dark text-center">
                <div class="card-body">
                    <div class="display-4 mb-2"><?php echo count(array_filter($tickets, function($t) { return $t['status'] === 'closed'; })); ?></div>
                    <div class="text-danger">Closed Tickets</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Open Tickets -->
    <?php
    $openTickets = array_filter($tickets, function($t) {
        return $t['status'] === 'open' || $t['status'] === 'answered';
    });
    
    if (!empty($openTickets)):
    ?>
    <h2 class="fw-bold mb-4">Open Tickets</h2>
    <div class="row">
        <?php foreach ($openTickets as $ticket): ?>
            <div class="col-md-6 mb-4">
                <div class="card bg-dark h-100">
                    <div class="card-header bg-darker d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><?php echo clean($ticket['subject']); ?></h5>
                        <?php
                        switch ($ticket['status']) {
                            case 'open':
                                echo '<span class="badge bg-success">Open</span>';
                                break;
                            case 'answered':
                                echo '<span class="badge bg-primary">Answered</span>';
                                break;
                        }
                        ?>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="small text-muted mb-1">Ticket ID</div>
                            <div class="fw-bold">#<?php echo substr(md5($ticket['id']), 0, 8); ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="small text-muted mb-1">Created</div>
                            <div class="fw-bold"><?php echo formatDate($ticket['created_at'], 'F j, Y, g:i A'); ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="small text-muted mb-1">Last Updated</div>
                            <div class="fw-bold"><?php echo formatDate($ticket['updated_at'], 'F j, Y, g:i A'); ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="small text-muted mb-1">Priority</div>
                            <div class="fw-bold">
                                <?php
                                switch ($ticket['priority']) {
                                    case 'low':
                                        echo '<span class="text-success">Low</span>';
                                        break;
                                    case 'medium':
                                        echo '<span class="text-warning">Medium</span>';
                                        break;
                                    case 'high':
                                        echo '<span class="text-danger">High</span>';
                                        break;
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="small text-muted mb-1">Replies</div>
                            <div class="fw-bold"><?php echo $ticket['reply_count']; ?></div>
                        </div>
                        
                        <a href="/dash/ticket/view/<?php echo $ticket['id']; ?>" class="btn btn-primary w-100">View Ticket</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Knowledge Base Section -->
    <div class="card bg-dark mt-5">
        <div class="card-header bg-darker">
            <h4 class="card-title mb-0">Knowledge Base</h4>
        </div>
        <div class="card-body">
            <p class="card-text">Before opening a new ticket, you might find answers to your questions in our knowledge base.</p>
            
            <div class="row mt-4">
                <div class="col-md-4 mb-4">
                    <div class="card bg-darker h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box rounded bg-primary-subtle me-3">
                                    <i class="fas fa-server text-primary"></i>
                                </div>
                                <h5 class="card-title mb-0">Server Management</h5>
                            </div>
                            <p class="card-text text-muted">Learn how to manage your servers, including reboots, upgrades, and configuration.</p>
                            <a href="/support/kb/server-management" class="btn btn-outline-primary stretched-link">Read Articles</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card bg-darker h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box rounded bg-success-subtle me-3">
                                    <i class="fas fa-credit-card text-success"></i>
                                </div>
                                <h5 class="card-title mb-0">Billing & Payments</h5>
                            </div>
                            <p class="card-text text-muted">Find information about payment methods, billing cycles, and how to update your billing details.</p>
                            <a href="/support/kb/billing" class="btn btn-outline-primary stretched-link">Read Articles</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card bg-darker h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box rounded bg-info-subtle me-3">
                                    <i class="fas fa-shield-alt text-info"></i>
                                </div>
                                <h5 class="card-title mb-0">Security</h5>
                            </div>
                            <p class="card-text text-muted">Learn about best practices for securing your servers and services against various threats.</p>
                            <a href="/support/kb/security" class="btn btn-outline-primary stretched-link">Read Articles</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Simple ticket search functionality
    const searchInput = document.getElementById('ticket-search');
    const ticketsTable = document.getElementById('tickets-table');
    const rows = ticketsTable.querySelectorAll('tbody tr');

    searchInput.addEventListener('keyup', function() {
        const searchTerm = searchInput.value.toLowerCase();

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>