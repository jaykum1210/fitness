<?php
/**
 * Authentication API Endpoints
 * Handles user registration, login, and authentication
 */

require_once '../includes/config.php';

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
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit();
    }
    
    // Check if user already exists
    $existingUser = fetchOne("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
    if ($existingUser) {
        handleError('Username or email already exists', 409);
    }
    
    // Create user
    $passwordHash = hashPassword($password);
    $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    
    executeQuery($sql, [$username, $email, $passwordHash, $firstName, $lastName]);
    $userId = getLastInsertId();
    
    // Log in the user
    loginUser($userId);
    
    sendSuccess([
        'user_id' => $userId,
        'username' => $username,
        'email' => $email,
        'first_name' => $firstName,
        'last_name' => $lastName
    ], 'Registration successful');
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
    
    // Get user from database
    $user = fetchOne("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1", [$username, $username]);
    
    if (!$user || !verifyPassword($password, $user['password_hash'])) {
        handleError('Invalid username or password', 401);
    }
    
    // Log in the user
    loginUser($user['id']);
    
    // Set remember me cookie if requested
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        // Store token in database (you'd need a remember_tokens table)
    }
    
    // Remove sensitive data
    unset($user['password_hash']);
    
    sendSuccess($user, 'Login successful');
}

/**
 * Handle user logout
 */
function handleLogout() {
    logoutUser();
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    
    sendSuccess(null, 'Logout successful');
}

/**
 * Check authentication status
 */
function handleAuthCheck() {
    if (isLoggedIn()) {
        $user = getLoggedInUser();
        if ($user) {
            unset($user['password_hash']);
            sendSuccess($user, 'User is authenticated');
        }
    }
    
    sendSuccess(null, 'User is not authenticated');
}

/**
 * Get user profile
 */
function handleProfile() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    $user = getLoggedInUser();
    if (!$user) {
        handleError('User not found', 404);
    }
    
    unset($user['password_hash']);
    sendSuccess($user, 'Profile retrieved successfully');
}

/**
 * Update user profile
 */
function handleProfileUpdate() {
    if (!isLoggedIn()) {
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
    
    $userId = $_SESSION['user_id'];
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
    
    // Get updated user
    $user = getLoggedInUser();
    unset($user['password_hash']);
    
    sendSuccess($user, 'Profile updated successfully');
}
?>
