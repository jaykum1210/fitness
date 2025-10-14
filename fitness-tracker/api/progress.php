<?php
/**
 * Progress Tracking API Endpoints
 * Handles user progress, goals, and analytics
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
        case 'dashboard':
            handleDashboard();
            break;
        case 'workout_history':
            handleWorkoutHistory();
            break;
        case 'progress_entries':
            handleProgressEntries();
            break;
        case 'add_progress':
            handleAddProgress();
            break;
        case 'goals':
            handleGoals();
            break;
        case 'add_goal':
            handleAddGoal();
            break;
        case 'update_goal':
            handleUpdateGoal();
            break;
        case 'delete_goal':
            handleDeleteGoal();
            break;
        case 'stats':
            handleStats();
            break;
        case 'challenges':
            handleChallenges();
            break;
        case 'join_challenge':
            handleJoinChallenge();
            break;
        case 'update_challenge_progress':
            handleUpdateChallengeProgress();
            break;
        default:
            handleError('Invalid action', 400);
    }
} catch (Exception $e) {
    handleError($e->getMessage(), 500);
}

/**
 * Get dashboard data
 */
function handleDashboard() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    $userId = $_SESSION['user_id'];
    
    // Get recent workouts
    $recentWorkouts = fetchAll("SELECT ws.*, w.name as workout_name, w.image_url
                               FROM workout_sessions ws
                               JOIN workouts w ON ws.workout_id = w.id
                               WHERE ws.user_id = ? AND ws.completed_at IS NOT NULL
                               ORDER BY ws.completed_at DESC
                               LIMIT 5", [$userId]);
    
    // Get active goals
    $activeGoals = fetchAll("SELECT * FROM user_goals WHERE user_id = ? AND is_active = 1 ORDER BY created_at DESC", [$userId]);
    
    // Get current challenges
    $challenges = fetchAll("SELECT c.*, uc.progress_value, uc.is_completed
                           FROM user_challenges uc
                           JOIN challenges c ON uc.challenge_id = c.id
                           WHERE uc.user_id = ? AND c.is_active = 1
                           ORDER BY c.end_date ASC", [$userId]);
    
    // Get latest progress entry
    $latestProgress = fetchOne("SELECT * FROM user_progress WHERE user_id = ? ORDER BY measurement_date DESC LIMIT 1", [$userId]);
    
    // Calculate stats
    $totalWorkouts = fetchOne("SELECT COUNT(*) as count FROM workout_sessions WHERE user_id = ? AND completed_at IS NOT NULL", [$userId]);
    $totalCalories = fetchOne("SELECT SUM(calories_burned) as total FROM workout_sessions WHERE user_id = ? AND completed_at IS NOT NULL", [$userId]);
    $totalDuration = fetchOne("SELECT SUM(duration_minutes) as total FROM workout_sessions WHERE user_id = ? AND completed_at IS NOT NULL", [$userId]);
    
    $dashboard = [
        'recent_workouts' => $recentWorkouts,
        'active_goals' => $activeGoals,
        'challenges' => $challenges,
        'latest_progress' => $latestProgress,
        'stats' => [
            'total_workouts' => $totalWorkouts['count'] ?? 0,
            'total_calories' => $totalCalories['total'] ?? 0,
            'total_duration' => $totalDuration['total'] ?? 0
        ]
    ];
    
    sendSuccess($dashboard, 'Dashboard data retrieved successfully');
}

/**
 * Get workout history
 */
function handleWorkoutHistory() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    $userId = $_SESSION['user_id'];
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $workouts = fetchAll("SELECT ws.*, w.name as workout_name, w.image_url, w.difficulty_level
                         FROM workout_sessions ws
                         JOIN workouts w ON ws.workout_id = w.id
                         WHERE ws.user_id = ? AND ws.completed_at IS NOT NULL
                         ORDER BY ws.completed_at DESC
                         LIMIT ? OFFSET ?", [$userId, $limit, $offset]);
    
    // Get exercise logs for each workout
    foreach ($workouts as &$workout) {
        $exerciseLogs = fetchAll("SELECT el.*, e.name as exercise_name
                                 FROM exercise_logs el
                                 JOIN exercises e ON el.exercise_id = e.id
                                 WHERE el.session_id = ?
                                 ORDER BY el.created_at", [$workout['id']]);
        $workout['exercise_logs'] = $exerciseLogs;
    }
    
    sendSuccess($workouts, 'Workout history retrieved successfully');
}

/**
 * Get progress entries
 */
function handleProgressEntries() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    $userId = $_SESSION['user_id'];
    $limit = (int)($_GET['limit'] ?? 30);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $entries = fetchAll("SELECT * FROM user_progress 
                        WHERE user_id = ? 
                        ORDER BY measurement_date DESC 
                        LIMIT ? OFFSET ?", [$userId, $limit, $offset]);
    
    // Parse measurements JSON
    foreach ($entries as &$entry) {
        $entry['measurements'] = json_decode($entry['measurements'], true);
    }
    
    sendSuccess($entries, 'Progress entries retrieved successfully');
}

/**
 * Add progress entry
 */
function handleAddProgress() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Method not allowed', 405);
    }
    
    $userId = $_SESSION['user_id'];
    $measurementDate = $_POST['measurement_date'] ?? date('Y-m-d');
    $weight = $_POST['weight'] ?? null;
    $bodyFatPercentage = $_POST['body_fat_percentage'] ?? null;
    $muscleMass = $_POST['muscle_mass'] ?? null;
    $measurements = $_POST['measurements'] ?? [];
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    // Validate date
    if (!strtotime($measurementDate)) {
        handleError('Invalid date format', 400);
    }
    
    // Check if entry already exists for this date
    $existing = fetchOne("SELECT id FROM user_progress WHERE user_id = ? AND measurement_date = ?", [$userId, $measurementDate]);
    if ($existing) {
        handleError('Progress entry already exists for this date', 409);
    }
    
    // Convert measurements to JSON
    $measurementsJson = json_encode($measurements);
    
    $sql = "INSERT INTO user_progress (user_id, measurement_date, weight, body_fat_percentage, muscle_mass, measurements, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    executeQuery($sql, [$userId, $measurementDate, $weight, $bodyFatPercentage, $muscleMass, $measurementsJson, $notes]);
    $entryId = getLastInsertId();
    
    sendSuccess(['entry_id' => $entryId], 'Progress entry added successfully');
}

/**
 * Get user goals
 */
function handleGoals() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    $userId = $_SESSION['user_id'];
    $active = $_GET['active'] ?? null;
    
    $sql = "SELECT * FROM user_goals WHERE user_id = ?";
    $params = [$userId];
    
    if ($active !== null) {
        $sql .= " AND is_active = ?";
        $params[] = $active ? 1 : 0;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $goals = fetchAll($sql, $params);
    
    sendSuccess($goals, 'Goals retrieved successfully');
}

/**
 * Add new goal
 */
function handleAddGoal() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Method not allowed', 405);
    }
    
    $userId = $_SESSION['user_id'];
    $goalType = $_POST['goal_type'] ?? '';
    $targetValue = $_POST['target_value'] ?? 0;
    $currentValue = $_POST['current_value'] ?? 0;
    $unit = $_POST['unit'] ?? '';
    $targetDate = $_POST['target_date'] ?? null;
    $description = sanitizeInput($_POST['description'] ?? '');
    
    // Validate goal type
    $validTypes = ['weight_loss', 'weight_gain', 'muscle_gain', 'endurance', 'strength', 'flexibility'];
    if (!in_array($goalType, $validTypes)) {
        handleError('Invalid goal type', 400);
    }
    
    // Validate target date
    if ($targetDate && !strtotime($targetDate)) {
        handleError('Invalid target date format', 400);
    }
    
    $sql = "INSERT INTO user_goals (user_id, goal_type, target_value, current_value, unit, target_date, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    executeQuery($sql, [$userId, $goalType, $targetValue, $currentValue, $unit, $targetDate, $description]);
    $goalId = getLastInsertId();
    
    sendSuccess(['goal_id' => $goalId], 'Goal added successfully');
}

/**
 * Update goal
 */
function handleUpdateGoal() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Method not allowed', 405);
    }
    
    $userId = $_SESSION['user_id'];
    $goalId = $_POST['goal_id'] ?? '';
    $currentValue = $_POST['current_value'] ?? null;
    $targetValue = $_POST['target_value'] ?? null;
    $targetDate = $_POST['target_date'] ?? null;
    $description = $_POST['description'] ?? null;
    $isActive = $_POST['is_active'] ?? null;
    
    if (empty($goalId) || !is_numeric($goalId)) {
        handleError('Invalid goal ID', 400);
    }
    
    // Verify goal belongs to user
    $goal = fetchOne("SELECT * FROM user_goals WHERE id = ? AND user_id = ?", [$goalId, $userId]);
    if (!$goal) {
        handleError('Goal not found', 404);
    }
    
    // Build update query
    $fields = [];
    $params = [];
    
    if ($currentValue !== null) {
        $fields[] = "current_value = ?";
        $params[] = $currentValue;
    }
    
    if ($targetValue !== null) {
        $fields[] = "target_value = ?";
        $params[] = $targetValue;
    }
    
    if ($targetDate !== null) {
        if (!strtotime($targetDate)) {
            handleError('Invalid target date format', 400);
        }
        $fields[] = "target_date = ?";
        $params[] = $targetDate;
    }
    
    if ($description !== null) {
        $fields[] = "description = ?";
        $params[] = sanitizeInput($description);
    }
    
    if ($isActive !== null) {
        $fields[] = "is_active = ?";
        $params[] = $isActive ? 1 : 0;
    }
    
    if (empty($fields)) {
        handleError('No fields to update', 400);
    }
    
    $fields[] = "updated_at = NOW()";
    $params[] = $goalId;
    
    $sql = "UPDATE user_goals SET " . implode(', ', $fields) . " WHERE id = ?";
    executeQuery($sql, $params);
    
    sendSuccess(null, 'Goal updated successfully');
}

/**
 * Delete goal
 */
function handleDeleteGoal() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Method not allowed', 405);
    }
    
    $userId = $_SESSION['user_id'];
    $goalId = $_POST['goal_id'] ?? '';
    
    if (empty($goalId) || !is_numeric($goalId)) {
        handleError('Invalid goal ID', 400);
    }
    
    // Verify goal belongs to user
    $goal = fetchOne("SELECT * FROM user_goals WHERE id = ? AND user_id = ?", [$goalId, $userId]);
    if (!$goal) {
        handleError('Goal not found', 404);
    }
    
    // Delete goal
    executeQuery("DELETE FROM user_goals WHERE id = ?", [$goalId]);
    
    sendSuccess(null, 'Goal deleted successfully');
}

/**
 * Get user statistics
 */
function handleStats() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    $userId = $_SESSION['user_id'];
    $period = $_GET['period'] ?? '30'; // days
    
    $startDate = date('Y-m-d', strtotime("-$period days"));
    
    // Workout statistics
    $workoutStats = fetchOne("SELECT 
                             COUNT(*) as total_workouts,
                             SUM(duration_minutes) as total_duration,
                             SUM(calories_burned) as total_calories,
                             AVG(rating) as avg_rating
                             FROM workout_sessions 
                             WHERE user_id = ? AND completed_at IS NOT NULL AND DATE(completed_at) >= ?", 
                             [$userId, $startDate]);
    
    // Progress statistics
    $progressStats = fetchAll("SELECT measurement_date, weight, body_fat_percentage, muscle_mass
                              FROM user_progress 
                              WHERE user_id = ? AND measurement_date >= ?
                              ORDER BY measurement_date", 
                              [$userId, $startDate]);
    
    // Goal progress
    $goalProgress = fetchAll("SELECT goal_type, target_value, current_value, unit, target_date
                             FROM user_goals 
                             WHERE user_id = ? AND is_active = 1", 
                             [$userId]);
    
    $stats = [
        'workout_stats' => $workoutStats,
        'progress_data' => $progressStats,
        'goal_progress' => $goalProgress,
        'period_days' => $period
    ];
    
    sendSuccess($stats, 'Statistics retrieved successfully');
}

/**
 * Get available challenges
 */
function handleChallenges() {
    $active = $_GET['active'] ?? true;
    $limit = (int)($_GET['limit'] ?? 10);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $sql = "SELECT * FROM challenges WHERE is_active = 1";
    $params = [];
    
    if ($active) {
        $sql .= " AND (start_date IS NULL OR start_date <= CURDATE()) AND (end_date IS NULL OR end_date >= CURDATE())";
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $challenges = fetchAll($sql, $params);
    
    // Add user participation status if logged in
    if (isLoggedIn()) {
        $userId = $_SESSION['user_id'];
        foreach ($challenges as &$challenge) {
            $participation = fetchOne("SELECT * FROM user_challenges WHERE user_id = ? AND challenge_id = ?", 
                                     [$userId, $challenge['id']]);
            $challenge['is_joined'] = $participation !== false;
            $challenge['user_progress'] = $participation ? $participation['progress_value'] : 0;
            $challenge['is_completed'] = $participation ? $participation['is_completed'] : false;
        }
    }
    
    sendSuccess($challenges, 'Challenges retrieved successfully');
}

/**
 * Join a challenge
 */
function handleJoinChallenge() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Method not allowed', 405);
    }
    
    $userId = $_SESSION['user_id'];
    $challengeId = $_POST['challenge_id'] ?? '';
    
    if (empty($challengeId) || !is_numeric($challengeId)) {
        handleError('Invalid challenge ID', 400);
    }
    
    // Verify challenge exists and is active
    $challenge = fetchOne("SELECT * FROM challenges WHERE id = ? AND is_active = 1", [$challengeId]);
    if (!$challenge) {
        handleError('Challenge not found', 404);
    }
    
    // Check if already joined
    $existing = fetchOne("SELECT id FROM user_challenges WHERE user_id = ? AND challenge_id = ?", [$userId, $challengeId]);
    if ($existing) {
        handleError('Already joined this challenge', 409);
    }
    
    // Join challenge
    $sql = "INSERT INTO user_challenges (user_id, challenge_id, joined_at) VALUES (?, ?, NOW())";
    executeQuery($sql, [$userId, $challengeId]);
    
    sendSuccess(null, 'Challenge joined successfully');
}

/**
 * Update challenge progress
 */
function handleUpdateChallengeProgress() {
    if (!isLoggedIn()) {
        handleError('Authentication required', 401);
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Method not allowed', 405);
    }
    
    $userId = $_SESSION['user_id'];
    $challengeId = $_POST['challenge_id'] ?? '';
    $progressValue = $_POST['progress_value'] ?? 0;
    
    if (empty($challengeId) || !is_numeric($challengeId)) {
        handleError('Invalid challenge ID', 400);
    }
    
    // Verify user is participating
    $participation = fetchOne("SELECT * FROM user_challenges WHERE user_id = ? AND challenge_id = ?", [$userId, $challengeId]);
    if (!$participation) {
        handleError('Not participating in this challenge', 404);
    }
    
    // Get challenge details
    $challenge = fetchOne("SELECT * FROM challenges WHERE id = ?", [$challengeId]);
    
    // Check if completed
    $isCompleted = $progressValue >= $challenge['target_value'];
    
    // Update progress
    $sql = "UPDATE user_challenges SET 
            progress_value = ?, 
            is_completed = ?, 
            completed_at = " . ($isCompleted ? "NOW()" : "NULL") . "
            WHERE user_id = ? AND challenge_id = ?";
    
    executeQuery($sql, [$progressValue, $isCompleted ? 1 : 0, $userId, $challengeId]);
    
    $message = $isCompleted ? 'Challenge completed!' : 'Progress updated successfully';
    sendSuccess(['is_completed' => $isCompleted], $message);
}
?>
