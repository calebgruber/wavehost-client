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

// Get web hosting plans from the database
$db = db();
$plans = $db->select(
    "SELECT * FROM web_hosting_plans ORDER BY price ASC"
);

// Define pricing tiers
$pricingTiers = [
    'starter' => [
        'name' => 'Starter',
        'features' => [
            '10GB SSD Storage',
            '100GB Bandwidth',
            '1 Website',
            '5 Databases',
            '10 Email Accounts',
            'Free SSL Certificate',
            '99.9% Uptime Guarantee',
            '24/7 Support'
        ],
        'price' => 3.99
    ],
    'business' => [
        'name' => 'Business',
        'features' => [
            '25GB SSD Storage',
            'Unlimited Bandwidth',
            '10 Websites',
            'Unlimited Databases',
            'Unlimited Email Accounts',
            'Free SSL Certificate',
            'Free Domain',
            '99.9% Uptime Guarantee',
            'Priority Support'
        ],
        'price' => 7.99
    ],
    'premium' => [
        'name' => 'Premium',
        'features' => [
            '100GB SSD Storage',
            'Unlimited Bandwidth',
            'Unlimited Websites',
            'Unlimited Databases',
            'Unlimited Email Accounts',
            'Free SSL Certificate',
            'Free Domain',
            'Daily Backups',
            '99.9% Uptime Guarantee',
            'Priority Support',
            'Staging Environment'
        ],
        'price' => 12.99
    ]
];

// Set page title
$pageTitle = 'Web Hosting';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section text-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">Fast & Reliable Web Hosting</h1>
                <p class="lead mb-5">Lightning-fast SSD hosting with free SSL, 99.9% uptime guarantee, and 24/7 expert support.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="#pricing" class="btn btn-primary btn-lg">View Plans</a>
                    <a href="#features" class="btn btn-outline-primary btn-lg">Explore Features</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5 bg-darker">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Why Choose Our Web Hosting?</h2>
            <p class="lead text-muted">We provide the features you need to succeed online</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-primary-subtle rounded-circle p-3 mb-3">
                            <i class="fas fa-bolt text-primary fa-2x"></i>
                        </div>
                        <h4>Lightning Fast</h4>
                        <p class="text-muted">Our web hosting runs on high-performance NVMe SSDs and the latest server hardware for blazing-fast website loading times.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-success-subtle rounded-circle p-3 mb-3">
                            <i class="fas fa-shield-alt text-success fa-2x"></i>
                        </div>
                        <h4>Enhanced Security</h4>
                        <p class="text-muted">All hosting plans include free SSL certificates, DDoS protection, and regular security updates to keep your site safe.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-info-subtle rounded-circle p-3 mb-3">
                            <i class="fas fa-server text-info fa-2x"></i>
                        </div>
                        <h4>99.9% Uptime</h4>
                        <p class="text-muted">We guarantee 99.9% uptime for your website with our fault-tolerant infrastructure and redundant network.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-warning-subtle rounded-circle p-3 mb-3">
                            <i class="fas fa-hdd text-warning fa-2x"></i>
                        </div>
                        <h4>Daily Backups</h4>
                        <p class="text-muted">We perform automatic daily backups of your website, ensuring your data is always safe and can be restored if needed.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-danger-subtle rounded-circle p-3 mb-3">
                            <i class="fas fa-tools text-danger fa-2x"></i>
                        </div>
                        <h4>Easy Management</h4>
                        <p class="text-muted">Manage your website, databases, email accounts, and more with our intuitive cPanel control panel.</p>
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
                        <p class="text-muted">Our expert support team is available around the clock to help with any issues or questions you may have.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Technology Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="fw-bold mb-4">Cutting-Edge Technology</h2>
                <p class="lead mb-4">Our hosting platform is built on the latest technology to provide the best performance, security, and reliability for your website.</p>
                
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">NVMe SSD Storage</h5>
                            <p class="text-muted mb-0">Up to 20x faster than traditional SSDs</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">LiteSpeed Web Server</h5>
                            <p class="text-muted mb-0">Faster page loading and better resource efficiency</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">Latest PHP & MySQL</h5>
                            <p class="text-muted mb-0">Support for the latest versions for better performance</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">Free Cloudflare CDN</h5>
                            <p class="text-muted mb-0">Faster content delivery worldwide</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card bg-dark border-0 shadow-lg">
                    <img src="/assets/images/datacenter.jpg" alt="Data Center" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- One-Click Installs Section -->
<section class="py-5 bg-darker">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">One-Click Application Installs</h2>
            <p class="lead text-muted">Install your favorite apps with a single click</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card bg-dark">
                    <div class="card-body p-4">
                        <div class="row g-4 text-center">
                            <div class="col-4 col-md-2">
                                <div class="app-icon mb-2">
                                    <img src="/assets/images/apps/wordpress.png" alt="WordPress" class="img-fluid" width="60">
                                </div>
                                <div>WordPress</div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="app-icon mb-2">
                                    <img src="/assets/images/apps/joomla.png" alt="Joomla" class="img-fluid" width="60">
                                </div>
                                <div>Joomla</div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="app-icon mb-2">
                                    <img src="/assets/images/apps/drupal.png" alt="Drupal" class="img-fluid" width="60">
                                </div>
                                <div>Drupal</div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="app-icon mb-2">
                                    <img src="/assets/images/apps/magento.png" alt="Magento" class="img-fluid" width="60">
                                </div>
                                <div>Magento</div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="app-icon mb-2">
                                    <img src="/assets/images/apps/prestashop.png" alt="PrestaShop" class="img-fluid" width="60">
                                </div>
                                <div>PrestaShop</div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="app-icon mb-2">
                                    <img src="/assets/images/apps/opencart.png" alt="OpenCart" class="img-fluid" width="60">
                                </div>
                                <div>OpenCart</div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted">Plus 100+ more applications available for instant installation</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Simple, Transparent Pricing</h2>
            <p class="lead text-muted">Choose the plan that suits your needs</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($pricingTiers as $id => $tier): ?>
                <div class="col-md-4">
                    <div class="card h-100 <?php echo $id === 'business' ? 'border-primary' : ''; ?>">
                        <?php if ($id === 'business'): ?>
                            <div class="card-header bg-primary text-white text-center py-3">
                                <span class="badge bg-white text-primary">Most Popular</span>
                            </div>
                        <?php endif; ?>
                        <div class="card-body p-4">
                            <h4 class="card-title text-center mb-4"><?php echo $tier['name']; ?></h4>
                            <div class="display-6 text-center mb-4">€<?php echo number_format($tier['price'], 2); ?><span class="text-muted fs-6">/month</span></div>
                            <ul class="list-unstyled mb-4">
                                <?php foreach ($tier['features'] as $feature): ?>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo $feature; ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="/order/web?plan=<?php echo $id; ?>" class="btn btn-primary w-100">Get Started</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-muted">All plans include a 30-day money-back guarantee</p>
        </div>
    </div>
</section>

<!-- Control Panel Section -->
<section class="py-5 bg-darker">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
                <h2 class="fw-bold mb-4">Easy-to-use Control Panel</h2>
                <p class="lead mb-4">Manage your website, domains, databases, email accounts, and more with our intuitive cPanel control panel.</p>
                
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">Domain Management</h5>
                            <p class="text-muted mb-0">Manage domains, subdomains, and DNS settings</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">Email Management</h5>
                            <p class="text-muted mb-0">Create and manage email accounts, forwarders, and more</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">Database Management</h5>
                            <p class="text-muted mb-0">Create and manage MySQL databases and users</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">File Manager</h5>
                            <p class="text-muted mb-0">Upload, download, and manage your files with ease</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 order-lg-1">
                <div class="card bg-dark border-0 shadow-lg">
                    <img src="/assets/images/cpanel.jpg" alt="Control Panel" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">What Our Customers Say</h2>
            <p class="lead text-muted">Trusted by thousands of website owners worldwide</p>
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
                        <p class="mb-4">"I've tried several web hosting providers, and WaveHost is by far the best. My website loads lightning fast, and their support team is always there when I need help. Highly recommended!"</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <span class="fw-bold text-white">SJ</span>
                            </div>
                            <div>
                                <div class="fw-bold">Sarah Johnson</div>
                                <div class="text-muted small">E-commerce Store Owner</div>
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
                        <p class="mb-4">"I migrated my WordPress site to WaveHost and saw an immediate improvement in loading speed. The one-click installer made it so easy to set up, and I haven't experienced any downtime in over a year."</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <span class="fw-bold text-white">MP</span>
                            </div>
                            <div>
                                <div class="fw-bold">Michael Patel</div>
                                <div class="text-muted small">Blogger</div>
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
                        <p class="mb-4">"As a web developer, I've used many hosting providers, but WaveHost stands out for their performance and support. Their servers are fast, reliable, and the cPanel makes management a breeze."</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <span class="fw-bold text-white">LR</span>
                            </div>
                            <div>
                                <div class="fw-bold">Laura Rodriguez</div>
                                <div class="text-muted small">Web Developer</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-darker">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Frequently Asked Questions</h2>
            <p class="lead text-muted">Find answers to common questions about our web hosting</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item bg-dark border-secondary mb-3">
                        <h2 class="accordion-header" id="faqHeading1">
                            <button class="accordion-button bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="true" aria-controls="faqCollapse1">
                                Can I migrate my existing website to WaveHost?
                            </button>
                        </h2>
                        <div id="faqCollapse1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Yes, we offer free website migration for all new customers. Our team will handle the entire migration process, ensuring a smooth transition without any downtime. This includes transferring your website files, databases, emails, and configurations. Simply contact our support team after signing up, and we'll take care of everything for you.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-dark border-secondary mb-3">
                        <h2 class="accordion-header" id="faqHeading2">
                            <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                                Do you offer a money-back guarantee?
                            </button>
                        </h2>
                        <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Yes, we offer a 30-day money-back guarantee for all our hosting plans. If you're not satisfied with our service for any reason, you can cancel within the first 30 days and receive a full refund. This guarantee covers the hosting fees only and does not apply to add-on services such as domain registrations or SSL certificates.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-dark border-secondary mb-3">
                        <h2 class="accordion-header" id="faqHeading3">
                            <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                                What control panel do you use?
                            </button>
                        </h2>
                        <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                We use cPanel, the industry-leading control panel, for all our web hosting plans. cPanel provides an intuitive interface to manage all aspects of your hosting account, including files, databases, domains, email accounts, and more. It also includes one-click installers for popular applications like WordPress, Joomla, and Drupal, making it easy to set up your website even if you have limited technical knowledge.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-dark border-secondary mb-3">
                        <h2 class="accordion-header" id="faqHeading4">
                            <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
                                Can I upgrade my hosting plan later?
                            </button>
                        </h2>
                        <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Yes, you can easily upgrade your hosting plan at any time through your client area. If you find that you need more resources, storage, or features, you can upgrade to a higher plan with just a few clicks. The upgrade process is seamless and usually completed within minutes, with no downtime for your website. We'll also prorate the cost based on your remaining subscription time.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-dark border-secondary">
                        <h2 class="accordion-header" id="faqHeading5">
                            <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse5" aria-expanded="false" aria-controls="faqCollapse5">
                                What kind of support do you offer?
                            </button>
                        </h2>
                        <div id="faqCollapse5" class="accordion-collapse collapse" aria-labelledby="faqHeading5" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                We offer 24/7 support via live chat, email, and ticket system. Our support team consists of experienced hosting professionals who can assist with technical issues, provide guidance on using our services, and answer any questions you may have. Business and Premium plan customers also receive priority support, ensuring faster response times when you need assistance.
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
                <h2 class="fw-bold text-white mb-4">Ready to get started with WaveHost?</h2>
                <p class="lead text-white-50 mb-4">Sign up today and get your website online in minutes with our easy setup process.</p>
                <a href="#pricing" class="btn btn-light btn-lg">Choose Your Plan</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>