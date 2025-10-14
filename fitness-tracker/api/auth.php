<?php
/**
 * Authentication API Endpoints
 * Handles user registration, login, and authentication
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'register':
            handleRegistration();
            break;
        case 'login':
            handleLogin();
            break;
        case 'logout':
            handleLogout();
            break;
        case 'check':
            handleAuthCheck();
            break;
        case 'profile':
            handleProfile();
            break;
        case 'update_profile':
            handleProfileUpdate();
            break;
        case 'reset_password':
            handlePasswordReset();
            break;
        case 'clean_sessions':
            handleCleanSessions();
            break;
        default:
            handleError('Invalid action', 400);
    }
} catch (Exception $e) {
    handleError($e->getMessage(), 500);
}

/**
 * Handle user registration
 */
function handleRegistration() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Method not allowed', 405);
    }
    
    // Validate CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        handleError('Invalid CSRF token', 403);
    }
    
    // Get and validate input
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $firstName = sanitizeInput($_POST['first_name'] ?? '');
    $lastName = sanitizeInput($_POST['last_name'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters long';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!validateEmail($email)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit();
    }
    
    try {
        // Register user using auth functions
        $result = registerUser($username, $email, $password, $firstName, $lastName);
        
        // Create session
        $sessionResult = createUserSession($result['user_id']);
        
        sendSuccess([
            'user_id' => $result['user_id'],
            'username' => $username,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'session_token' => $sessionResult
        ], 'Registration successful');
        
    } catch (Exception $e) {
        handleError($e->getMessage(), 400);
    }
}

/**
 * Handle user login
 */
function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Method not allowed', 405);
    }
    
    // Validate CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        handleError('Invalid CSRF token', 403);
    }
    
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        handleError('Username and password are required', 400);
    }
    
    try {
        // Authenticate user using auth functions
        $result = authenticateUser($username, $password, $remember);
        
        // Remove sensitive data
        unset($result['user']['password_hash']);
        
        sendSuccess($result['user'], 'Login successful');
        
    } catch (Exception $e) {
        handleError($e->getMessage(), 401);
    }
}

/**
 * Handle user logout
 */
function handleLogout() {
    $sessionToken = $_COOKIE['session_token'] ?? null;
    
    if (logoutUser($sessionToken)) {
        sendSuccess(null, 'Logout successful');
    } else {
        handleError('Logout failed', 500);
    }
}

/**
 * Check authentication status
 */
function handleAuthCheck() {
    $user = getCurrentUser();
    
    if ($user) {
        unset($user['password_hash']);
        sendSuccess($user, 'User is authenticated');
    } else {
        sendSuccess(null, 'User is not authenticated');
    }
}

/**
 * Get user profile
 */
function handleProfile() {
    $user = getCurrentUser();
    
    if (!$user) {
        handleError('Authentication required', 401);
    }
    
    unset($user['password_hash']);
    sendSuccess($user, 'Profile retrieved successfully');
}

/**
 * Update user profile
 */
function handleProfileUpdate() {
    $user = getCurrentUser();
    
    if (!$user) {
        handleError('Authentication required', 401);
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Method not allowed', 405);
    }
    
    // Validate CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        handleError('Invalid CSRF token', 403);
    }
    
    $userId = $user['user_id'];
    $firstName = sanitizeInput($_POST['first_name'] ?? '');
    $lastName = sanitizeInput($_POST['last_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $dateOfBirth = $_POST['date_of_birth'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $height = $_POST['height'] ?? null;
    $weight = $_POST['weight'] ?? null;
    $activityLevel = $_POST['activity_level'] ?? null;
    $fitnessGoal = $_POST['fitness_goal'] ?? null;
    
    // Validate email if provided
    if (!empty($email) && !validateEmail($email)) {
        handleError('Invalid email format', 400);
    }
    
    // Check if email is already taken by another user
    if (!empty($email)) {
        $existingUser = fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $userId]);
        if ($existingUser) {
            handleError('Email already exists', 409);
        }
    }
    
    // Build update query
    $fields = [];
    $params = [];
    
    if (!empty($firstName)) {
        $fields[] = "first_name = ?";
        $params[] = $firstName;
    }
    
    if (!empty($lastName)) {
        $fields[] = "last_name = ?";
        $params[] = $lastName;
    }
    
    if (!empty($email)) {
        $fields[] = "email = ?";
        $params[] = $email;
    }
    
    if (!empty($dateOfBirth)) {
        $fields[] = "date_of_birth = ?";
        $params[] = $dateOfBirth;
    }
    
    if (!empty($gender) && in_array($gender, ['male', 'female', 'other'])) {
        $fields[] = "gender = ?";
        $params[] = $gender;
    }
    
    if (!empty($height) && is_numeric($height)) {
        $fields[] = "height = ?";
        $params[] = $height;
    }
    
    if (!empty($weight) && is_numeric($weight)) {
        $fields[] = "weight = ?";
        $params[] = $weight;
    }
    
    if (!empty($activityLevel) && in_array($activityLevel, ['sedentary', 'light', 'moderate', 'active', 'very_active'])) {
        $fields[] = "activity_level = ?";
        $params[] = $activityLevel;
    }
    
    if (!empty($fitnessGoal) && in_array($fitnessGoal, ['weight_loss', 'muscle_gain', 'maintenance', 'endurance', 'strength'])) {
        $fields[] = "fitness_goal = ?";
        $params[] = $fitnessGoal;
    }
    
    if (empty($fields)) {
        handleError('No valid fields to update', 400);
    }
    
    $fields[] = "updated_at = NOW()";
    $params[] = $userId;
    
    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
    executeQuery($sql, $params);
    
    // Log activity
    logUserActivity($userId, 'profile_update');
    
    // Get updated user
    $updatedUser = fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
    unset($updatedUser['password_hash']);
    
    sendSuccess($updatedUser, 'Profile updated successfully');
}

/**
 * Handle password reset request
 */
function handlePasswordReset() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Method not allowed', 405);
    }
    
    $email = sanitizeInput($_POST['email'] ?? '');
    
    if (empty($email) || !validateEmail($email)) {
        handleError('Valid email is required', 400);
    }
    
    try {
        $token = generatePasswordResetToken($email);
        // In a real application, you would send an email with the reset link
        sendSuccess(['token' => $token], 'Password reset token generated');
        
    } catch (Exception $e) {
        handleError($e->getMessage(), 400);
    }
}

/**
 * Handle cleaning expired sessions
 */
function handleCleanSessions() {
    if (cleanExpiredSessions()) {
        sendSuccess(null, 'Expired sessions cleaned successfully');
    } else {
        handleError('Failed to clean expired sessions', 500);
    }
}
?>
