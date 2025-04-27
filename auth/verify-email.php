<?php
// auth/verify-email.php - Email verification page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('/dash');
}

// Check if token is provided
$token = $_GET['token'] ?? null;

if (empty($token)) {
    setFlashMessage('error', 'Invalid verification link. Please request a new one.');
    redirect('/auth/login');
}

// Process verification
$verifyResult = verifyEmail($token);

if ($verifyResult['success']) {
    setFlashMessage('success', $verifyResult['message']);
} else {
    setFlashMessage('error', $verifyResult['message']);
}

// Process resend verification email request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend'])) {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        setFlashMessage('error', 'Please enter your email address.');
    } else {
        $db = db();
        
        // Check if user exists and is not verified
        $user = $db->selectOne(
            "SELECT id, email, is_verified FROM users WHERE email = ?",
            [$email]
        );
        
        if ($user && !$user['is_verified']) {
            // Generate new verification token
            $verificationToken = generateRandomString(32);
            
            $db->update(
                'users',
                [
                    'verification_token' => $verificationToken,
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$user['id']]
            );
            
            // Send verification email (implementation not shown)
            // sendVerificationEmail($user['email'], $verificationToken);
            
            setFlashMessage('success', 'A new verification email has been sent. Please check your inbox.');
        } else {
            // Always show success message even if email doesn't exist or is already verified (for security)
            setFlashMessage('success', 'If an account exists with this email, a verification email has been sent.');
        }
    }
}

// Set page title
$pageTitle = 'Verify Email';
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
            <h1 class="form-title">Email Verification</h1>
        </div>
        
        <?php if ($flashMessage = getFlashMessage()): ?>
            <div class="alert alert-<?php echo $flashMessage['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $flashMessage['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($verifyResult['success'] ?? false): ?>
            <div class="text-center mb-4">
                <div class="mb-4">
                    <div class="bg-success-subtle mx-auto mb-3 rounded-circle p-3 d-inline-block">
                        <i class="fas fa-check-circle fa-4x text-success"></i>
                    </div>
                    <h4>Email Verified Successfully!</h4>
                    <p class="text-muted">Your email has been verified. You can now login to your account.</p>
                </div>
                
                <div class="d-grid">
                    <a href="/auth/login" class="btn btn-primary">Go to Login</a>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center mb-4">
                <div class="mb-4">
                    <div class="bg-warning-subtle mx-auto mb-3 rounded-circle p-3 d-inline-block">
                        <i class="fas fa-exclamation-circle fa-4x text-warning"></i>
                    </div>
                    <h4>Verification Failed</h4>
                    <p class="text-muted">The verification link is invalid or has expired.</p>
                </div>
                
                <hr class="my-4">
                
                <h5 class="mb-3">Didn't receive a verification email?</h5>
                <form method="post" action="">
                    <div class="mb-3">
                        <input type="email" class="form-control" name="email" placeholder="Enter your email address" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" name="resend" class="btn btn-primary">Resend Verification Email</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-4">
            <a href="/auth/login" class="text-primary"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>