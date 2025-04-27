<?php
// dash/invoices.php - Invoices page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
    setFlashMessage('info', 'Please login to access your invoices.');
    redirect('/auth/login');
}

// Get current user
$currentUser = getCurrentUser();

// Get invoices
$db = db();
$invoices = $db->select(
    "SELECT i.*, s.name as service_name, s.service_type 
     FROM invoices i 
     LEFT JOIN services s ON i.service_id = s.id 
     WHERE i.user_id = ? 
     ORDER BY i.created_at DESC",
    [$currentUser['id']]
);

// Handle payment form submission
if (isset($_POST['pay_invoice']) && isset($_POST['invoice_id'])) {
    $invoiceId = $_POST['invoice_id'];
    
    // Check if invoice exists and belongs to user
    $invoice = $db->selectOne(
        "SELECT * FROM invoices WHERE id = ? AND user_id = ? AND status = 'unpaid'",
        [$invoiceId, $currentUser['id']]
    );
    
    if ($invoice) {
        // Redirect to payment page
        redirect('/payment/gateway/' . $invoiceId);
    } else {
        setFlashMessage('error', 'Invalid invoice or already paid.');
        redirect('/dash/invoices');
    }
}

// Set page title
$pageTitle = 'Invoices';

// Include header
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/loader.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-0">Invoices</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dash" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Invoices</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="/dash" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Invoice Balance Summary -->
    <div class="card bg-dark mb-4">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="small text-muted mb-1">Total Due</div>
                    <div class="fw-bold fs-4">
                        <?php
                        $totalDue = 0;
                        foreach ($invoices as $invoice) {
                            if ($invoice['status'] === 'unpaid') {
                                $totalDue += $invoice['amount'];
                            }
                        }
                        echo formatCurrency($totalDue);
                        ?>
                    </div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="small text-muted mb-1">Overdue</div>
                    <div class="fw-bold fs-4">
                        <?php
                        $overdue = 0;
                        foreach ($invoices as $invoice) {
                            if ($invoice['status'] === 'overdue') {
                                $overdue += $invoice['amount'];
                            }
                        }
                        echo formatCurrency($overdue);
                        ?>
                    </div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="small text-muted mb-1">Paid (This Month)</div>
                    <div class="fw-bold fs-4">
                        <?php
                        $paidThisMonth = 0;
                        $thisMonth = date('Y-m');
                        foreach ($invoices as $invoice) {
                            if ($invoice['status'] === 'paid' && strpos($invoice['paid_at'], $thisMonth) === 0) {
                                $paidThisMonth += $invoice['amount'];
                            }
                        }
                        echo formatCurrency($paidThisMonth);
                        ?>
                    </div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="small text-muted mb-1">Total Invoices</div>
                    <div class="fw-bold fs-4"><?php echo count($invoices); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Unpaid Invoices -->
    <?php
    $unpaidInvoices = array_filter($invoices, function($invoice) {
        return $invoice['status'] === 'unpaid' || $invoice['status'] === 'overdue';
    });
    
    if (!empty($unpaidInvoices)):
    ?>
    <div class="card bg-dark mb-4">
        <div class="card-header bg-darker">
            <h4 class="card-title mb-0">Unpaid Invoices</h4>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th>Service</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unpaidInvoices as $invoice): ?>
                        <tr>
                            <td>
                                <a href="/invoice/view/<?php echo $invoice['id']; ?>" class="text-primary text-decoration-none">
                                    #<?php echo str_pad($invoice['id'], 5, '0', STR_PAD_LEFT); ?>
                                </a>
                            </td>
                            <td><?php echo formatDate($invoice['created_at'], 'd/m/Y'); ?></td>
                            <td>
                                <?php 
                                echo formatDate($invoice['due_date'], 'd/m/Y');
                                if ($invoice['status'] === 'overdue') {
                                    echo ' <span class="badge bg-danger">Overdue</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($invoice['service_name'])): ?>
                                    <span class="d-block"><?php echo $invoice['service_name']; ?></span>
                                    <small class="text-muted">
                                        <?php
                                        switch ($invoice['service_type']) {
                                            case 'game_server':
                                                echo 'Game Server';
                                                break;
                                            case 'web_hosting':
                                                echo 'Web Hosting';
                                                break;
                                            case 'vps':
                                                echo 'VPS Hosting';
                                                break;
                                            default:
                                                echo ucfirst($invoice['service_type']);
                                        }
                                        ?>
                                    </small>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatCurrency($invoice['amount']); ?></td>
                            <td>
                                <?php if ($invoice['status'] === 'unpaid'): ?>
                                    <span class="badge bg-warning text-dark">Unpaid</span>
                                <?php elseif ($invoice['status'] === 'overdue'): ?>
                                    <span class="badge bg-danger">Overdue</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <form method="post" action="" class="d-inline">
                                    <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                                    <button type="submit" name="pay_invoice" class="btn btn-primary btn-sm">
                                        <i class="fas fa-credit-card me-1"></i> Pay Now
                                    </button>
                                </form>
                                <a href="/invoice/view/<?php echo $invoice['id']; ?>" class="btn btn-outline-primary btn-sm ms-1">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Invoice History -->
    <div class="card bg-dark">
        <div class="card-header bg-darker d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Invoice History</h4>
            <div>
                <div class="input-group">
                    <input type="text" class="form-control bg-dark border-secondary" placeholder="Search invoices..." id="invoice-search">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0" id="invoice-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th>Service</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">No invoices found</td>
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
                                <td>
                                    <?php if (!empty($invoice['service_name'])): ?>
                                        <span class="d-block"><?php echo $invoice['service_name']; ?></span>
                                        <small class="text-muted">
                                            <?php
                                            switch ($invoice['service_type']) {
                                                case 'game_server':
                                                    echo 'Game Server';
                                                    break;
                                                case 'web_hosting':
                                                    echo 'Web Hosting';
                                                    break;
                                                case 'vps':
                                                    echo 'VPS Hosting';
                                                    break;
                                                default:
                                                    echo ucfirst($invoice['service_type']);
                                            }
                                            ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
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
                                        <form method="post" action="" class="d-inline">
                                            <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                                            <button type="submit" name="pay_invoice" class="btn btn-primary btn-sm">
                                                <i class="fas fa-credit-card me-1"></i> Pay Now
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="/invoice/view/<?php echo $invoice['id']; ?>" class="btn btn-outline-primary btn-sm ms-1">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($invoice['status'] === 'paid'): ?>
                                        <a href="/invoice/pdf/<?php echo $invoice['id']; ?>" class="btn btn-outline-primary btn-sm ms-1">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Simple invoice search functionality
    const searchInput = document.getElementById('invoice-search');
    const invoiceTable = document.getElementById('invoice-table');
    const rows = invoiceTable.querySelectorAll('tbody tr');

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