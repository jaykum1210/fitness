<?php
session_start();

// Include database connection
require_once __DIR__ . '/database.php';

// Backward compatibility - JSON file paths
define('DATA_DIR', __DIR__ . '/../data/');
define('USERS_FILE', DATA_DIR . 'users.json');
define('TRACKER_FILE', DATA_DIR . 'tracker.json');
define('WORKOUTS_FILE', DATA_DIR . 'workouts.json');

/**
 * JSON file functions (for backward compatibility)
 */
function readJSON($file) {
    if (!file_exists($file)) {
        return [];
    }
    $content = file_get_contents($file);
    return json_decode($content, true) ?: [];
}

function writeJSON($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

/**
 * Authentication functions
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getLoggedInUser() {
    if (!isLoggedIn()) return null;
    
    try {
        $user = fetchOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [$_SESSION['user_id']]);
        return $user;
    } catch (Exception $e) {
        // Fallback to JSON if database fails
        $users = readJSON(USERS_FILE);
        foreach ($users as $user) {
            if ($user['id'] == $_SESSION['user_id']) {
                return $user;
            }
        }
        return null;
    }
}

function loginUser($userId) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['login_time'] = time();
}

function logoutUser() {
    session_destroy();
    session_start();
}

function redirect($page) {
    header("Location: $page");
    exit();
}

/**
 * Security functions
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Utility functions
 */
function formatDate($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    return floor($time/31536000) . ' years ago';
}

function calculateBMI($weight, $height) {
    if ($height <= 0 || $weight <= 0) return 0;
    return round($weight / (($height / 100) ** 2), 1);
}

function getBMICategory($bmi) {
    if ($bmi < 18.5) return 'Underweight';
    if ($bmi < 25) return 'Normal weight';
    if ($bmi < 30) return 'Overweight';
    return 'Obese';
}

function calculateCalories($weight, $height, $age, $gender, $activity_level) {
    // Basal Metabolic Rate calculation
    if ($gender === 'male') {
        $bmr = 88.362 + (13.397 * $weight) + (4.799 * $height) - (5.677 * $age);
    } else {
        $bmr = 447.593 + (9.247 * $weight) + (3.098 * $height) - (4.330 * $age);
    }
    
    // Activity level multipliers
    $multipliers = [
        'sedentary' => 1.2,
        'light' => 1.375,
        'moderate' => 1.55,
        'active' => 1.725,
        'very_active' => 1.9
    ];
    
    return round($bmr * ($multipliers[$activity_level] ?? 1.2));
}

/**
 * Error handling
 */
function handleError($message, $code = 500) {
    http_response_code($code);
    if (defined('DEBUG') && DEBUG) {
        echo json_encode(['error' => $message]);
    } else {
        echo json_encode(['error' => 'An error occurred']);
    }
    exit();
}

/**
 * Success response
 */
function sendSuccess($data = null, $message = 'Success') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

/**
 * Check if database is available
 */
function isDatabaseAvailable() {
    try {
        return testConnection();
    } catch (Exception $e) {
        return false;
    }
}
?>