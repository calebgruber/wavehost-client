<?php
// dash/account.php - Account settings page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
    setFlashMessage('info', 'Please login to access your account settings.');
    redirect('/auth/login');
}

// Get current user
$currentUser = getCurrentUser();

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = db();
    
    // Profile update
    if (isset($_POST['update_profile'])) {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $postalCode = trim($_POST['postal_code'] ?? '');
        $country = trim($_POST['country'] ?? '');
        
        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlashMessage('error', 'Please enter a valid email address.');
            redirect('/dash/account');
        }
        
        // Check if email is already taken by another user
        if ($email !== $currentUser['email']) {
            $emailExists = $db->selectOne(
                "SELECT id FROM users WHERE email = ? AND id != ?",
                [$email, $currentUser['id']]
            );
            
            if ($emailExists) {
                setFlashMessage('error', 'Email address is already in use by another account.');
                redirect('/dash/account');
            }
        }
        
        // Update user profile
        $db->update(
            'users',
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'postal_code' => $postalCode,
                'country' => $country,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = ?',
            [$currentUser['id']]
        );
        
        setFlashMessage('success', 'Profile updated successfully.');
        redirect('/dash/account');
    }
    
    // Password change
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate current password
        if (!password_verify($currentPassword, $currentUser['password'])) {
            setFlashMessage('error', 'Current password is incorrect.');
            redirect('/dash/account');
        }
        
        // Validate new password
        if (strlen($newPassword) < 8) {
            setFlashMessage('error', 'New password must be at least 8 characters long.');
            redirect('/dash/account');
        }
        
        // Validate password confirmation
        if ($newPassword !== $confirmPassword) {
            setFlashMessage('error', 'New passwords do not match.');
            redirect('/dash/account');
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $db->update(
            'users',
            [
                'password' => $hashedPassword,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = ?',
            [$currentUser['id']]
        );
        
        setFlashMessage('success', 'Password changed successfully.');
        redirect('/dash/account');
    }
    
    // Notification preferences update
    if (isset($_POST['update_notifications'])) {
        $emailInvoices = isset($_POST['email_invoices']) ? 1 : 0;
        $emailSupport = isset($_POST['email_support']) ? 1 : 0;
        $emailMarketing = isset($_POST['email_marketing']) ? 1 : 0;
        
        // Check if notification preferences exist
        $notifExists = $db->selectOne(
            "SELECT id FROM notification_preferences WHERE user_id = ?",
            [$currentUser['id']]
        );
        
        if ($notifExists) {
            // Update existing preferences
            $db->update(
                'notification_preferences',
                [
                    'email_invoices' => $emailInvoices,
                    'email_support' => $emailSupport,
                    'email_marketing' => $emailMarketing,
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                'user_id = ?',
                [$currentUser['id']]
            );
        } else {
            // Create new preferences
            $db->insert('notification_preferences', [
                'user_id' => $currentUser['id'],
                'email_invoices' => $emailInvoices,
                'email_support' => $emailSupport,
                'email_marketing' => $emailMarketing,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        setFlashMessage('success', 'Notification preferences updated successfully.');
        redirect('/dash/account');
    }
    
    // API token generation
    if (isset($_POST['generate_api_token'])) {
        $tokenName = trim($_POST['token_name'] ?? '');
        
        if (empty($tokenName)) {
            setFlashMessage('error', 'Please enter a name for your API token.');
            redirect('/dash/account');
        }
        
        // Generate token
        $token = bin2hex(random_bytes(32));
        
        // Save token
        $db->insert('api_tokens', [
            'user_id' => $currentUser['id'],
            'name' => $tokenName,
            'token' => $token,
            'created_at' => date('Y-m-d H:i:s'),
            'last_used_at' => null
        ]);
        
        setFlashMessage('success', 'API token generated successfully.');
        $_SESSION['new_api_token'] = $token; // Store temporarily to display once
        redirect('/dash/account');
    }
    
    // API token deletion
    if (isset($_POST['delete_api_token'])) {
        $tokenId = (int)($_POST['token_id'] ?? 0);
        
        if ($tokenId > 0) {
            $db->delete(
                'api_tokens',
                'id = ? AND user_id = ?',
                [$tokenId, $currentUser['id']]
            );
            
            setFlashMessage('success', 'API token deleted successfully.');
        }
        
        redirect('/dash/account');
    }
    
    // Two-factor authentication toggle
    if (isset($_POST['toggle_2fa'])) {
        $enable2fa = isset($_POST['enable_2fa']) ? 1 : 0;
        
        // Update user
        $db->update(
            'users',
            [
                'two_factor_enabled' => $enable2fa,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = ?',
            [$currentUser['id']]
        );
        
        if ($enable2fa) {
            // Generate and store 2FA secret
            require_once __DIR__ . '/../vendor/autoload.php'; // Assuming TOTP library is installed
            
            $secret = 'TESTSECRETKEY'; // In production, use proper TOTP library to generate this
            
            $db->update(
                'users',
                [
                    'two_factor_secret' => $secret,
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$currentUser['id']]
            );
            
            setFlashMessage('success', 'Two-factor authentication has been enabled.');
            $_SESSION['show_2fa_qr'] = true;
        } else {
            setFlashMessage('success', 'Two-factor authentication has been disabled.');
        }
        
        redirect('/dash/account');
    }
}

// Get notification preferences
$db = db();
$notificationPreferences = $db->selectOne(
    "SELECT * FROM notification_preferences WHERE user_id = ?",
    [$currentUser['id']]
);

if (!$notificationPreferences) {
    $notificationPreferences = [
        'email_invoices' => 1,
        'email_support' => 1,
        'email_marketing' => 0
    ];
}

// Get API tokens
$apiTokens = $db->select(
    "SELECT * FROM api_tokens WHERE user_id = ? ORDER BY created_at DESC",
    [$currentUser['id']]
);

// Check if new token was generated
$newApiToken = $_SESSION['new_api_token'] ?? null;
if ($newApiToken) {
    unset($_SESSION['new_api_token']);
}

// Check if 2FA QR code should be shown
$show2faQr = $_SESSION['show_2fa_qr'] ?? false;
if ($show2faQr) {
    unset($_SESSION['show_2fa_qr']);
}

// List of countries for the form
$countries = [
    'AT' => 'Austria',
    'BE' => 'Belgium',
    'BG' => 'Bulgaria',
    'HR' => 'Croatia',
    'CY' => 'Cyprus',
    'CZ' => 'Czech Republic',
    'DK' => 'Denmark',
    'EE' => 'Estonia',
    'FI' => 'Finland',
    'FR' => 'France',
    'DE' => 'Germany',
    'GR' => 'Greece',
    'HU' => 'Hungary',
    'IE' => 'Ireland',
    'IT' => 'Italy',
    'LV' => 'Latvia',
    'LT' => 'Lithuania',
    'LU' => 'Luxembourg',
    'MT' => 'Malta',
    'NL' => 'Netherlands',
    'PL' => 'Poland',
    'PT' => 'Portugal',
    'RO' => 'Romania',
    'SK' => 'Slovakia',
    'SI' => 'Slovenia',
    'ES' => 'Spain',
    'SE' => 'Sweden',
    'GB' => 'United Kingdom',
    'US' => 'United States',
    // Add more countries as needed
];

// Set page title
$pageTitle = 'Account Settings';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php require_once __DIR__ . '/../includes/loader.php'; ?>

<div class="container py-5">
    <h1 class="fw-bold mb-4">Account Settings</h1>
    
    <?php if ($flashMessage = getFlashMessage()): ?>
        <div class="alert alert-<?php echo $flashMessage['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $flashMessage['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($newApiToken): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h5 class="alert-heading"><i class="fas fa-key me-2"></i> New API Token</h5>
            <p>Your new API token has been generated. Please copy it now as it won't be shown again.</p>
            <div class="input-group mb-3">
                <input type="text" class="form-control bg-dark border-secondary text-white" value="<?php echo $newApiToken; ?>" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="copyApiToken(this)">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-3 mb-4">
            <!-- Navigation Tabs -->
            <div class="card bg-dark mb-4">
                <div class="card-body p-0">
                    <div class="nav flex-column nav-pills" id="account-tab" role="tablist">
                        <button class="nav-link active text-start p-3 border-bottom border-secondary" id="profile-tab" data-bs-toggle="pill" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">
                            <i class="fas fa-user me-2"></i> Profile
                        </button>
                        <button class="nav-link text-start p-3 border-bottom border-secondary" id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">
                            <i class="fas fa-shield-alt me-2"></i> Security
                        </button>
                        <button class="nav-link text-start p-3 border-bottom border-secondary" id="notifications-tab" data-bs-toggle="pill" data-bs-target="#notifications" type="button" role="tab" aria-controls="notifications" aria-selected="false">
                            <i class="fas fa-bell me-2"></i> Notifications
                        </button>
                        <button class="nav-link text-start p-3" id="api-tab" data-bs-toggle="pill" data-bs-target="#api" type="button" role="tab" aria-controls="api" aria-selected="false">
                            <i class="fas fa-code me-2"></i> API Tokens
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Account Status -->
            <div class="card bg-dark">
                <div class="card-header bg-darker">
                    <h5 class="card-title mb-0">Account Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="me-2">
                                <i class="fas fa-user-shield text-success"></i>
                            </div>
                            <div>
                                <strong>Status:</strong> Active
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-2">
                            <div class="me-2">
                                <i class="fas fa-calendar-alt text-primary"></i>
                            </div>
                            <div>
                                <strong>Member Since:</strong> <?php echo formatDate($currentUser['created_at'], 'd M Y'); ?>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <i class="fas fa-lock <?php echo $currentUser['two_factor_enabled'] ? 'text-success' : 'text-warning'; ?>"></i>
                            </div>
                            <div>
                                <strong>Two-Factor Auth:</strong> <?php echo $currentUser['two_factor_enabled'] ? 'Enabled' : 'Disabled'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <!-- Tab Contents -->
            <div class="tab-content" id="account-tab-content">
                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="card bg-dark">
                        <div class="card-header bg-darker">
                            <h4 class="card-title mb-0">Personal Information</h4>
                        </div>
                        <div class="card-body">
                            <form action="" method="post">
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label for="first-name" class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control bg-dark border-secondary text-white" id="first-name" name="first_name" value="<?php echo $currentUser['first_name'] ?? ''; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last-name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control bg-dark border-secondary text-white" id="last-name" name="last_name" value="<?php echo $currentUser['last_name'] ?? ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control bg-dark border-secondary text-white" id="email" name="email" value="<?php echo $currentUser['email'] ?? ''; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control bg-dark border-secondary text-white" id="phone" name="phone" value="<?php echo $currentUser['phone'] ?? ''; ?>">
                                    </div>
                                </div>
                                
                                <hr class="border-secondary my-4">
                                
                                <h5 class="mb-3">Billing Address</h5>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control bg-dark border-secondary text-white" id="address" name="address" value="<?php echo $currentUser['address'] ?? ''; ?>">
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control bg-dark border-secondary text-white" id="city" name="city" value="<?php echo $currentUser['city'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="state" class="form-label">State/Province</label>
                                        <input type="text" class="form-control bg-dark border-secondary text-white" id="state" name="state" value="<?php echo $currentUser['state'] ?? ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label for="postal-code" class="form-label">Postal Code</label>
                                        <input type="text" class="form-control bg-dark border-secondary text-white" id="postal-code" name="postal_code" value="<?php echo $currentUser['postal_code'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="country" class="form-label">Country</label>
                                        <select class="form-select bg-dark border-secondary text-white" id="country" name="country">
                                            <option value="">Select Country</option>
                                            <?php foreach ($countries as $code => $name): ?>
                                                <option value="<?php echo $code; ?>" <?php echo ($currentUser['country'] ?? '') === $code ? 'selected' : ''; ?>><?php echo $name; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="d-grid mt-4">
                                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Security Tab -->
                <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                    <div class="card bg-dark mb-4">
                        <div class="card-header bg-darker">
                            <h4 class="card-title mb-0">Change Password</h4>
                        </div>
                        <div class="card-body">
                            <form action="" method="post">
                                <div class="mb-3">
                                    <label for="current-password" class="form-label">Current Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control bg-dark border-secondary text-white" id="current-password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new-password" class="form-label">New Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control bg-dark border-secondary text-white" id="new-password" name="new_password" required>
                                    <div class="form-text">Password must be at least 8 characters long.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm-password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control bg-dark border-secondary text-white" id="confirm-password" name="confirm_password" required>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card bg-dark">
                        <div class="card-header bg-darker">
                            <h4 class="card-title mb-0">Two-Factor Authentication</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <p class="mb-3">Two-factor authentication adds an additional layer of security to your account by requiring more than just a password to sign in.</p>
                                
                                <?php if ($show2faQr): ?>
                                    <div class="alert alert-info mb-3">
                                        <h5 class="alert-heading">Set Up Your Authenticator App</h5>
                                        <p>Scan this QR code with your authenticator app (such as Google Authenticator, Authy, or Microsoft Authenticator).</p>
                                        <div class="text-center mb-3">
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=otpauth://totp/WaveHost:<?php echo urlencode($currentUser['email']); ?>?secret=TESTSECRETKEY&issuer=WaveHost" alt="QR Code" class="img-fluid border p-2 bg-white">
                                        </div>
                                        <p class="mb-0">Or enter this code manually: <strong>TESTSECRETKEY</strong></p>
                                    </div>
                                <?php endif; ?>
                                
                                <form action="" method="post">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="enable-2fa" name="enable_2fa" <?php echo $currentUser['two_factor_enabled'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="enable-2fa">
                                            <?php echo $currentUser['two_factor_enabled'] ? 'Disable' : 'Enable'; ?> Two-Factor Authentication
                                        </label>
                                    </div>
                                    
                                    <button type="submit" name="toggle_2fa" class="btn btn-primary">
                                        <?php echo $currentUser['two_factor_enabled'] ? 'Disable' : 'Enable'; ?> Two-Factor Authentication
                                    </button>
                                </form>
                            </div>
                            
                            <hr class="border-secondary my-4">
                            
                            <h5 class="mb-3">Active Sessions</h5>
                            
                            <div class="table-responsive">
                                <table class="table table-dark table-hover">
                                    <thead>
                                        <tr>
                                            <th>Device</th>
                                            <th>Location</th>
                                            <th>Last Active</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-desktop me-2 text-primary"></i>
                                                    <div>Windows / Chrome</div>
                                                </div>
                                            </td>
                                            <td>Amsterdam, Netherlands</td>
                                            <td>Just now</td>
                                            <td>
                                                <span class="badge bg-success">Current</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Notifications Tab -->
                <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                    <div class="card bg-dark">
                        <div class="card-header bg-darker">
                            <h4 class="card-title mb-0">Notification Preferences</h4>
                        </div>
                        <div class="card-body">
                            <form action="" method="post">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="email-invoices" name="email_invoices" <?php echo ($notificationPreferences['email_invoices'] ?? 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="email-invoices">
                                            <strong>Billing Notifications</strong><br>
                                            <span class="text-muted">Receive emails about invoices, payment reminders, and receipts.</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="email-support" name="email_support" <?php echo ($notificationPreferences['email_support'] ?? 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="email-support">
                                            <strong>Support Notifications</strong><br>
                                            <span class="text-muted">Receive emails about support ticket updates and service status.</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="email-marketing" name="email_marketing" <?php echo ($notificationPreferences['email_marketing'] ?? 0) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="email-marketing">
                                            <strong>Marketing Emails</strong><br>
                                            <span class="text-muted">Receive emails about promotions, new services, and updates.</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="submit" name="update_notifications" class="btn btn-primary">Update Preferences</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- API Tokens Tab -->
                <div class="tab-pane fade" id="api" role="tabpanel" aria-labelledby="api-tab">
                    <div class="card bg-dark mb-4">
                        <div class="card-header bg-darker">
                            <h4 class="card-title mb-0">Generate API Token</h4>
                        </div>
                        <div class="card-body">
                            <form action="" method="post">
                                <div class="mb-3">
                                    <label for="token-name" class="form-label">Token Name</label>
                                    <input type="text" class="form-control bg-dark border-secondary text-white" id="token-name" name="token_name" placeholder="e.g. My Application" required>
                                    <div class="form-text">Give your token a descriptive name so you can easily identify it later.</div>
                                </div>
                                
                                <button type="submit" name="generate_api_token" class="btn btn-primary">Generate Token</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card bg-dark">
                        <div class="card-header bg-darker">
                            <h4 class="card-title mb-0">Your API Tokens</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Created</th>
                                            <th>Last Used</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($apiTokens) > 0): ?>
                                            <?php foreach ($apiTokens as $token): ?>
                                                <tr>
                                                    <td><?php echo $token['name']; ?></td>
                                                    <td><?php echo formatDate($token['created_at']); ?></td>
                                                    <td><?php echo $token['last_used_at'] ? formatDate($token['last_used_at']) : 'Never'; ?></td>
                                                    <td>
                                                        <form action="" method="post" class="d-inline">
                                                            <input type="hidden" name="token_id" value="<?php echo $token['id']; ?>">
                                                            <button type="submit" name="delete_api_token" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this token?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4">
                                                    <div class="text-muted">No API tokens generated yet.</div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyApiToken(button) {
    const input = button.previousElementSibling;
    input.select();
    document.execCommand('copy');
    
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> Copied!';
    
    setTimeout(() => {
        button.innerHTML = originalHtml;
    }, 2000);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>