<?php
// legal/fair-use-policy.php - Fair Use Policy Page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Set page title
$pageTitle = 'Fair Use Policy';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="fw-bold mb-4">Fair Use Policy</h1>
            
            <div class="card bg-dark mb-4">
                <div class="card-body">
                    <p class="mb-4">At WaveHost, we are committed to providing reliable, high-quality hosting services to all our customers. Our Fair Use Policy is designed to ensure that all customers can enjoy optimal performance without being negatively impacted by the excessive usage patterns of others.</p>
                    
                    <h4 class="text-primary mb-3">1. Purpose</h4>
                    <p class="mb-4">This Fair Use Policy ("Policy") outlines acceptable usage of WaveHost services, including but not limited to our VPS hosting, web hosting, and game server hosting services. By using our services, you acknowledge and agree to comply with this Policy.</p>
                    
                    <h4 class="text-primary mb-3">2. Unlimited Resources</h4>
                    <p class="mb-4">Where we offer "unlimited" resources (such as bandwidth or storage), these are subject to the following fair use conditions:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">Resource usage should be consistent with normal operation of the intended service.</li>
                        <li class="mb-2">Resources should not be used in a way that degrades the experience of other customers.</li>
                        <li class="mb-2">Usage patterns should not place excessive burden on our infrastructure.</li>
                    </ul>
                    
                    <h4 class="text-primary mb-3">3. Bandwidth Usage</h4>
                    <p class="mb-4">While we provide generous bandwidth allowances, we monitor for patterns that may suggest abuse, including:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">Continuous, excessive data transfer that impacts network performance</li>
                        <li class="mb-2">Operating high-traffic file sharing or distribution services</li>
                        <li class="mb-2">Running applications that consume disproportionate network resources</li>
                    </ul>
                    
                    <h4 class="text-primary mb-3">4. CPU and Memory Usage</h4>
                    <p class="mb-4">For shared and VPS hosting plans, we expect CPU and memory usage to remain within reasonable limits:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">Sustained CPU usage should not exceed 25% of allocated resources for extended periods</li>
                        <li class="mb-2">Memory usage should remain within the allocated plan limits</li>
                        <li class="mb-2">Resource-intensive cron jobs should be scheduled responsibly</li>
                    </ul>
                    
                    <h4 class="text-primary mb-3">5. Game Server Hosting</h4>
                    <p class="mb-4">For game server hosting services:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">Servers should be operated within the player slot limits specified in your plan</li>
                        <li class="mb-2">Plugin and mod usage should be optimized to prevent excessive resource consumption</li>
                        <li class="mb-2">Servers should not be used to host unauthorized game modifications or pirated content</li>
                    </ul>
                    
                    <h4 class="text-primary mb-3">6. Storage Usage</h4>
                    <p class="mb-4">Storage space must be used for the legitimate operation of your service:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">Storage should not be used as a backup or file repository unrelated to the service</li>
                        <li class="mb-2">Excessive storage of media files, backups, or archives may be considered a violation</li>
                        <li class="mb-2">Files should be relevant to the operation of your website, application, or game server</li>
                    </ul>
                    
                    <h4 class="text-primary mb-3">7. Email Usage</h4>
                    <p class="mb-4">Email services are subject to the following conditions:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">Mass mailing should comply with anti-spam regulations</li>
                        <li class="mb-2">Sending more than 500 emails per hour may trigger automated restrictions</li>
                        <li class="mb-2">Email accounts should not be used primarily for storage</li>
                    </ul>
                    
                    <h4 class="text-primary mb-3">8. Enforcement</h4>
                    <p class="mb-4">If we determine that your usage violates this Policy, we may take the following actions:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">Contact you to discuss your usage patterns and potential solutions</li>
                        <li class="mb-2">Temporarily limit or throttle your resource usage</li>
                        <li class="mb-2">Recommend an upgrade to a more appropriate service plan</li>
                        <li class="mb-2">In extreme cases, suspend or terminate your service</li>
                    </ul>
                    
                    <h4 class="text-primary mb-3">9. Changes to this Policy</h4>
                    <p class="mb-4">We reserve the right to modify this Policy at any time. Changes will be effective upon posting to our website. Your continued use of our services after such modifications constitutes your acceptance of the revised Policy.</p>
                    
                    <h4 class="text-primary mb-3">10. Contact Information</h4>
                    <p>If you have questions about this Policy or concerns about your resource usage, please contact our support team at <a href="mailto:support@wavehost.com" class="text-primary">support@wavehost.com</a> or via our <a href="/support" class="text-primary">support portal</a>.</p>
                </div>
            </div>
            
            <p class="text-muted">Last updated: April, 2025</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>