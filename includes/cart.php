<?php
// includes/cart.php
// Shopping cart functionality

require_once 'config.php';
require_once 'functions.php';

// Initialize cart in session
function initCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

// Get cart items
function getCartItems() {
    initCart();
    return $_SESSION['cart'];
}

// Add item to cart
function addToCart($item) {
    initCart();
    
    // Generate unique ID if not provided
    if (!isset($item['id'])) {
        $item['id'] = uniqid();
    }
    
    // Add item to cart
    $_SESSION['cart'][$item['id']] = $item;
    
    return true;
}

// Remove item from cart
function removeFromCart($itemId) {
    initCart();
    
    if (isset($_SESSION['cart'][$itemId])) {
        unset($_SESSION['cart'][$itemId]);
        return true;
    }
    
    return false;
}

// Update item in cart
function updateCartItem($itemId, $data) {
    initCart();
    
    if (isset($_SESSION['cart'][$itemId])) {
        $_SESSION['cart'][$itemId] = array_merge($_SESSION['cart'][$itemId], $data);
        return true;
    }
    
    return false;
}

// Clear cart
function clearCart() {
    $_SESSION['cart'] = [];
    return true;
}

// Get cart item count
function getCartItemCount() {
    initCart();
    return count($_SESSION['cart']);
}

// Get cart subtotal
function getCartSubtotal() {
    initCart();
    
    $subtotal = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'];
    }
    
    return $subtotal;
}

// Calculate discount based on billing period
function calculateDiscount($subtotal, $billingPeriod) {
    $discountPercentages = [
        'monthly' => 0,
        'quarterly' => 5,
        'semiannually' => 10,
        'annually' => 15,
        'biennially' => 20,
        'triennially' => 25
    ];
    
    $percentage = $discountPercentages[$billingPeriod] ?? 0;
    
    return ($subtotal * $percentage) / 100;
}

// Get cart summary with discount and VAT
function getCartSummary($billingPeriod = 'monthly') {
    $subtotal = getCartSubtotal();
    
    // Calculate discount
    $discountPercentage = 0;
    
    switch ($billingPeriod) {
        case 'quarterly':
            $discountPercentage = 5;
            break;
        case 'semiannually':
            $discountPercentage = 10;
            break;
        case 'annually':
            $discountPercentage = 15;
            break;
        case 'biennially':
            $discountPercentage = 20;
            break;
        case 'triennially':
            $discountPercentage = 25;
            break;
    }
    
    $discountAmount = ($subtotal * $discountPercentage) / 100;
    $afterDiscount = $subtotal - $discountAmount;
    
    // Apply VAT (0% for now, can be changed later)
    $vatRate = 0;
    $vatAmount = ($afterDiscount * $vatRate) / 100;
    
    // Calculate total
    $total = $afterDiscount + $vatAmount;
    
    return [
        'subtotal' => $subtotal,
        'discount_percentage' => $discountPercentage,
        'discount_amount' => $discountAmount,
        'vat_rate' => $vatRate,
        'vat_amount' => $vatAmount,
        'total' => $total
    ];
}

// Get billing period options
function getBillingPeriodOptions() {
    return [
        'monthly' => [
            'label' => 'Monthly',
            'discount' => 0,
            'save' => '0%'
        ],
        'quarterly' => [
            'label' => 'Quarterly',
            'discount' => 5,
            'save' => '5%'
        ],
        'semiannually' => [
            'label' => 'Semiannually',
            'discount' => 10,
            'save' => '10%'
        ],
        'annually' => [
            'label' => 'Annually',
            'discount' => 15,
            'save' => '15%'
        ],
        'biennially' => [
            'label' => 'Biennially',
            'discount' => 20,
            'save' => '20%'
        ],
        'triennially' => [
            'label' => 'Triennially',
            'discount' => 25,
            'save' => '25%'
        ]
    ];
}

// Check if a specific product type is in the cart
function isProductTypeInCart($type) {
    initCart();
    
    foreach ($_SESSION['cart'] as $item) {
        if ($item['type'] === $type) {
            return true;
        }
    }
    
    return false;
}

// Get item from cart by ID
function getCartItem($itemId) {
    initCart();
    
    if (isset($_SESSION['cart'][$itemId])) {
        return $_SESSION['cart'][$itemId];
    }
    
    return null;
}

// Validate cart item configuration
function validateCartItemConfig($type, $config) {
    $errors = [];
    
    switch ($type) {
        case 'vps':
            // Validate hostname
            if (empty($config['hostname'])) {
                $errors[] = 'Hostname is required.';
            } elseif (!preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)*$/', $config['hostname'])) {
                $errors[] = 'Hostname format is invalid.';
            }
            
            // Validate server password
            if (empty($config['password'])) {
                $errors[] = 'Server password is required.';
            } elseif (strlen($config['password']) < 8) {
                $errors[] = 'Server password must be at least 8 characters.';
            }
            
            // Validate storage
            if (!isset($config['storage']) || $config['storage'] < 20) {
                $errors[] = 'Storage must be at least 20GB.';
            }
            
            // Validate RAM
            if (!isset($config['ram']) || $config['ram'] < 2) {
                $errors[] = 'RAM must be at least 2GB.';
            }
            
            // Validate CPU cores
            if (!isset($config['cores']) || $config['cores'] < 1) {
                $errors[] = 'CPU cores must be at least 1.';
            }
            
            // Validate IP addresses
            if (!isset($config['ipv4']) || $config['ipv4'] < 1) {
                $errors[] = 'At least 1 IPv4 address is required.';
            }
            
            if (!isset($config['ipv6']) || $config['ipv6'] < 1) {
                $errors[] = 'At least 1 IPv6 address is required.';
            }
            
            // Validate billing period
            if (empty($config['billing_period'])) {
                $errors[] = 'Billing period is required.';
            }
            
            break;
            
        case 'web_hosting':
            // Validate domain
            if (empty($config['domain'])) {
                $errors[] = 'Domain is required.';
            } elseif (!preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)*$/', $config['domain'])) {
                $errors[] = 'Domain format is invalid.';
            }
            
            // Validate password
            if (empty($config['password'])) {
                $errors[] = 'Password is required.';
            } elseif (strlen($config['password']) < 8) {
                $errors[] = 'Password must be at least 8 characters.';
            }
            
            // Validate disk space
            if (!isset($config['disk_space']) || $config['disk_space'] < 5) {
                $errors[] = 'Disk space must be at least 5GB.';
            }
            
            // Validate bandwidth
            if (!isset($config['bandwidth']) || $config['bandwidth'] < 10) {
                $errors[] = 'Bandwidth must be at least 10GB.';
            }
            
            // Validate database count
            if (!isset($config['databases']) || $config['databases'] < 1) {
                $errors[] = 'At least 1 database is required.';
            }
            
            // Validate email accounts
            if (!isset($config['email_accounts']) || $config['email_accounts'] < 1) {
                $errors[] = 'At least 1 email account is required.';
            }
            
            // Validate billing period
            if (empty($config['billing_period'])) {
                $errors[] = 'Billing period is required.';
            }
            
            break;
            
        case 'game_server':
            // Validate game type
            if (empty($config['game_type'])) {
                $errors[] = 'Game type is required.';
            }
            
            // Validate server name
            if (empty($config['server_name'])) {
                $errors[] = 'Server name is required.';
            }
            
            // Validate slots
            if (!isset($config['slots']) || $config['slots'] < 5) {
                $errors[] = 'Slots must be at least 5.';
            }
            
            // Validate location
            if (empty($config['location'])) {
                $errors[] = 'Location is required.';
            }
            
            // Validate RAM
            if (!isset($config['ram']) || $config['ram'] < 1) {
                $errors[] = 'RAM must be at least 1GB.';
            }
            
            // Validate CPU limit
            if (!isset($config['cpu_limit']) || $config['cpu_limit'] < 50) {
                $errors[] = 'CPU limit must be at least 50%.';
            }
            
            // Validate disk space
            if (!isset($config['disk_space']) || $config['disk_space'] < 5) {
                $errors[] = 'Disk space must be at least 5GB.';
            }
            
            // Validate password
            if (empty($config['password'])) {
                $errors[] = 'Password is required.';
            } elseif (strlen($config['password']) < 8) {
                $errors[] = 'Password must be at least 8 characters.';
            }
            
            // Validate billing period
            if (empty($config['billing_period'])) {
                $errors[] = 'Billing period is required.';
            }
            
            break;
    }
    
    return $errors;
}

// Format cart items for display
function formatCartItems() {
    $cartItems = getCartItems();
    $formattedItems = [];
    
    foreach ($cartItems as $itemId => $item) {
        $formattedItem = [
            'id' => $itemId,
            'type' => $item['type'],
            'price' => $item['price'],
            'billing_period' => $item['billing_period'] ?? 'monthly',
            'details' => []
        ];
        
        switch ($item['type']) {
            case 'vps':
                $formattedItem['name'] = 'Virtual Private Server';
                $formattedItem['details'] = [
                    'Hostname: ' . $item['hostname'],
                    'Storage: ' . $item['storage'] . 'GB',
                    'RAM: ' . $item['ram'] . 'GB',
                    'Cores: ' . $item['cores'],
                    'Location: ' . ucfirst($item['location'])
                ];
                break;
                
            case 'web_hosting':
                $formattedItem['name'] = 'Web Hosting';
                $formattedItem['details'] = [
                    'Domain: ' . $item['domain'],
                    'Disk Space: ' . $item['disk_space'] . 'GB',
                    'Bandwidth: ' . $item['bandwidth'] . 'GB',
                    'Databases: ' . $item['databases'],
                    'Email Accounts: ' . $item['email_accounts']
                ];
                break;
                
            case 'game_server':
                $formattedItem['name'] = 'Game Server (' . ucfirst($item['game_type']) . ')';
                $formattedItem['details'] = [
                    'Server Name: ' . $item['server_name'],
                    'Slots: ' . $item['slots'],
                    'RAM: ' . $item['ram'] . 'GB',
                    'CPU Limit: ' . $item['cpu_limit'] . '%',
                    'Location: ' . ucfirst($item['location'])
                ];
                break;
        }
        
        $formattedItems[$itemId] = $formattedItem;
    }
    
    return $formattedItems;
}