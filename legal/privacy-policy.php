<?php
// legal/privacy-policy.php - Privacy Policy Page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Set page title
$pageTitle = 'Privacy Policy';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php require_once __DIR__ . '/../includes/loader.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-3 mb-4 mb-lg-0">
            <div class="card bg-dark sticky-top" style="top: 100px;">
                <div class="card-header bg-darker">
                    <h5 class="card-title mb-0">Legal Documents</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush bg-transparent">
                        <a href="/legal/tos" class="list-group-item list-group-item-action bg-dark">Terms of Service</a>
                        <a href="/legal/privacy-policy" class="list-group-item list-group-item-action bg-dark active">Privacy Policy</a>
                        <a href="/legal/fair-use-policy" class="list-group-item list-group-item-action bg-dark">Fair Use Policy</a>
                        <a href="/legal/responsible-disclosure" class="list-group-item list-group-item-action bg-dark">Responsible Disclosure</a>
                        <a href="/legal/service-level-agreement" class="list-group-item list-group-item-action bg-dark">Service Level Agreement</a>
                        <a href="/legal/report-abuse" class="list-group-item list-group-item-action bg-dark">Report Abuse</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <h1 class="fw-bold mb-4">Privacy Policy</h1>
            <p class="text-muted mb-4">Last updated: April 15, 2025</p>
            
            <div class="card bg-dark mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">Table of Contents</h5>
                    <ol class="mb-0">
                        <li><a href="#introduction" class="text-primary">Introduction</a></li>
                        <li><a href="#information-we-collect" class="text-primary">Information We Collect</a></li>
                        <li><a href="#how-we-use" class="text-primary">How We Use Your Information</a></li>
                        <li><a href="#information-sharing" class="text-primary">Information Sharing and Disclosure</a></li>
                        <li><a href="#data-retention" class="text-primary">Data Retention</a></li>
                        <li><a href="#security" class="text-primary">Security</a></li>
                        <li><a href="#your-rights" class="text-primary">Your Rights</a></li>
                        <li><a href="#international-transfers" class="text-primary">International Data Transfers</a></li>
                        <li><a href="#children" class="text-primary">Children's Privacy</a></li>
                        <li><a href="#changes" class="text-primary">Changes to This Privacy Policy</a></li>
                        <li><a href="#contact" class="text-primary">Contact Us</a></li>
                    </ol>
                </div>
            </div>
            
            <section id="introduction" class="mb-5">
                <h2 class="mb-3">1. Introduction</h2>
                <p>WaveHost B.V. ("WaveHost," "we," "us," or "our") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website or use our services.</p>
                <p>We recognize the importance of protecting your personal information and are committed to processing it responsibly and in compliance with applicable data protection laws, including the General Data Protection Regulation (GDPR) and the California Consumer Privacy Act (CCPA).</p>
                <p>Please read this Privacy Policy carefully. If you do not agree with the terms of this Privacy Policy, please do not access our website or use our services.</p>
            </section>
            
            <section id="information-we-collect" class="mb-5">
                <h2 class="mb-3">2. Information We Collect</h2>
                
                <h4 class="mb-3">2.1 Personal Information</h4>
                <p>We may collect personal information that you provide directly to us, including:</p>
                <ul>
                    <li><strong>Account Information:</strong> When you create an account, we collect your name, email address, phone number, billing address, and payment information.</li>
                    <li><strong>Profile Information:</strong> When you create a profile, we collect information such as your username, password, and profile picture.</li>
                    <li><strong>Communication Information:</strong> When you contact us, we collect information such as your name, email address, phone number, and the content of your message.</li>
                    <li><strong>Service Usage Information:</strong> When you use our services, we collect information about the specific services you use, such as hosting plans, domain registrations, and other products.</li>
                </ul>
                
                <h4 class="mb-3">2.2 Automatically Collected Information</h4>
                <p>When you visit our website or use our services, certain information is automatically collected, including:</p>
                <ul>
                    <li><strong>Device Information:</strong> We may collect information about your device, including the hardware model, operating system and version, browser type, unique device identifiers, and mobile network information.</li>
                    <li><strong>Log Information:</strong> We collect log information when you use our services, including access times, pages viewed, IP address, and the page you visited before navigating to our website.</li>
                    <li><strong>Location Information:</strong> We may collect information about your precise or approximate location as determined through data such as your IP address.</li>
                    <li><strong>Cookie Information:</strong> We use cookies and similar tracking technologies to track activity on our website and hold certain information. Cookies are files with a small amount of data that may include an anonymous unique identifier. For more information about our use of cookies, please see our Cookie Policy.</li>
                </ul>
            </section>
            
            <section id="how-we-use" class="mb-5">
                <h2 class="mb-3">3. How We Use Your Information</h2>
                <p>We use the information we collect for various purposes, including:</p>
                <ul>
                    <li>To provide, maintain, and improve our services;</li>
                    <li>To process and complete transactions, and send related information, including confirmations;</li>
                    <li>To send technical notices, updates, security alerts, and support and administrative messages;</li>
                    <li>To respond to your comments, questions, and requests, and provide customer service;</li>
                    <li>To monitor and analyze trends, usage, and activities in connection with our services;</li>
                    <li>To detect, investigate, and prevent fraudulent transactions and other illegal activities;</li>
                    <li>To personalize and improve your experience on our website;</li>
                    <li>To send promotional communications, such as providing you with information about products, services, and events;</li>
                    <li>To comply with legal obligations;</li>
                    <li>For any other purpose we may describe when you provide the information.</li>
                </ul>
            </section>
            
            <section id="information-sharing" class="mb-5">
                <h2 class="mb-3">4. Information Sharing and Disclosure</h2>
                <p>We may share your information in the following circumstances:</p>
                <ul>
                    <li><strong>Service Providers:</strong> We may share your information with third-party service providers who perform services on our behalf, such as payment processing, data analysis, email delivery, hosting services, and customer service.</li>
                    <li><strong>Business Transfers:</strong> If we are involved in a merger, acquisition, or sale of all or a portion of our assets, your information may be transferred as part of that transaction.</li>
                    <li><strong>Legal Requirements:</strong> We may disclose your information if we believe that such action is necessary to (a) comply with the law or legal process; (b) protect and defend our rights or property; (c) prevent fraud; (d) act in urgent circumstances to protect the personal safety of users of our services or the public; or (e) protect against legal liability.</li>
                    <li><strong>With Your Consent:</strong> We may share your information with third parties when you have given us your consent to do so.</li>
                </ul>
                <p>We do not sell, rent, or otherwise provide your personal information to third parties for marketing purposes without your consent.</p>
            </section>
            
            <section id="data-retention" class="mb-5">
                <h2 class="mb-3">5. Data Retention</h2>
                <p>We retain personal information for as long as necessary to fulfill the purposes for which it was collected, including for the purposes of satisfying any legal, accounting, or reporting requirements, or to provide our services.</p>
                <p>To determine the appropriate retention period for personal information, we consider the amount, nature, and sensitivity of the personal information, the potential risk of harm from unauthorized use or disclosure of your personal information, the purposes for which we process your personal information, and whether we can achieve those purposes through other means, and the applicable legal requirements.</p>
                <p>When your account is terminated, we will delete or anonymize your personal information, unless we are legally required to retain it. We may retain some information for a limited period to comply with legal obligations, resolve disputes, enforce our agreements, or for similar business purposes.</p>
            </section>
            
            <section id="security" class="mb-5">
                <h2 class="mb-3">6. Security</h2>
                <p>We take reasonable measures to help protect your personal information from loss, theft, misuse, unauthorized access, disclosure, alteration, and destruction. However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.</p>
                <p>We implement various security measures, including:</p>
                <ul>
                    <li>Encryption of sensitive information;</li>
                    <li>Regular security assessments and audits;</li>
                    <li>Access controls and authentication mechanisms;</li>
                    <li>Secure network architecture and intrusion detection systems;</li>
                    <li>Employee training on data protection and security practices.</li>
                </ul>
                <p>You are responsible for maintaining the confidentiality of your account credentials and for restricting access to your computer or device. If you believe your account has been compromised, please contact us immediately.</p>
            </section>
            
            <section id="your-rights" class="mb-5">
                <h2 class="mb-3">7. Your Rights</h2>
                <p>Depending on your location, you may have certain rights regarding your personal information. These may include:</p>
                <ul>
                    <li><strong>Access:</strong> You have the right to request a copy of the personal information we hold about you.</li>
                    <li><strong>Rectification:</strong> You have the right to request correction of any inaccurate or incomplete personal information we hold about you.</li>
                    <li><strong>Erasure:</strong> You have the right to request that we delete your personal information in certain circumstances.</li>
                    <li><strong>Restriction:</strong> You have the right to request that we restrict the processing of your personal information in certain circumstances.</li>
                    <li><strong>Data Portability:</strong> You have the right to receive a copy of your personal information in a structured, commonly used, and machine-readable format.</li>
                    <li><strong>Objection:</strong> You have the right to object to our processing of your personal information in certain circumstances.</li>
                    <li><strong>Withdraw Consent:</strong> If we rely on your consent to process your personal information, you have the right to withdraw that consent at any time.</li>
                </ul>
                <p>To exercise these rights, please contact us using the information provided in the "Contact Us" section below. We will respond to your request within the timeframe required by applicable law.</p>
                <p>Please note that we may need to verify your identity before responding to your request. In some cases, we may be unable to fulfill your request, for example, if it would impact the privacy rights of others, if it would result in a breach of confidentiality, or if it is legally prohibited.</p>
            </section>
            
            <section id="international-transfers" class="mb-5">
                <h2 class="mb-3">8. International Data Transfers</h2>
                <p>We are based in the Netherlands and we process and store information on servers located in the European Union. However, we may transfer, process, and store information about you on servers located in other countries, where our service providers are located or have servers.</p>
                <p>When we transfer personal information outside of the European Economic Area (EEA), we implement appropriate safeguards to ensure your information is protected according to this Privacy Policy and applicable laws. These safeguards may include:</p>
                <ul>
                    <li>European Commission-approved standard contractual clauses;</li>
                    <li>Binding corporate rules;</li>
                    <li>Adherence to the EU-U.S. and Swiss-U.S. Privacy Shield Frameworks;</li>
                    <li>Explicit consent for specific transfers.</li>
                </ul>
                <p>By using our services, you understand that your information may be transferred to our facilities and those third parties with whom we share it as described in this Privacy Policy.</p>
            </section>
            
            <section id="children" class="mb-5">
                <h2 class="mb-3">9. Children's Privacy</h2>
                <p>Our services are not directed to children under the age of 16, and we do not knowingly collect personal information from children under 16. If you are a parent or guardian and you believe that your child has provided us with personal information, please contact us. If we become aware that we have collected personal information from a child under 16 without verification of parental consent, we will take steps to remove that information from our servers.</p>
            </section>
            
            <section id="changes" class="mb-5">
                <h2 class="mb-3">10. Changes to This Privacy Policy</h2>
                <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date. You are advised to review this Privacy Policy periodically for any changes.</p>
                <p>Changes to this Privacy Policy are effective when they are posted on this page. If we make material changes to how we treat your personal information, we will notify you through a notice on the home page of our website or via the email address you have provided to us.</p>
                <p>Your continued use of our services after the effective date of any updated Privacy Policy constitutes your acceptance of the updated Privacy Policy.</p>
            </section>
            
            <section id="contact" class="mb-5">
                <h2 class="mb-3">11. Contact Us</h2>
                <p>If you have any questions about this Privacy Policy or our data practices, please contact us at:</p>
                <address>
                    WaveHost B.V.<br>
                    Herengracht 182<br>
                    1016 BR Amsterdam<br>
                    The Netherlands<br><br>
                    Data Protection Officer<br>
                    Email: <a href="mailto:privacy@wavehost.com" class="text-primary">privacy@wavehost.com</a><br>
                    Phone: +31 20 123 4567
                </address>
                <p>If you have an unresolved privacy or data use concern that we have not addressed satisfactorily, please contact our third-party dispute resolution provider (free of charge) at <a href="https://feedback-form.truste.com/watchdog/request" class="text-primary" target="_blank">https://feedback-form.truste.com/watchdog/request</a>.</p>
            </section>
            
            <div class="alert alert-info">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="fas fa-info-circle fa-2x"></i>
                    </div>
                    <div>
                        <p class="mb-0">This document was last updated on April 15, 2025. If you require a previous version of our Privacy Policy, please contact our Data Protection Officer.</p>
                    </div>
                </div>
            </div>
            
            <div class="card bg-dark mt-5">
                <div class="card-body p-4">
                    <h4 class="mb-3">Cookie Preferences</h4>
                    <p>You can adjust your cookie preferences at any time by clicking the button below. This will allow you to choose which types of cookies you accept or reject.</p>
                    <button type="button" class="btn btn-primary" id="cookie-preferences-btn">Manage Cookie Preferences</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cookiePreferencesBtn = document.getElementById('cookie-preferences-btn');
    
    cookiePreferencesBtn.addEventListener('click', function() {
        // In a real implementation, this would open a cookie preferences modal
        alert('Cookie preferences functionality would be implemented here. For demo purposes, this is just a placeholder.');
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>