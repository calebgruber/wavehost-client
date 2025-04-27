<?php
// cart/configure/minecraft-server.php - Configure Minecraft Server Hosting
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
        "SELECT * FROM minecraft_plans WHERE id = ?",
        [$planId]
    );
    
    if ($selectedPlan) {
        $selectedRam = $selectedPlan['ram'] ?? 4;
        $selectedCpu = $selectedPlan['cpu'] ?? 100;
        $selectedDisk = $selectedPlan['disk'] ?? 25;
        $selectedSlots = $selectedPlan['slots'] ?? 20;
    }
}

// Set defaults if no plan selected or found
if (!$selectedPlan) {
    $selectedRam = isset($_GET['ram']) ? (int)$_GET['ram'] : 4;
    $selectedCpu = isset($_GET['cpu']) ? (int)$_GET['cpu'] : 100;
    $selectedDisk = isset($_GET['disk']) ? (int)$_GET['disk'] : 25;
    $selectedSlots = isset($_GET['slots']) ? (int)$_GET['slots'] : 20;
}

// Default location
$selectedLocation = isset($_GET['location']) ? $_GET['location'] : 'netherlands';

// Calculate price based on selections
$basePrice = 5; // Base price in EUR
$ramPrice = $selectedRam - 4; // €1 per GB over 4GB
$cpuPrice = max(0, floor(($selectedCpu - 100) / 25)) * 1; // €1 per 25% CPU over 100%
$diskPrice = max(0, floor(($selectedDisk - 25) / 5)) * 0.5; // €0.50 per 5GB over 25GB
$slotsPrice = max(0, floor(($selectedSlots - 20) / 10)) * 2; // €2 per 10 slots over 20

$totalPrice = $basePrice + $ramPrice + $cpuPrice + $diskPrice + $slotsPrice;

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
    $serverName = trim($_POST['server_name'] ?? '');
    $ram = (int)($_POST['ram'] ?? 4);
    $cpu = (int)($_POST['cpu'] ?? 100);
    $disk = (int)($_POST['disk'] ?? 25);
    $slots = (int)($_POST['slots'] ?? 20);
    $location = $_POST['location'] ?? 'netherlands';
    $billingPeriod = $_POST['billing_period'] ?? 'monthly';
    $serverType = $_POST['server_type'] ?? 'paper';
    $version = $_POST['version'] ?? '1.20.1';
    
    // Validate server name
    if (empty($serverName)) {
        setFlashMessage('error', 'Server name is required.');
        redirect('/cart/configure/minecraft-server');
    }
    
    // Calculate price
    $ramPrice = $ram - 4;
    $cpuPrice = max(0, floor(($cpu - 100) / 25)) * 1;
    $diskPrice = max(0, floor(($disk - 25) / 5)) * 0.5;
    $slotsPrice = max(0, floor(($slots - 20) / 10)) * 2;
    
    $price = $basePrice + $ramPrice + $cpuPrice + $diskPrice + $slotsPrice;
    
    // Create cart item
    $cartItem = [
        'id' => uniqid('mc_'),
        'plan_id' => $planId,
        'type' => 'game_server',
        'game_type' => 'minecraft',
        'price' => $price,
        'server_name' => $serverName,
        'ram' => $ram,
        'cpu_limit' => $cpu,
        'disk_space' => $disk,
        'slots' => $slots,
        'location' => $location,
        'server_type' => $serverType,
        'version' => $version,
        'billing_period' => $billingPeriod,
        'password' => generateRandomString(12)
    ];
    
    // Add email if user is logged in
    if ($currentUser) {
        $cartItem['email'] = $currentUser['email'];
    }
    
    // Add to cart
    if (addToCart($cartItem)) {
        // Set billing period in session
        $_SESSION['billing_period'] = $billingPeriod;
        
        setFlashMessage('success', 'Minecraft server added to cart successfully.');
        redirect('/cart');
    } else {
        setFlashMessage('error', 'Failed to add Minecraft server to cart.');
        redirect('/cart/configure/minecraft-server');
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

// Server type options
$serverTypes = [
    'paper' => [
        'name' => 'Paper',
        'icon' => '/assets/images/minecraft/paper.png',
        'description' => 'High performance Minecraft server with plugin support.'
    ],
    'spigot' => [
        'name' => 'Spigot',
        'icon' => '/assets/images/minecraft/spigot.png',
        'description' => 'Modified Minecraft server with plugin support.'
    ],
    'forge' => [
        'name' => 'Forge',
        'icon' => '/assets/images/minecraft/forge.png',
        'description' => 'Modded Minecraft server with extensive mod support.'
    ],
    'fabric' => [
        'name' => 'Fabric',
        'icon' => '/assets/images/minecraft/fabric.png',
        'description' => 'Lightweight modded Minecraft server.'
    ],
    'vanilla' => [
        'name' => 'Vanilla',
        'icon' => '/assets/images/minecraft/vanilla.png',
        'description' => 'Official Minecraft server without modifications.'
    ],
    'bungeecord' => [
        'name' => 'BungeeCord',
        'icon' => '/assets/images/minecraft/bungeecord.png',
        'description' => 'Proxy server to connect multiple Minecraft servers.'
    ]
];

// Minecraft versions
$versions = [
    '1.20.1' => 'Minecraft 1.20.1',
    '1.19.4' => 'Minecraft 1.19.4',
    '1.18.2' => 'Minecraft 1.18.2',
    '1.17.1' => 'Minecraft 1.17.1',
    '1.16.5' => 'Minecraft 1.16.5',
    '1.15.2' => 'Minecraft 1.15.2',
    '1.14.4' => 'Minecraft 1.14.4',
    '1.12.2' => 'Minecraft 1.12.2',
    '1.8.8' => 'Minecraft 1.8.8'
];

// Set page title
$pageTitle = 'Configure: Minecraft Server';
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<?php require_once __DIR__ . '/../../includes/loader.php'; ?>

<div class="container py-5">
    <h1 class="fw-bold mb-4">Configure: Minecraft Server</h1>
    <p class="lead mb-4">Configure your Minecraft server just the way you want it.</p>
    
    <form id="minecraft-configurator" method="post" action="">
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
                                    <div>Pterodactyl Panel Access</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-tachometer-alt text-primary me-2"></i>
                                    <div>Unlimited Bandwidth <span class="text-muted">(Fair Use Policy)</span></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3 mb-0">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fas fa-info-circle fa-2x"></i>
                                </div>
                                <div>
                                    Your server will be instantly deployed after payment.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Server Type -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-cubes me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Server Type</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Choose the Minecraft server software you want to use.</p>
                        
                        <div class="row g-3">
                            <?php foreach ($serverTypes as $typeId => $type): ?>
                                <div class="col-md-4 mb-3">
                                    <input type="radio" class="btn-check" name="server_type" id="type-<?php echo $typeId; ?>" value="<?php echo $typeId; ?>" <?php echo $typeId === 'paper' ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center p-3" for="type-<?php echo $typeId; ?>">
                                        <img src="<?php echo $type['icon']; ?>" alt="<?php echo $type['name']; ?>" class="mb-2" width="48">
                                        <div class="fw-bold"><?php echo $type['name']; ?></div>
                                        <small class="text-muted text-center mt-2"><?php echo $type['description']; ?></small>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Minecraft Version -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-code-branch me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Minecraft Version</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Select which version of Minecraft you want to run.</p>
                        
                        <select class="form-select bg-dark border-secondary text-white" id="version-select" name="version">
                            <?php foreach ($versions as $versionId => $versionName): ?>
                                <option value="<?php echo $versionId; ?>" <?php echo $versionId === '1.20.1' ? 'selected' : ''; ?>><?php echo $versionName; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- RAM Slider -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-memory me-3 text-primary"></i>
                            <h4 class="card-title mb-0">RAM (<?php echo $selectedRam; ?>GB)</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Select the amount of RAM for your Minecraft server.</p>
                        
                        <div class="range-slider">
                            <div class="d-flex justify-content-between mb-2">
                                <span>4GB</span>
                                <span>16GB</span>
                            </div>
                            <input type="range" class="form-range" id="ram-slider" name="ram" min="4" max="16" step="1" value="<?php echo $selectedRam; ?>">
                        </div>
                        
                        <div class="alert alert-primary bg-primary-subtle mt-3 mb-0">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-lightbulb me-3 mt-1"></i>
                                <div>
                                    <strong>Recommended:</strong> 4GB RAM for up to 20 players, 8GB for up to 40 players, 12GB for up to 80 players.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- CPU Slider -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-microchip me-3 text-primary"></i>
                            <h4 class="card-title mb-0">CPU Limit (<?php echo $selectedCpu; ?>%)</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Select the CPU power for your Minecraft server.</p>
                        
                        <div class="range-slider">
                            <div class="d-flex justify-content-between mb-2">
                                <span>100%</span>
                                <span>400%</span>
                            </div>
                            <input type="range" class="form-range" id="cpu-slider" name="cpu" min="100" max="400" step="25" value="<?php echo $selectedCpu; ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Disk Slider -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-hdd me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Storage (<?php echo $selectedDisk; ?>GB)</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Select the disk space for your Minecraft server.</p>
                        
                        <div class="range-slider">
                            <div class="d-flex justify-content-between mb-2">
                                <span>25GB</span>
                                <span>100GB</span>
                            </div>
                            <input type="range" class="form-range" id="disk-slider" name="disk" min="25" max="100" step="5" value="<?php echo $selectedDisk; ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Player Slots Slider -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-users me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Player Slots (<?php echo $selectedSlots; ?>)</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Select the number of player slots for your Minecraft server.</p>
                        
                        <div class="range-slider">
                            <div class="d-flex justify-content-between mb-2">
                                <span>20 slots</span>
                                <span>100 slots</span>
                            </div>
                            <input type="range" class="form-range" id="slots-slider" name="slots" min="20" max="100" step="10" value="<?php echo $selectedSlots; ?>">
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
                        <p class="card-text text-muted mb-3">Select the location of your Minecraft server. This affects latency for your players.</p>
                        
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
                
                <!-- Server Details -->
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-cogs me-3 text-primary"></i>
                            <h4 class="card-title mb-0">Server Details</h4>
                        </div>
                        <p class="card-text text-muted mb-3">Provide a name for your Minecraft server.</p>
                        
                        <div class="mb-3">
                            <label for="server-name-input" class="form-label">Server Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-dark border-secondary text-white" id="server-name-input" name="server_name" placeholder="My Awesome Server" required>
                            <div class="form-text">This will help you identify your server in your dashboard.</div>
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
                                <div>Minecraft Server</div>
                                <div>€<?php echo $basePrice; ?></div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Server Type</div>
                                <div>Paper</div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Version</div>
                                <div>1.20.1</div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Location</div>
                                <div><?php echo ucfirst($selectedLocation); ?></div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>RAM</div>
                                <div><?php echo $selectedRam; ?>GB (+€<?php echo $ramPrice; ?>)</div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>CPU</div>
                                <div><?php echo $selectedCpu; ?>% (+€<?php echo $cpuPrice; ?>)</div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Storage</div>
                                <div><?php echo $selectedDisk; ?>GB (+€<?php echo $diskPrice; ?>)</div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <div>Player Slots</div>
                                <div><?php echo $selectedSlots; ?> (+€<?php echo $slotsPrice; ?>)</div>
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

<!-- JavaScript for the Minecraft configurator -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const ramSlider = document.getElementById('ram-slider');
    const cpuSlider = document.getElementById('cpu-slider');
    const diskSlider = document.getElementById('disk-slider');
    const slotsSlider = document.getElementById('slots-slider');
    const serverTypeOptions = document.querySelectorAll('input[name="server_type"]');
    const versionSelect = document.getElementById('version-select');
    const billingOptions = document.querySelectorAll('input[name="billing_period"]');
    const locationOptions = document.querySelectorAll('input[name="location"]');
    
    // Get overview elements
    const serverTypeOverview = document.querySelector('.card-body .d-flex:nth-child(2) div:last-child');
    const versionOverview = document.querySelector('.card-body .d-flex:nth-child(3) div:last-child');
    const locationOverview = document.querySelector('.card-body .d-flex:nth-child(4) div:last-child');
    const ramOverview = document.querySelector('.card-body .d-flex:nth-child(5) div:last-child');
    const cpuOverview = document.querySelector('.card-body .d-flex:nth-child(6) div:last-child');
    const diskOverview = document.querySelector('.card-body .d-flex:nth-child(7) div:last-child');
    const slotsOverview = document.querySelector('.card-body .d-flex:nth-child(8) div:last-child');
    const subtotalElement = document.querySelector('.card-body .d-flex:nth-child(11) div:last-child');
    const discountElement = document.querySelector('.card-body .d-flex:nth-child(12) div:last-child');
    const vatElement = document.querySelector('.card-body .d-flex:nth-child(13) div:last-child');
    const totalElement = document.querySelector('.d-flex.justify-content-between.mb-4 div');
    
    // Base price and rates
    const basePrice = 5;
    const ramRate = 1; // €1 per GB over 4GB
    const cpuRate = 1 / 25; // €1 per 25% over 100%
    const diskRate = 0.5 / 5; // €0.50 per 5GB over 25GB
    const slotsRate = 2 / 10; // €2 per 10 slots over 20
    
    // Billing period discounts
    const discounts = {
        'monthly': 0,
        'quarterly': 5,
        'semiannually': 10,
        'annually': 15,
        'biennially': 20,
        'triennially': 25
    };
    
    // Server type names
    const serverTypeNames = {
        'paper': 'Paper',
        'spigot': 'Spigot',
        'forge': 'Forge',
        'fabric': 'Fabric',
        'vanilla': 'Vanilla',
        'bungeecord': 'BungeeCord'
    };
    
    // Current values
    let currentRam = parseInt(ramSlider.value);
    let currentCpu = parseInt(cpuSlider.value);
    let currentDisk = parseInt(diskSlider.value);
    let currentSlots = parseInt(slotsSlider.value);
    let currentBillingPeriod = Array.from(billingOptions).find(option => option.checked).value;
    let currentLocation = Array.from(locationOptions).find(option => option.checked).value;
    let currentServerType = Array.from(serverTypeOptions).find(option => option.checked).value;
    let currentVersion = versionSelect.value;
    
    // Calculate prices
    function calculatePrices() {
        // Resource costs
        const ramPrice = Math.max(0, currentRam - 4);
        const cpuPrice = Math.max(0, Math.floor((currentCpu - 100) / 25)) * 1;
        const diskPrice = Math.max(0, Math.floor((currentDisk - 25) / 5)) * 0.5;
        const slotsPrice = Math.max(0, Math.floor((currentSlots - 20) / 10)) * 2;
        
        // Total before discount
        const subtotal = basePrice + ramPrice + cpuPrice + diskPrice + slotsPrice;
        
        // Apply discount
        const discountPercentage = discounts[currentBillingPeriod];
        const discountAmount = (subtotal * discountPercentage) / 100;
        const total = subtotal - discountAmount;
        
        // Update UI
        serverTypeOverview.textContent = serverTypeNames[currentServerType];
        versionOverview.textContent = versionSelect.options[versionSelect.selectedIndex].text.replace('Minecraft ', '');
        locationOverview.textContent = currentLocation.charAt(0).toUpperCase() + currentLocation.slice(1);
        ramOverview.textContent = currentRam + 'GB (+€' + ramPrice + ')';
        cpuOverview.textContent = currentCpu + '% (+€' + cpuPrice + ')';
        diskOverview.textContent = currentDisk + 'GB (+€' + diskPrice + ')';
        slotsOverview.textContent = currentSlots + ' (+€' + slotsPrice + ')';
        subtotalElement.textContent = '€' + subtotal.toFixed(2);
        discountElement.textContent = '-€' + discountAmount.toFixed(2);
        totalElement.textContent = '€' + total.toFixed(2) + ' Total';
        
        // Update slider labels
        document.querySelector('.d-flex.align-items-center.mb-3 h4.card-title').textContent = 'RAM (' + currentRam + 'GB)';
        document.querySelectorAll('.d-flex.align-items-center.mb-3 h4.card-title')[3].textContent = 'CPU Limit (' + currentCpu + '%)';
        document.querySelectorAll('.d-flex.align-items-center.mb-3 h4.card-title')[4].textContent = 'Storage (' + currentDisk + 'GB)';
        document.querySelectorAll('.d-flex.align-items-center.mb-3 h4.card-title')[5].textContent = 'Player Slots (' + currentSlots + ')';
    }
    
    // Event listeners
    ramSlider.addEventListener('input', function() {
        currentRam = parseInt(this.value);
        calculatePrices();
    });
    
    cpuSlider.addEventListener('input', function() {
        currentCpu = parseInt(this.value);
        calculatePrices();
    });
    
    diskSlider.addEventListener('input', function() {
        currentDisk = parseInt(this.value);
        calculatePrices();
    });
    
    slotsSlider.addEventListener('input', function() {
        currentSlots = parseInt(this.value);
        calculatePrices();
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
    
    locationOptions.forEach(option => {
        option.addEventListener('change', function() {
            currentLocation = this.value;
            calculatePrices();
        });
    });
    
    serverTypeOptions.forEach(option => {
        option.addEventListener('change', function() {
            currentServerType = this.value;
            calculatePrices();
        });
    });
    
    versionSelect.addEventListener('change', function() {
        currentVersion = this.value;
        calculatePrices();
    });
    
    // Initialize prices
    calculatePrices();