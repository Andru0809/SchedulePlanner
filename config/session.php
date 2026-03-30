<?php
// Session management
session_start();

// Set session timeout (30 minutes)
$session_timeout = 1800;

// Check if session is expired
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to get current user ID
function get_current_user_id() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Function to get current user data
function get_logged_in_user() {
    global $conn;
    
    if (!is_logged_in()) {
        return null;
    }
    
    $user_id = get_current_user_id();
    $sql = "SELECT id, username, email, full_name, created_at FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// Function to redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        header("Location: ../index.php");
        exit();
    }
}

?>
