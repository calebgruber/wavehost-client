<?php
// auth/login.php

// Include the required files in the correct order
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('/dash');
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOrUsername = trim($_POST['email_or_username'] ?? '');
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);
    
    // Validate inputs
    if (empty($emailOrUsername)) {
        setFlashMessage('error', 'Please enter your email or username.');
    } elseif (empty($password)) {
        setFlashMessage('error', 'Please enter your password.');
    } else {
        // Attempt login
        $loginResult = loginUser($emailOrUsername, $password, $rememberMe);
        
        if ($loginResult['success']) {
            // Redirect to intended URL if set, otherwise to dashboard
            $redirectUrl = $_SESSION['intended_url'] ?? '/dash';
            unset($_SESSION['intended_url']);
            
            redirect($redirectUrl);
        } else {
            setFlashMessage('error', $loginResult['message']);
        }
    }
}

// Check for remember token
if (!isLoggedIn() && isset($_COOKIE['remember_token'])) {
    $db = db();
    $token = $_COOKIE['remember_token'];
    
    $rememberedUser = $db->selectOne(
        "SELECT u.* FROM users u 
        JOIN remember_tokens rt ON u.id = rt.user_id 
        WHERE rt.token = ? AND rt.expires_at > ?",
        [$token, date('Y-m-d H:i:s')]
    );
    
    if ($rememberedUser) {
        // Set session variables
        $_SESSION['user_id'] = $rememberedUser['id'];
        $_SESSION['username'] = $rememberedUser['username'];
        $_SESSION['is_admin'] = (bool) $rememberedUser['is_admin'];
        $_SESSION['is_staff'] = (bool) $rememberedUser['is_staff'];
        
        // Redirect to dashboard
        redirect('/dash');
    }
}

// Check for register success message
$registerSuccess = false;
if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $registerSuccess = true;
}

// Set page title
$pageTitle = 'Login';
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
    
    <style>
        .auth-background {
            background-color: #fd5c4c;
                background-image: url("https://auth.wavehost.org/media/public/flow-backgrounds/Macos_Wallpaper_3_Vr9sJLy.png")!important;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        
        .auth-container {
            background-color: #111827;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            color: white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .auth-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: 500;
        }
        
        .auth-logo {
            display: block;
            margin: 0 auto 25px;
            max-width: 150px;
        }
        
        .auth-form .form-control {
            background-color: #1f2937;
            border: 1px solid #374151;
            color: #fff;
            padding: 12px 15px;
            border-radius: 6px;
        }
        
        .auth-form .form-control:focus {
            background-color: #1f2937;
            border-color: #0984e3;
            box-shadow: 0 0 0 0.25rem rgba(9, 132, 227, 0.25);
            color: #fff;
        }
        
        .auth-form .btn-primary {
            background-color: #0984e3;
            border-color: #0984e3;
            padding: 12px 15px;
            font-weight: 500;
            border-radius: 6px;
        }
        
        .auth-form .btn-primary:hover {
            background-color: #0070c9;
            border-color: #0070c9;
        }
        
        .auth-form .form-check-input:checked {
            background-color: #0984e3;
            border-color: #0984e3;
        }
        
        .auth-form a {
            color: #0984e3;
            text-decoration: none;
        }
        
        .auth-form a:hover {
            color: #0070c9;
            text-decoration: underline;
        }
        
        .auth-divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        
        .auth-divider::before,
        .auth-divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #374151;
        }
        
        .auth-divider-text {
            padding: 0 10px;
            color: #6b7280;
        }
        
        .staff-login-btn {
            background-color: transparent;
            border: 1px solid #6b7280;
            color: white;
            padding: 12px 15px;
            font-weight: 500;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .staff-login-btn:hover {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: white;
            color: white;
        }
        
        .staff-login-btn i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../includes/loader.php'; ?>
    
    <div class="auth-background">
        <div class="auth-container">
            <img src="/assets/images/wavehost-logo.png" alt="<?php echo SITE_NAME; ?>" class="auth-logo">
            <h1 class="auth-title">myWaveHost</h1>
            
            <?php if ($flashMessage = getFlashMessage()): ?>
                <div class="alert alert-<?php echo $flashMessage['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $flashMessage['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($registerSuccess): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Registration successful!</strong> Please check your email to verify your account.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" class="auth-form">
                <div class="mb-3">
                    <label for="email-or-username" class="form-label">Email or Username</label>
                    <input type="text" class="form-control" id="email-or-username" name="email_or_username" placeholder="Enter your email or username" required autofocus>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <div class="row mb-4">
                    <div class="col">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember-me" name="remember_me">
                            <label class="form-check-label" for="remember-me">
                                Remember me
                            </label>
                        </div>
                    </div>
                    
                    <div class="col text-end">
                        <a href="/auth/reset-password">Forgot password?</a>
                    </div>
                </div>
                
                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary">Log in</button>
                </div>
                
                <div class="text-center">
                    <p>Don't have an account? <a href="/auth/register">Create one</a></p>
                </div>
            </form>
            
            <?php if (!empty(OAUTH_CLIENT_ID) && !empty(OAUTH_PROVIDER_URL)): ?>
                <div class="auth-divider">
                    <span class="auth-divider-text">OR</span>
                </div>
                
                <div class="d-grid">
                    <a href="/auth/oauth" class="staff-login-btn">
                        <i class="fas fa-shield-alt"></i> Staff Login
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>