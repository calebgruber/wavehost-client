<?php
// legal/report-abuse.php - Report Abuse Page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Get current user if logged in
$currentUser = null;
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
}

// Process form submission
$formSubmitted = false;
$formErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $name = clean($_POST['name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $abuseType = clean($_POST['abuse_type'] ?? '');
    $url = clean($_POST['url'] ?? '');
    $details = clean($_POST['details'] ?? '');
    $evidence = $_FILES['evidence'] ?? null;
    
    // Perform validation
    if (empty($name)) {
        $formErrors[] = 'Name is required';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formErrors[] = 'Valid email address is required';
    }
    
    if (empty($abuseType)) {
        $formErrors[] = 'Abuse type is required';
    }
    
    if (empty($url)) {
        $formErrors[] = 'URL or IP address is required';
    }
    
    if (empty($details)) {
        $formErrors[] = 'Details are required';
    }
    
    // Process if no errors
    if (empty($formErrors)) {
        $db = db();
        
        // Handle file upload if provided
        $evidenceFilePath = null;
        if ($evidence && $evidence['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/abuse_reports/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid('evidence_') . '_' . basename($evidence['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($evidence['tmp_name'], $uploadFile)) {
                $evidenceFilePath = '/uploads/abuse_reports/' . $fileName;
            }
        }
        
        // Insert into database
        $userId = $currentUser ? $currentUser['id'] : null;
        
        $db->insert('abuse_reports', [
            'user_id' => $userId,
            'name' => $name,
            'email' => $email,
            'abuse_type' => $abuseType,
            'url' => $url,
            'details' => $details,
            'evidence_file' => $evidenceFilePath,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);
        
        // Send email notification to admin (implementation not shown)
        // sendAbuseReportNotification($name, $email, $abuseType, $url);
        
        $formSubmitted = true;
    }
}

// Set page title
$pageTitle = 'Report Abuse';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="fw-bold mb-4">Report Abuse</h1>
            
            <?php if ($formSubmitted): ?>
                <div class="alert alert-success mb-4">
                    <h4 class="alert-heading"><i class="fas fa-check-circle me-2"></i> Report Submitted</h4>
                    <p>Thank you for your report. Our abuse team will investigate and take appropriate action. If needed, we'll contact you at the email address provided.</p>
                    <hr>
                    <p class="mb-0">Reference ID: <?php echo date('YmdHis'); ?></p>
                </div>>
            <?php else: ?>
                <div class="card bg-dark mb-4">
                    <div class="card-body">
                        <p class="mb-4">WaveHost is committed to maintaining a safe and lawful hosting environment. If you've encountered content or behavior that violates our Terms of Service or applicable laws, please report it using this form.</p>
                        
                        <div class="alert alert-primary mb-4">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fas fa-info-circle fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="alert-heading">Before Reporting</h5>
                                    <p class="mb-0">Please review our <a href="/legal/tos" class="alert-link">Terms of Service</a> to understand what constitutes a violation. For DMCA copyright complaints, please use our <a href="/legal/dmca" class="alert-link">DMCA form</a> instead.</p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($formErrors)): ?>
                            <div class="alert alert-danger mb-4">
                                <h5 class="alert-heading">Please correct the following errors:</h5>
                                <ul class="mb-0">
                                    <?php foreach ($formErrors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control bg-dark border-secondary text-white" id="name" name="name" value="<?php echo $currentUser ? $currentUser['first_name'] . ' ' . $currentUser['last_name'] : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Your Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control bg-dark border-secondary text-white" id="email" name="email" value="<?php echo $currentUser ? $currentUser['email'] : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="abuse_type" class="form-label">Type of Abuse <span class="text-danger">*</span></label>
                                <select class="form-select bg-dark border-secondary text-white" id="abuse_type" name="abuse_type" required>
                                    <option value="" selected disabled>Select type of abuse</option>
                                    <option value="illegal_content">Illegal Content</option>
                                    <option value="phishing">Phishing or Fraud</option>
                                    <option value="malware">Malware Distribution</option>
                                    <option value="spam">Spam/Unsolicited Email</option>
                                    <option value="ddos">DDoS or Network Attacks</option>
                                    <option value="child_abuse">Child Exploitation</option>
                                    <option value="terrorism">Terrorism/Violent Extremism</option>
                                    <option value="harassment">Harassment or Threats</option>
                                    <option value="other">Other Abuse</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="url" class="form-label">URL or IP Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-dark border-secondary text-white" id="url" name="url" placeholder="https://example.com or 192.0.2.1" required>
                                <div class="form-text">Please provide the full URL or IP address where the abuse is occurring.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="details" class="form-label">Detailed Description <span class="text-danger">*</span></label>
                                <textarea class="form-control bg-dark border-secondary text-white" id="details" name="details" rows="5" placeholder="Please describe the specific abuse in detail, including when you encountered it and how it violates our terms." required></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label for="evidence" class="form-label">Supporting Evidence</label>
                                <input class="form-control bg-dark border-secondary text-white" type="file" id="evidence" name="evidence">
                                <div class="form-text">Attach screenshots, logs, or other evidence (max 10MB, formats: jpg, png, pdf, txt, log).</div>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="confirm" name="confirm" required>
                                <label class="form-check-label" for="confirm">
                                    I confirm that the information provided is accurate and complete to the best of my knowledge. I understand that false reports may result in account termination.
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Submit Report</button>
                        </form>
                    </div>
                </div>
                
                <div class="card bg-dark">
                    <div class="card-body">
                        <h5 class="mb-3">What Happens Next?</h5>
                        <p class="mb-3">After submitting your report:</p>
                        
                        <ol>
                            <li class="mb-2">Our abuse team will review your submission (typically within 24-48 hours).</li>
                            <li class="mb-2">We may contact you for additional information if needed.</li>
                            <li class="mb-2">If a violation is confirmed, appropriate action will be taken according to our policies.</li>
                            <li class="mb-0">In cases involving illegal activity, we may report to relevant authorities.</li>
                        </ol>
                    </div>
                </div>
            <?php endif; ?>
            
            <p class="text-muted mt-4">Last updated: April, 2025</p>
        </div>
    </div