<?php
// Beginning of index.php, web-hosting.php, game-server.php, etc.
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/cart.php';


// Get current user if logged in
$currentUser = null;
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
}

// Get VPS plans from the database
$db = db();
$plans = $db->select(
    "SELECT * FROM vps_plans ORDER BY ram ASC"
);

// Set default values for configurator
$selectedStorage = isset($_GET['storage']) ? (int)$_GET['storage'] : 20;
$selectedRam = isset($_GET['ram']) ? (int)$_GET['ram'] : 2;
$selectedCores = isset($_GET['cores']) ? (int)$_GET['cores'] : 1;
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

// Set default billing period
$selectedBillingPeriod = isset($_GET['billing']) ? $_GET['billing'] : 'monthly';

// Calculate discount
$discountPercentage = $billingPeriods[$selectedBillingPeriod]['discount'];
$discountAmount = ($totalPrice * $discountPercentage) / 100;
$discountedTotal = $totalPrice - $discountAmount;

// VAT rate
$vatRate = 0;
$vatAmount = ($discountedTotal * $vatRate) / 100;
$finalTotal = $discountedTotal + $vatAmount;

// Set page title
$pageTitle = 'VPS Hosting';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-md-8 mb-4 mb-md-0">
            <h1 class="fw-bold mb-4">Virtual Private Servers</h1>
            <p class="lead mb-4">Powerful and fully customizable VPS solutions with high-performance SSD storage, dedicated resources, and 99.9% uptime guarantee.</p>
            
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="feature-icon bg-primary-subtle rounded-circle p-3 me-3">
                            <i class="fas fa-server text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Full Root Access</h5>
                            <p class="mb-0 text-muted">Complete control over your server</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="feature-icon bg-success-subtle rounded-circle p-3 me-3">
                            <i class="fas fa-shield-alt text-success"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">DDoS Protection</h5>
                            <p class="mb-0 text-muted">Enterprise-grade security</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="feature-icon bg-info-subtle rounded-circle p-3 me-3">
                            <i class="fas fa-bolt text-info"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">NVMe SSD</h5>
                            <p class="mb-0 text-muted">Ultra-fast storage performance</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <form id="vps-configurator" method="post" action="/order/checkout">
                <input type="hidden" name="product_type" value="vps">
                
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
            </form>
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
                    
                    <button type="submit" form="vps-configurator" class="btn btn-primary w-100">
                        Complete Order <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="fw-bold mb-4">VPS Features</h2>
            <div class="row g-4">
                <div class="col-md-4 mb-4">
                    <div class="card bg-dark h-100">
                        <div class="card-body p-4">
                            <div class="icon-box bg-primary-subtle rounded-circle p-3 mb-3">
                                <i class="fas fa-tachometer-alt text-primary fa-2x"></i>
                            </div>
                            <h4>High Performance</h4>
                            <p class="text-muted">Our VPS servers are powered by Intel Xeon Platinum processors and NVMe SSD storage, providing exceptional performance for demanding workloads.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card bg-dark h-100">
                        <div class="card-body p-4">
                            <div class="icon-box bg-success-subtle rounded-circle p-3 mb-3">
                                <i class="fas fa-shield-alt text-success fa-2x"></i>
                            </div>
                            <h4>DDoS Protection</h4>
                            <p class="text-muted">All VPS plans include enterprise-grade DDoS protection to keep your services online even during attacks, with filtering up to 2Tbps.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card bg-dark h-100">
                        <div class="card-body p-4">
                            <div class="icon-box bg-info-subtle rounded-circle p-3 mb-3">
                                <i class="fas fa-network-wired text-info fa-2x"></i>
                            </div>
                            <h4>Premium Network</h4>
                            <p class="text-muted">Our global network with multiple Tier-1 providers ensures low latency and high availability for all your services, with up to 10Gbps uplink.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card bg-dark h-100">
                        <div class="card-body p-4">
                            <div class="icon-box bg-warning-subtle rounded-circle p-3 mb-3">
                                <i class="fas fa-server text-warning fa-2x"></i>
                            </div>
                            <h4>Full Root Access</h4>
                            <p class="text-muted">Get complete control of your server with full root access, allowing you to install any software and customize your environment.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card bg-dark h-100">
                        <div class="card-body p-4">
                            <div class="icon-box bg-danger-subtle rounded-circle p-3 mb-3">
                                <i class="fas fa-database text-danger fa-2x"></i>
                            </div>
                            <h4>Daily Backups</h4>
                            <p class="text-muted">We provide automated daily backups for all VPS plans, ensuring your data is safe and can be restored quickly if needed.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card bg-dark h-100">
                        <div class="card-body p-4">
                            <div class="icon-box bg-secondary-subtle rounded-circle p-3 mb-3">
                                <i class="fas fa-headset text-secondary fa-2x"></i>
                            </div>
                            <h4>24/7 Support</h4>
                            <p class="text-muted">Our expert support team is available around the clock to help you with any issues or questions you may have about your VPS.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <h2 class="fw-bold mb-4">Frequently Asked Questions</h2>
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item bg-dark border-secondary mb-3">
                    <h2 class="accordion-header" id="faqHeading1">
                        <button class="accordion-button bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="true" aria-controls="faqCollapse1">
                            What is a VPS and how does it differ from shared hosting?
                        </button>
                    </h2>
                    <div id="faqCollapse1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted">
                            A Virtual Private Server (VPS) is a virtualized server that simulates a dedicated server within a shared hosting environment. Unlike shared hosting, where resources are shared among all users on the server, a VPS provides dedicated resources (CPU, RAM, storage) that are exclusively available to you. This results in better performance, reliability, and security compared to shared hosting.
                        </div>
                    </div>
                </div>
                <div class="accordion-item bg-dark border-secondary mb-3">
                    <h2 class="accordion-header" id="faqHeading2">
                        <button class="accordion-button bg-dark text-white collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                            Can I upgrade my VPS resources later?
                        </button>
                    </h2>
                    <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted">
                            Yes, you can easily upgrade your VPS resources at any time through your client dashboard. You can increase RAM, storage, CPU cores, and bandwidth as your needs grow. In most cases, upgrades are applied instantly without any downtime for your services.
                        </div>
                    </div>
                </div>
                <div class="accordion-item bg-dark border-secondary mb-3">
                    <h2 class="accordion-header" id="faqHeading3">
                        <button class="accordion-button bg-dark text-white collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                            What operating systems are available for my VPS?
                        </button>
                    </h2>
                    <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted">
                            We offer a wide range of operating systems for your VPS, including various Linux distributions (Ubuntu, Debian, CentOS, Fedora, Rocky Linux) and Windows Server. You can select your preferred OS during the ordering process, or change it later using our control panel.
                        </div>
                    </div>
                </div>
                <div class="accordion-item bg-dark border-secondary mb-3">
                    <h2 class="accordion-header" id="faqHeading4">
                        <button class="accordion-button bg-dark text-white collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
                            How long does it take to set up my VPS?
                        </button>
                    </h2>
                    <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted">
                            Our VPS provisioning is automated and typically completed within minutes after we receive your payment. You'll receive login details via email once your server is ready, allowing you to start using your VPS right away.
                        </div>
                    </div>
                </div>
                <div class="accordion-item bg-dark border-secondary">
                    <h2 class="accordion-header" id="faqHeading5">
                        <button class="accordion-button bg-dark text-white collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse5" aria-expanded="false" aria-controls="faqCollapse5">
                            Do you offer managed VPS services?
                        </button>
                    </h2>
                    <div id="faqCollapse5" class="accordion-collapse collapse" aria-labelledby="faqHeading5" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted">
                            Yes, we offer both unmanaged and managed VPS services. With our managed VPS plans, our technical team handles server administration tasks, including security updates, monitoring, and troubleshooting. This allows you to focus on your applications without worrying about server management. Managed services can be added to any VPS plan during checkout or later through your client area.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    
    // Get overview elements
    const storageOverview = document.querySelector('.card-body .d-flex:nth-child(5) div:last-child');
    const ramOverview = document.querySelector('.card-body .d-flex:nth-child(6) div:last-child');
    const coresOverview = document.querySelector('.card-body .d-flex:nth-child(7) div:last-child');
    const subtotalElement = document.querySelector('.card-body .d-flex:nth-child(10) div:last-child');
    const discountElement = document.querySelector('.card-body .d-flex:nth-child(11) div:last-child');
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
    
    // Current values
    let currentStorage = parseInt(storageSlider.value);
    let currentRam = parseInt(ramSlider.value);
    let currentCores = parseInt(coresSlider.value);
    let currentBillingPeriod = Array.from(billingOptions).find(option => option.checked).value;
    let currentLocation = Array.from(locationOptions).find(option => option.checked).value;
    
    // Calculate prices
    function calculatePrices() {
        // Resource costs
        const storagePrice = Math.floor(currentStorage / 20) * 1;
        const ramPrice = Math.floor(currentRam / 2) * 1;
        const coresPrice = currentCores * 2;
        
        // Total before discount
        const subtotal = basePrice + storagePrice + ramPrice + coresPrice;
        
        // Apply discount
        const discountPercentage = discounts[currentBillingPeriod];
        const discountAmount = (subtotal * discountPercentage) / 100;
        const total = subtotal - discountAmount;
        
        // Update UI
        storageOverview.textContent = `${currentStorage}GB (+€${storagePrice})`;
        ramOverview.textContent = `${currentRam}GB (+€${ramPrice})`;
        coresOverview.textContent = `${currentCores} (+€${coresPrice})`;
        subtotalElement.textContent = `€${subtotal.toFixed(2)}`;
        discountElement.textContent = `-€${discountAmount.toFixed(2)}`;
        totalElement.textContent = `€${total.toFixed(2)} Total`;
        
        // Update slider labels
        document.querySelector('.d-flex.align-items-center.mb-3 h4').textContent = `Storage (${currentStorage}gb)`;
        document.querySelector('.d-flex.align-items-center.mb-3:nth-of-type(2) h4').textContent = `RAM (${currentRam}gb)`;
        document.querySelector('.d-flex.align-items-center.mb-3:nth-of-type(3) h4').textContent = `Cores (${currentCores})`;
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
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>