<?php
// cart/configure/vps.php - Configure VPS Hosting
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
        "SELECT * FROM vps_plans WHERE id = ?",
        [$planId]
    );
    
    if ($selectedPlan) {
        $selectedStorage = $selectedPlan['storage'] ?? 20;
        $selectedRam = $selectedPlan['ram'] ?? 2;
        $selectedCores = $selectedPlan['cores'] ?? 1;
    }
}

// Set defaults if no plan selected or found
if (!$selectedPlan) {
    $selectedStorage = isset($_GET['storage']) ? (int)$_GET['storage'] : 20;
    $selectedRam = isset($_GET['ram']) ? (int)$_GET['ram'] : 2;
    $selectedCores = isset($_GET['cores']) ? (int)$_GET['cores'] : 1;
}

// Default location
$selectedLocation = isset($_GET['location']) ? $_GET['location'] : 'netherlands';

// Calculate price based on selections
$basePrice = 5; // Base price in EUR
$storagePrice = floor($selectedStorage / 20) * 1;
$ramPrice = floor($selectedRam / 2) * 1;
$coresPrice = $selectedCores * 2;

$totalPrice = $basePrice + $storagePrice + $ramPrice + $coresPrice;

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
    $hostname = trim($_POST['hostname'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $storage = (int)($_POST['storage'] ?? 20);
    $ram = (int)($_POST['ram'] ?? 2);
    $cores = (int)($_POST['cores'] ?? 1);
    $location = $_POST['location'] ?? 'netherlands';
    $ipv4 = (int)($_POST['ipv4'] ?? 1);
    $ipv6 = (int)($_POST['ipv6'] ?? 1);
    $billingPeriod = $_POST['billing_period'] ?? 'monthly';
    $os = $_POST['os'] ?? 'ubuntu';
    
    // Validate hostname
    if (empty($hostname)) {
        setFlashMessage('error', 'Hostname is required.');
        redirect('/cart/configure/vps');
    }
    
    // Validate password
    if (empty($password)) {
        setFlashMessage('error', 'Server password is required.');
        redirect('/cart/configure/vps');
    }
    
    // Calculate price
    $storagePrice = floor($storage / 20) * 1;
    $ramPrice = floor($ram / 2) * 1;
    $coresPrice = $cores * 2;
    $price = $basePrice + $storagePrice + $ramPrice + $coresPrice;
    
    // Create cart item
    $cartItem = [
        'id' => uniqid('vps_'),
        'plan_id' => $planId,
        'type' => 'vps',
        'price' => $price,
        'hostname' => $hostname,
        'storage' => $storage,
        'ram' => $ram,
        'cores' => $cores,
        'location' => $location,
        'ipv4' => $ipv4,
        'ipv6' => $ipv6,
        'os' => $os,
        'billing_period' => $billingPeriod
    ];
    
    // Add email if user is logged in
    if ($currentUser) {
        $cartItem['email'] = $currentUser['email'];
    }
    
    // Add to cart
    if (addToCart($cartItem)) {
        // Set billing period in session
        $_SESSION['billing_period'] = $billingPeriod;
        
        setFlashMessage('success', 'VPS added to cart successfully.');
        redirect('/cart');
    } else {
        setFlashMessage('error', 'Failed to add VPS to cart.');
        redirect('/cart/configure/vps');
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

// Operating system options
$operatingSystems = [
    'ubuntu' => [
        'name' => 'Ubuntu',
        'icon' => '/assets/images/os/ubuntu.png'
    ],
    'alma' => [
        'name' => 'Alma',
        'icon' => '/assets/images/os/alma.png'
    ],
    'debian' => [
        'name' => 'Debian',
        'icon' => '/assets/images/os/debian.png'
    ],
    'fedora' => [
        'name' => 'Fedora',
        'icon' => '/assets/images/os/fedora.png'
    ],
    'rocky' => [
        'name' => 'Rocky',
        'icon' => '/assets/images/os/rocky.png'
    ],
    'windows' => [
        'name' => 'Windows',
        'icon' => '/assets/images/os/windows.png'
    ],
    'freebsd' => [
        'name' => 'FreeBSD',
        'icon' => '/assets/images/os/freebsd.png'
    ],
    'centos' => [
        'name' => 'CentOS',
        'icon' => '/assets/images/os/centos.png'
    ]
];

// Uplink options
$uplinkOptions = [
    '1gbit' => [
        'name' => '1Gbit',
        'price' => 0
    ],
    '5gbit' => [
        'name' => '5Gbit',
        'price' => 5
    ],
    '10gbit' => [
        'name' => '10Gbit',
        'price' => 10
    ]
];

// Set page title
$pageTitle = 'Configure: Virtual Private Server';
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="container py-5">
    <h1 class="fw-bold mb-4">Configure: Virtual Private Server</h1>
    <p class="lead mb-4">Configure the technical details for your new service with us.</p>
    
    <form id="vps-configurator" method="post" action="">
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
                                    <div>2-5 Tbps DDoS Protection</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-server text-primary me-2"></i>
                                    <div>Shield Panel Access</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-tachometer-alt text-primary me-2"></i>
                                    <div>Unlimited Bandwidth <span class="text-muted">(Fair Use Policy)</span></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mt-3 mb-0">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fas fa-info-circle fa-2x"></i>
                                </div>
                                <div>
                                    Your service will be deployed in Amsterdam, Netherlands.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mailing Port -->
                <div class="card bg-dark mb-4">
                    <div class="card-body" style="background-color: #2c1e1a;">
                        <h5 class="mb-3">Mailing Port</h5>
                        <p class="mb-3">Ports 25 and 465 are blocked by default on your VPS orders to prevent mail spam. If you would like to send emails from your VPS please <a href="/support" class="text-primary">contact support</a>. We will evaluate your request and unblock the port if necessary.</p>
                        <p class="mb-0">You will still be able to use port 587 to send emails via external mail delivery services.</p>
                    </div>
                </div>
                
                <!-- Operating System -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-desktop me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Operating System</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Choose the operating system for your VPS.</p>
                        
                        <div class="row g-3">
                            <?php foreach ($operatingSystems as $osId => $os): ?>
                                <div class="col-md-3 col-6">
                                    <input type="radio" class="btn-check" name="os" id="os-<?php echo $osId; ?>" value="<?php echo $osId; ?>" <?php echo $osId === 'ubuntu' ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary w-100 d-flex flex-column align-items-center p-3" for="os-<?php echo $osId; ?>">
                                        <img src="<?php echo $os['icon']; ?>" alt="<?php echo $os['name']; ?>" class="mb-2" width="40">
                                        <div><?php echo $os['name']; ?></div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Uplink -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-network-wired me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Uplink</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Select the uplink for your VPS.</p>
                        
                        <div class="row g-3">
                            <?php foreach ($uplinkOptions as $uplinkId => $uplink): ?>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="uplink" id="uplink-<?php echo $uplinkId; ?>" value="<?php echo $uplinkId; ?>" <?php echo $uplinkId === '1gbit' ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" for="uplink-<?php echo $uplinkId; ?>">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-network-wired me-2"></i>
                                            <span><?php echo $uplink['name']; ?></span>
                                        </div>
                                        <?php if ($uplink['price'] > 0): ?>
                                            <small>+€<?php echo $uplink['price']; ?>/month</small>
                                        <?php else: ?>
                                            <small>Included</small>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Storage Slider -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-hdd me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Storage (<?php echo $selectedStorage; ?>gb)</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Select the storage amount for your VPS.</p>
                        
                        <div class="range-slider">
                            <div class="d-flex justify-content-between mb-2">
                                <span>20gb</span>
                                <span>500gb</span>
                            </div>
                            <input type="range" class="form-range" id="storage-slider" name="storage" min="20" max="500" step="20" value="<?php echo $selectedStorage; ?>">
                        </div>
                    </div>
                </div>
                
                <!-- RAM Slider -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-memory me-3 text-primary"></i>
                            <h4 class="card-title mb-0">RAM (<?php echo $selectedRam; ?>gb)</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Select the RAM amount for your VPS.</p>
                        
                        <div class="range-slider">
                            <div class="d-flex justify-content-between mb-2">
                                <span>2gb</span>
                                <span>128gb</span>
                            </div>
                            <input type="range" class="form-range" id="ram-slider" name="ram" min="2" max="128" step="2" value="<?php echo $selectedRam; ?>">
                        </div>
                    </div>
                </div>
                
                <!-- CPU Cores Slider -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-microchip me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Cores (<?php echo $selectedCores; ?>)</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Select the amount of CPU cores for your VPS.</p>
                        
                        <div class="range-slider">
                            <div class="d-flex justify-content-between mb-2">
                                <span>1 core</span>
                                <span>16 cores</span>
                            </div>
                            <input type="range" class="form-range" id="cores-slider" name="cores" min="1" max="16" step="1" value="<?php echo $selectedCores; ?>">
                        </div>
                    </div>
                </div>
                
                <!-- IP Addresses -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-network-wired me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Number of IP's</h4>
                        </div>
                        <p class="card-text text-muted mb-3">The number of IP's used by the server.</p>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="ipv4-input" class="form-label">IPv4 <span class="text-danger">*</span></label>
                                <input type="number" class="form-control bg-dark border-secondary text-white" id="ipv4-input" name="ipv4" min="1" max="5" value="1">
                            </div>
                            <div class="col-md-6">
                                <label for="ipv6-input" class="form-label">IPv6 <span class="text-danger">*</span></label>
                                <input type="number" class="form-control bg-dark border-secondary text-white" id="ipv6-input" name="ipv6" min="1" max="5" value="1">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Server Details -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-cogs me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Server Details</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Provide the server details for your VPS.</p>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="hostname-input" class="form-label">Hostname <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-dark border-secondary text-white" id="hostname-input" name="hostname" placeholder="server1.example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label for="password-input" class="form-label">Server Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control bg-dark border-secondary text-white" id="password-input" name="password" placeholder="••••••••" required>
                            </div>
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
                        <p class="card-text text-muted mb-3">Select the location of your VPS. Your server will be deployed in the selected datacenter.</p>
                        
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
                                <div>Virtual Private Server</div>
                                <div>€<?php echo $basePrice; ?></div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Location</div>
                                <div><?php echo ucfirst($selectedLocation); ?></div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Operating System</div>
                                <div>Ubuntu</div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Uplink</div>
                                <div>1Gbit (+€0)</div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Storage</div>
                                <div><?php echo $selectedStorage; ?>GB (+€<?php echo $storagePrice; ?>)</div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Ram</div>
                                <div><?php echo $selectedRam; ?>GB (+€<?php echo $ramPrice; ?>)</div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Cores</div>
                                <div><?php echo $selectedCores; ?> (+€<?php echo $coresPrice; ?>)</div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>IPv4</div>
                                <div>1 (+€0)</div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>IPv6</div>
                                <div>1 (+€0)</div>
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

<!-- JavaScript for the VPS configurator -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const storageSlider = document.getElementById('storage-slider');
    const ramSlider = document.getElementById('ram-slider');
    const coresSlider = document.getElementById('cores-slider');
    const billingOptions = document.querySelectorAll('input[name="billing_period"]');
    const locationOptions = document.querySelectorAll('input[name="location"]');
    const osOptions = document.querySelectorAll('input[name="os"]');
    const uplinkOptions = document.querySelectorAll('input[name="uplink"]');
    
    // Get overview elements
    const osOverview = document.querySelector('.card-body .d-flex:nth-child(3) div:last-child');
    const uplinkOverview = document.querySelector('.card-body .d-flex:nth-child(4) div:last-child');
    const storageOverview = document.querySelector('.card-body .d-flex:nth-child(5) div:last-child');
    const ramOverview = document.querySelector('.card-body .d-flex:nth-child(6) div:last-child');
    const coresOverview = document.querySelector('.card-body .d-flex:nth-child(7) div:last-child');
    const ipv4Overview = document.querySelector('.card-body .d-flex:nth-child(8) div:last-child');
    const ipv6Overview = document.querySelector('.card-body .d-flex:nth-child(9) div:last-child');
    const subtotalElement = document.querySelector('.card-body .d-flex:nth-child(11) div:last-child');
    const discountElement = document.querySelector('.card-body .d-flex:nth-child(12) div:last-child');
    const totalElement = document.querySelector('.d-flex.justify-content-between.mb-4 div');
    
    // Base price and rates
    const basePrice = 5;
    const storageRate = 1 / 20; // €1 per 20GB
    const ramRate = 1 / 2;     // €1 per 2GB
    const coreRate = 2;        // €2 per core
    
    // Billing period discounts
    const discounts = {
        'monthly': 0,
        'quarterly': 5,
        'semiannually': 10,
        'annually': 15,
        'biennially': 20,
        'triennially': 25
    };
    
    // Uplink prices
    const uplinkPrices = {
        '1gbit': 0,
        '5gbit': 5,
        '10gbit': 10
    };
    
    // OS names
    const osNames = {
        'ubuntu': 'Ubuntu',
        'alma': 'Alma',
        'debian': 'Debian',
        'fedora': 'Fedora',
        'rocky': 'Rocky',
        'windows': 'Windows',
        'freebsd': 'FreeBSD',
        'centos': 'CentOS'
    };
    
    // Current values
    let currentStorage = parseInt(storageSlider.value);
    let currentRam = parseInt(ramSlider.value);
    let currentCores = parseInt(coresSlider.value);
    let currentBillingPeriod = Array.from(billingOptions).find(option => option.checked).value;
    let currentLocation = Array.from(locationOptions).find(option => option.checked).value;
    let currentOS = Array.from(osOptions).find(option => option.checked).value;
    let currentUplink = Array.from(uplinkOptions).find(option => option.checked).value;
    
    // IPv4 and IPv6 inputs
    const ipv4Input = document.getElementById('ipv4-input');
    const ipv6Input = document.getElementById('ipv6-input');
    
    // Calculate prices
    function calculatePrices() {
        // Resource costs
        const storagePrice = Math.floor(currentStorage / 20) * 1;
        const ramPrice = Math.floor(currentRam / 2) * 1;
        const coresPrice = currentCores * 2;
        const uplinkPrice = uplinkPrices[currentUplink];
        
        // Total before discount
        const subtotal = basePrice + storagePrice + ramPrice + coresPrice + uplinkPrice;
        
        // Apply discount
        const discountPercentage = discounts[currentBillingPeriod];
        const discountAmount = (subtotal * discountPercentage) / 100;
        const total = subtotal - discountAmount;
        
        // Update UI
        osOverview.textContent = osNames[currentOS];
        uplinkOverview.textContent = currentUplink + ' (+€' + uplinkPrice + ')';
        storageOverview.textContent = currentStorage + 'GB (+€' + storagePrice + ')';
        ramOverview.textContent = currentRam + 'GB (+€' + ramPrice + ')';
        coresOverview.textContent = currentCores + ' (+€' + coresPrice + ')';
        ipv4Overview.textContent = ipv4Input.value + ' (+€0)';
        ipv6Overview.textContent = ipv6Input.value + ' (+€0)';
        subtotalElement.textContent = '€' + subtotal.toFixed(2);
        discountElement.textContent = '-€' + discountAmount.toFixed(2);
        totalElement.textContent = '€' + total.toFixed(2) + ' Total';
        
        // Update slider labels
        document.querySelector('.d-flex.align-items-center.mb-3 h4.card-title').textContent = 'Storage (' + currentStorage + 'gb)';
        document.querySelectorAll('.d-flex.align-items-center.mb-3 h4.card-title')[3].textContent = 'RAM (' + currentRam + 'gb)';
        document.querySelectorAll('.d-flex.align-items-center.mb-3 h4.card-title')[4].textContent = 'Cores (' + currentCores + ')';
    }
    
    // Event listeners
    storageSlider.addEventListener('input', function() {
        currentStorage = parseInt(this.value);
        calculatePrices();
    });
    
    ramSlider.addEventListener('input', function() {
        currentRam = parseInt(this.value);
        calculatePrices();
    });
    
    coresSlider.addEventListener('input', function() {
        currentCores = parseInt(this.value);
        calculatePrices();
    });
    
    billingOptions.forEach(option => {
        option.addEventListener('change', function() {
            currentBillingPeriod = this.value;
            calculatePrices();
        });
    });
    
    locationOptions.forEach(option => {
        option.addEventListener('change', function() {
            currentLocation = this.value;
            document.querySelector('.card-body .d-flex:nth-child(2) div:last-child').textContent = currentLocation.charAt(0).toUpperCase() + currentLocation.slice(1);
        });
    });
    
    osOptions.forEach(option => {
        option.addEventListener('change', function() {
            currentOS = this.value;
            calculatePrices();
        });
    });
    
    uplinkOptions.forEach(option => {
        option.addEventListener('change', function() {
            currentUplink = this.value;
            calculatePrices();
        });
    });
    
    // IP address inputs
    ipv4Input.addEventListener('change', function() {
        calculatePrices();
    });
    
    ipv6Input.addEventListener('change', function() {
        calculatePrices();
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>