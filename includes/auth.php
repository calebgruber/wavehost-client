<?php
// includes/auth.php
// Authentication functions

require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

// Register a new user
function registerUser($email, $username, $password, $firstName, $lastName) {
    $db = db();
    
    // Check if email already exists
    $existingUser = $db->selectOne(
        "SELECT id FROM users WHERE email = ?",
        [$email]
    );
    
    if ($existingUser) {
        return [
            'success' => false,
            'message' => 'Email already registered'
        ];
    }
    
    // Check if username already exists
    $existingUsername = $db->selectOne(
        "SELECT id FROM users WHERE username = ?",
        [$username]
    );
    
    if ($existingUsername) {
        return [
            'success' => false,
            'message' => 'Username already taken'
        ];
    }
    
    // Validate password complexity
    if (!validatePasswordComplexity($password)) {
        return [
            'success' => false,
            'message' => 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character'
        ];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Generate verification token
    $verificationToken = generateRandomString(32);
    
    // Insert new user
    $userId = $db->insert('users', [
        'email' => $email,
        'username' => $username,
        'password' => $hashedPassword,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'verification_token' => $verificationToken,
        'is_verified' => 0,
        'is_admin' => 0,
        'is_staff' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    // Send verification email
    sendVerificationEmail($email, $verificationToken);
    
    return [
        'success' => true,
        'message' => 'Registration successful. Please check your email to verify your account.',
        'user_id' => $userId
    ];
}

// Send verification email
function sendVerificationEmail($email, $token) {
    $verificationUrl = SITE_URL . '/auth/verify-email?token=' . $token;
    
    $subject = SITE_NAME . ' - Verify Your Email';
    
    $message = "Hello,\n\n";
    $message .= "Thank you for registering with " . SITE_NAME . ".\n\n";
    $message .= "Please click the link below to verify your email address:\n";
    $message .= $verificationUrl . "\n\n";
    $message .= "If you did not register for an account, please ignore this email.\n\n";
    $message .= "Regards,\n";
    $message .= SITE_NAME . " Team";
    
    $headers = 'From: ' . ADMIN_EMAIL . "\r\n" .
               'Reply-To: ' . ADMIN_EMAIL . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    mail($email, $subject, $message, $headers);
}

// Login a user
function loginUser($emailOrUsername, $password, $rememberMe = false) {
    $db = db();
    
    // Find user by email or username
    $user = $db->selectOne(
        "SELECT * FROM users WHERE email = ? OR username = ?",
        [$emailOrUsername, $emailOrUsername]
    );
    
    if (!$user) {
        return [
            'success' => false,
            'message' => 'Invalid credentials'
        ];
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        // Log failed login attempt
        logActivity($user['id'], 'failed_login', [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ]);
        
        return [
            'success' => false,
            'message' => 'Invalid credentials'
        ];
    }
    
    // Check if account is verified
    if (!$user['is_verified']) {
        return [
            'success' => false,
            'message' => 'Account not verified. Please check your email.'
        ];
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['is_admin'] = (bool) $user['is_admin'];
    $_SESSION['is_staff'] = (bool) $user['is_staff'];
    
    // Handle remember me functionality
    if ($rememberMe) {
        $token = generateRandomString(32);
        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $db->insert('remember_tokens', [
            'user_id' => $user['id'],
            'token' => $token,
            'expires_at' => $expiry,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        setcookie('remember_token', $token, strtotime('+30 days'), '/', '', true, true);
    }
    
    // Log activity
    logActivity($user['id'], 'login', [
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ]);
    
    return [
        'success' => true,
        'message' => 'Login successful',
        'user' => $user
    ];
}

// Logout user
function logoutUser() {
    // Clear remember token if exists
    if (isset($_COOKIE['remember_token'])) {
        $db = db();
        
        $db->delete(
            'remember_tokens',
            'token = ?',
            [$_COOKIE['remember_token']]
        );
        
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    }
    
    // Log activity if logged in
    if (isset($_SESSION['user_id'])) {
        logActivity($_SESSION['user_id'], 'logout');
    }
    
    // Destroy session
    session_unset();
    session_destroy();
    
    return [
        'success' => true,
        'message' => 'Logout successful'
    ];
}

// Check remember me token
function checkRememberToken() {
    if (!isset($_COOKIE['remember_token'])) {
        return false;
    }
    
    $db = db();
    
    $token = $db->selectOne(
        "SELECT rt.*, u.* 
         FROM remember_tokens rt 
         JOIN users u ON rt.user_id = u.id 
         WHERE rt.token = ? AND rt.expires_at > ?",
        [$_COOKIE['remember_token'], date('Y-m-d H:i:s')]
    );
    
    if (!$token) {
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        return false;
    }
    
    // Set session variables
    $_SESSION['user_id'] = $token['user_id'];
    $_SESSION['username'] = $token['username'];
    $_SESSION['is_admin'] = (bool) $token['is_admin'];
    $_SESSION['is_staff'] = (bool) $token['is_staff'];
    
    // Log activity
    logActivity($token['user_id'], 'auto_login', [
        'method' => 'remember_token',
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ]);
    
    return true;
}

// Verify email
function verifyEmail($token) {
    $db = db();
    
    $user = $db->selectOne(
        "SELECT id FROM users WHERE verification_token = ? AND is_verified = 0",
        [$token]
    );
    
    if (!$user) {
        return [
            'success' => false,
            'message' => 'Invalid or expired verification token'
        ];
    }
    
    $db->update(
        'users',
        [
            'is_verified' => 1,
            'verification_token' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ],
        'id = ?',
        [$user['id']]
    );
    
    // Log activity
    logActivity($user['id'], 'email_verified');
    
    return [
        'success' => true,
        'message' => 'Email verified successfully. You can now login.'
    ];
}

// Reset password request
function requestPasswordReset($email) {
    $db = db();
    
    $user = $db->selectOne(
        "SELECT id, email FROM users WHERE email = ? AND is_verified = 1",
        [$email]
    );
    
    if (!$user) {
        return [
            'success' => false,
            'message' => 'If an account exists with this email, a password reset link has been sent.'
        ];
    }
    
    $token = generateRandomString(32);
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $db->insert('password_resets', [
        'user_id' => $user['id'],
        'token' => $token,
        'expires_at' => $expiry,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    // Send password reset email
    sendPasswordResetEmail($user['email'], $token);
    
    // Log activity
    logActivity($user['id'], 'password_reset_requested');
    
    return [
        'success' => true,
        'message' => 'If an account exists with this email, a password reset link has been sent.'
    ];
}

// Send password reset email
function sendPasswordResetEmail($email, $token) {
    $resetUrl = SITE_URL . '/auth/reset-password?token=' . $token;
    
    $subject = SITE_NAME . ' - Password Reset';
    
    $message = "Hello,\n\n";
    $message .= "You have requested to reset your password for your " . SITE_NAME . " account.\n\n";
    $message .= "Please click the link below to reset your password:\n";
    $message .= $resetUrl . "\n\n";
    $message .= "This link will expire in 1 hour.\n\n";
    $message .= "If you did not request a password reset, please ignore this email.\n\n";
    $message .= "Regards,\n";
    $message .= SITE_NAME . " Team";
    
    $headers = 'From: ' . ADMIN_EMAIL . "\r\n" .
               'Reply-To: ' . ADMIN_EMAIL . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    mail($email, $subject, $message, $headers);
}

// Reset password
function resetPassword($token, $newPassword) {
    $db = db();
    
    $reset = $db->selectOne(
        "SELECT pr.user_id FROM password_resets pr 
         WHERE pr.token = ? AND pr.expires_at > ?",
        [$token, date('Y-m-d H:i:s')]
    );
    
    if (!$reset) {
        return [
            'success' => false,
            'message' => 'Invalid or expired reset token'
        ];
    }
    
    // Validate password complexity
    if (!validatePasswordComplexity($newPassword)) {
        return [
            'success' => false,
            'message' => 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character'
        ];
    }
    
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $db->update(
        'users',
        [
            'password' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ],
        'id = ?',
        [$reset['user_id']]
    );
    
    $db->delete('password_resets', 'user_id = ?', [$reset['user_id']]);
    
    // Log activity
    logActivity($reset['user_id'], 'password_reset_completed');
    
    return [
        'success' => true,
        'message' => 'Password reset successful. You can now login with your new password.'
    ];
}

// OAuth login for staff
function oauthLogin() {
    $state = generateRandomString(16);
    $_SESSION['oauth_state'] = $state;
    
    $params = [
        'client_id' => OAUTH_CLIENT_ID,
        'redirect_uri' => OAUTH_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'profile email',
        'state' => $state
    ];
    
    $authUrl = OAUTH_PROVIDER_URL . '/auth?' . http_build_query($params);
    
    redirect($authUrl);
}

// OAuth callback
function oauthCallback($code, $state) {
    if (!isset($_SESSION['oauth_state']) || $_SESSION['oauth_state'] !== $state) {
        return [
            'success' => false,
            'message' => 'Invalid OAuth state'
        ];
    }
    
    unset($_SESSION['oauth_state']);
    
    // Exchange code for token
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => OAUTH_PROVIDER_URL . '/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'authorization_code',
            'client_id' => OAUTH_CLIENT_ID,
            'client_secret' => OAUTH_CLIENT_SECRET,
            'redirect_uri' => OAUTH_REDIRECT_URI,
            'code' => $code
        ])
    ]);
    
    $response = curl_exec($curl);
    curl_close($curl);
    
    $tokenData = json_decode($response, true);
    
    if (!isset($tokenData['access_token'])) {
        return [
            'success' => false,
            'message' => 'Failed to get access token'
        ];
    }
    
    // Get user info
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => OAUTH_PROVIDER_URL . '/userinfo',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $tokenData['access_token']
        ]
    ]);
    
    $response = curl_exec($curl);
    curl_close($curl);
    
    $userData = json_decode($response, true);
    
    if (!isset($userData['email'])) {
        return [
            'success' => false,
            'message' => 'Failed to get user info'
        ];
    }
    
    $db = db();
    
    // Check if the staff exists
    $staff = $db->selectOne(
        "SELECT s.*, u.id as user_id, u.username, u.email, u.is_admin, u.is_staff 
         FROM staff s 
         JOIN users u ON s.user_id = u.id 
         WHERE s.oauth_id = ?",
        [$userData['sub']]
    );
    
    if (!$staff) {
        // Check if user with this email exists
        $user = $db->selectOne(
            "SELECT id, is_admin, is_staff FROM users WHERE email = ?",
            [$userData['email']]
        );
        
        if ($user) {
            // Link existing user with OAuth
            $db->insert('staff', [
                'user_id' => $user['id'],
                'oauth_provider' => 'staff_provider',
                'oauth_id' => $userData['sub'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update user as staff if not already
            if (!$user['is_staff']) {
                $db->update(
                    'users',
                    [
                        'is_staff' => 1,
                        'updated_at' => date('Y-m-d H:i:s')
                    ],
                    'id = ?',
                    [$user['id']]
                );
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = (bool) $user['is_admin'];
            $_SESSION['is_staff'] = true;
            
            return [
                'success' => true,
                'message' => 'Staff login successful',
                'user_id' => $user['id']
            ];
        }
        
        // Create new user
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $userData['name'])) . rand(100, 999);
        
        $userId = $db->insert('users', [
            'email' => $userData['email'],
            'username' => $username,
            'password' => password_hash(generateRandomString(16), PASSWORD_DEFAULT),
            'first_name' => $userData['given_name'] ?? '',
            'last_name' => $userData['family_name'] ?? '',
            'is_verified' => 1,
            'is_admin' => 0,
            'is_staff' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $db->insert('staff', [
            'user_id' => $userId,
            'oauth_provider' => 'staff_provider',
            'oauth_id' => $userData['sub'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Set session variables
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = false;
        $_SESSION['is_staff'] = true;
        
        return [
            'success' => true,
            'message' => 'Staff login successful',
            'user_id' => $userId
        ];
    }
    
    // Staff already exists, login
    $_SESSION['user_id'] = $staff['user_id'];
    $_SESSION['username'] = $staff['username'];
    $_SESSION['is_admin'] = (bool) $staff['is_admin'];
    $_SESSION['is_staff'] = true;
    
    return [
        'success' => true,
        'message' => 'Staff login successful',
        'user_id' => $staff['user_id']
    ];
}

// Check if user requires password change
function requiresPasswordChange($userId) {
    $db = db();
    
    $user = $db->selectOne(
        "SELECT requires_password_change FROM users WHERE id = ?",
        [$userId]
    );
    
    return $user && isset($user['requires_password_change']) && $user['requires_password_change'] == 1;
}

// Update user password
function updatePassword($userId, $currentPassword, $newPassword) {
    $db = db();
    
    // Get user
    $user = $db->selectOne(
        "SELECT * FROM users WHERE id = ?",
        [$userId]
    );
    
    if (!$user) {
        return [
            'success' => false,
            'message' => 'User not found'
        ];
    }
    
    // Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        return [
            'success' => false,
            'message' => 'Current password is incorrect'
        ];
    }
    
    // Validate password complexity
    if (!validatePasswordComplexity($newPassword)) {
        return [
            'success' => false,
            'message' => 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character'
        ];
    }
    
    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $db->update(
        'users',
        [
            'password' => $hashedPassword,
            'requires_password_change' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ],
        'id = ?',
        [$userId]
    );
    
    // Log activity
    logActivity($userId, 'password_changed');
    
    return [
        'success' => true,
        'message' => 'Password updated successfully'
    ];
}

// Update user profile
function updateUserProfile($userId, $data) {
    $db = db();
    
    // Validate email if changing
    if (isset($data['email'])) {
        $existingUser = $db->selectOne(
            "SELECT id FROM users WHERE email = ? AND id != ?",
            [$data['email'], $userId]
        );
        
        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'Email already registered'
            ];
        }
    }
    
    // Validate username if changing
    if (isset($data['username'])) {
        $existingUser = $db->selectOne(
            "SELECT id FROM users WHERE username = ? AND id != ?",
            [$data['username'], $userId]
        );
        
        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'Username already taken'
            ];
        }
    }
    
    // Add updated timestamp
    $data['updated_at'] = date('Y-m-d H:i:s');
    
    // Update user
    $db->update(
        'users',
        $data,
        'id = ?',
        [$userId]
    );
    
    // Log activity
    logActivity($userId, 'profile_updated');
    
    return [
        'success' => true,
        'message' => 'Profile updated successfully'
    ];
}

// Check permissions
function checkPermission($permission) {
    // Admin has all permissions
    if (isAdmin()) {
        return true;
    }
    
    // Staff permissions
    if (isStaff()) {
        $staffPermissions = getStaffPermissions();
        return in_array($permission, $staffPermissions);
    }
    
    return false;
}

// Get staff permissions
function getStaffPermissions() {
    if (!isStaff()) {
        return [];
    }
    
    $db = db();
    
    $staff = $db->selectOne(
        "SELECT s.*, r.permissions 
         FROM staff s 
         LEFT JOIN staff_roles r ON s.role = r.id 
         WHERE s.user_id = ?",
        [$_SESSION['user_id']]
    );
    
    if (!$staff || !isset($staff['permissions'])) {
        return [];
    }
    
    return json_decode($staff['permissions'], true) ?: [];
}

// Check if user owns resource
function ownsResource($resourceType, $resourceId) {
    if (isAdmin()) {
        return true;
    }
    
    $db = db();
    
    switch ($resourceType) {
        case 'service':
            $resource = $db->selectOne(
                "SELECT user_id FROM services WHERE id = ?",
                [$resourceId]
            );
            break;
            
        case 'invoice':
            $resource = $db->selectOne(
                "SELECT user_id FROM invoices WHERE id = ?",
                [$resourceId]
            );
            break;
            
        case 'ticket':
            $resource = $db->selectOne(
                "SELECT user_id FROM tickets WHERE id = ?",
                [$resourceId]
            );
            break;
            
        default:
            return false;
    }
    
    return $resource && $resource['user_id'] == $_SESSION['user_id'];
}