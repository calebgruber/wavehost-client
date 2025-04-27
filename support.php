<?php
// support.php - Support Page
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Get current user if logged in
$currentUser = null;
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
}

// Fetch knowledge base articles from database
$db = db();
$faqArticles = $db->select(
    "SELECT * FROM knowledge_base WHERE is_active = 1 ORDER BY category_id, title"
);

// Organize articles by category
$categories = [];
foreach ($faqArticles as $article) {
    if (!isset($categories[$article['category_id']])) {
        // Get category name
        $category = $db->selectOne(
            "SELECT * FROM knowledge_base_categories WHERE id = ?",
            [$article['category_id']]
        );
        
        $categories[$article['category_id']] = [
            'name' => $category['name'],
            'icon' => $category['icon'],
            'articles' => []
        ];
    }
    
    $categories[$article['category_id']]['articles'][] = $article;
}

// Process contact form
$formSubmitted = false;
$formError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $formError = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = 'Please enter a valid email address.';
    } else {
        // In a real application, send an email or create a ticket
        // For now, just simulate success
        $formSubmitted = true;
        
        // Create a ticket if user is logged in
        if ($currentUser) {
            $ticketId = $db->insert('tickets', [
                'user_id' => $currentUser['id'],
                'subject' => $subject,
                'status' => 'open',
                'priority' => 'medium',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $db->insert('ticket_replies', [
                'ticket_id' => $ticketId,
                'user_id' => $currentUser['id'],
                'staff_id' => null,
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Redirect to ticket view
            setFlashMessage('success', 'Your support ticket has been created successfully.');
            redirect('/dash/ticket/' . $ticketId);
        }
    }
}

// Set page title
$pageTitle = 'Support';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php require_once __DIR__ . '/includes/loader.php'; ?>

<div class="container py-5">
    <h1 class="fw-bold mb-2">Support Center</h1>
    <p class="lead mb-5">Get help with your account, services, and technical issues.</p>
    
    <!-- Support Options -->
    <div class="row mb-5">
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card bg-dark h-100">
                <div class="card-body text-center p-4">
                    <div class="icon-box bg-primary-subtle text-primary rounded-circle mx-auto mb-4">
                        <i class="fas fa-ticket-alt fa-lg"></i>
                    </div>
                    <h4 class="mb-3">Submit a Ticket</h4>
                    <p class="text-muted mb-4">Submit a support ticket for technical issues, billing questions, or account help.</p>
                    <a href="#contact-form" class="btn btn-primary">Open Ticket</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card bg-dark h-100">
                <div class="card-body text-center p-4">
                    <div class="icon-box bg-info-subtle text-info rounded-circle mx-auto mb-4">
                        <i class="fas fa-book fa-lg"></i>
                    </div>
                    <h4 class="mb-3">Knowledge Base</h4>
                    <p class="text-muted mb-4">Browse our FAQs and guides for immediate answers to common questions.</p>
                    <a href="#knowledge-base" class="btn btn-info">View Articles</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-dark h-100">
                <div class="card-body text-center p-4">
                    <div class="icon-box bg-success-subtle text-success rounded-circle mx-auto mb-4">
                        <i class="fas fa-comments fa-lg"></i>
                    </div>
                    <h4 class="mb-3">Live Chat</h4>
                    <p class="text-muted mb-4">Chat with our support team in real-time for immediate assistance.</p>
                    <button type="button" class="btn btn-success" id="start-chat-btn">Start Chat</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Knowledge Base Section -->
    <div id="knowledge-base" class="mb-5">
        <h2 class="fw-bold mb-4">Frequently Asked Questions</h2>
        
        <div class="row">
            <?php if (empty($categories)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No FAQ articles found. Please check back later.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $categoryId => $category): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card bg-dark">
                            <div class="card-header bg-darker">
                                <h4 class="card-title mb-0">
                                    <i class="fas <?php echo $category['icon']; ?> me-2 text-primary"></i>
                                    <?php echo $category['name']; ?>
                                </h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="accordion" id="faq-category-<?php echo $categoryId; ?>">
                                    <?php foreach ($category['articles'] as $index => $article): ?>
                                        <div class="accordion-item bg-dark border-secondary">
                                            <h2 class="accordion-header" id="faq-heading-<?php echo $article['id']; ?>">
                                                <button class="accordion-button collapsed bg-darker text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-<?php echo $article['id']; ?>" aria-expanded="false" aria-controls="faq-collapse-<?php echo $article['id']; ?>">
                                                    <?php echo $article['title']; ?>
                                                </button>
                                            </h2>
                                            <div id="faq-collapse-<?php echo $article['id']; ?>" class="accordion-collapse collapse" aria-labelledby="faq-heading-<?php echo $article['id']; ?>" data-bs-parent="#faq-category-<?php echo $categoryId; ?>">
                                                <div class="accordion-body">
                                                    <?php echo $article['content']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Contact Form Section -->
    <div id="contact-form" class="mb-5">
        <h2 class="fw-bold mb-4">Contact Support</h2>
        
        <?php if ($formSubmitted): ?>
            <div class="alert alert-success">
                <h4 class="alert-heading">Message Sent!</h4>
                <p>Thank you for contacting us. We have received your message and will respond to you shortly.</p>
                <?php if (!$currentUser): ?>
                    <hr>
                    <p class="mb-0">For faster support, consider <a href="/auth/login" class="alert-link">logging in</a> or <a href="/auth/register" class="alert-link">creating an account</a>.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php if ($formError): ?>
                <div class="alert alert-danger">
                    <?php echo $formError; ?>
                </div>
            <?php endif; ?>
            
            <div class="card bg-dark">
                <div class="card-body p-4">
                    <form method="post" action="#contact-form">
                        <input type="hidden" name="contact_form" value="1">
                        
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-dark border-secondary text-white" id="name" name="name" value="<?php echo $currentUser ? $currentUser['first_name'] . ' ' . $currentUser['last_name'] : ''; ?>" required <?php echo $currentUser ? 'readonly' : ''; ?>>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control bg-dark border-secondary text-white" id="email" name="email" value="<?php echo $currentUser ? $currentUser['email'] : ''; ?>" required <?php echo $currentUser ? 'readonly' : ''; ?>>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-dark border-secondary text-white" id="subject" name="subject" required>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control bg-dark border-secondary text-white" id="message" name="message" rows="6" required></textarea>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Contact Information -->
    <div class="row">
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card bg-dark h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-envelope fa-2x text-primary me-3"></i>
                        <h4 class="mb-0">Email Support</h4>
                    </div>
                    <p class="text-muted mb-0">For general inquiries and support:</p>
                    <p class="mb-0"><a href="mailto:support@wavehost.com" class="text-white">support@wavehost.com</a></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card bg-dark h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-phone-alt fa-2x text-primary me-3"></i>
                        <h4 class="mb-0">Phone Support</h4>
                    </div>
                    <p class="text-muted mb-0">Available Monday to Friday, 9am - 5pm CET:</p>
                    <p class="mb-0"><a href="tel:+31201234567" class="text-white">+31 20 123 4567</a></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-dark h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-clock fa-2x text-primary me-3"></i>
                        <h4 class="mb-0">Support Hours</h4>
                    </div>
                    <p class="text-muted mb-1">Monday - Friday: 9am - 5pm CET</p>
                    <p class="text-muted mb-0">Weekends: Emergency support only</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live Chat Widget Script (Simulated) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatButton = document.getElementById('start-chat-btn');
    
    chatButton.addEventListener('click', function() {
        // In a real implementation, this would initialize a chat widget
        alert('Chat functionality would be initialized here. For demo purposes, please use the contact form or open a ticket.');
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>