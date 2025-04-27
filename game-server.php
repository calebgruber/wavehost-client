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

// Get game server plans from the database
$db = db();
$games = $db->select(
    "SELECT * FROM game_types ORDER BY name ASC"
);

// Define pricing tiers
$pricingTiers = [
    'standard' => [
        'name' => 'Standard',
        'features' => [
            '2GB RAM',
            '10GB SSD Storage',
            'Unlimited Traffic',
            'DDoS Protection',
            'Mod Support',
            '24/7 Support'
        ],
        'price' => 5
    ],
    'premium' => [
        'name' => 'Premium',
        'features' => [
            '4GB RAM',
            '20GB SSD Storage',
            'Unlimited Traffic',
            'DDoS Protection',
            'Mod Support',
            'Priority Support',
            'Daily Backups'
        ],
        'price' => 10
    ],
    'ultimate' => [
        'name' => 'Ultimate',
        'features' => [
            '8GB RAM',
            '50GB SSD Storage',
            'Unlimited Traffic',
            'DDoS Protection',
            'Mod Support',
            'Priority Support',
            'Daily Backups',
            'Dedicated IP',
            'Premium CPU Priority'
        ],
        'price' => 20
    ]
];

// Popular games
$popularGames = [
    [
        'name' => 'Minecraft',
        'image' => '/assets/images/games/minecraft.jpg',
        'description' => 'Create and explore your very own world where the only limit is your imagination.',
        'players' => '10-100',
        'starting_price' => 5
    ],
    [
        'name' => 'ARK: Survival Evolved',
        'image' => '/assets/images/games/ark.jpg',
        'description' => 'Tame dinosaurs, conquer territories, and team up with or fight against hundreds of players.',
        'players' => '10-70',
        'starting_price' => 10
    ],
    [
        'name' => 'Counter-Strike 2',
        'image' => '/assets/images/games/cs2.jpg',
        'description' => 'Competitive tactical first-person shooter with intense team-based gameplay.',
        'players' => '12-32',
        'starting_price' => 8
    ],
    [
        'name' => 'Rust',
        'image' => '/assets/images/games/rust.jpg',
        'description' => 'Survive in a harsh world: gather resources, build a shelter, and raid other players.',
        'players' => '50-300',
        'starting_price' => 12
    ],
    [
        'name' => 'Valheim',
        'image' => '/assets/images/games/valheim.jpg',
        'description' => 'A brutal exploration and survival game set in a procedurally-generated world.',
        'players' => '10-20',
        'starting_price' => 7
    ],
    [
        'name' => 'Terraria',
        'image' => '/assets/images/games/terraria.jpg',
        'description' => '2D sandbox adventure with exploration, crafting, building, and combat.',
        'players' => '8-16',
        'starting_price' => 5
    ]
];

// Set page title
$pageTitle = 'Game Server Hosting';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section text-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">Premium Game Server Hosting</h1>
                <p class="lead mb-5">Experience lag-free gaming with our high-performance servers. One-click installation, DDoS protection, and 24/7 support included.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="#games" class="btn btn-primary btn-lg">Browse Games</a>
                    <a href="#pricing" class="btn btn-outline-primary btn-lg">View Pricing</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-darker">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Why Choose Our Game Servers?</h2>
            <p class="lead text-muted">We provide the best hosting experience for gamers</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-primary-subtle rounded-circle p-3 mb-3">
                            <i class="fas fa-bolt text-primary fa-2x"></i>
                        </div>
                        <h4>High Performance</h4>
                        <p class="text-muted">Our game servers run on the latest AMD Ryzen and Intel processors with NVMe SSD storage for maximum performance and minimal lag.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-success-subtle rounded-circle p-3 mb-3">
                            <i class="fas fa-shield-alt text-success fa-2x"></i>
                        </div>
                        <h4>DDoS Protection</h4>
                        <p class="text-muted">All our game servers include enterprise-grade DDoS protection to ensure your game stays online even during attacks.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-info-subtle rounded-circle p-3 mb-3">
                            <i class="fas fa-sliders-h text-info fa-2x"></i>
                        </div>
                        <h4>Easy Setup</h4>
                        <p class="text-muted">Get your server up and running in minutes with our one-click installer and intuitive control panel.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-warning-subtle rounded-circle p-3 mb-3">
                            <i class="fas fa-puzzle-piece text-warning fa-2x"></i>
                        </div>
                        <h4>Mod Support</h4>
                        <p class="text-muted">Install your favorite mods and plugins with ease using our custom mod manager.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-danger-subtle rounded-circle p-3 mb-3">
                            <i class="fas fa-globe text-danger fa-2x"></i>
                        </div>
                        <h4>Global Locations</h4>
                        <p class="text-muted">Choose from multiple server locations worldwide to minimize latency for you and your players.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-secondary-subtle rounded-circle p-3 mb-3">
                            <i class="fas fa-headset text-secondary fa-2x"></i>
                        </div>
                        <h4>24/7 Support</h4>
                        <p class="text-muted">Our expert support team is available around the clock to help with any issues.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Games Section -->
<section id="games" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Popular Games</h2>
            <p class="lead text-muted">Host your favorite games with instant setup</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($popularGames as $game): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <img src="<?php echo $game['image']; ?>" class="card-img-top" alt="<?php echo $game['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $game['name']; ?></h5>
                            <p class="card-text text-muted"><?php echo $game['description']; ?></p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="text-primary"><i class="fas fa-users me-2"></i> <?php echo $game['players']; ?> players</div>
                                <div>From €<?php echo $game['starting_price']; ?>/month</div>
                            </div>
                            <a href="/order/game/<?php echo strtolower(str_replace(' ', '-', $game['name'])); ?>" class="btn btn-primary w-100">Order Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="/games/all" class="btn btn-outline-primary btn-lg">View All Games</a>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="py-5 bg-darker">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Simple, Transparent Pricing</h2>
            <p class="lead text-muted">Choose the plan that suits your needs</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($pricingTiers as $id => $tier): ?>
                <div class="col-md-4">
                    <div class="card h-100 <?php echo $id === 'premium' ? 'border-primary' : ''; ?>">
                        <?php if ($id === 'premium'): ?>
                            <div class="card-header bg-primary text-white text-center py-3">
                                <span class="badge bg-white text-primary">Most Popular</span>
                            </div>
                        <?php endif; ?>
                        <div class="card-body p-4">
                            <h4 class="card-title text-center mb-4"><?php echo $tier['name']; ?></h4>
                            <div class="display-6 text-center mb-4">€<?php echo $tier['price']; ?><span class="text-muted fs-6">/month</span></div>
                            <ul class="list-unstyled mb-4">
                                <?php foreach ($tier['features'] as $feature): ?>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo $feature; ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="/order/game?plan=<?php echo $id; ?>" class="btn btn-primary w-100">Get Started</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Control Panel Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="fw-bold mb-4">Easy-to-use Control Panel</h2>
                <p class="lead mb-4">Manage your game server with our intuitive control panel. Start, stop, restart, and customize your server with just a few clicks.</p>
                
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">One-click game installation</h5>
                            <p class="text-muted mb-0">Install your game server with a single click</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">Easy mod management</h5>
                            <p class="text-muted mb-0">Install and manage mods and plugins effortlessly</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">File manager</h5>
                            <p class="text-muted mb-0">Upload and manage files with our built-in file manager</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">Backup system</h5>
                            <p class="text-muted mb-0">Create and restore backups with a single click</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card bg-dark border-0 shadow-lg">
                    <img src="/assets/images/control-panel.jpg" alt="Control Panel" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-darker">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">What Our Customers Say</h2>
            <p class="lead text-muted">Trusted by thousands of gamers worldwide</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rating text-warning mb-3">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="mb-4">"I've tried several Minecraft server hosts, and WaveHost is by far the best. No lag, easy setup, and their support team is always there when I need help. Highly recommended!"</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <span class="fw-bold text-white">TG</span>
                            </div>
                            <div>
                                <div class="fw-bold">Tom Garcia</div>
                                <div class="text-muted small">Minecraft Server Owner</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rating text-warning mb-3">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="mb-4">"The ARK server I host with WaveHost runs flawlessly even with 50+ players online. The control panel makes it super easy to manage mods and settings. Best hosting service I've used!"</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <span class="fw-bold text-white">AK</span>
                            </div>
                            <div>
                                <div class="fw-bold">Alex Kim</div>
                                <div class="text-muted small">ARK Server Admin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rating text-warning mb-3">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                        <p class="mb-4">"Our CS2 team switched to WaveHost after having lag issues with our previous provider. The difference is night and day - stable performance, great tick rate, and the DDoS protection saved us during tournaments."</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <span class="fw-bold text-white">EM</span>
                            </div>
                            <div>
                                <div class="fw-bold">Emma Martinez</div>
                                <div class="text-muted small">CS2 Team Manager</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Frequently Asked Questions</h2>
            <p class="lead text-muted">Find answers to common questions about our game server hosting</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item bg-dark border-secondary mb-3">
                        <h2 class="accordion-header" id="faqHeading1">
                            <button class="accordion-button bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="true" aria-controls="faqCollapse1">
                                How long does it take to set up a game server?
                            </button>
                        </h2>
                        <div id="faqCollapse1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Our game servers are set up instantly after payment. You'll receive your server details via email, and you can start playing right away. Our one-click installer makes it easy to get your favorite games up and running in minutes.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-dark border-secondary mb-3">
                        <h2 class="accordion-header" id="faqHeading2">
                            <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                                Can I install mods on my game server?
                            </button>
                        </h2>
                        <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Yes, all our game servers support mods and plugins. Our control panel includes a mod manager that makes it easy to install and manage mods with just a few clicks. For games like Minecraft, we support popular modpacks and server types like Spigot, Paper, and Forge.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-dark border-secondary mb-3">
                        <h2 class="accordion-header" id="faqHeading3">
                            <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                                What kind of DDoS protection do you offer?
                            </button>
                        </h2>
                        <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                All our game servers include enterprise-grade DDoS protection at no extra cost. Our protection system can mitigate attacks of up to 1Tbps, ensuring your server stays online even during large-scale attacks. This protection is especially important for competitive gaming and popular public servers.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-dark border-secondary mb-3">
                        <h2 class="accordion-header" id="faqHeading4">
                            <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
                                Can I upgrade my server later if I need more resources?
                            </button>
                        </h2>
                        <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Yes, you can easily upgrade your server at any time through our client area. If you find that you need more RAM, storage, or player slots, you can upgrade with just a few clicks. The upgrade process is usually completed within minutes, with minimal downtime for your server.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-dark border-secondary">
                        <h2 class="accordion-header" id="faqHeading5">
                            <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse5" aria-expanded="false" aria-controls="faqCollapse5">
                                What payment methods do you accept?
                            </button>
                        </h2>
                        <div id="faqCollapse5" class="accordion-collapse collapse" aria-labelledby="faqHeading5" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                We accept various payment methods including credit/debit cards (Visa, Mastercard, American Express), PayPal, and cryptocurrencies (Bitcoin, Ethereum). We also offer multiple billing cycles including monthly, quarterly, semi-annual, and annual options, with discounts for longer billing periods.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="fw-bold text-white mb-4">Ready to host your game server?</h2>
                <p class="lead text-white-50 mb-4">Get started with WaveHost today and experience the best game server hosting available.</p>
                <a href="#games" class="btn btn-light btn-lg">Order Now</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>