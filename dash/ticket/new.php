<?php
// dash/ticket/new.php - Create new ticket
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
    setFlashMessage('info', 'Please login to create a support ticket.');
    redirect('/auth/login');
}

// Get current user
$currentUser = getCurrentUser();

// Get user's services for dropdown
$db = db();
$services = $db->select(
    "SELECT s.id, s.name, s.service_type 
     FROM services s 
     WHERE s.user_id = ? AND s.status != 'terminated' AND s.status != 'cancelled'
     ORDER BY s.created_at DESC",
    [$currentUser['id']]
);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $serviceId = $_POST['service_id'] ?? null;
    $priority = $_POST['priority'] ?? 'medium';
    
    // Validate form inputs
    $errors = [];
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    if (empty($errors)) {
        // Create new ticket
        $ticketId = $db->insert('tickets', [
            'user_id' => $currentUser['id'],
            'service_id' => $serviceId ?: null,
            'subject' => $subject,
            'status' => 'open',
            'priority' => $priority,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Add ticket message
        $db->insert('ticket_replies', [
            'ticket_id' => $ticketId,
            'user_id' => $currentUser['id'],
            'staff_id' => null,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Log activity
        logActivity($currentUser['id'], 'create_ticket', ['ticket_id' => $ticketId]);
        
        // Upload file attachments if present
        if (!empty($_FILES['attachments']['name'][0])) {
            $uploadDir = __DIR__ . '/../../uploads/tickets/' . $ticketId . '/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Process each attachment
            $fileCount = count($_FILES['attachments']['name']);
            
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
                    $fileName = $_FILES['attachments']['name'][$i];
                    $tempFile = $_FILES['attachments']['tmp_name'][$i];
                    $fileSize = $_FILES['attachments']['size'][$i];
                    $fileType = $_FILES['attachments']['type'][$i];
                    
                    // Generate unique filename
                    $uniqueFileName = uniqid() . '_' . $fileName;
                    
                    // Move file to uploads directory
                    if (move_uploaded_file($tempFile, $uploadDir . $uniqueFileName)) {
                        // Save file info to database
                        $db->insert('ticket_attachments', [
                            'ticket_id' => $ticketId,
                            'reply_id' => null,
                            'filename' => $fileName,
                            'filepath' => '/uploads/tickets/' . $ticketId . '/' . $uniqueFileName,
                            'filesize' => $fileSize,
                            'filetype' => $fileType,
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        }
        
        // Success message
        setFlashMessage('success', 'Support ticket created successfully.');
        
        // Redirect to view ticket
        redirect('/dash/ticket/view/' . $ticketId);
    }
}

// Set page title
$pageTitle = 'Create New Ticket';

// Include header
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/loader.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-0">Create New Ticket</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dash" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/dash/tickets" class="text-decoration-none">Tickets</a></li>
                    <li class="breadcrumb-item active" aria-current="page">New Ticket</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="/dash/tickets" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i> Back to Tickets
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- New Ticket Form -->
            <div class="card bg-dark mb-4">
                <div class="card-header bg-darker">
                    <h4 class="card-title mb-0">New Support Ticket</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-dark border-secondary text-white" id="subject" name="subject" value="<?php echo $_POST['subject'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="service_id" class="form-label">Related Service</label>
                            <select class="form-select bg-dark border-secondary text-white" id="service_id" name="service_id">
                                <option value="">-- None --</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['id']; ?>" <?php echo (isset($_POST['service_id']) && $_POST['service_id'] == $service['id']) ? 'selected' : ''; ?>>
                                        <?php echo $service['name']; ?> (<?php echo getServiceTypeLabel($service['service_type']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted">Select a service if this ticket is related to a specific service.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select bg-dark border-secondary text-white" id="priority" name="priority">
                                <option value="low" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'low') ? 'selected' : ''; ?>>Low</option>
                                <option value="medium" <?php echo (!isset($_POST['priority']) || $_POST['priority'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                                <option value="high" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'high') ? 'selected' : ''; ?>>High</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control bg-dark border-secondary text-white" id="message" name="message" rows="8" required><?php echo $_POST['message'] ?? ''; ?></textarea>
                            <div class="form-text text-muted">Please provide as much detail as possible so we can best assist you.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="attachments" class="form-label">Attachments</label>
                            <input class="form-control bg-dark border-secondary text-white" type="file" id="attachments" name="attachments[]" multiple>
                            <div class="form-text text-muted">
                                Max 5 files. Allowed file types: .jpg, .jpeg, .png, .gif, .pdf, .doc, .docx, .txt<br>
                                Maximum file size: 5MB per file
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Submit Ticket</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Help & Tips -->
            <div class="card bg-dark mb-4">
                <div class="card-header bg-darker">
                    <h5 class="card-title mb-0">Tips for Faster Support</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Be specific</strong> - Provide as much detail as possible about your issue
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Include screenshots</strong> - If applicable, include screenshots showing the issue
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Check knowledge base</strong> - Your question might already be answered in our <a href="/support/kb" class="text-primary">knowledge base</a>
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Use proper priority</strong> - Only mark tickets as high priority for urgent issues
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Support Hours -->
            <div class="card bg-dark mb-4">
                <div class="card-header bg-darker">
                    <h5 class="card-title mb-0">Support Hours</h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">Our support team is available during the following hours:</p>
                    
                    <div class="mb-2 d-flex justify-content-between">
                        <div><strong>Monday - Friday:</strong></div>
                        <div>9:00 AM - 8:00 PM CET</div>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <div><strong>Saturday:</strong></div>
                        <div>10:00 AM - 6:00 PM CET</div>
                    </div>
                    <div class="mb-3 d-flex justify-content-between">
                        <div><strong>Sunday:</strong></div>
                        <div>Closed</div>
                    </div>
                    
                    <p class="mb-0">Emergency support for critical issues is available 24/7 for customers with active services.</p>
                </div>
            </div>
            
            <!-- Expected Response Time -->
            <div class="card bg-dark">
                <div class="card-header bg-darker">
                    <h5 class="card-title mb-0">Response Time</h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">Expected first response time based on priority:</p>
                    
                    <div class="mb-2 d-flex justify-content-between">
                        <div><span class="badge bg-danger me-2">High</span> <strong>Priority:</strong></div>
                        <div>1-2 Hours</div>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <div><span class="badge bg-warning text-dark me-2">Medium</span> <strong>Priority:</strong></div>
                        <div>4-8 Hours</div>
                    </div>
                    <div class="mb-3 d-flex justify-content-between">
                        <div><span class="badge bg-success me-2">Low</span> <strong>Priority:</strong></div>
                        <div>12-24 Hours</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Helper function for getting service type label
function getServiceTypeLabel(type) {
    switch (type) {
        case 'game_server':
            return 'Game Server';
        case 'web_hosting':
            return 'Web Hosting';
        case 'vps':
            return 'Virtual Private Server';
        default:
            return type.charAt(0).toUpperCase() + type.slice(1);
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>