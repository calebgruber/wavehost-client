<?php
// includes/functions.php
// Helper functions for the WaveHost application

require_once 'config.php';
require_once 'db.php';

// Clean user input
function clean($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Generate random string
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $randomString;
}

// Format currency
function formatCurrency($amount, $currency = '€') {
    return $currency . number_format($amount, 2);
}

// Format date
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Check if user is staff
function isStaff() {
    return isset($_SESSION['is_staff']) && $_SESSION['is_staff'] === true;
}

// Redirect to URL
function redirect($url) {
    header("Location: $url");
    exit;
}

// Flash messages
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flashMessage = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flashMessage;
    }
    
    return null;
}

// Display flash messages
function displayFlashMessages() {
    $flashMessage = getFlashMessage();
    
    if ($flashMessage) {
        $type = $flashMessage['type'];
        $message = $flashMessage['message'];
        
        $alertClass = 'alert-info';
        $icon = 'info-circle';
        
        switch ($type) {
            case 'success':
                $alertClass = 'alert-success';
                $icon = 'check-circle';
                break;
            case 'error':
                $alertClass = 'alert-danger';
                $icon = 'exclamation-circle';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                $icon = 'exclamation-triangle';
                break;
        }
        
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show mb-4" role="alert">';
        echo '<div class="d-flex">';
        echo '<div class="me-3"><i class="fas fa-' . $icon . ' fa-lg"></i></div>';
        echo '<div>' . $message . '</div>';
        echo '</div>';
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = db();
    
    return $db->selectOne(
        "SELECT * FROM users WHERE id = ?",
        [$_SESSION['user_id']]
    );
}

// Log activity
function logActivity($userId, $action, $details = null) {
    $db = db();
    
    $db->insert('activity_logs', [
        'user_id' => $userId,
        'action' => $action,
        'details' => $details ? json_encode($details) : null,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
}

// Get user services
function getUserServices($userId, $status = null) {
    $db = db();
    
    $sql = "SELECT s.*, 
                CASE 
                    WHEN s.service_type = 'game_server' THEN 'Game Server'
                    WHEN s.service_type = 'web_hosting' THEN 'Web Hosting'
                    WHEN s.service_type = 'vps' THEN 'Virtual Private Server'
                    ELSE s.service_type
                END as service_type_name
            FROM services s 
            WHERE s.user_id = ?";
    
    $params = [$userId];
    
    if ($status) {
        $sql .= " AND s.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY s.created_at DESC";
    
    return $db->select($sql, $params);
}

// Get service details
function getServiceDetails($serviceId, $serviceType) {
    $db = db();
    
    switch ($serviceType) {
        case 'game_server':
            return $db->selectOne(
                "SELECT gs.*, s.name, s.status, s.billing_cycle, s.amount, s.due_date 
                FROM game_servers gs 
                JOIN services s ON gs.service_id = s.id 
                WHERE s.id = ?",
                [$serviceId]
            );
            
        case 'web_hosting':
            return $db->selectOne(
                "SELECT wh.*, s.name, s.status, s.billing_cycle, s.amount, s.due_date 
                FROM web_hosting wh 
                JOIN services s ON wh.service_id = s.id 
                WHERE s.id = ?",
                [$serviceId]
            );
            
        case 'vps':
            return $db->selectOne(
                "SELECT vh.*, s.name, s.status, s.billing_cycle, s.amount, s.due_date 
                FROM vps_hosting vh 
                JOIN services s ON vh.service_id = s.id 
                WHERE s.id = ?",
                [$serviceId]
            );
            
        default:
            return null;
    }
}

// Get user invoices
function getUserInvoices($userId, $status = null) {
    $db = db();
    
    $sql = "SELECT i.*, s.name as service_name, s.service_type 
            FROM invoices i 
            LEFT JOIN services s ON i.service_id = s.id 
            WHERE i.user_id = ?";
    
    $params = [$userId];
    
    if ($status) {
        $sql .= " AND i.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY i.created_at DESC";
    
    return $db->select($sql, $params);
}

// Get user tickets
function getUserTickets($userId, $status = null) {
    $db = db();
    
    $sql = "SELECT t.*, 
                (SELECT COUNT(*) FROM ticket_replies tr WHERE tr.ticket_id = t.id) as reply_count 
            FROM tickets t 
            WHERE t.user_id = ?";
    
    $params = [$userId];
    
    if ($status) {
        $sql .= " AND t.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY t.updated_at DESC";
    
    return $db->select($sql, $params);
}

// Get ticket replies
function getTicketReplies($ticketId) {
    $db = db();
    
    return $db->select(
        "SELECT tr.*, 
            u.username, u.first_name, u.last_name, 
            s.role as staff_role 
        FROM ticket_replies tr 
        JOIN users u ON tr.user_id = u.id 
        LEFT JOIN staff s ON tr.staff_id = s.id 
        WHERE tr.ticket_id = ? 
        ORDER BY tr.created_at ASC",
        [$ticketId]
    );
}

// Connect to Pterodactyl Panel API
function pterodactylApi($endpoint, $method = 'GET', $data = null) {
    $url = PTERODACTYL_URL . '/api/application/' . $endpoint;
    
    $curl = curl_init();
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . PTERODACTYL_API_KEY,
            'Accept: application/json',
            'Content-Type: application/json',
        ],
    ];
    
    if ($data && ($method === 'POST' || $method === 'PATCH')) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    
    curl_setopt_array($curl, $options);
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    
    return json_decode($response, true);
}

// Get service status badge
function getStatusBadge($status) {
    switch ($status) {
        case 'active':
            return '<span class="badge bg-success-subtle text-success">Active</span>';
        case 'pending':
            return '<span class="badge bg-warning-subtle text-warning">Pending</span>';
        case 'suspended':
            return '<span class="badge bg-danger-subtle text-danger">Suspended</span>';
        case 'terminated':
            return '<span class="badge bg-danger-subtle text-danger">Terminated</span>';
        case 'cancelled':
            return '<span class="badge bg-secondary-subtle text-secondary">Cancelled</span>';
        default:
            return '<span class="badge bg-secondary-subtle text-secondary">' . ucfirst($status) . '</span>';
    }
}

// Format bytes to human readable format
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Get time ago
function timeAgo($timestamp) {
    $time = time() - strtotime($timestamp);
    
    $tokens = [
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    ];
    
    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '') . ' ago';
    }
    
    return 'just now';
}

// Get breadcrumb
function getBreadcrumb() {
    $uri = $_SERVER['REQUEST_URI'];
    $uri = strtok($uri, '?'); // Remove query string
    $segments = array_filter(explode('/', $uri));
    
    $breadcrumb = [];
    $path = '';
    
    $breadcrumb[] = [
        'name' => 'Home',
        'url' => '/',
        'active' => false
    ];
    
    foreach ($segments as $segment) {
        $path .= '/' . $segment;
        
        // Get readable name
        $name = str_replace('-', ' ', $segment);
        $name = ucwords($name);
        
        $breadcrumb[] = [
            'name' => $name,
            'url' => $path,
            'active' => ($path === $uri)
        ];
    }
    
    return $breadcrumb;
}

// Display breadcrumb
function displayBreadcrumb() {
    $breadcrumb = getBreadcrumb();
    
    echo '<nav aria-label="breadcrumb">';
    echo '<ol class="breadcrumb">';
    
    foreach ($breadcrumb as $index => $item) {
        if ($item['active']) {
            echo '<li class="breadcrumb-item active" aria-current="page">' . $item['name'] . '</li>';
        } else {
            echo '<li class="breadcrumb-item"><a href="' . $item['url'] . '">' . $item['name'] . '</a></li>';
        }
    }
    
    echo '</ol>';
    echo '</nav>';
}

// Get unique token for CSRF protection
function getCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        setFlashMessage('error', 'Invalid CSRF token. Please try again.');
        redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}

// Get current page URL
function getCurrentPageURL() {
    $pageURL = 'http';
    
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $pageURL .= 's';
    }
    
    $pageURL .= '://';
    
    if ($_SERVER['SERVER_PORT'] != '80' && $_SERVER['SERVER_PORT'] != '443') {
        $pageURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
    } else {
        $pageURL .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    }
    
    return $pageURL;
}

// Get active menu class
function getActiveClass($path) {
    $currentPath = $_SERVER['REQUEST_URI'];
    $currentPath = strtok($currentPath, '?'); // Remove query string
    
    // Handle home page specially
    if ($path === '/' && $currentPath === '/') {
        return 'active';
    }
    
    // For other pages, check if current path starts with the given path
    if ($path !== '/' && strpos($currentPath, $path) === 0) {
        return 'active';
    }
    
    return '';
}

// Validate password complexity
function validatePasswordComplexity($password) {
    // At least 8 characters
    if (strlen($password) < 8) {
        return false;
    }
    
    // At least one lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    
    // At least one uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    // At least one number
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    // At least one special character
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        return false;
    }
    
    return true;
}

// Generate avatar URL based on user's name
function generateAvatar($name) {
    $name = urlencode($name);
    return "https://ui-avatars.com/api/?name={$name}&background=0984e3&color=fff&size=256";
}