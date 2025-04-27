<?php
// auth/reset-password.php - Password reset request page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('/dash');
}

// Check if token is provided for reset form
$token = $_GET['token'] ?? null;
$resetForm = !empty($token);

// Process request form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$resetForm) {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        setFlashMessage('error', 'Please enter your email address.');
    } else {
        // Send reset password request
        $result = requestPasswordReset($email);
        
        // Always show success message even if email doesn't exist (for security)
        setFlashMessage('success', $result['message']);
        redirect('/auth/reset-password');
    }
}

// Process reset form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $resetForm) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        setFlashMessage('error', 'Please enter a new password.');
    } elseif (strlen($password) < 8) {
        setFlashMessage('error', 'Password must be at least 8 characters long.');
    } elseif ($password !== $confirmPassword) {
        setFlashMessage('error', 'Passwords do not match.');
    } else {
        // Reset password
        $result = resetPassword($token, $password);
        
        if ($result['success']) {
            setFlashMessage('success', $result['message']);
            redirect('/auth/login');
        } else {
            setFlashMessage('error', $result['message']);
            redirect('/auth/reset-password');
        }
    }
}

// Verify token if provided
$validToken = false;
if ($resetForm) {
    $db = db();
    
    $resetRequest = $db->selectOne(
        "SELECT * FROM password_resets WHERE token = ? AND expires_at > ?",
        [$token, date('Y-m-d H:i:s')]
    );
    
    $validToken = !empty($resetRequest);
    
    // If token is invalid, redirect to request form
    if (!$validToken) {
        setFlashMessage('error', 'Invalid or expired password reset token. Please request a new reset link.');
        redirect('/auth/reset-password');
    }
}

// Set page title based on form type
$pageTitle = $resetForm ? 'Reset Password' : 'Forgot Password';
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
    
    <div class="login-container">
        <div class="text-center mb-4">
            <a href="/">
                <img src="/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="img-fluid mb-4" style="max-width: 200px;">
            </a>
            <h1 class="form-title"><?php echo $pageTitle; ?></h1>
        </div>
        
        <?php if ($flashMessage = getFlashMessage()): ?>
            <div class="alert alert-<?php echo $flashMessage['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $flashMessage['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($resetForm && $validToken): ?>
            <!-- Reset Password Form -->
            <form method="post" action="" id="reset-form">
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your new password" required>
                    <div class="form-text">Password must be at least 8 characters long.</div>
                </div>
                
                <div class="mb-4">
                    <label for="confirm-password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm-password" name="confirm_password" placeholder="Confirm your new password" required>
                </div>
                
                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>
        <?php else: ?>
            <!-- Request Reset Form -->
            <form method="post" action="">
                <div class="mb-3">
                    <p class="text-muted">Enter your email address below and we will send you instructions to reset your password.</p>
                </div>
                
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address" required>
                </div>
                
                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary">Send Reset Link</button>
                </div>
            </form>
        <?php endif; ?>
        
        <div class="text-center">
            <a href="/auth/login" class="text-primary"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if ($resetForm): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const resetForm = document.getElementById('reset-form');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm-password');
        
        resetForm.addEventListener('submit', function(e) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                e.preventDefault();
                alert('Passwords do not match');
                confirmPasswordInput.focus();
            }
        });
    });
    </script>
    <?php endif; ?>
</body>
</html>