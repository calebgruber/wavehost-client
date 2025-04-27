<?php
// about.php - About Page
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Get current user if logged in
$currentUser = null;
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
}

// Set page title
$pageTitle = 'About Us';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- Hero Section -->
<section class="py-5 bg-darker">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="fw-bold mb-4">About WaveHost</h1>
                <p class="lead mb-4">We provide premium game, web, and VPS hosting solutions designed for performance, reliability, and exceptional customer support.</p>
                <p class="mb-4">Founded in 2015, WaveHost has grown from a small team of enthusiasts to a trusted hosting provider serving thousands of customers worldwide. Our mission is to deliver the highest quality hosting services at competitive prices.</p>
                <div class="d-flex gap-3">
                    <a href="/about/careers" class="btn btn-primary">Join Our Team</a>
                    <a href="/support" class="btn btn-outline-primary">Contact Us</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card bg-dark border-0 shadow-lg overflow-hidden">
                    <img src="/assets/images/about/team.jpg" alt="WaveHost Team" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Story Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Our Story</h2>
            <p class="lead text-muted">From humble beginnings to a leading hosting provider</p>
        </div>
        
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="timeline">
                    <div class="row g-0">
                        <div class="col-lg-6">
                            <div class="timeline-card">
                                <div class="card bg-dark h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-primary-subtle rounded-circle p-3 me-3">
                                                <i class="fas fa-flag text-primary"></i>
                                            </div>
                                            <h4 class="mb-0">2015</h4>
                                        </div>
                                        <p class="text-muted">WaveHost was founded by a small team of gaming enthusiasts who saw the need for better game server hosting. Starting with just a few Minecraft servers, we quickly built a reputation for reliability and performance.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6"></div>
                    </div>
                    
                    <div class="row g-0">
                        <div class="col-lg-6"></div>
                        <div class="col-lg-6">
                            <div class="timeline-card">
                                <div class="card bg-dark h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-success-subtle rounded-circle p-3 me-3">
                                                <i class="fas fa-expand-alt text-success"></i>
                                            </div>
                                            <h4 class="mb-0">2017</h4>
                                        </div>
                                        <p class="text-muted">After growing our game server hosting business, we expanded into web hosting and VPS solutions. We invested in state-of-the-art infrastructure and opened our first data center in Amsterdam.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-0">
                        <div class="col-lg-6">
                            <div class="timeline-card">
                                <div class="card bg-dark h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-info-subtle rounded-circle p-3 me-3">
                                                <i class="fas fa-globe text-info"></i>
                                            </div>
                                            <h4 class="mb-0">2019</h4>
                                        </div>
                                        <p class="text-muted">To better serve our global customer base, we expanded our network with new data centers in New York and Singapore. We also launched our custom control panel and automated deployment system.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6"></div>
                    </div>
                    
                    <div class="row g-0">
                        <div class="col-lg-6"></div>
                        <div class="col-lg-6">
                            <div class="timeline-card">
                                <div class="card bg-dark h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-warning-subtle rounded-circle p-3 me-3">
                                                <i class="fas fa-shield-alt text-warning"></i>
                                            </div>
                                            <h4 class="mb-0">2021</h4>
                                        </div>
                                        <p class="text-muted">Security became our top priority. We implemented enterprise-grade DDoS protection across all our services and achieved ISO 27001 certification for our security management system.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-0">
                        <div class="col-lg-6">
                            <div class="timeline-card">
                                <div class="card bg-dark h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-danger-subtle rounded-circle p-3 me-3">
                                                <i class="fas fa-rocket text-danger"></i>
                                            </div>
                                            <h4 class="mb-0">Today</h4>
                                        </div>
                                        <p class="text-muted">WaveHost now serves thousands of customers worldwide with a team of over 50 professionals. We continue to innovate and expand our services while maintaining our core values of performance, reliability, and exceptional support.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Values Section -->
<section class="py-5 bg-darker">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Our Values</h2>
            <p class="lead text-muted">The principles that guide everything we do</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card bg-dark h-100">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <div class="icon-box bg-primary-subtle rounded-circle p-3 mx-auto">
                                <i class="fas fa-bolt text-primary fa-2x"></i>
                            </div>
                        </div>
                        <h4 class="text-center mb-3">Performance</h4>
                        <p class="text-muted text-center">We're obsessed with speed and efficiency. Our infrastructure is built using the latest hardware and optimized for maximum performance.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-dark h-100">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <div class="icon-box bg-success-subtle rounded-circle p-3 mx-auto">
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                        </div>
                        <h4 class="text-center mb-3">Reliability</h4>
                        <p class="text-muted text-center">We understand that downtime costs money. That's why we've built our infrastructure with redundancy at every level, ensuring 99.9% uptime.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-dark h-100">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <div class="icon-box bg-info-subtle rounded-circle p-3 mx-auto">
                                <i class="fas fa-headset text-info fa-2x"></i>
                            </div>
                        </div>
                        <h4 class="text-center mb-3">Support</h4>
                        <p class="text-muted text-center">Our support team is available 24/7 to assist you with any issues. We believe in providing fast, friendly, and knowledgeable support.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-dark h-100">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <div class="icon-box bg-warning-subtle rounded-circle p-3 mx-auto">
                                <i class="fas fa-lock text-warning fa-2x"></i>
                            </div>
                        </div>
                        <h4 class="text-center mb-3">Security</h4>
                        <p class="text-muted text-center">Security is built into everything we do. From DDoS protection to regular security audits, we take every measure to keep your data safe.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-dark h-100">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <div class="icon-box bg-danger-subtle rounded-circle p-3 mx-auto">
                                <i class="fas fa-chart-line text-danger fa-2x"></i>
                            </div>
                        </div>
                        <h4 class="text-center mb-3">Innovation</h4>
                        <p class="text-muted text-center">We're constantly exploring new technologies and approaches to improve our services and provide better solutions for our customers.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-dark h-100">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <div class="icon-box bg-secondary-subtle rounded-circle p-3 mx-auto">
                                <i class="fas fa-leaf text-secondary fa-2x"></i>
                            </div>
                        </div>
                        <h4 class="text-center mb-3">Sustainability</h4>
                        <p class="text-muted text-center">We're committed to minimizing our environmental impact. Our data centers use energy-efficient hardware and renewable energy sources where possible.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Team Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Our Leadership Team</h2>
            <p class="lead text-muted">Meet the people behind WaveHost</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card bg-dark h-100">
                    <img src="/assets/images/team/ceo.jpg" class="card-img-top" alt="CEO">
                    <div class="card-body p-4">
                        <h4 class="mb-1">Michael Chen</h4>
                        <p class="text-primary mb-3">CEO & Co-Founder</p>
                        <p class="text-muted">With over 15 years of experience in the hosting industry, Michael leads our company vision and strategy. Prior to founding WaveHost, he worked at several leading tech companies.</p>
                        <div class="d-flex gap-2 mt-3">
                            <a href="#" class="text-primary"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-primary"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-dark h-100">
                    <img src="/assets/images/team/cto.jpg" class="card-img-top" alt="CTO">
                    <div class="card-body p-4">
                        <h4 class="mb-1">Sarah Johnson</h4>
                        <p class="text-primary mb-3">CTO & Co-Founder</p>
                        <p class="text-muted">Sarah oversees all technical aspects of WaveHost, from infrastructure to software development. She brings a wealth of experience in cloud architecture and systems engineering.</p>
                        <div class="d-flex gap-2 mt-3">
                            <a href="#" class="text-primary"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-primary"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-dark h-100">
                    <img src="/assets/images/team/coo.jpg" class="card-img-top" alt="COO">
                    <div class="card-body p-4">
                        <h4 class="mb-1">David Rodriguez</h4>
                        <p class="text-primary mb-3">COO</p>
                        <p class="text-muted">David manages our day-to-day operations, ensuring that we deliver the highest quality service to our customers. He joined WaveHost in 2017 after a successful career in operations management.</p>
                        <div class="d-flex gap-2 mt-3">
                            <a href="#" class="text-primary"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-primary"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <a href="/about/careers" class="btn btn-primary btn-lg">Join Our Team</a>
        </div>
    </div>
</section>

<!-- Data Centers Section -->
<section class="py-5 bg-darker">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Our Global Infrastructure</h2>
            <p class="lead text-muted">Strategically located data centers for optimal performance</p>
        </div>
        
        <div class="row align-items-center mb-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="card bg-dark border-0 shadow-lg overflow-hidden">
                    <img src="/assets/images/datacenter-map.jpg" alt="Data Center Map" class="img-fluid">
                </div>
            </div>
            <div class="col-lg-6">
                <h3 class="fw-bold mb-4">Strategic Global Presence</h3>
                <p class="mb-4">Our network of data centers is strategically positioned around the world to provide low-latency connections and excellent performance for all our customers, regardless of their location.</p>
                
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-primary-subtle rounded-circle p-3 me-3">
                            <i class="fas fa-map-marker-alt text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Amsterdam, Netherlands</h5>
                            <p class="text-muted mb-0">Our European headquarters and primary data center</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-success-subtle rounded-circle p-3 me-3">
                            <i class="fas fa-map-marker-alt text-success"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">New York, USA</h5>
                            <p class="text-muted mb-0">Serving North America with high-speed connectivity</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-info-subtle rounded-circle p-3 me-3">
                            <i class="fas fa-map-marker-alt text-info"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Los Angeles, USA</h5>
                            <p class="text-muted mb-0">Our West Coast facility for optimal Pacific coverage</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-warning-subtle rounded-circle p-3 me-3">
                            <i class="fas fa-map-marker-alt text-warning"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Singapore</h5>
                            <p class="text-muted mb-0">Providing low-latency service to Asia-Pacific regions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card bg-dark h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-primary-subtle rounded-circle p-3 me-3">
                                <i class="fas fa-server text-primary fa-2x"></i>
                            </div>
                            <h4 class="mb-0">Tier-3+ Data Centers</h4>
                        </div>
                        <p class="text-muted">All our facilities meet or exceed Tier-3 standards, with redundant power, cooling, and network connections. This ensures 99.9% uptime and exceptional reliability for your services.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card bg-dark h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-success-subtle rounded-circle p-3 me-3">
                                <i class="fas fa-network-wired text-success fa-2x"></i>
                            </div>
                            <h4 class="mb-0">Premium Network</h4>
                        </div>
                        <p class="text-muted">Our network is built on multiple Tier-1 providers with redundant connections, ensuring fast and reliable connectivity. We maintain low-latency routes between all our data centers for optimal performance.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card bg-dark h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-info-subtle rounded-circle p-3 me-3">
                                <i class="fas fa-shield-alt text-info fa-2x"></i>
                            </div>
                            <h4 class="mb-0">Security</h4>
                        </div>
                        <p class="text-muted">Physical security is a top priority, with 24/7 monitoring, biometric access controls, and comprehensive surveillance systems. Our facilities are also protected against fire, flood, and other physical threats.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card bg-dark h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-warning-subtle rounded-circle p-3 me-3">
                                <i class="fas fa-leaf text-warning fa-2x"></i>
                            </div>
                            <h4 class="mb-0">Sustainability</h4>
                        </div>
                        <p class="text-muted">We're committed to reducing our environmental impact. Our newer data centers use energy-efficient designs and equipment, and we're progressively increasing our use of renewable energy sources.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Partners Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Our Partners</h2>
            <p class="lead text-muted">Working with industry leaders to deliver the best service</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card bg-dark">
                    <div class="card-body p-5">
                        <div class="row g-5 text-center">
                            <div class="col-4 col-md-2">
                                <img src="/assets/images/partners/partner1.png" alt="Partner 1" class="img-fluid" style="max-height: 60px;">
                            </div>
                            <div class="col-4 col-md-2">
                                <img src="/assets/images/partners/partner2.png" alt="Partner 2" class="img-fluid" style="max-height: 60px;">
                            </div>
                            <div class="col-4 col-md-2">
                                <img src="/assets/images/partners/partner3.png" alt="Partner 3" class="img-fluid" style="max-height: 60px;">
                            </div>
                            <div class="col-4 col-md-2">
                                <img src="/assets/images/partners/partner4.png" alt="Partner 4" class="img-fluid" style="max-height: 60px;">
                            </div>
                            <div class="col-4 col-md-2">
                                <img src="/assets/images/partners/partner5.png" alt="Partner 5" class="img-fluid" style="max-height: 60px;">
                            </div>
                            <div class="col-4 col-md-2">
                                <img src="/assets/images/partners/partner6.png" alt="Partner 6" class="img-fluid" style="max-height: 60px;">
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
                <h2 class="fw-bold text-white mb-4">Ready to experience the WaveHost difference?</h2>
                <p class="lead text-white-50 mb-4">Join thousands of satisfied customers who trust us with their hosting needs.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="/games" class="btn btn-light btn-lg">Game Servers</a>
                    <a href="/web" class="btn btn-outline-light btn-lg">Web Hosting</a>
                    <a href="/vps" class="btn btn-outline-light btn-lg">VPS Hosting</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Timeline Styles */
.timeline {
    position: relative;
    padding: 2rem 0;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    height: 100%;
    width: 2px;
    background-color: #1e2133;
    transform: translateX(-50%);
}

.timeline-card {
    position: relative;
    margin: 2rem 0;
    padding-right: 2rem;
}

.col-lg-6:nth-child(2) .timeline-card {
    padding-right: 0;
    padding-left: 2rem;
}

@media (max-width: 992px) {
    .timeline::before {
        left: 0;
    }
    
    .timeline-card {
        padding-left: 2rem;
        padding-right: 0;
    }
    
    .col-lg-6:nth-child(2) .timeline-card {
        padding-left: 2rem;
        padding-right: 0;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>