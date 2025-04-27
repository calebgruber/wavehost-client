<?php
// checkout/index.php - Checkout Page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cart.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
    setFlashMessage('info', 'Please login or register to complete your order.');
    redirect('/auth/login');
}

// Get current user
$currentUser = getCurrentUser();

// Redirect to cart if empty
if (getCartItemCount() === 0) {
    setFlashMessage('info', 'Your cart is empty. Please add services before checkout.');
    redirect('/cart');
}

// Get cart items and summary
$cartItems = getCartItems();
$billingPeriod = $_SESSION['billing_period'] ?? 'monthly';
$cartSummary = getCartSummary($billingPeriod);

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate payment method
    $paymentMethod = $_POST['payment_method'] ?? '';
    
    if (empty($paymentMethod)) {
        setFlashMessage('error', 'Please select a payment method.');
        redirect('/checkout');
    }
    
    // Start transaction
    $db = db();
    try {
        $db->getConnection()->beginTransaction();
        
        // Create invoice
        $invoiceId = $db->insert('invoices', [
            'user_id' => $currentUser['id'],
            'amount' => $cartSummary['total'],
            'status' => 'unpaid',
            'due_date' => date('Y-m-d', strtotime('+7 days')),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Process each cart item
        foreach ($cartItems as $cartItemId => $item) {
            // Create service
            $serviceId = $db->insert('services', [
                'user_id' => $currentUser['id'],
                'service_type' => $item['type'],
                'name' => isset($item['hostname']) ? $item['hostname'] : (isset($item['domain']) ? $item['domain'] : 'New Service'),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'due_date' => date('Y-m-d', strtotime('+1 ' . $item['billing_period'])),
                'billing_cycle' => $item['billing_period'],
                'amount' => $item['price']
            ]);
            
            // Update invoice with service ID
            $db->update('invoices', [
                'service_id' => $serviceId
            ], 'id = ?', [$invoiceId]);
            
            // Add service details based on type
            switch ($item['type']) {
                case 'vps':
                    $db->insert('vps_hosting', [
                        'service_id' => $serviceId,
                        'hostname' => $item['hostname'],
                        'ram' => $item['ram'],
                        'cpu_cores' => $item['cores'],
                        'storage' => $item['storage'],
                        'operating_system' => $item['os'],
                        'ip_addresses' => json_encode(['ipv4' => $item['ipv4'], 'ipv6' => $item['ipv6']]),
                        'bandwidth' => 'unlimited',
                        'location' => $item['location'],
                        'server_details' => json_encode([
                            'password' => $item['password'], // In production, this should be encrypted
                            'uplink' => $item['uplink'] ?? '1gbit'
                        ])
                    ]);
                    break;
                    
                case 'web_hosting':
                    $db->insert('web_hosting', [
                        'service_id' => $serviceId,
                        'domain' => $item['domain'],
                        'disk_space' => $item['disk_space'],
                        'bandwidth' => $item['bandwidth'],
                        'db_count' => $item['databases'],
                        'email_accounts' => $item['email_accounts'],
                        'server_details' => json_encode([
                            'password' => $item['password'], // In production, this should be encrypted
                            'features' => $item['features'] ?? []
                        ])
                    ]);
                    break;
                    
                case 'game_server':
                    $db->insert('game_servers', [
                        'service_id' => $serviceId,
                        'game_type' => $item['game_type'],
                        'slots' => $item['slots'],
                        'location' => $item['location'],
                        'ram' => $item['ram'],
                        'cpu_limit' => $item['cpu_limit'],
                        'disk_space' => $item['disk_space'],
                        'server_details' => json_encode([
                            'password' => $item['password'], // In production, this should be encrypted
                            'mods' => $item['mods'] ?? [],
                            'settings' => $item['settings'] ?? []
                        ])
                    ]);
                    break;
            }
        }
        
        // Create payment record
        $db->insert('payments', [
            'user_id' => $currentUser['id'],
            'invoice_id' => $invoiceId,
            'amount' => $cartSummary['total'],
            'payment_method' => $paymentMethod,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Commit transaction
        $db->getConnection()->commit();
        
        // Clear cart
        clearCart();
        
        // Success message
        setFlashMessage('success', 'Your order has been placed successfully. You will be redirected to the payment gateway.');
        
        // Redirect to payment gateway
        redirect('/payment/gateway/' . $invoiceId);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->getConnection()->rollBack();
        setFlashMessage('error', 'An error occurred while processing your order. Please try again.');
        redirect('/checkout');
    }
}

// Payment methods
$paymentMethods = [
    'credit_card' => [
        'name' => 'Credit/Debit Card',
        'icon' => 'fa-credit-card'
    ],
    'paypal' => [
        'name' => 'PayPal',
        'icon' => 'fa-paypal'
    ],
    'bitcoin' => [
        'name' => 'Bitcoin',
        'icon' => 'fa-bitcoin'
    ],
    'bank_transfer' => [
        'name' => 'Bank Transfer',
        'icon' => 'fa-university'
    ]
];

// Billing period options
$billingPeriods = [
    'monthly' => [
        'label' => 'Monthly',
        'discount' => 0,
        'save' => '0%'
    ],
    'quarterly' => [
        'label' => 'Quarterly',
        'discount' => 5,
        'save' => '5%'
    ],
    'semiannually' => [
        'label' => 'Semiannually',
        'discount' => 10,
        'save' => '10%'
    ],
    'annually' => [
        'label' => 'Annually',
        'discount' => 15,
        'save' => '15%'
    ],
    'biennially' => [
        'label' => 'Biennially',
        'discount' => 20,
        'save' => '20%'
    ],
    'triennially' => [
        'label' => 'Triennially',
        'discount' => 25,
        'save' => '25%'
    ]
];

// Set page title
$pageTitle = 'Checkout';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container py-5">
    <h1 class="fw-bold mb-4">Checkout</h1>
    
    <div class="row">
        <!-- Order Summary -->
        <div class="col-lg-4 order-lg-2 mb-4">
            <div class="card bg-dark mb-4">
                <div class="card-header bg-darker">
                    <h4 class="card-title mb-0">Order Summary</h4>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush bg-transparent">
                        <?php foreach ($cartItems as $cartItemId => $item): ?>
                            <div class="list-group-item bg-transparent border-bottom border-secondary px-4 py-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <?php 
                                        $typeName = '';
                                        switch ($item['type']) {
                                            case 'game_server':
                                                $typeName = 'Game Server';
                                                break;
                                            case 'web_hosting':
                                                $typeName = 'Web Hosting';
                                                break;
                                            case 'vps':
                                                $typeName = 'Virtual Private Server';
                                                break;
                                        }
                                        echo $typeName; 
                                        ?>
                                    </div>
                                    <div>€<?php echo number_format($item['price'], 2); ?></div>
                                </div>
                                <div class="small text-muted">
                                    <?php if (isset($item['hostname'])): ?>
                                        <?php echo $item['hostname']; ?>
                                    <?php elseif (isset($item['domain'])): ?>
                                        <?php echo $item['domain']; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-footer bg-darker">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <div>Sub Total</div>
                            <div>€<?php echo number_format($cartSummary['subtotal'], 2); ?></div>
                        </div>
                        
                        <?php if ($cartSummary['discount_percentage'] > 0): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <div>Cycle discount (<?php echo $billingPeriods[$billingPeriod]['label']; ?>)</div>
                                <div>-€<?php echo number_format($cartSummary['discount_amount'], 2); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <div><?php echo $cartSummary['vat_rate']; ?>% VAT</div>
                            <div>€<?php echo number_format($cartSummary['vat_amount'], 2); ?></div>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <div>Setup</div>
                            <div>€0.00</div>
                        </div>
                    </div>
                    
                    <hr class="border-secondary my-3">
                    
                    <div class="d-flex justify-content-between">
                        <div class="fw-bold">Total</div>
                        <div class="fw-bold">€<?php echo number_format($cartSummary['total'], 2); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="card bg-dark">
                <div class="card-body">
                    <h5 class="mb-3">Need Help?</h5>
                    <p class="text-muted mb-0">If you have any questions or need assistance with your order, please <a href="/support" class="text-primary">contact our support team</a>.</p>
                </div>
            </div>
        </div>
        
        <!-- Customer Details & Payment -->
        <div class="col-lg-8 order-lg-1">
            <div class="card bg-dark mb-4">
                <div class="card-header bg-darker">
                    <h4 class="card-title mb-0">Customer Details</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6>Personal Information</h6>
                            <p class="mb-1"><?php echo $currentUser['first_name'] . ' ' . $currentUser['last_name']; ?></p>
                            <p class="mb-1"><?php echo $currentUser['email']; ?></p>
                            <p class="mb-0"><?php echo $currentUser['phone'] ?? 'No phone number'; ?></p>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>Billing Address</h6>
                            <?php if (!empty($currentUser['address'])): ?>
                                <p class="mb-1"><?php echo $currentUser['address']; ?></p>
                                <p class="mb-1"><?php echo $currentUser['city'] . ', ' . $currentUser['state'] . ' ' . $currentUser['postal_code']; ?></p>
                                <p class="mb-0"><?php echo $currentUser['country']; ?></p>
                            <?php else: ?>
                                <p class="text-muted mb-0">No billing address available. <a href="/dash/account" class="text-primary">Add billing address</a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <a href="/dash/account" class="btn btn-outline-primary">Edit Details</a>
                    </div>
                </div>
            </div>
            
            <div class="card bg-dark mb-4">
                <div class="card-header bg-darker">
                    <h4 class="card-title mb-0">Payment Method</h4>
                </div>
                <div class="card-body">
                    <form method="post" action="" id="payment-form">
                        <div class="row g-3 mb-4">
                            <?php foreach ($paymentMethods as $id => $method): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check card bg-darker h-100 p-0">
                                        <input class="form-check-input visually-hidden" type="radio" name="payment_method" id="payment-<?php echo $id; ?>" value="<?php echo $id; ?>" <?php echo $id === 'credit_card' ? 'checked' : ''; ?>>
                                        <label class="form-check-label card-body d-flex align-items-center" for="payment-<?php echo $id; ?>">
                                            <div class="me-3">
                                                <i class="fas <?php echo $method['icon']; ?> fa-2x text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo $method['name']; ?></div>
                                                <div class="small text-muted">Pay using your <?php echo strtolower($method['name']); ?></div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="agree-terms" required>
                            <label class="form-check-label" for="agree-terms">
                                I agree to the <a href="/legal/tos" class="text-primary">Terms of Service</a> and <a href="/privacy" class="text-primary">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="/cart" class="btn btn-outline-primary">Back to Cart</a>
                            <button type="submit" class="btn btn-primary">Complete Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment method selection
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            // Remove selected class from all
            document.querySelectorAll('.form-check.card').forEach(card => {
                card.classList.remove('border-primary');
            });
            
            // Add selected class to checked
            if (this.checked) {
                this.closest('.form-check.card').classList.add('border-primary');
            }
        });
        
        // Initialize selected state
        if (method.checked) {
            method.closest('.form-check.card').classList.add('border-primary');
        }
    });
    
    // Form validation
    const paymentForm = document.getElementById('payment-form');
    
    paymentForm.addEventListener('submit', function(event) {
        const termsCheckbox = document.getElementById('agree-terms');
        
        if (!termsCheckbox.checked) {
            event.preventDefault();
            alert('You must agree to the Terms of Service and Privacy Policy to proceed.');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>