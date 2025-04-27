<?php
// about/careers.php - Careers Page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Get open positions from database
$db = db();
$openPositions = $db->select(
    "SELECT * FROM job_positions WHERE is_active = 1 ORDER BY department, title"
);

// Group positions by department
$departments = [];
foreach ($openPositions as $position) {
    if (!isset($departments[$position['department']])) {
        $departments[$position['department']] = [];
    }
    
    $departments[$position['department']][] = $position;
}

// Process job application form
$formSubmitted = false;
$formError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_form'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Basic validation
    if (empty($name) || empty($email) || empty($position) || empty($message)) {
        $formError = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = 'Please enter a valid email address.';
    } else {
        // In a real application, send an email or store the application
        // For now, just simulate success
        $formSubmitted = true;
    }
}

// Set page title
$pageTitle = 'Careers';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php require_once __DIR__ . '/../includes/loader.php'; ?>

<div class="container py-5">
    <h1 class="fw-bold mb-2">Join Our Team</h1>
    <p class="lead mb-5">Discover exciting career opportunities at WaveHost.</p>
    
    <!-- About Working at WaveHost -->
    <div class="row mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <h2 class="mb-4">Why Work With Us?</h2>
            
            <div class="mb-4">
                <h4 class="mb-3">Innovate and Grow</h4>
                <p>At WaveHost, we're at the forefront of hosting technology. Join us to work on cutting-edge solutions and grow your skills in a supportive environment.</p>
            </div>
            
            <div class="mb-4">
                <h4 class="mb-3">Make an Impact</h4>
                <p>Our team members make a real difference in the digital world, helping thousands of websites and applications stay online and secure.</p>
            </div>
            
            <div class="mb-4">
                <h4 class="mb-3">Work-Life Balance</h4>
                <p>We believe in flexible work arrangements, competitive compensation, and a healthy work-life balance for all our team members.</p>
            </div>
            
            <div>
                <h4 class="mb-3">Inclusive Culture</h4>
                <p>We foster a diverse and inclusive work environment where everyone's voice is heard and valued.</p>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card bg-dark">
                <div class="card-body p-4">
                    <h4 class="mb-4">Benefits & Perks</h4>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-primary-subtle text-primary rounded-circle me-3">
                                    <i class="fas fa-laptop-code"></i>
                                </div>
                                <div>Remote-First Workplace</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-info-subtle text-info rounded-circle me-3">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <div>Competitive Salary</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-success-subtle text-success rounded-circle me-3">
                                    <i class="fas fa-heartbeat"></i>
                                </div>
                                <div>Health Insurance</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-warning-subtle text-warning rounded-circle me-3">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>Flexible Hours</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-danger-subtle text-danger rounded-circle me-3">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div>Learning Budget</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-secondary-subtle text-secondary rounded-circle me-3">
                                    <i class="fas fa-plane"></i>
                                </div>
                                <div>Paid Time Off</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-primary-subtle text-primary rounded-circle me-3">
                                    <i class="fas fa-desktop"></i>
                                </div>
                                <div>Equipment Allowance</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-info-subtle text-info rounded-circle me-3">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>Team Retreats</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Open Positions -->
    <div id="open-positions" class="mb-5">
        <h2 class="mb-4">Open Positions</h2>
        
        <?php if (empty($departments)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> We don't have any open positions at the moment. Please check back later or send us your resume for future opportunities.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($departments as $department => $positions): ?>
                    <div class="col-12 mb-4">
                        <div class="card bg-dark">
                            <div class="card-header bg-darker">
                                <h3 class="h5 mb-0"><?php echo $department; ?></h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush bg-transparent">
                                    <?php foreach ($positions as $position): ?>
                                        <div class="list-group-item bg-transparent border-bottom border-secondary p-4">
                                            <div class="row align-items-center">
                                                <div class="col-md-6 mb-3 mb-md-0">
                                                    <h4 class="h6 mb-1"><?php echo $position['title']; ?></h4>
                                                    <div class="d-flex align-items-center text-muted">
                                                        <div class="me-3">
                                                            <i class="fas fa-map-marker-alt me-1"></i> 
                                                            <?php echo $position['location']; ?>
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-clock me-1"></i> 
                                                            <?php echo $position['employment_type']; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 d-flex justify-content-md-end">
                                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#job-modal-<?php echo $position['id']; ?>">
                                                        View Details
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Job Details Modal -->
                                        <div class="modal fade" id="job-modal-<?php echo $position['id']; ?>" tabindex="-1" aria-labelledby="job-modal-label-<?php echo $position['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content bg-dark">
                                                    <div class="modal-header bg-darker border-secondary">
                                                        <h5 class="modal-title" id="job-modal-label-<?php echo $position['id']; ?>"><?php echo $position['title']; ?></h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-4">
                                                            <div class="d-flex align-items-center text-muted mb-3">
                                                                <div class="me-3">
                                                                    <i class="fas fa-map-marker-alt me-1"></i> 
                                                                    <?php echo $position['location']; ?>
                                                                </div>
                                                                <div class="me-3">
                                                                    <i class="fas fa-clock me-1"></i> 
                                                                    <?php echo $position['employment_type']; ?>
                                                                </div>
                                                                <div>
                                                                    <i class="fas fa-building me-1"></i> 
                                                                    <?php echo $position['department']; ?>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mb-4">
                                                                <h6 class="mb-2">About the Role:</h6>
                                                                <p><?php echo $position['description']; ?></p>
                                                            </div>
                                                            
                                                            <div class="mb-4">
                                                                <h6 class="mb-2">Requirements:</h6>
                                                                <?php echo $position['requirements']; ?>
                                                            </div>
                                                            
                                                            <div>
                                                                <h6 class="mb-2">Responsibilities:</h6>
                                                                <?php echo $position['responsibilities']; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-secondary">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <a href="#apply-form" class="btn btn-primary" data-bs-dismiss="modal" onclick="document.getElementById('position-input').value = '<?php echo addslashes($position['title']); ?>';">Apply Now</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Application Form -->
    <div id="apply-form" class="mb-5">
        <h2 class="mb-4">Apply Now</h2>
        
        <?php if ($formSubmitted): ?>
            <div class="alert alert-success">
                <h4 class="alert-heading">Application Received!</h4>
                <p>Thank you for your interest in joining WaveHost. We have received your application and will contact you if your qualifications match our needs.</p>
            </div>
        <?php else: ?>
            <?php if ($formError): ?>
                <div class="alert alert-danger">
                    <?php echo $formError; ?>
                </div>
            <?php endif; ?>
            
            <div class="card bg-dark">
                <div class="card-body p-4">
                    <form method="post" action="#apply-form" enctype="multipart/form-data">
                        <input type="hidden" name="apply_form" value="1">
                        
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-dark border-secondary text-white" id="name" name="name" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control bg-dark border-secondary text-white" id="email" name="email" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="position-input" class="form-label">Position <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-dark border-secondary text-white" id="position-input" name="position" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="resume" class="form-label">Resume/CV <span class="text-danger">*</span></label>
                                <input type="file" class="form-control bg-dark border-secondary text-white" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                                <div class="form-text">Allowed formats: PDF, DOC, DOCX</div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="message" class="form-label">Cover Letter <span class="text-danger">*</span></label>
                                <textarea class="form-control bg-dark border-secondary text-white" id="message" name="message" rows="6" required></textarea>
                                <div class="form-text">Tell us about yourself and why you're interested in this position.</div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="privacy-check" required>
                                    <label class="form-check-label" for="privacy-check">
                                        I agree to the processing of my personal data in accordance with the <a href="/legal/privacy-policy">Privacy Policy</a>.
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Submit Application</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Company Culture -->
    <div class="row mb-5">
        <div class="col-12 mb-4">
            <h2 class="mb-4">Our Culture</h2>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-dark h-100">
                <div class="card-body p-4 text-center">
                    <div class="icon-box bg-primary-subtle text-primary rounded-circle mx-auto mb-4">
                        <i class="fas fa-lightbulb fa-lg"></i>
                    </div>
                    <h4 class="mb-3">Innovation</h4>
                    <p class="text-muted mb-0">We embrace new ideas and technologies to continuously improve our services.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-dark h-100">
                <div class="card-body p-4 text-center">
                    <div class="icon-box bg-info-subtle text-info rounded-circle mx-auto mb-4">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                    <h4 class="mb-3">Collaboration</h4>
                    <p class="text-muted mb-0">We work together across teams to achieve common goals and deliver exceptional results.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-dark h-100">
                <div class="card-body p-4 text-center">
                    <div class="icon-box bg-success-subtle text-success rounded-circle mx-auto mb-4">
                        <i class="fas fa-shield-alt fa-lg"></i>
                    </div>
                    <h4 class="mb-3">Reliability</h4>
                    <p class="text-muted mb-0">We're committed to providing reliable services and being dependable teammates.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- FAQ Section -->
    <div class="mb-5">
        <h2 class="mb-4">Frequently Asked Questions</h2>
        
        <div class="accordion" id="careers-faq">
            <div class="accordion-item bg-dark border-secondary">
                <h2 class="accordion-header" id="faq-heading-1">
                    <button class="accordion-button collapsed bg-darker text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-1" aria-expanded="false" aria-controls="faq-collapse-1">
                        What is the hiring process like?
                    </button>
                </h2>
                <div id="faq-collapse-1" class="accordion-collapse collapse" aria-labelledby="faq-heading-1" data-bs-parent="#careers-faq">
                    <div class="accordion-body">
                        <p>Our hiring process typically includes the following steps:</p>
                        <ol>
                            <li>Initial application review</li>
                            <li>First interview (remote video call)</li>
                            <li>Technical assessment or case study (if applicable)</li>
                            <li>Final interview with team members</li>
                            <li>Offer and onboarding</li>
                        </ol>
                        <p>The entire process usually takes 2-3 weeks, and we strive to keep candidates informed at every stage.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item bg-dark border-secondary">
                <h2 class="accordion-header" id="faq-heading-2">
                    <button class="accordion-button collapsed bg-darker text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-2" aria-expanded="false" aria-controls="faq-collapse-2">
                        Do you offer remote work options?
                    </button>
                </h2>
                <div id="faq-collapse-2" class="accordion-collapse collapse" aria-labelledby="faq-heading-2" data-bs-parent="#careers-faq">
                    <div class="accordion-body">
                        <p>Yes, WaveHost is a remote-first company. Most of our positions are fully remote, allowing you to work from anywhere in the world (with some time zone considerations). Some roles may require occasional visits to our offices for team meetings or special events.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item bg-dark border-secondary">
                <h2 class="accordion-header" id="faq-heading-3">
                    <button class="accordion-button collapsed bg-darker text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-3" aria-expanded="false" aria-controls="faq-collapse-3">
                        What's the onboarding process like?
                    </button>
                </h2>
                <div id="faq-collapse-3" class="accordion-collapse collapse" aria-labelledby="faq-heading-3" data-bs-parent="#careers-faq">
                    <div class="accordion-body">
                        <p>We have a structured onboarding program to help new team members get up to speed quickly. This includes:</p>
                        <ul>
                            <li>Welcome package with equipment and company swag</li>
                            <li>Orientation sessions with different teams</li>
                            <li>Assigned mentor for your first few months</li>
                            <li>Regular check-ins with your manager</li>
                            <li>Access to training resources and documentation</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item bg-dark border-secondary">
                <h2 class="accordion-header" id="faq-heading-4">
                    <button class="accordion-button collapsed bg-darker text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-4" aria-expanded="false" aria-controls="faq-collapse-4">
                        What opportunities are there for growth and advancement?
                    </button>
                </h2>
                <div id="faq-collapse-4" class="accordion-collapse collapse" aria-labelledby="faq-heading-4" data-bs-parent="#careers-faq">
                    <div class="accordion-body">
                        <p>We're committed to helping our team members grow professionally. We offer:</p>
                        <ul>
                            <li>Regular performance reviews with clear paths for advancement</li>
                            <li>Learning and development budget for courses, certifications, and conferences</li>
                            <li>Internal mobility opportunities across teams and departments</li>
                            <li>Mentorship programs and knowledge sharing sessions</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item bg-dark border-secondary">
                <h2 class="accordion-header" id="faq-heading-5">
                    <button class="accordion-button collapsed bg-darker text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-5" aria-expanded="false" aria-controls="faq-collapse-5">
                        I don't see a position that matches my skills. Can I still apply?
                    </button>
                </h2>
                <div id="faq-collapse-5" class="accordion-collapse collapse" aria-labelledby="faq-heading-5" data-bs-parent="#careers-faq">
                    <div class="accordion-body">
                        <p>Absolutely! We're always looking for talented individuals. If you don't see a position that matches your skill set but are passionate about web hosting, cloud infrastructure, or customer support, we encourage you to submit a general application. We'll keep your resume on file for future opportunities that match your profile.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Team Testimonials -->
    <div>
        <h2 class="mb-4">What Our Team Says</h2>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card bg-dark h-100">
                    <div class="card-body p-4">
                        <div class="d-flex mb-4">
                            <img src="/assets/images/team/alex.jpg" alt="Alex" class="rounded-circle me-3" width="64" height="64">
                            <div>
                                <h5 class="mb-1">Alex Morgan</h5>
                                <p class="text-muted mb-0">Senior DevOps Engineer</p>
                            </div>
                        </div>
                        <p class="mb-0">"Working at WaveHost has been a game-changer for my career. I've been able to work with cutting-edge technologies while maintaining a healthy work-life balance. The team is supportive, and there's always something new to learn."</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card bg-dark h-100">
                    <div class="card-body p-4">
                        <div class="d-flex mb-4">
                            <img src="/assets/images/team/sarah.jpg" alt="Sarah" class="rounded-circle me-3" width="64" height="64">
                            <div>
                                <h5 class="mb-1">Sarah Chen</h5>
                                <p class="text-muted mb-0">Customer Success Manager</p>
                            </div>
                        </div>
                        <p class="mb-0">"The best thing about WaveHost is the culture. Everyone is passionate about what they do, and we truly care about our customers' success. I've grown so much professionally in my time here, and I appreciate the emphasis on continuous learning."</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>