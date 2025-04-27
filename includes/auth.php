<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

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
    
    return [
        'success' => true,
        'message' => 'Login successful',
        'user' => $user
    ];
}

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
