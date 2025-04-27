<?php
// dash/affiliate.php - Affiliate program page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
    setFlashMessage('info', 'Please login to access your affiliate dashboard.');
    redirect('/auth/login');
}

// Get current user
$currentUser = getCurrentUser();

// Get affiliate data
$db = db();
$affiliateData = $db->selectOne(
    "SELECT * FROM affiliate_data WHERE user_id = ?",
    [$currentUser['id']]
);

// If user doesn't have affiliate data, create it
if (!$affiliateData) {
    $referralCode = $currentUser['username'] . '_' . substr(md5(uniqid()), 0, 8);
    
    $db->insert('affiliate_data', [
        'user_id' => $currentUser['id'],
        'referral_code' => $referralCode,
        'commission_rate' => 10, // Default 10%
        'earned_amount' => 0,
        'paid_amount' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $affiliateData = $db->selectOne(
        "SELECT * FROM affiliate_data WHERE user_id = ?",
        [$currentUser['id']]
    );
}

// Get referrals
$referrals = $db->select(
    "SELECT ad.*, u.username, u.created_at as registration_date 
     FROM affiliate_data ad 
     JOIN users u ON ad.user_id = u.id 
     WHERE ad.referred_by = ?",
    [$currentUser['id']]
);

// Get commissions
$commissions = $db->select(
    "SELECT ac.*, i.id as invoice_id, i.amount as invoice_amount, u.username 
     FROM affiliate_commissions ac 
     JOIN invoices i ON ac.invoice_id = i.id 
     JOIN users u ON i.user_id = u.id 
     WHERE ac.affiliate_id = ? 
     ORDER BY ac.created_at DESC",
    [$currentUser['id']]
);

// Get withdrawals
$withdrawals = $db->select(
    "SELECT * FROM affiliate_withdrawals 
     WHERE affiliate_id = ? 
     ORDER BY created_at DESC",
    [$currentUser['id']]
);

// Calculate statistics
$totalReferrals = count($referrals);
$totalCommissions = $affiliateData['earned_amount'] ?? 0;
$availableBalance = ($affiliateData['earned_amount'] ?? 0) - ($affiliateData['paid_amount'] ?? 0);

// Process withdrawal request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw'])) {
    $amount = floatval($_POST['amount'] ?? 0);
    $paymentMethod = $_POST['payment_method'] ?? '';
    $paymentDetails = $_POST['payment_details'] ?? '';
    
    if ($amount <= 0) {
        setFlashMessage('error', 'Please enter a valid amount.');
    } elseif ($amount > $availableBalance) {
        setFlashMessage('error', 'You cannot withdraw more than your available balance.');
    } elseif (empty($paymentMethod)) {
        setFlashMessage('error', 'Please select a payment method.');
    } elseif (empty($paymentDetails)) {
        setFlashMessage('error', 'Please enter your payment details.');
    } else {
        // Create withdrawal request
        $db->insert('affiliate_withdrawals', [
            'affiliate_id' => $currentUser['id'],
            'amount' => $amount,
            'status' => 'pending',
            'payment_method' => $paymentMethod,
            'payment_details' => $paymentDetails,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        setFlashMessage('success', 'Your withdrawal request has been submitted and is pending approval.');
        redirect('/dash/affiliate');
    }
}

// Set page title
$pageTitle = 'Affiliate Program';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php require_once __DIR__ . '/../includes/loader.php'; ?>

<div class="container py-5">
    <h1 class="fw-bold mb-4">Affiliate Program</h1>
    
    <?php if ($flashMessage = getFlashMessage()): ?>
        <div class="alert alert-<?php echo $flashMessage['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $flashMessage['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <!-- Statistics Cards -->
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card bg-dark h-100">
                <div class="card-body text-center">
                    <div class="icon-box bg-primary-subtle mx-auto mb-3 rounded-circle">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                    <h3 class="display-6"><?php echo $totalReferrals; ?></h3>
                    <p class="text-muted mb-0">Total Referrals</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card bg-dark h-100">
                <div class="card-body text-center">
                    <div class="icon-box bg-success-subtle mx-auto mb-3 rounded-circle">
                        <i class="fas fa-euro-sign fa-2x text-success"></i>
                    </div>
                    <h3 class="display-6">€<?php echo number_format($totalCommissions, 2); ?></h3>
                    <p class="text-muted mb-0">Total Commissions</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-dark h-100">
                <div class="card-body text-center">
                    <div class="icon-box bg-info-subtle mx-auto mb-3 rounded-circle">
                        <i class="fas fa-wallet fa-2x text-info"></i>
                    </div>
                    <h3 class="display-6">€<?php echo number_format($availableBalance, 2); ?></h3>
                    <p class="text-muted mb-0">Available Balance</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <!-- Referral Link -->
        <div class="col-lg-8 mb-4">
            <div class="card bg-dark mb-4">
                <div class="card-header bg-darker">
                    <h4 class="card-title mb-0">Your Referral Link</h4>
                </div>
                <div class="card-body">
                    <p class="mb-3">Share this link with your friends and earn <?php echo $affiliateData['commission_rate']; ?>% commission on every payment they make!</p>
                    
                    <div class="input-group mb-3">
                        <input type="text" class="form-control bg-dark border-secondary text-white" id="referral-link" value="<?php echo SITE_URL . '/register?ref=' . ($affiliateData['referral_code'] ?? ''); ?>" readonly>
                        <button class="btn btn-primary" type="button" id="copy-link" onclick="copyReferralLink()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    
                    <div class="alert alert-info mb-0">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-info-circle fa-2x"></i>
                            </div>
                            <div>
                                <p class="mb-0">You'll earn <?php echo $affiliateData['commission_rate']; ?>% of each payment your referrals make. Commissions are added to your balance after the payment is confirmed.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Withdrawal Form -->
            <div class="card bg-dark">
                <div class="card-header bg-darker">
                    <h4 class="card-title mb-0">Request Withdrawal</h4>
                </div>
                <div class="card-body">
                    <?php if ($availableBalance >= 10): ?>
                        <form method="post" action="">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="amount" class="form-label">Amount (€)</label>
                                    <input type="number" class="form-control bg-dark border-secondary text-white" id="amount" name="amount" min="10" max="<?php echo $availableBalance; ?>" step="0.01" required>
                                    <div class="form-text">Minimum withdrawal: €10.00</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="payment-method" class="form-label">Payment Method</label>
                                    <select class="form-select bg-dark border-secondary text-white" id="payment-method" name="payment_method" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="paypal">PayPal</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="bitcoin">Bitcoin</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="payment-details" class="form-label">Payment Details</label>
                                <textarea class="form-control bg-dark border-secondary text-white" id="payment-details" name="payment_details" rows="3" placeholder="Enter your payment details (e.g. PayPal email, bank account details, Bitcoin address)" required></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" name="withdraw" class="btn btn-primary">Request Withdrawal</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                                <div>
                                    <p class="mb-0">You need a minimum balance of €10.00 to request a withdrawal. Your current balance is €<?php echo number_format($availableBalance, 2); ?>.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Marketing Materials -->
        <div class="col-lg-4">
            <div class="card bg-dark">
                <div class="card-header bg-darker">
                    <h4 class="card-title mb-0">Marketing Materials</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>Banner (468x60)</h5>
                        <img src="/assets/images/affiliate/banner-468x60.png" alt="WaveHost Affiliate Banner" class="img-fluid mb-2 border border-dark">
                        <div class="input-group input-group-sm mb-2">
                            <input type="text" class="form-control bg-dark border-secondary text-white" value='<a href="<?php echo SITE_URL; ?>/register?ref=<?php echo $affiliateData['referral_code'] ?? ''; ?>"><img src="<?php echo SITE_URL; ?>/assets/images/affiliate/banner-468x60.png" alt="WaveHost" /></a>' readonly>
                            <button class="btn btn-outline-primary btn-sm" type="button" onclick="copyToClipboard(this.previousElementSibling.value)">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Square (250x250)</h5>
                        <img src="/assets/images/affiliate/banner-250x250.png" alt="WaveHost Affiliate Banner" class="img-fluid mb-2 border border-dark">
                        <div class="input-group input-group-sm mb-2">
                            <input type="text" class="form-control bg-dark border-secondary text-white" value='<a href="<?php echo SITE_URL; ?>/register?ref=<?php echo $affiliateData['referral_code'] ?? ''; ?>"><img src="<?php echo SITE_URL; ?>/assets/images/affiliate/banner-250x250.png" alt="WaveHost" /></a>' readonly>
                            <button class="btn btn-outline-primary btn-sm" type="button" onclick="copyToClipboard(this.previousElementSibling.value)">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <h5>Text Link</h5>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control bg-dark border-secondary text-white" value='<a href="<?php echo SITE_URL; ?>/register?ref=<?php echo $affiliateData['referral_code'] ?? ''; ?>">Get reliable hosting with WaveHost!</a>' readonly>
                            <button class="btn btn-outline-primary btn-sm" type="button" onclick="copyToClipboard(this.previousElementSibling.value)">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Referrals Table -->
        <div class="col-lg-6 mb-4">
            <div class="card bg-dark">
                <div class="card-header bg-darker">
                    <h4 class="card-title mb-0">Your Referrals</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Registration Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($referrals) > 0): ?>
                                    <?php foreach ($referrals as $referral): ?>
                                        <tr>
                                            <td><?php echo $referral['username']; ?></td>
                                            <td><?php echo formatDate($referral['registration_date']); ?></td>
                                            <td>
                                                <span class="badge bg-success">Active</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4">
                                            <div class="text-muted">No referrals yet. Share your referral link to start earning!</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Commission History -->
        <div class="col-lg-6 mb-4">
            <div class="card bg-dark">
                <div class="card-header bg-darker">
                    <h4 class="card-title mb-0">Commission History</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Referred User</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($commissions) > 0): ?>
                                    <?php foreach ($commissions as $commission): ?>
                                        <tr>
                                            <td><?php echo formatDate($commission['created_at']); ?></td>
                                            <td><?php echo $commission['username']; ?></td>
                                            <td>€<?php echo number_format($commission['amount'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4">
                                            <div class="text-muted">No commissions yet. Refer users to earn commissions!</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Withdrawal History -->
        <div class="col-12">
            <div class="card bg-dark">
                <div class="card-header bg-darker">
                    <h4 class="card-title mb-0">Withdrawal History</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($withdrawals) > 0): ?>
                                    <?php foreach ($withdrawals as $withdrawal): ?>
                                        <tr>
                                            <td><?php echo formatDate($withdrawal['created_at']); ?></td>
                                            <td>€<?php echo number_format($withdrawal['amount'], 2); ?></td>
                                            <td><?php echo ucfirst(str_replace('_', ' ', $withdrawal['payment_method'])); ?></td>
                                            <td>
                                                <?php if ($withdrawal['status'] === 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php elseif ($withdrawal['status'] === 'completed'): ?>
                                                    <span class="badge bg-success">Completed</span>
                                                <?php elseif ($withdrawal['status'] === 'rejected'): ?>
                                                    <span class="badge bg-danger">Rejected</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="text-muted">No withdrawals yet. Earn commissions to request withdrawals!</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyReferralLink() {
    const linkInput = document.getElementById('referral-link');
    linkInput.select();
    document.execCommand('copy');
    
    const copyButton = document.getElementById('copy-link');
    const originalText = copyButton.innerHTML;
    
    copyButton.innerHTML = '<i class="fas fa-check"></i> Copied!';
    
    setTimeout(() => {
        copyButton.innerHTML = originalText;
    }, 2000);
}

function copyToClipboard(text) {
    const el = document.createElement('textarea');
    el.value = text;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    
    // Show a tooltip or some indication that it was copied
    const button = event.currentTarget;
    const originalHtml = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-check"></i>';
    
    setTimeout(() => {
        button.innerHTML = originalHtml;
    }, 2000);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>