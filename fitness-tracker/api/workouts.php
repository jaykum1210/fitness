<?php
/**
 * Workouts API Endpoints
 * Handles workout and exercise management
 */

require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            handleWorkoutList();
            break;
        case 'get':
            handleGetWorkout();
            break;
        case 'categories':
            handleGetCategories();
            break;
        case 'exercises':
            handleGetExercises();
            break;
        case 'start_session':
            handleStartSession();
            break;
        case 'end_session':
            handleEndSession();
            break;
        case 'log_exercise':
            handleLogExercise();
            break;
        case 'favorite':
            handleFavoriteWorkout();
            break;
        case 'unfavorite':
            handleUnfavoriteWorkout();
            break;
        case 'favorites':
            handleGetFavorites();
            break;
        default:
            handleError('Invalid action', 400);
    }
} catch (Exception $e) {
    handleError($e->getMessage(), 500);
}

/**
 * Get list of workouts with optional filtering
 */
function handleWorkoutList() {
    $category = $_GET['category'] ?? '';
    $difficulty = $_GET['difficulty'] ?? '';
    $search = $_GET['search'] ?? '';
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $sql = "SELECT w.*, wc.name as category_name, wc.icon as category_icon 
            FROM workouts w 
            LEFT JOIN workout_categories wc ON w.category_id = wc.id 
            WHERE w.is_active = 1";
    $params = [];
    
    if (!empty($category)) {
        $sql .= " AND wc.name = ?";
        $params[] = $category;
    }
    
    if (!empty($difficulty)) {
        $sql .= " AND w.difficulty_level = ?";
        $params[] = $difficulty;
    }
    
    if (!empty($search)) {
        $sql .= " AND (w.name LIKE ? OR w.description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $sql .= " ORDER BY w.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $workouts = fetchAll($sql, $params);
    
    // Get exercise count for each workout
    foreach ($workouts as &$workout) {
        $exerciseCount = fetchOne("SELECT COUNT(*) as count FROM workout_exercises WHERE workout_id = ?", [$workout['id']]);
        $workout['exercise_count'] = $exerciseCount['count'];
        
        // Check if user has favorited this workout
        if (isLoggedIn()) {
            $favorite = fetchOne("SELECT id FROM user_favorites WHERE user_id = ? AND workout_id = ?", [$_SESSION['user_id'], $workout['id']]);
            $workout['is_favorited'] = $favorite !== false;
        } else {
            $workout['is_favorited'] = false;
        }
    }
    
    sendSuccess($workouts, 'Workouts retrieved successfully');
}

/**
 * Get single workout with exercises
 */
function handleGetWorkout() {
    $workoutId = $_GET['id'] ?? '';
    
    if (empty($workoutId) || !is_numeric($workoutId)) {
        handleError('Invalid workout ID', 400);
    }
    
    $workout = fetchOne("SELECT w.*, wc.name as category_name, wc.icon as category_icon 
                        FROM workouts w 
                        LEFT JOIN workout_categories wc ON w.category_id = wc.id 
                        WHERE w.id = ? AND w.is_active = 1", [$workoutId]);
    
    if (!$workout) {
        handleError('Workout not found', 404);
    }
    
    // Get exercises for this workout
    $exercises = fetchAll("SELECT e.*, we.sets, we.reps, we.weight, we.rest_seconds, we.order_index
                          FROM workout_exercises we
                          JOIN exercises e ON we.exercise_id = e.id
                          WHERE we.workout_id = ? AND e.is_active = 1
                          ORDER BY we.order_index", [$workoutId]);
    
    $workout['exercises'] = $exercises;
    
    // Check if user has favorited this workout
    if (isLoggedIn()) {
        $favorite = fetchOne("SELECT id FROM user_favorites WHERE user_id = ? AND workout_id = ?", [$_SESSION['user_id'], $workoutId]);
        $workout['is_favorited'] = $favorite !== false;
    } else {
        $workout['is_favorited'] = false;
    }
    
    sendSuccess($workout, 'Workout retrieved successfully');
}

/**
 * Get workout categories
 */
function handleGetCategories() {
    $categories = fetchAll("SELECT * FROM workout_categories ORDER BY name");
    sendSuccess($categories, 'Categories retrieved successfully');
}

/**
 * Get exercises
 */
function handleGetExercises() {
    $muscleGroup = $_GET['muscle_group'] ?? '';
    $difficulty = $_GET['difficulty'] ?? '';
    $search = $_GET['search'] ?? '';
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $sql = "SELECT * FROM exercises WHERE is_active = 1";
    $params = [];
    
    if (!empty($muscleGroup)) {
        $sql .= " AND JSON_CONTAINS(muscle_groups, ?)";
        $params[] = json_encode($muscleGroup);
    }
    
    if (!empty($difficulty)) {
        $sql .= " AND difficulty_level = ?";
        $params[] = $difficulty;
    }
    
    if (!empty($search)) {
        $sql .= " AND (name LIKE ? OR description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $sql .= " ORDER BY name LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $exercises = fetchAll($sql, $params);
    
    // Parse muscle groups JSON
    foreach ($exercises as &$exercise) {
        $exercise['muscle_groups'] = json_decode($exercise['muscle_groups'], true);
    }
    
    sendSuccess($exercises, 'Exercises retrieved successfully');
}

/**
 * Start a workout session
 */
function handleStartSession() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Method not allowed', 405);
    }
    
    $workoutId = $_POST['workout_id'] ?? '';
    
    if (empty($workoutId) || !is_numeric($workoutId)) {
        handleError('Invalid workout ID', 400);
    }
    
    // Verify workout exists
    $workout = fetchOne("SELECT * FROM workouts WHERE id = ? AND is_active = 1", [$workoutId]);
    if (!$workout) {
        handleError('Workout not found', 404);
    }
    
    // Create workout session
    $sql = "INSERT INTO workout_sessions (user_id, workout_id, started_at) VALUES (?, ?, NOW())";
    executeQuery($sql, [$_SESSION['user_id'], $workoutId]);
    $sessionId = getLastInsertId();
    
    sendSuccess(['session_id' => $sessionId], 'Workout session started');
}

/**
 * End a workout session
 */
function handleEndSession() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Method not allowed', 405);
    }
    
    $sessionId = $_POST['session_id'] ?? '';
    $duration = (int)($_POST['duration'] ?? 0);
    $calories = (int)($_POST['calories'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    if (empty($sessionId) || !is_numeric($sessionId)) {
        handleError('Invalid session ID', 400);
    }
    
    // Verify session belongs to user
    $session = fetchOne("SELECT * FROM workout_sessions WHERE id = ? AND user_id = ?", [$sessionId, $_SESSION['user_id']]);
    if (!$session) {
        handleError('Session not found', 404);
    }
    
    // Update session
    $sql = "UPDATE workout_sessions SET 
            completed_at = NOW(), 
            duration_minutes = ?, 
            calories_burned = ?, 
            rating = ?, 
            notes = ? 
            WHERE id = ?";
    
    executeQuery($sql, [$duration, $calories, $rating, $notes, $sessionId]);
    
    sendSuccess(null, 'Workout session completed');
}

/**
 * Log individual exercise performance
 */
function handleLogExercise() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Method not allowed', 405);
    }
    
    $sessionId = $_POST['session_id'] ?? '';
    $exerciseId = $_POST['exercise_id'] ?? '';
    $setsCompleted = (int)($_POST['sets_completed'] ?? 0);
    $repsCompleted = sanitizeInput($_POST['reps_completed'] ?? '');
    $weightUsed = $_POST['weight_used'] ?? null;
    $durationSeconds = (int)($_POST['duration_seconds'] ?? 0);
    $restTakenSeconds = (int)($_POST['rest_taken_seconds'] ?? 0);
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    if (empty($sessionId) || !is_numeric($sessionId)) {
        handleError('Invalid session ID', 400);
    }
    
    if (empty($exerciseId) || !is_numeric($exerciseId)) {
        handleError('Invalid exercise ID', 400);
    }
    
    // Verify session belongs to user
    $session = fetchOne("SELECT * FROM workout_sessions WHERE id = ? AND user_id = ?", [$sessionId, $_SESSION['user_id']]);
    if (!$session) {
        handleError('Session not found', 404);
    }
    
    // Create exercise log
    $sql = "INSERT INTO exercise_logs 
            (session_id, exercise_id, sets_completed, reps_completed, weight_used, duration_seconds, rest_taken_seconds, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    executeQuery($sql, [$sessionId, $exerciseId, $setsCompleted, $repsCompleted, $weightUsed, $durationSeconds, $restTakenSeconds, $notes]);
    $logId = getLastInsertId();
    
    sendSuccess(['log_id' => $logId], 'Exercise logged successfully');
}

/**
 * Add workout to favorites
 */
function handleFavoriteWorkout() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Method not allowed', 405);
    }
    
    $workoutId = $_POST['workout_id'] ?? '';
    
    if (empty($workoutId) || !is_numeric($workoutId)) {
        handleError('Invalid workout ID', 400);
    }
    
    // Check if already favorited
    $existing = fetchOne("SELECT id FROM user_favorites WHERE user_id = ? AND workout_id = ?", [$_SESSION['user_id'], $workoutId]);
    if ($existing) {
        handleError('Workout already in favorites', 409);
    }
    
    // Add to favorites
    $sql = "INSERT INTO user_favorites (user_id, workout_id) VALUES (?, ?)";
    executeQuery($sql, [$_SESSION['user_id'], $workoutId]);
    
    sendSuccess(null, 'Workout added to favorites');
}

/**
 * Remove workout from favorites
 */
function handleUnfavoriteWorkout() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Method not allowed', 405);
    }
    
    $workoutId = $_POST['workout_id'] ?? '';
    
    if (empty($workoutId) || !is_numeric($workoutId)) {
        handleError('Invalid workout ID', 400);
    }
    
    // Remove from favorites
    $sql = "DELETE FROM user_favorites WHERE user_id = ? AND workout_id = ?";
    executeQuery($sql, [$_SESSION['user_id'], $workoutId]);
    
    sendSuccess(null, 'Workout removed from favorites');
}

/**
 * Get user's favorite workouts
 */
function handleGetFavorites() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    $favorites = fetchAll("SELECT w.*, wc.name as category_name, wc.icon as category_icon
                          FROM user_favorites uf
                          JOIN workouts w ON uf.workout_id = w.id
                          LEFT JOIN workout_categories wc ON w.category_id = wc.id
                          WHERE uf.user_id = ? AND w.is_active = 1
                          ORDER BY uf.created_at DESC", [$_SESSION['user_id']]);
    
    sendSuccess($favorites, 'Favorites retrieved successfully');
}
?>
