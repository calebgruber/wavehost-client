<?php
// cart/configure/web-hosting.php - Configure Web Hosting
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/cart.php';

// Get current user if logged in
$currentUser = null;
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
}

// Get plan ID from URL if provided
$planId = isset($_GET['plan']) ? $_GET['plan'] : null;

// Set default values or load from plan
$db = db();
$selectedPlan = null;

if ($planId) {
    // Load plan details from database
    $selectedPlan = $db->selectOne(
        "SELECT * FROM web_hosting_plans WHERE id = ?",
        [$planId]
    );
    
    if ($selectedPlan) {
        $selectedDiskSpace = $selectedPlan['disk_space'] ?? 10;
        $selectedBandwidth = $selectedPlan['bandwidth'] ?? 100;
        $selectedDatabases = $selectedPlan['databases'] ?? 5;
        $selectedEmails = $selectedPlan['email_accounts'] ?? 10;
    }
}

// Set defaults if no plan selected or found
if (!$selectedPlan) {
    $selectedDiskSpace = isset($_GET['disk']) ? (int)$_GET['disk'] : 10;
    $selectedBandwidth = isset($_GET['bandwidth']) ? (int)$_GET['bandwidth'] : 100;
    $selectedDatabases = isset($_GET['databases']) ? (int)$_GET['databases'] : 5;
    $selectedEmails = isset($_GET['emails']) ? (int)$_GET['emails'] : 10;
}

// Default location
$selectedLocation = isset($_GET['location']) ? $_GET['location'] : 'netherlands';

// Calculate price based on selections
$basePrice = 3; // Base price in EUR
$diskPrice = floor($selectedDiskSpace / 10) * 1; // €1 per 10GB
$bandwidthPrice = 0; // Bandwidth is unlimited
$databasesPrice = max(0, ceil(($selectedDatabases - 5) / 5)) * 1; // €1 per 5 databases over 5
$emailsPrice = max(0, ceil(($selectedEmails - 10) / 10)) * 1; // €1 per 10 email accounts over 10

// Calculate total price
$totalPrice = $basePrice + $diskPrice + $bandwidthPrice + $databasesPrice + $emailsPrice;

// Billing period discounts
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

// Set default billing period or get from session
$selectedBillingPeriod = $_SESSION['billing_period'] ?? 'monthly';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $domain = trim($_POST['domain'] ?? '');
    $diskSpace = (int)($_POST['disk_space'] ?? 10);
    $bandwidth = (int)($_POST['bandwidth'] ?? 100);
    $databases = (int)($_POST['databases'] ?? 5);
    $emails = (int)($_POST['emails'] ?? 10);
    $location = $_POST['location'] ?? 'netherlands';
    $billingPeriod = $_POST['billing_period'] ?? 'monthly';
    $password = trim($_POST['password'] ?? '');
    
    // Validate domain
    if (empty($domain)) {
        setFlashMessage('error', 'Domain is required.');
        redirect('/cart/configure/web-hosting');
    }
    
    // Validate password
    if (empty($password)) {
        setFlashMessage('error', 'Password is required.');
        redirect('/cart/configure/web-hosting');
    }
    
    // Calculate price
    $diskPrice = floor($diskSpace / 10) * 1;
    $databasesPrice = max(0, ceil(($databases - 5) / 5)) * 1;
    $emailsPrice = max(0, ceil(($emails - 10) / 10)) * 1;
    
    $price = $basePrice + $diskPrice + $databasesPrice + $emailsPrice;
    
    // Create cart item
    $cartItem = [
        'id' => uniqid('web_'),
        'plan_id' => $planId,
        'type' => 'web_hosting',
        'price' => $price,
        'domain' => $domain,
        'disk_space' => $diskSpace,
        'bandwidth' => 'unlimited',
        'databases' => $databases,
        'email_accounts' => $emails,
        'location' => $location,
        'billing_period' => $billingPeriod,
        'password' => $password,
        'features' => [
            'cpanel' => true,
            'ssl' => true,
            'backup' => true
        ]
    ];
    
    // Add email if user is logged in
    if ($currentUser) {
        $cartItem['email'] = $currentUser['email'];
    }
    
    // Add to cart
    if (addToCart($cartItem)) {
        // Set billing period in session
        $_SESSION['billing_period'] = $billingPeriod;
        
        setFlashMessage('success', 'Web hosting added to cart successfully.');
        redirect('/cart');
    } else {
        setFlashMessage('error', 'Failed to add web hosting to cart.');
        redirect('/cart/configure/web-hosting');
    }
}

// Calculate discount
$discountPercentage = $billingPeriods[$selectedBillingPeriod]['discount'];
$discountAmount = ($totalPrice * $discountPercentage) / 100;
$discountedTotal = $totalPrice - $discountAmount;

// VAT rate
$vatRate = 0;
$vatAmount = ($discountedTotal * $vatRate) / 100;
$finalTotal = $discountedTotal + $vatAmount;

// Set page title
$pageTitle = 'Configure: Web Hosting';
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<?php require_once __DIR__ . '/../../includes/loader.php'; ?>

<div class="container py-5">
    <h1 class="fw-bold mb-4">Configure: Web Hosting</h1>
    <p class="lead mb-4">Set up your web hosting plan with the features you need.</p>
    
    <form id="web-hosting-configurator" method="post" action="">
        <div class="row mb-5">
            <div class="col-md-8 mb-4 mb-md-0">
                <!-- Product Specifications -->
                <div class="card bg-dark mb-4">
                    <div class="card-header bg-darker">
                        <h4 class="card-title mb-0">Product Specifications:</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-shield-alt text-primary me-2"></i>
                                    <div>Free SSL Certificates</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-server text-primary me-2"></i>
                                    <div>cPanel Control Panel</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-tachometer-alt text-primary me-2"></i>
                                    <div>99.9% Uptime Guarantee</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3 mb-0">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fas fa-info-circle fa-2x"></i>
                                </div>
                                <div>
                                    Your hosting will be activated within 15 minutes after payment.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Domain -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-globe me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Domain</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Enter the domain name you want to host with us.</p>
                        
                        <div class="mb-3">
                            <label for="domain-input" class="form-label">Domain Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-dark border-secondary text-white" id="domain-input" name="domain" placeholder="example.com" required>
                            <div class="form-text">Enter your domain without www or http://</div>
                        </div>
                        
                        <div class="alert alert-warning mt-3 mb-0">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-triangle me-3 mt-1"></i>
                                <div>
                                    <strong>Important:</strong> You need to point your domain's nameservers to our nameservers after purchasing. We'll provide instructions in the welcome email.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Password -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-lock me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Password</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Set a secure password for your hosting account.</p>
                        
                        <div class="mb-3">
                            <label for="password-input" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <input type="password" class="form-control bg-dark border-secondary text-white" id="password-input" name="password" placeholder="Password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" id="generate-password">
                                    Generate
                                </button>
                            </div>
                            <div class="form-text">Password must be at least 8 characters and include letters and numbers.</div>
                        </div>
                    </div>
                </div>
                
                <!-- Disk Space Slider -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-hdd me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Disk Space (<?php echo $selectedDiskSpace; ?>GB)</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Select the amount of storage space for your website.</p>
                        
                        <div class="range-slider">
                            <div class="d-flex justify-content-between mb-2">
                                <span>10GB</span>
                                <span>100GB</span>
                            </div>
                            <input type="range" class="form-range" id="disk-slider" name="disk_space" min="10" max="100" step="10" value="<?php echo $selectedDiskSpace; ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Bandwidth -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-tachometer-alt me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Bandwidth</h4>
                        </div>
                        <p class="card-text text-muted mb-3">All of our web hosting plans include unlimited bandwidth.</p>
                        
                        <div class="d-flex align-items-center px-4 py-3 bg-success-subtle rounded mb-0">
                            <i class="fas fa-infinity fa-2x me-3 text-success"></i>
                            <div>
                                <h5 class="mb-1">Unlimited Bandwidth</h5>
                                <p class="mb-0 text-muted">No restrictions on legitimate website traffic. Fair use policy applies.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Databases Slider -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-database me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Databases (<?php echo $selectedDatabases; ?>)</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Select the number of MySQL databases you need.</p>
                        
                        <div class="range-slider">
                            <div class="d-flex justify-content-between mb-2">
                                <span>5 databases</span>
                                <span>30 databases</span>
                            </div>
                            <input type="range" class="form-range" id="databases-slider" name="databases" min="5" max="30" step="5" value="<?php echo $selectedDatabases; ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Email Accounts Slider -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-envelope me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Email Accounts (<?php echo $selectedEmails; ?>)</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Select the number of email accounts you need.</p>
                        
                        <div class="range-slider">
                            <div class="d-flex justify-content-between mb-2">
                                <span>10 accounts</span>
                                <span>50 accounts</span>
                            </div>
                            <input type="range" class="form-range" id="emails-slider" name="emails" min="10" max="50" step="10" value="<?php echo $selectedEmails; ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Location -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-map-marker-alt me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Location</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Select the server location for your website.</p>
                        
                        <div class="row g-3">
                            <div class="col-md-3 mb-3">
                                <input type="radio" class="btn-check" name="location" id="location-netherlands" value="netherlands" <?php echo $selectedLocation === 'netherlands' ? 'checked' : ''; ?>>
                                <label class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" for="location-netherlands">
                                    <img src="/assets/images/flags/netherlands.png" alt="Netherlands Flag" class="mb-2" width="40">
                                    <div>Netherlands</div>
                                    <small class="text-muted">Amsterdam</small>
                                </label>
                            </div>
                            <div class="col-md-3 mb-3">
                                <input type="radio" class="btn-check" name="location" id="location-usa" value="usa" <?php echo $selectedLocation === 'usa' ? 'checked' : ''; ?>>
                                <label class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" for="location-usa">
                                    <img src="/assets/images/flags/usa.png" alt="USA Flag" class="mb-2" width="40">
                                    <div>USA</div>
                                    <small class="text-muted">New York, NY</small>
                                </label>
                            </div>
                            <div class="col-md-3 mb-3">
                                <input type="radio" class="btn-check" name="location" id="location-usa-west" value="usa-west" <?php echo $selectedLocation === 'usa-west' ? 'checked' : ''; ?>>
                                <label class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" for="location-usa-west">
                                    <img src="/assets/images/flags/usa.png" alt="USA Flag" class="mb-2" width="40">
                                    <div>USA (west)</div>
                                    <small class="text-muted">Los Angeles, CA</small>
                                </label>
                            </div>
                            <div class="col-md-3 mb-3">
                                <input type="radio" class="btn-check" name="location" id="location-singapore" value="singapore" <?php echo $selectedLocation === 'singapore' ? 'checked' : ''; ?>>
                                <label class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" for="location-singapore">
                                    <img src="/assets/images/flags/singapore.png" alt="Singapore Flag" class="mb-2" width="40">
                                    <div>Singapore</div>
                                    <small class="text-muted">Singapore</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Billing Period -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-calendar-alt me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Billing Period</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Choose a billing period, you can always change this later.</p>
                        
                        <div class="row g-3">
                            <?php foreach ($billingPeriods as $key => $period): ?>
                                <div class="col-md-4 col-lg-4 mb-3">
                                    <div class="card bg-darker h-100 <?php echo $key === $selectedBillingPeriod ? 'border-primary' : ''; ?>">
                                        <div class="card-body text-center p-3">
                                            <h6 class="mb-2"><?php echo $period['label']; ?></h6>
                                            <div class="small text-muted">Save <?php echo $period['save']; ?></div>
                                            <input type="radio" class="btn-check" name="billing_period" id="billing-<?php echo $key; ?>" value="<?php echo $key; ?>" <?php echo $key === $selectedBillingPeriod ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-primary w-100 mt-2" for="billing-<?php echo $key; ?>"><?php echo $period['discount'] > 0 ? 'Save ' . $period['save'] : 'Select'; ?></label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-dark sticky-top" style="top: 100px; z-index: 1000;">
                    <div class="card-header bg-darker">
                        <h4 class="card-title mb-0">Overview</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <div>Web Hosting</div>
                                <div>€<?php echo $basePrice; ?></div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Location</div>
                                <div><?php echo ucfirst($selectedLocation); ?></div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Disk Space</div>
                                <div><?php echo $selectedDiskSpace; ?>GB (+€<?php echo $diskPrice; ?>)</div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Bandwidth</div>
                                <div>Unlimited</div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Databases</div>
                                <div><?php echo $selectedDatabases; ?> (+€<?php echo $databasesPrice; ?>)</div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Email Accounts</div>
                                <div><?php echo $selectedEmails; ?> (+€<?php echo $emailsPrice; ?>)</div>
                            </div>
                        </div>
                        
                        <hr class="border-secondary my-3">
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <div>Setup</div>
                                <div>€0</div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Sub Total</div>
                                <div>€<?php echo number_format($totalPrice, 2); ?></div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Cycle discount (<?php echo $billingPeriods[$selectedBillingPeriod]['label']; ?>)</div>
                                <div>-€<?php echo number_format($discountAmount, 2); ?></div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>0% VAT</div>
                                <div>€<?php echo number_format($vatAmount, 2); ?></div>
                            </div>
                        </div>
                        
                        <hr class="border-secondary my-3">
                        
                        <div class="d-flex justify-content-between mb-4">
                            <div class="fw-bold">€<?php echo number_format($finalTotal, 2); ?> Total</div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            Complete Order <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript for the Web Hosting configurator -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const diskSlider = document.getElementById('disk-slider');
    const databasesSlider = document.getElementById('databases-slider');
    const emailsSlider = document.getElementById('emails-slider');
    const locationOptions = document.querySelectorAll('input[name="location"]');
    const billingOptions = document.querySelectorAll('input[name="billing_period"]');
    const passwordInput = document.getElementById('password-input');
    const togglePasswordBtn = document.getElementById('toggle-password');
    const generatePasswordBtn = document.getElementById('generate-password');
    
    // Get overview elements
    const locationOverview = document.querySelector('.card-body .d-flex:nth-child(2) div:last-child');
    const diskOverview = document.querySelector('.card-body .d-flex:nth-child(3) div:last-child');
    const databasesOverview = document.querySelector('.card-body .d-flex:nth-child(5) div:last-child');
    const emailsOverview = document.querySelector('.card-body .d-flex:nth-child(6) div:last-child');
    const subtotalElement = document.querySelector('.card-body .d-flex:nth-child(9) div:last-child');
    const discountElement = document.querySelector('.card-body .d-flex:nth-child(10) div:last-child');
    const vatElement = document.querySelector('.card-body .d-flex:nth-child(11) div:last-child');
    const totalElement = document.querySelector('.d-flex.justify-content-between.mb-4 div');
    
    // Base price and rates
    const basePrice = 3;
    const diskRate = 1 / 10; // €1 per 10GB
    const databaseRate = 1 / 5; // €1 per 5 databases over 5
    const emailRate = 1 / 10; // €1 per 10 emails over 10
    
    // Billing period discounts
    const discounts = {
        'monthly': 0,
        'quarterly': 5,
        'semiannually': 10,
        'annually': 15,
        'biennially': 20,
        'triennially': 25
    };
    
    // Current values
    let currentDisk = parseInt(diskSlider.value);
    let currentDatabases = parseInt(databasesSlider.value);
    let currentEmails = parseInt(emailsSlider.value);
    let currentLocation = Array.from(locationOptions).find(option => option.checked).value;
    let currentBillingPeriod = Array.from(billingOptions).find(option => option.checked).value;
    
    // Calculate prices
    function calculatePrices() {
        // Resource costs
        const diskPrice = Math.floor(currentDisk / 10) * 1;
        const databasesPrice = Math.max(0, Math.ceil((currentDatabases - 5) / 5)) * 1;
        const emailsPrice = Math.max(0, Math.ceil((currentEmails - 10) / 10)) * 1;
        
        // Total before discount
        const subtotal = basePrice + diskPrice + databasesPrice + emailsPrice;
        
        // Apply discount
        const discountPercentage = discounts[currentBillingPeriod];
        const discountAmount = (subtotal * discountPercentage) / 100;
        const total = subtotal - discountAmount;
        
        // Update UI
        locationOverview.textContent = currentLocation.charAt(0).toUpperCase() + currentLocation.slice(1);
        diskOverview.textContent = currentDisk + 'GB (+€' + diskPrice + ')';
        databasesOverview.textContent = currentDatabases + ' (+€' + databasesPrice + ')';
        emailsOverview.textContent = currentEmails + ' (+€' + emailsPrice + ')';
        subtotalElement.textContent = '€' + subtotal.toFixed(2);
        discountElement.textContent = '-€' + discountAmount.toFixed(2);
        totalElement.textContent = '€' + total.toFixed(2) + ' Total';
        
        // Update slider labels
        document.querySelectorAll('.d-flex.align-items-center.mb-3 h4.card-title')[3].textContent = 'Disk Space (' + currentDisk + 'GB)';
        document.querySelectorAll('.d-flex.align-items-center.mb-3 h4.card-title')[6].textContent = 'Databases (' + currentDatabases + ')';
        document.querySelectorAll('.d-flex.align-items-center.mb-3 h4.card-title')[7].textContent = 'Email Accounts (' + currentEmails + ')';
    }
    
    // Event listeners
    diskSlider.addEventListener('input', function() {
        currentDisk = parseInt(this.value);
        calculatePrices();
    });
    
    databasesSlider.addEventListener('input', function() {
        currentDatabases = parseInt(this.value);
        calculatePrices();
    });
    
    emailsSlider.addEventListener('input', function() {
        currentEmails = parseInt(this.value);
        calculatePrices();
    });
    
    locationOptions.forEach(option => {
        option.addEventListener('change', function() {
            currentLocation = this.value;
            calculatePrices();
        });
    });
    
    billingOptions.forEach(option => {
        option.addEventListener('change', function() {
            currentBillingPeriod = this.value;
            
            // Update styling for selected billing period
            document.querySelectorAll('.card.bg-darker').forEach(card => {
                card.classList.remove('border-primary');
            });
            this.closest('.card').classList.add('border-primary');
            
            calculatePrices();
        });
    });
    
    // Toggle password visibility
    togglePasswordBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    
    // Generate random password
    generatePasswordBtn.addEventListener('click', function() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
        let password = '';
        for (let i = 0; i < 12; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        passwordInput.value = password;
        passwordInput.setAttribute('type', 'text');
        togglePasswordBtn.querySelector('i').classList.remove('fa-eye');
        togglePasswordBtn.querySelector('i').classList.add('fa-eye-slash');
    });
    
    // Initialize prices
    calculatePrices();
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>