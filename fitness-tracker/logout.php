<?php
require_once 'includes/config.php';

// Handle logout via API
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        logoutUser();
        echo json_encode(['success' => true, 'message' => 'Logout successful']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Logout failed']);
    }
    exit();
}

// Redirect to login page
logoutUser();
redirect('login.php');
?>