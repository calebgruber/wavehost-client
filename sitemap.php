<?php
// sitemap.php - Site Map Page
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Define site structure
$siteMap = [
    'Main Pages' => [
        ['name' => 'Homepage', 'url' => '/'],
        ['name' => 'Game Server Hosting', 'url' => '/game-server'],
        ['name' => 'Web Hosting', 'url' => '/web-hosting'],
        ['name' => 'VPS Hosting', 'url' => '/vps-hosting'],
        ['name' => 'About Us', 'url' => '/about'],
        ['name' => 'Support', 'url' => '/support'],
        ['name' => 'Blog', 'url' => '/blog']
    ],
    'About Pages' => [
        ['name' => 'Careers', 'url' => '/about/careers']
    ],
    'Legal Pages' => [
        ['name' => 'Terms of Service', 'url' => '/legal/tos'],
        ['name' => 'Privacy Policy', 'url' => '/legal/privacy-policy'],
        ['name' => 'Fair Use Policy', 'url' => '/legal/fair-use-policy'],
        ['name' => 'Responsible Disclosure', 'url' => '/legal/responsible-disclosure'],
        ['name' => 'Service Level Agreement', 'url' => '/legal/service-level-agreement'],
        ['name' => 'Report Abuse', 'url' => '/legal/report-abuse']
    ],
    'Client Area' => [
        ['name' => 'Login', 'url' => '/auth/login'],
        ['name' => 'Register', 'url' => '/auth/register'],
        ['name' => 'Password Reset', 'url' => '/auth/reset-password'],
        ['name' => 'Dashboard', 'url' => '/dash'],
        ['name' => 'Services', 'url' => '/dash/services'],
        ['name' => 'Invoices', 'url' => '/dash/invoices'],
        ['name' => 'Support Tickets', 'url' => '/dash/tickets'],
        ['name' => 'Create Ticket', 'url' => '/dash/ticket/new'],
        ['name' => 'Affiliate Program', 'url' => '/dash/affiliate'],
        ['name' => 'Account Settings', 'url' => '/dash/account']
    ],
    'Cart System' => [
        ['name' => 'Shopping Cart', 'url' => '/cart'],
        ['name' => 'Configure VPS', 'url' => '/cart/configure/vps'],
        ['name' => 'Configure Web Hosting', 'url' => '/cart/configure/web-hosting'],
        ['name' => 'Configure Game Server', 'url' => '/cart/configure/game-server'],
        ['name' => 'Checkout', 'url' => '/checkout']
    ],
    'API' => [
        ['name' => 'API Documentation', 'url' => '/api'],
        ['name' => 'API Authentication', 'url' => '/api/auth']
    ]
];

// Set page title
$pageTitle = 'Site Map';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="container py-5">
    <h1 class="fw-bold mb-4">Site Map</h1>
    
    <div class="row">
        <?php foreach ($siteMap as $category => $links): ?>
            <div class="col-md-6 mb-4">
                <div class="card bg-dark h-100">
                    <div class="card-header bg-darker">
                        <h4 class="card-title mb-0"><?php echo $category; ?></h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($links as $link): ?>
                                <li class="mb-2">
                                    <a href="<?php echo $link['url']; ?>" class="d-flex align-items-center">
                                        <i class="fas fa-link text-primary me-2"></i>
                                        <span><?php echo $link['name']; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="mt-4">
        <h3 class="mb-3">Looking for something specific?</h3>
        <p>Use the search function or <a href="/support" class="text-primary">contact our support team</a> if you need assistance finding any information on our website.</p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>