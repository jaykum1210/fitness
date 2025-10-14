<?php
$pageTitle = 'Workout Tracker - Fitness Tracker';
include 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getLoggedInUser();
if (!$user) {
    redirect('login.php');
}

// Get current session if provided
$currentSession = null;
$currentWorkout = null;
$sessionId = $_GET['session_id'] ?? '';

if (!empty($sessionId) && is_numeric($sessionId) && isDatabaseAvailable()) {
    try {
        $currentSession = fetchOne("SELECT * FROM workout_sessions WHERE id = ? AND user_id = ?", [$sessionId, $user['id']]);
        if ($currentSession) {
            $currentWorkout = fetchOne("SELECT w.*, wc.name as category_name 
                                       FROM workouts w 
                                       LEFT JOIN workout_categories wc ON w.category_id = wc.id 
                                       WHERE w.id = ?", [$currentSession['workout_id']]);
        }
    } catch (Exception $e) {
        $currentSession = null;
        $currentWorkout = null;
    }
}

// Get available workouts for starting new sessions
$availableWorkouts = [];
if (isDatabaseAvailable()) {
    try {
        $availableWorkouts = fetchAll("SELECT w.*, wc.name as category_name, wc.icon as category_icon 
                                      FROM workouts w 
                                      LEFT JOIN workout_categories wc ON w.category_id = wc.id 
                                      WHERE w.is_active = 1 
                                      ORDER BY w.name");
    } catch (Exception $e) {
        $availableWorkouts = [];
    }
}
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Workout Tracker</h1>
        <p style="text-align: center; color: var(--text-light); margin-bottom: 3rem;">Track your workouts and monitor your progress in real-time.</p>
        
        <?php if ($currentSession && $currentWorkout): ?>
            <!-- Active Workout Session -->
            <div class="active-session fade-in stagger-1">
                <div class="session-header">
                    <h2><?php echo htmlspecialchars($currentWorkout['name']); ?></h2>
                    <div class="session-meta">
                        <span>Started: <?php echo formatDate($currentSession['started_at'], 'H:i'); ?></span>
                        <span id="sessionDuration">Duration: 0:00</span>
                    </div>
                </div>
                
                <div class="workout-exercises">
                    <?php
                    // Get exercises for this workout
                    $exercises = [];
                    if (isDatabaseAvailable()) {
                        try {
                            $exercises = fetchAll("SELECT e.*, we.sets, we.reps, we.weight, we.rest_seconds, we.order_index
                                                  FROM workout_exercises we
                                                  JOIN exercises e ON we.exercise_id = e.id
                                                  WHERE we.workout_id = ? AND e.is_active = 1
                                                  ORDER BY we.order_index", [$currentWorkout['id']]);
                        } catch (Exception $e) {
                            $exercises = [];
                        }
                    }
                    ?>
                    
                    <?php if (!empty($exercises)): ?>
                        <?php foreach ($exercises as $exercise): ?>
                            <div class="exercise-tracker" data-exercise-id="<?php echo $exercise['id']; ?>">
                                <div class="exercise-header">
                                    <h3><?php echo htmlspecialchars($exercise['name']); ?></h3>
                                    <div class="exercise-target">
                                        <span>Target: <?php echo $exercise['sets']; ?> sets × <?php echo $exercise['reps']; ?> reps</span>
                                        <?php if ($exercise['weight']): ?>
                                            <span>@ <?php echo $exercise['weight']; ?> kg</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="exercise-description">
                                    <p><?php echo htmlspecialchars($exercise['description']); ?></p>
                                </div>
                                
                                <div class="sets-tracker">
                                    <h4>Sets Completed</h4>
                                    <div class="sets-list" id="sets-<?php echo $exercise['id']; ?>">
                                        <!-- Sets will be added dynamically -->
                                    </div>
                                    <button onclick="addSet(<?php echo $exercise['id']; ?>)" class="btn btn-primary">Add Set</button>
                                </div>
                                
                                <div class="exercise-actions">
                                    <button onclick="completeExercise(<?php echo $exercise['id']; ?>)" class="btn btn-success">Mark Complete</button>
                                    <button onclick="skipExercise(<?php echo $exercise['id']; ?>)" class="btn btn-secondary">Skip</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No exercises found for this workout.</p>
                    <?php endif; ?>
                </div>
                
                <div class="session-actions">
                    <button onclick="finishWorkout()" class="btn btn-primary">Finish Workout</button>
                    <button onclick="pauseWorkout()" class="btn btn-secondary">Pause</button>
                </div>
            </div>
            
            <!-- Workout Summary Modal -->
            <div id="workoutSummaryModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <h3>Workout Complete!</h3>
                    <form id="workoutSummaryForm">
                        <div class="form-group">
                            <label for="duration">Duration (minutes)</label>
                            <input type="number" id="duration" name="duration" required>
                        </div>
                        <div class="form-group">
                            <label for="calories">Calories Burned</label>
                            <input type="number" id="calories" name="calories" value="<?php echo $currentWorkout['calories_burned']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="rating">Rating (1-5)</label>
                            <select id="rating" name="rating" required>
                                <option value="">Select rating</option>
                                <option value="1">1 - Poor</option>
                                <option value="2">2 - Fair</option>
                                <option value="3">3 - Good</option>
                                <option value="4">4 - Very Good</option>
                                <option value="5">5 - Excellent</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" rows="3" placeholder="How did the workout feel? Any notes..."></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save Workout</button>
                        </div>
                    </form>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Start New Workout -->
            <div class="start-workout fade-in stagger-1">
                <h2>Start a New Workout</h2>
                <p>Choose a workout to begin tracking your session.</p>
                
                <?php if (!empty($availableWorkouts)): ?>
                    <div class="workouts-grid">
                        <?php foreach ($availableWorkouts as $workout): ?>
                            <div class="workout-card">
                                <div class="workout-info">
                                    <h3><?php echo htmlspecialchars($workout['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($workout['description']); ?></p>
                                    <div class="workout-meta">
                                        <span><?php echo $workout['duration_minutes']; ?> min</span>
                                        <span><?php echo ucfirst($workout['difficulty_level']); ?></span>
                                        <span><?php echo $workout['calories_burned']; ?> cal</span>
                                    </div>
                                </div>
                                <button onclick="startWorkout(<?php echo $workout['id']; ?>)" class="btn btn-primary">Start Workout</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No workouts available at the moment.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.active-session {
    background: var(--bg);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.session-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border);
}

.session-meta {
    display: flex;
    gap: 1rem;
    color: var(--text-light);
}

.workout-exercises {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.exercise-tracker {
    background: var(--bg-gray);
    padding: 1.5rem;
    border-radius: 12px;
    border-left: 4px solid var(--primary);
}

.exercise-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.exercise-target {
    display: flex;
    gap: 1rem;
    color: var(--text-light);
    font-size: 0.875rem;
}

.sets-tracker {
    margin: 1rem 0;
}

.sets-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin: 1rem 0;
}

.set-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.5rem;
    background: white;
    border-radius: 6px;
}

.set-item input {
    width: 60px;
    padding: 0.25rem;
    border: 1px solid var(--border);
    border-radius: 4px;
}

.exercise-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.session-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid var(--border);
}

.start-workout {
    text-align: center;
}

.workouts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.workout-card {
    background: var(--bg);
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    text-align: left;
}

.workout-info h3 {
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.workout-meta {
    display: flex;
    gap: 1rem;
    margin: 1rem 0;
    font-size: 0.875rem;
    color: var(--text-light);
}

.workout-meta span {
    background: var(--bg-gray);
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
}
</style>

<script>
let sessionStartTime = new Date();
let sessionTimer;

// Start timer when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('sessionDuration')) {
        sessionTimer = setInterval(updateSessionDuration, 1000);
    }
});

// Update session duration
function updateSessionDuration() {
    const now = new Date();
    const duration = Math.floor((now - sessionStartTime) / 1000);
    const minutes = Math.floor(duration / 60);
    const seconds = duration % 60;
    document.getElementById('sessionDuration').textContent = `Duration: ${minutes}:${seconds.toString().padStart(2, '0')}`;
}

// Start workout
async function startWorkout(workoutId) {
    try {
        const formData = new FormData();
        formData.append('action', 'start_session');
        formData.append('workout_id', workoutId);
        
        const response = await fetch('api/workouts.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.location.href = `tracker.php?session_id=${result.data.session_id}`;
        } else {
            alert('Failed to start workout: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        alert('An error occurred while starting the workout');
        console.error('Start workout error:', error);
    }
}

// Add set
function addSet(exerciseId) {
    const setsList = document.getElementById(`sets-${exerciseId}`);
    const setNumber = setsList.children.length + 1;
    
    const setItem = document.createElement('div');
    setItem.className = 'set-item';
    setItem.innerHTML = `
        <span>Set ${setNumber}</span>
        <input type="number" placeholder="Reps" class="reps-input">
        <input type="number" step="0.1" placeholder="Weight (kg)" class="weight-input">
        <button onclick="logSet(${exerciseId}, ${setNumber})" class="btn btn-success">Log</button>
    `;
    
    setsList.appendChild(setItem);
}

// Log set
async function logSet(exerciseId, setNumber) {
    const setItem = event.target.closest('.set-item');
    const repsInput = setItem.querySelector('.reps-input');
    const weightInput = setItem.querySelector('.weight-input');
    
    const reps = repsInput.value;
    const weight = weightInput.value;
    
    if (!reps) {
        alert('Please enter the number of reps');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'log_exercise');
        formData.append('session_id', '<?php echo $sessionId; ?>');
        formData.append('exercise_id', exerciseId);
        formData.append('sets_completed', setNumber);
        formData.append('reps_completed', reps);
        if (weight) {
            formData.append('weight_used', weight);
        }
        
        const response = await fetch('api/workouts.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            setItem.style.background = '#d1fae5';
            setItem.querySelector('button').textContent = 'Logged';
            setItem.querySelector('button').disabled = true;
        } else {
            alert('Failed to log set: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        alert('An error occurred while logging the set');
        console.error('Log set error:', error);
    }
}

// Complete exercise
function completeExercise(exerciseId) {
    const exerciseTracker = document.querySelector(`[data-exercise-id="${exerciseId}"]`);
    exerciseTracker.style.opacity = '0.6';
    exerciseTracker.querySelector('.exercise-actions').innerHTML = '<span class="btn btn-success">Completed ✓</span>';
}

// Skip exercise
function skipExercise(exerciseId) {
    const exerciseTracker = document.querySelector(`[data-exercise-id="${exerciseId}"]`);
    exerciseTracker.style.opacity = '0.4';
    exerciseTracker.querySelector('.exercise-actions').innerHTML = '<span class="btn btn-secondary">Skipped</span>';
}

// Finish workout
function finishWorkout() {
    document.getElementById('workoutSummaryModal').style.display = 'flex';
    
    // Calculate duration
    const now = new Date();
    const duration = Math.floor((now - sessionStartTime) / 1000 / 60);
    document.getElementById('duration').value = duration;
}

// Pause workout
function pauseWorkout() {
    if (sessionTimer) {
        clearInterval(sessionTimer);
        sessionTimer = null;
        event.target.textContent = 'Resume';
    } else {
        sessionTimer = setInterval(updateSessionDuration, 1000);
        event.target.textContent = 'Pause';
    }
}

// Submit workout summary
document.getElementById('workoutSummaryForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'end_session');
    formData.append('session_id', '<?php echo $sessionId; ?>');
    
    try {
        const response = await fetch('api/workouts.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Workout saved successfully!');
            window.location.href = 'progress.php';
        } else {
            alert('Failed to save workout: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        alert('An error occurred while saving the workout');
        console.error('Save workout error:', error);
    }
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('workoutSummaryModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include 'includes/footer.php'; ?>