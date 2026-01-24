<?php
// Security and utility functions

// Escape output to prevent XSS
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Generate CSRF token
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Validate date format
function validateDate($date, $format = 'Y-m-d H:i:s') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Get severity color class
function getSeverityColor($severity) {
    switch ($severity) {
        case 'Critical':
            return 'severity-critical';
        case 'High':
            return 'severity-high';
        case 'Medium':
            return 'severity-medium';
        case 'Low':
            return 'severity-low';
        default:
            return '';
    }
}

// Get status color class
function getStatusColor($status) {
    switch ($status) {
        case 'Detected':
            return 'status-detected';
        case 'Investigating':
            return 'status-investigating';
        case 'Resolved':
            return 'status-resolved';
        default:
            return '';
    }
}
?>
