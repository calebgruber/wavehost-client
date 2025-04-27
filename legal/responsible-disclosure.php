<?php
// legal/responsible-disclosure.php - Responsible Disclosure Policy Page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Set page title
$pageTitle = 'Responsible Disclosure Policy';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="fw-bold mb-4">Responsible Disclosure Policy</h1>
            
            <div class="card bg-dark mb-4">
                <div class="card-body">
                    <p class="mb-4">At WaveHost, we take security seriously. We value the efforts of security researchers and the wider community in helping us maintain the security of our systems. This Responsible Disclosure Policy outlines how to report vulnerabilities and what you can expect from us.</p>
                    
                    <h4 class="text-primary mb-3">1. Introduction</h4>
                    <p class="mb-4">We believe that coordinated vulnerability disclosure is the most effective approach to address security issues. We encourage security researchers to report potential vulnerabilities to us directly, allowing us sufficient time to investigate, address, and patch the issue before public disclosure.</p>
                    
                    <h4 class="text-primary mb-3">2. Reporting Guidelines</h4>
                    <p class="mb-4">If you believe you've found a security vulnerability in our services, we encourage you to:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">Email your findings to <a href="mailto:security@wavehost.com" class="text-primary">security@wavehost.com</a></li>
                        <li class="mb-2">Provide sufficient information to reproduce the vulnerability</li>
                        <li class="mb-2">Include your contact information for follow-up questions</li>
                        <li class="mb-2">Report the vulnerability as soon as possible after discovery</li>
                    </ul>
                    
                    <div class="alert alert-primary mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-shield-alt fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading">PGP Encryption</h5>
                                <p class="mb-0">For sensitive reports, you can encrypt your message using our PGP key, which is available on our <a href="/security" class="alert-link">security page</a>.</p>
                            </div>
                        </div>
                    </div>
                    
                    <h4 class="text-primary mb-3">3. Information to Include</h4>
                    <p class="mb-4">To help us understand and address the issue efficiently, please include:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">A description of the vulnerability and potential impact</li>
                        <li class="mb-2">Step-by-step instructions to reproduce the issue</li>
                        <li class="mb-2">Affected URLs, parameters, and/or services</li>
                        <li class="mb-2">Any proof-of-concept code or screenshots</li>
                        <li class="mb-2">Your assessment of the severity and possible mitigations</li>
                    </ul>
                    
                    <h4 class="text-primary mb-3">4. Our Commitment</h4>
                    <p class="mb-4">When you submit a vulnerability report, we will:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">Acknowledge receipt of your report within 48 hours</li>
                        <li class="mb-2">Provide an initial assessment of the report within 5 business days</li>
                        <li class="mb-2">Keep you informed about our progress in resolving the issue</li>
                        <li class="mb-2">Work with you to understand and validate the issue</li>
                        <li class="mb-2">Take appropriate steps to address the vulnerability</li>
                        <li class="mb-2">Publicly acknowledge your contribution (with your permission)</li>
                    </ul>
                    
                    <h4 class="text-primary mb-3">5. Rules of Engagement</h4>
                    <p class="mb-4">While researching potential security issues, we ask that you:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">Do not access, modify, or delete data that does not belong to you</li>
                        <li class="mb-2">Do not attempt denial of service attacks</li>
                        <li class="mb-2">Do not impact other users or disrupt our services</li>
                        <li class="mb-2">Do not exploit vulnerabilities beyond what is necessary to demonstrate the issue</li>
                        <li class="mb-2">Do not share information about vulnerabilities with others until they've been resolved</li>
                        <li class="mb-2">Act in good faith and ethically</li>
                    </ul>
                    
                    <h4 class="text-primary mb-3">6. Scope</h4>
                    <p class="mb-4">This policy applies to the following WaveHost systems and services:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">WaveHost website (wavehost.com and its subdomains)</li>
                        <li class="mb-2">Customer dashboard and control panel</li>
                        <li class="mb-2">WaveHost API services</li>
                        <li class="mb-2">Billing and payment systems</li>
                        <li class="mb-2">Management infrastructure for our hosting services</li>
                    </ul>
                    
                    <div class="alert alert-warning mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading">Out of Scope</h5>
                                <p class="mb-0">Customer applications and content hosted on our infrastructure are not in scope unless they directly impact the security of our systems. Please contact the application owner for vulnerabilities in customer applications.</p>
                            </div>
                        </div>
                    </div>
                    
                    <h4 class="text-primary mb-3">7. Legal Protection</h4>
                    <p class="mb-4">We value security research conducted under this policy and will not pursue legal action against individuals who:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">Make a good faith effort to comply with this policy</li>
                        <li class="mb-2">Avoid intentional harm to us or our customers</li>
                        <li class="mb-2">Work with us to resolve vulnerabilities before public disclosure</li>
                    </ul>
                    
                    <h4 class="text-primary mb-3">8. Acknowledgment</h4>
                    <p>With your permission, we'd like to acknowledge your contribution to our security. We may include your name or handle in a security acknowledgments page, security advisories, or blog posts related to the vulnerability you discovered.</p>
                </div>
            </div>
            
            <p class="text-muted">Last updated: April, 2025</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>