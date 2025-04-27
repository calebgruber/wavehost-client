<?php
// auth/register.php - Registration page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('/dash');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $agreeTerms = isset($_POST['agree_terms']);
    
    // Get referral code if set
    $referralCode = $_POST['referral_code'] ?? ($_GET['ref'] ?? null);
    
    // Validate inputs
    $errors = [];
    
    if (empty($firstName)) {
        $errors[] = 'First name is required.';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required.';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    
    if (empty($username) || strlen($username) < 4) {
        $errors[] = 'Username is required and must be at least 4 characters.';
    }
    
    if (empty($password) || strlen($password) < 8) {
        $errors[] = 'Password is required and must be at least 8 characters.';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (!$agreeTerms) {
        $errors[] = 'You must agree to the Terms of Service and Privacy Policy.';
    }
    
    // Check if username contains invalid characters
    if (!empty($username) && !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }
    
    if (empty($errors)) {
        // Check for valid referral code
        $db = db();
        $referrer = null;
        
        if ($referralCode) {
            $referrer = $db->selectOne(
                "SELECT user_id FROM affiliate_data WHERE referral_code = ?",
                [$referralCode]
            );
        }
        
        // Register user
        $registerResult = registerUser($email, $username, $password, $firstName, $lastName);
        
        if ($registerResult['success']) {
            // Add referral information if valid referral code
            if ($referrer) {
                $db->update(
                    'affiliate_data',
                    [
                        'referred_by' => $referrer['user_id'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ],
                    'user_id = ?',
                    [$registerResult['user_id']]
                );
            }
            
            // Redirect to login with success message
            redirect('/auth/login?registered=1');
        } else {
            setFlashMessage('error', $registerResult['message']);
        }
    } else {
        // Display the first error
        setFlashMessage('error', $errors[0]);
    }
}

// Get referral code from query parameter
$referralCode = $_GET['ref'] ?? '';

// Set page title
$pageTitle = 'Register';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo $pageTitle; ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="/assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="auth-page">
    <?php require_once __DIR__ . '/../includes/loader.php'; ?>
    
    <div class="login-container" style="max-width: 500px;">
        <div class="text-center mb-4">
            <a href="/">
                <img src="/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="img-fluid mb-4" style="max-width: 200px;">
            </a>
            <h1 class="form-title">Create an Account</h1>
        </div>
        
        <?php if ($flashMessage = getFlashMessage()): ?>
            <div class="alert alert-<?php echo $flashMessage['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $flashMessage['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <form method="post" action="" id="register-form">
            <div class="row mb-3">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label for="first-name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first-name" name="first_name" placeholder="Enter your first name" value="<?php echo $_POST['first_name'] ?? ''; ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label for="last-name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last-name" name="last_name" placeholder="Enter your last name" value="<?php echo $_POST['last_name'] ?? ''; ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address" value="<?php echo $_POST['email'] ?? ''; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Choose a username" value="<?php echo $_POST['username'] ?? ''; ?>" required>
                <div class="form-text">Username must be at least 4 characters and can only contain letters, numbers, and underscores.</div>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                <div class="form-text">Password must be at least 8 characters long.</div>
            </div>
            
            <div class="mb-3">
                <label for="confirm-password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm-password" name="confirm_password" placeholder="Confirm your password" required>
            </div>
            
            <?php if (!empty($referralCode)): ?>
                <div class="mb-3">
                    <label for="referral-code" class="form-label">Referral Code</label>
                    <input type="text" class="form-control" id="referral-code" name="referral_code" value="<?php echo $referralCode; ?>" readonly>
                    <div class="form-text">You were referred by someone!</div>
                </div>
            <?php endif; ?>
            
            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="agree-terms" name="agree_terms" required>
                    <label class="form-check-label" for="agree-terms">
                        I agree to the <a href="/legal/tos" class="text-primary">Terms of Service</a> and <a href="/legal/privacy-policy" class="text-primary">Privacy Policy</a>
                    </label>
                </div>
            </div>
            
            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-primary">Create Account</button>
            </div>
            
            <div class="text-center">
                <p>Already have an account? <a href="/auth/login" class="text-primary">Log in</a></p>
            </div>
        </form>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const registerForm = document.getElementById('register-form');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm-password');
        
        registerForm.addEventListener('submit', function(e) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                e.preventDefault();
                alert('Passwords do not match');
                confirmPasswordInput.focus();
            }
        });
    });
    </script>
</body>
</html>