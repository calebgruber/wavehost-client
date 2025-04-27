<?php
// index.php - Homepage
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Get current user if logged in
$currentUser = null;
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
}

// Set page title
$pageTitle = 'High Performance Game, Web & VPS Hosting';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section text-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">Next-Gen Hosting for Gamers and Developers</h1>
                <p class="lead mb-5">Experience premium game servers, lightning-fast web hosting, and powerful VPS solutions with 24/7 support and 99.9% uptime guarantee.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="/games" class="btn btn-primary btn-lg">Game Servers</a>
                    <a href="/web" class="btn btn-outline-primary btn-lg">Web Hosting</a>
                    <a href="/vps" class="btn btn-outline-primary btn-lg">VPS Hosting</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-darker">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Why Choose WaveHost?</h2>
            <p class="lead text-muted">We provide the best hosting experience with premium features</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-primary-subtle rounded-circle p-3 mb-3">
                            <i class="fas fa-bolt text-primary fa-2x"></i>
                        </div>
                        <h4>High Performance</h4>
                        <p class="text-muted">Powered by latest-gen AMD EPYC and Intel Xeon processors with NVMe SSD storage for lightning-fast performance.</p>
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
                        <p class="text-muted">Enterprise-grade protection included with all plans to keep your services running smoothly even during attacks.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-info-subtle rounded-circle p-3 mb-3">
                            <i class="fas fa-headset text-info fa-2x"></i>
                        </div>
                        <h4>24/7 Support</h4>
                        <p class="text-muted">Our expert team is available around the clock to assist you with any issues or questions you may have.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Game Servers Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="fw-bold mb-4">Game Servers</h2>
                <p class="lead mb-4">Premium game hosting for Minecraft, CS:GO, ARK, Rust and more with instant setup and powerful control panel.</p>
                
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">One-Click Game Installation</h5>
                            <p class="text-muted mb-0">Install your favorite games with a single click</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">Mod Support</h5>
                            <p class="text-muted mb-0">Easy installation of mods and plugins</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">Multiple Locations</h5>
                            <p class="text-muted mb-0">Servers in North America, Europe, and Asia</p>
                        </div>
                    </div>
                </div>
                
                <a href="/games" class="btn btn-primary">Explore Game Servers</a>
            </div>
            
            <div class="col-lg-6">
                <div class="card bg-dark border-0 shadow-lg overflow-hidden">
                    <img src="/assets/images/minecraft-server.jpg" alt="Minecraft Server" class="img-fluid">
                    <div class="card-body">
                        <h5 class="card-title">Minecraft Server Hosting</h5>
                        <p class="card-text text-muted">Starting at €5/month</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-primary">50+ Plugin Support</div>
                            <a href="/games/minecraft" class="btn btn-sm btn-outline-primary">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Web Hosting Section -->
<section class="py-5 bg-darker">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
                <h2 class="fw-bold mb-4">Web Hosting</h2>
                <p class="lead mb-4">Fast and reliable web hosting with cPanel, one-click WordPress installation, and free SSL certificates.</p>
                
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">99.9% Uptime Guarantee</h5>
                            <p class="text-muted mb-0">Keep your website online at all times</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">Free Domain & SSL</h5>
                            <p class="text-muted mb-0">Get a free domain and SSL certificate with annual plans</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">Daily Backups</h5>
                            <p class="text-muted mb-0">Automatic backups to keep your data safe</p>
                        </div>
                    </div>
                </div>
                
                <a href="/web" class="btn btn-primary">Explore Web Hosting</a>
            </div>
            
            <div class="col-lg-6 order-lg-1">
                <div class="card bg-dark border-0 shadow-lg overflow-hidden">
                    <img src="/assets/images/web-hosting.jpg" alt="Web Hosting" class="img-fluid">
                    <div class="card-body">
                        <h5 class="card-title">WordPress Hosting</h5>
                        <p class="card-text text-muted">Starting at €3.99/month</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-primary">Free Migration</div>
                            <a href="/web/wordpress" class="btn btn-sm btn-outline-primary">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- VPS Hosting Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="fw-bold mb-4">VPS Hosting</h2>
                <p class="lead mb-4">Powerful virtual private servers with full root access, dedicated resources, and multiple OS options.</p>
                
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">NVMe SSD Storage</h5>
                            <p class="text-muted mb-0">Ultra-fast storage for better performance</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">Full Root Access</h5>
                            <p class="text-muted mb-0">Complete control over your server</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">Instant Deployment</h5>
                            <p class="text-muted mb-0">Get your VPS up and running in minutes</p>
                        </div>
                    </div>
                </div>
                
                <a href="/vps" class="btn btn-primary">Explore VPS Hosting</a>
            </div>
            
            <div class="col-lg-6">
                <div class="card bg-dark border-0 shadow-lg overflow-hidden">
                    <img src="/assets/images/vps-hosting.jpg" alt="VPS Hosting" class="img-fluid">
                    <div class="card-body">
                        <h5 class="card-title">SSD VPS Hosting</h5>
                        <p class="card-text text-muted">Starting at €5/month</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-primary">Customizable Resources</div>
                            <a href="/vps" class="btn btn-sm btn-outline-primary">Learn More</a>
                        </div>
                    </div>
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
            <p class="lead text-muted">Trusted by thousands of gamers and developers worldwide</p>
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
                        <p class="mb-4">"WaveHost provides the best Minecraft server hosting I've ever used. The performance is amazing and their support team is always helpful when I need assistance."</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <span class="fw-bold text-white">JM</span>
                            </div>
                            <div>
                                <div class="fw-bold">James Miller</div>
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
                        <p class="mb-4">"I've been using WaveHost's VPS for my web development business for over a year now. The speed and reliability have been exceptional, and their DDoS protection saved me during an attack."</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <span class="fw-bold text-white">SC</span>
                            </div>
                            <div>
                                <div class="fw-bold">Sarah Chen</div>
                                <div class="text-muted small">Web Developer</div>
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
                        <p class="mb-4">"WaveHost's web hosting service has been a game-changer for our small business. Fast loading times, great uptime, and the support team goes above and beyond to help with any issues."</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <span class="fw-bold text-white">MJ</span>
                            </div>
                            <div>
                                <div class="fw-bold">Michael Johnson</div>
                                <div class="text-muted small">Business Owner</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Data Centers Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Global Data Centers</h2>
            <p class="lead text-muted">Choose from multiple strategic locations worldwide</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card bg-dark border-0">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-3 col-6 text-center">
                                <img src="/assets/images/flags/netherlands.png" alt="Netherlands Flag" class="mb-3" width="60">
                                <h5>Amsterdam</h5>
                                <p class="text-muted small mb-0">Netherlands</p>
                            </div>
                            <div class="col-md-3 col-6 text-center">
                                <img src="/assets/images/flags/usa.png" alt="USA Flag" class="mb-3" width="60">
                                <h5>New York</h5>
                                <p class="text-muted small mb-0">United States</p>
                            </div>
                            <div class="col-md-3 col-6 text-center">
                                <img src="/assets/images/flags/usa.png" alt="USA Flag" class="mb-3" width="60">
                                <h5>Los Angeles</h5>
                                <p class="text-muted small mb-0">United States</p>
                            </div>
                            <div class="col-md-3 col-6 text-center">
                                <img src="/assets/images/flags/singapore.png" alt="Singapore Flag" class="mb-3" width="60">
                                <h5>Singapore</h5>
                                <p class="text-muted small mb-0">Singapore</p>
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
                <p class="lead text-white-50 mb-4">Choose the perfect hosting solution for your needs and experience the WaveHost difference.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="/games" class="btn btn-light btn-lg">Game Servers</a>
                    <a href="/web" class="btn btn-outline-light btn-lg">Web Hosting</a>
                    <a href="/vps" class="btn btn-outline-light btn-lg">VPS Hosting</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>