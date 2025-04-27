<?php
// cart/index.php - Shopping Cart
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cart.php';

// Get current user if logged in
$currentUser = null;
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
}

// Process cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Remove item from cart
        if ($_POST['action'] === 'remove' && isset($_POST['cart_item_id'])) {
            removeFromCart($_POST['cart_item_id']);
            setFlashMessage('success', 'Item removed from cart.');
            redirect('/cart');
        }
        
        // Clear cart
        if ($_POST['action'] === 'clear') {
            clearCart();
            setFlashMessage('success', 'Cart cleared.');
            redirect('/cart');
        }
        
        // Update billing period
        if ($_POST['action'] === 'update_billing' && isset($_POST['billing_period'])) {
            $_SESSION['billing_period'] = $_POST['billing_period'];
            setFlashMessage('success', 'Billing period updated.');
            redirect('/cart');
        }
    }
}

// Get cart items and summary
$cartItems = getCartItems();
$billingPeriod = $_SESSION['billing_period'] ?? 'monthly';
$cartSummary = getCartSummary($billingPeriod);

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
$pageTitle = 'Your Cart';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container py-5">
    <h1 class="fw-bold mb-4">Your Basket</h1>
    
    <?php if (empty($cartItems)): ?>
        <div class="card bg-dark mb-4">
            <div class="card-body p-4 text-center">
                <p class="mb-4">Your cart is empty.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="/games" class="btn btn-primary">Game Servers</a>
                    <a href="/web" class="btn btn-outline-primary">Web Hosting</a>
                    <a href="/vps" class="btn btn-outline-primary">VPS Hosting</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card bg-dark mb-4">
                    <div class="card-header bg-darker d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Product</h4>
                        <h4 class="card-title mb-0">Price</h4>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach ($cartItems as $cartItemId => $item): ?>
                            <div class="d-flex justify-content-between align-items-center p-4 border-bottom border-secondary">
                                <div>
                                    <h5 class="mb-1">
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
                                    </h5>
                                    <?php if (isset($item['email'])): ?>
                                        <p class="text-muted mb-0"><?php echo $item['email']; ?></p>
                                    <?php endif; ?>
                                    <?php if (isset($item['hostname'])): ?>
                                        <p class="text-muted mb-0"><?php echo $item['hostname']; ?></p>
                                    <?php endif; ?>
                                    <?php if (isset($item['domain'])): ?>
                                        <p class="text-muted mb-0"><?php echo $item['domain']; ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="mt-2">
                                        <a href="<?php echo getConfigureUrl($item['type'], $item['plan_id'] ?? null); ?>" class="text-primary me-3">
                                            Edit service →
                                        </a>
                                        <form method="post" action="" class="d-inline">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="cart_item_id" value="<?php echo $cartItemId; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger rounded-circle">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="h5 mb-0">€<?php echo number_format($item['price'], 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer bg-darker text-end">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-outline-danger">Clear Cart</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card bg-dark mb-4">
                    <div class="card-header bg-darker">
                        <h4 class="card-title mb-0">Overview</h4>
                    </div>
                    <div class="card-body">
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
                        
                        <div class="d-flex justify-content-between mb-4">
                            <div class="fw-bold">Total</div>
                            <div class="fw-bold">€<?php echo number_format($cartSummary['total'], 2); ?></div>
                        </div>
                        
                        <!-- Billing Period Selection -->
                        <div class="mb-4">
                            <h5 class="mb-3">Billing Period</h5>
                            <form method="post" action="">
                                <input type="hidden" name="action" value="update_billing">
                                <div class="row g-2">
                                    <?php foreach ($billingPeriods as $key => $period): ?>
                                        <div class="col-6 mb-2">
                                            <input type="radio" class="btn-check" name="billing_period" id="billing-<?php echo $key; ?>" value="<?php echo $key; ?>" <?php echo $key === $billingPeriod ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-primary w-100" for="billing-<?php echo $key; ?>">
                                                <?php echo $period['label']; ?>
                                                <?php if ($period['discount'] > 0): ?>
                                                    <br><small>Save <?php echo $period['save']; ?></small>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="submit" class="btn btn-sm btn-outline-primary mt-2 w-100">Update Period</button>
                            </form>
                        </div>
                        
                        <!-- Promocode input -->
                        <div class="mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control bg-dark border-secondary text-white" placeholder="Promocode">
                                <button class="btn btn-outline-primary" type="button">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <a href="/checkout" class="btn btn-primary w-100">
                            Complete Order →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>