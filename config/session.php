<?php
// session.php - Start and manage sessions

// Ensure session settings are applied before session_start()
if (session_status() == PHP_SESSION_NONE) {
    // Secure session cookie settings (set before session_start)
    ini_set('session.cookie_secure', '1');  // Only send cookies over HTTPS (optional, based on your setup)
    ini_set('session.cookie_httponly', '1'); // Restrict access to cookies to HTTP only (for security)
    ini_set('session.cookie_samesite', 'Strict'); // Prevent CSRF attacks
    
    session_start(); // Start the session
}

// Session timeout after 30 minutes of inactivity
$timeout_duration = 1800; // 30 minutes in seconds

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset(); // Unset session variables
    session_destroy(); // Destroy session data
    header("Location: login.php?timeout=true"); // Redirect to login page with timeout flag
    exit();
}

$_SESSION['last_activity'] = time(); // Update the last activity time
?>