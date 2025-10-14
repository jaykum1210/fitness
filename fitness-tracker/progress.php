<?php
$pageTitle = 'Progress - Fitness Tracker';
include 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getLoggedInUser();
if (!$user) {
    redirect('login.php');
}

// Get user's progress data
$recentWorkouts = [];
$progressEntries = [];
$activeGoals = [];
$userStats = [];

if (isDatabaseAvailable()) {
    try {
        // Get recent workouts
        $recentWorkouts = fetchAll("SELECT ws.*, w.name as workout_name, w.image_url, w.difficulty_level
                                   FROM workout_sessions ws
                                   JOIN workouts w ON ws.workout_id = w.id
                                   WHERE ws.user_id = ? AND ws.completed_at IS NOT NULL
                                   ORDER BY ws.completed_at DESC
                                   LIMIT 10", [$user['id']]);
        
        // Get progress entries
        $progressEntries = fetchAll("SELECT * FROM user_progress 
                                    WHERE user_id = ? 
                                    ORDER BY measurement_date DESC 
                                    LIMIT 20", [$user['id']]);
        
        // Get active goals
        $activeGoals = fetchAll("SELECT * FROM user_goals WHERE user_id = ? AND is_active = 1 ORDER BY created_at DESC", [$user['id']]);
        
        // Get user statistics
        $totalWorkouts = fetchOne("SELECT COUNT(*) as count FROM workout_sessions WHERE user_id = ? AND completed_at IS NOT NULL", [$user['id']]);
        $totalCalories = fetchOne("SELECT SUM(calories_burned) as total FROM workout_sessions WHERE user_id = ? AND completed_at IS NOT NULL", [$user['id']]);
        $totalDuration = fetchOne("SELECT SUM(duration_minutes) as total FROM workout_sessions WHERE user_id = ? AND completed_at IS NOT NULL", [$user['id']]);
        $avgRating = fetchOne("SELECT AVG(rating) as avg FROM workout_sessions WHERE user_id = ? AND completed_at IS NOT NULL AND rating > 0", [$user['id']]);
        
        $userStats = [
            'total_workouts' => $totalWorkouts['count'] ?? 0,
            'total_calories' => $totalCalories['total'] ?? 0,
            'total_duration' => $totalDuration['total'] ?? 0,
            'avg_rating' => round($avgRating['avg'] ?? 0, 1)
        ];
        
    } catch (Exception $e) {
        // Fallback to empty arrays if database fails
        $recentWorkouts = [];
        $progressEntries = [];
        $activeGoals = [];
        $userStats = ['total_workouts' => 0, 'total_calories' => 0, 'total_duration' => 0, 'avg_rating' => 0];
    }
}
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Your Progress Dashboard</h1>
        <p style="text-align: center; color: var(--text-light); margin-bottom: 3rem;">Track your fitness journey and celebrate your achievements.</p>
        
        <!-- User Stats Cards -->
        <div class="workout-stats fade-in stagger-1">
            <div class="stat-card">
                <h3><?php echo $userStats['total_workouts']; ?></h3>
                <p>Workouts Completed</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $userStats['total_calories']; ?></h3>
                <p>Calories Burned</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $userStats['total_duration']; ?></h3>
                <p>Minutes Exercised</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $userStats['avg_rating']; ?>/5</h3>
                <p>Average Rating</p>
            </div>
        </div>
        
        <!-- Progress Tracking Form -->
        <div class="exercise-detail fade-in stagger-2">
            <h3>Add Progress Entry</h3>
            <form id="progressForm" class="form-row">
                <div class="form-group">
                    <label for="measurement_date">Date</label>
                    <input type="date" id="measurement_date" name="measurement_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="weight">Weight (kg)</label>
                    <input type="number" id="weight" name="weight" step="0.1" placeholder="Current weight">
                </div>
                <div class="form-group">
                    <label for="body_fat_percentage">Body Fat %</label>
                    <input type="number" id="body_fat_percentage" name="body_fat_percentage" step="0.1" placeholder="Body fat percentage">
                </div>
                <div class="form-group">
                    <label for="muscle_mass">Muscle Mass (kg)</label>
                    <input type="number" id="muscle_mass" name="muscle_mass" step="0.1" placeholder="Muscle mass">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Any additional notes about your progress..."></textarea>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <button type="submit" class="btn btn-primary">Add Progress Entry</button>
                </div>
            </form>
            <div id="progressMessage" style="margin-top: 1rem;"></div>
        </div>
        
        <!-- Goals Section -->
        <div class="exercise-detail fade-in stagger-3">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3>Your Goals</h3>
                <button onclick="showAddGoalModal()" class="btn btn-primary">Add New Goal</button>
            </div>
            
            <?php if (!empty($activeGoals)): ?>
                <div class="goals-grid">
                    <?php foreach ($activeGoals as $goal): ?>
                        <div class="goal-card">
                            <h4><?php echo ucfirst(str_replace('_', ' ', $goal['goal_type'])); ?></h4>
                            <div class="goal-progress">
                                <div class="progress-bar">
                                    <?php 
                                    $progress = $goal['target_value'] > 0 ? ($goal['current_value'] / $goal['target_value']) * 100 : 0;
                                    $progress = min(100, max(0, $progress));
                                    ?>
                                    <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                                </div>
                                <div class="progress-text">
                                    <?php echo $goal['current_value']; ?> / <?php echo $goal['target_value']; ?> <?php echo $goal['unit']; ?>
                                </div>
                            </div>
                            <?php if ($goal['target_date']): ?>
                                <p class="goal-date">Target: <?php echo formatDate($goal['target_date']); ?></p>
                            <?php endif; ?>
                            <?php if ($goal['description']): ?>
                                <p class="goal-description"><?php echo htmlspecialchars($goal['description']); ?></p>
                            <?php endif; ?>
                            <div class="goal-actions">
                                <button onclick="updateGoalProgress(<?php echo $goal['id']; ?>)" class="btn btn-secondary">Update Progress</button>
                                <button onclick="deleteGoal(<?php echo $goal['id']; ?>)" class="btn btn-danger">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: var(--text-light); padding: 2rem;">No active goals yet. Add your first goal to start tracking your progress!</p>
            <?php endif; ?>
        </div>
        
        <!-- Recent Workouts -->
        <div class="exercise-detail fade-in stagger-4">
            <h3>Recent Workouts</h3>
            <?php if (!empty($recentWorkouts)): ?>
                <div class="workout-history">
                    <?php foreach ($recentWorkouts as $workout): ?>
                        <div class="workout-entry">
                            <div class="workout-info">
                                <h4><?php echo htmlspecialchars($workout['workout_name']); ?></h4>
                                <p><?php echo formatDate($workout['completed_at']); ?> • <?php echo $workout['duration_minutes']; ?> min • <?php echo $workout['calories_burned']; ?> cal</p>
                                <?php if ($workout['rating']): ?>
                                    <div class="workout-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= $workout['rating'] ? 'filled' : ''; ?>">⭐</span>
                                        <?php endfor; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="workout-actions">
                                <span class="difficulty-badge <?php echo $workout['difficulty_level']; ?>">
                                    <?php echo ucfirst($workout['difficulty_level']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: var(--text-light); padding: 2rem;">No workouts completed yet. Start your first workout to see your progress here!</p>
            <?php endif; ?>
        </div>
        
        <!-- Progress Chart -->
        <?php if (!empty($progressEntries)): ?>
            <div class="exercise-detail fade-in stagger-5">
                <h3>Weight Progress</h3>
                <div class="chart-container">
                    <canvas id="progressChart" width="400" height="200"></canvas>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Add Goal Modal -->
<div id="addGoalModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="modal-close" onclick="hideAddGoalModal()">&times;</span>
        <h3>Add New Goal</h3>
        <form id="addGoalForm">
            <div class="form-group">
                <label for="goal_type">Goal Type</label>
                <select id="goal_type" name="goal_type" required>
                    <option value="">Select goal type</option>
                    <option value="weight_loss">Weight Loss</option>
                    <option value="weight_gain">Weight Gain</option>
                    <option value="muscle_gain">Muscle Gain</option>
                    <option value="endurance">Endurance</option>
                    <option value="strength">Strength</option>
                    <option value="flexibility">Flexibility</option>
                </select>
            </div>
            <div class="form-group">
                <label for="target_value">Target Value</label>
                <input type="number" id="target_value" name="target_value" step="0.1" required>
            </div>
            <div class="form-group">
                <label for="unit">Unit</label>
                <input type="text" id="unit" name="unit" placeholder="kg, lbs, minutes, etc." required>
            </div>
            <div class="form-group">
                <label for="target_date">Target Date</label>
                <input type="date" id="target_date" name="target_date">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Describe your goal..."></textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Add Goal</button>
                <button type="button" onclick="hideAddGoalModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
        <div id="goalMessage" style="margin-top: 1rem;"></div>
    </div>
</div>

<style>
.goals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.goal-card {
    background: var(--bg-gray);
    padding: 1.5rem;
    border-radius: 12px;
    border-left: 4px solid var(--primary);
}

.goal-progress {
    margin: 1rem 0;
}

.progress-bar {
    background: #e5e7eb;
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-fill {
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    height: 100%;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.875rem;
    color: var(--text-light);
}

.goal-date {
    font-size: 0.875rem;
    color: var(--text-light);
    margin: 0.5rem 0;
}

.goal-description {
    color: var(--text);
    margin: 0.5rem 0;
}

.goal-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.workout-history {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 1rem;
}

.workout-entry {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: var(--bg-gray);
    border-radius: 8px;
}

.workout-rating {
    margin-top: 0.5rem;
}

.star {
    opacity: 0.3;
}

.star.filled {
    opacity: 1;
}

.difficulty-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.difficulty-badge.beginner {
    background: #10b981;
    color: white;
}

.difficulty-badge.intermediate {
    background: #f59e0b;
    color: white;
}

.difficulty-badge.advanced {
    background: #ef4444;
    color: white;
}

.chart-container {
    background: var(--bg-gray);
    padding: 2rem;
    border-radius: 12px;
    margin-top: 1rem;
}
</style>

<script>
// Add progress entry
document.getElementById('progressForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'add_progress');
    
    try {
        const response = await fetch('api/progress.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('progressMessage').innerHTML = '<div class="alert alert-success">Progress entry added successfully!</div>';
            this.reset();
            document.getElementById('measurement_date').value = new Date().toISOString().split('T')[0];
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            document.getElementById('progressMessage').innerHTML = '<div class="alert alert-danger">' + (result.error || 'Failed to add progress entry') + '</div>';
        }
    } catch (error) {
        document.getElementById('progressMessage').innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
    }
});

// Show add goal modal
function showAddGoalModal() {
    document.getElementById('addGoalModal').style.display = 'flex';
}

// Hide add goal modal
function hideAddGoalModal() {
    document.getElementById('addGoalModal').style.display = 'none';
    document.getElementById('addGoalForm').reset();
    document.getElementById('goalMessage').innerHTML = '';
}

// Add goal form
document.getElementById('addGoalForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'add_goal');
    
    try {
        const response = await fetch('api/progress.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('goalMessage').innerHTML = '<div class="alert alert-success">Goal added successfully!</div>';
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            document.getElementById('goalMessage').innerHTML = '<div class="alert alert-danger">' + (result.error || 'Failed to add goal') + '</div>';
        }
    } catch (error) {
        document.getElementById('goalMessage').innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
    }
});

// Update goal progress
async function updateGoalProgress(goalId) {
    const newProgress = prompt('Enter new progress value:');
    if (newProgress === null || newProgress === '') return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'update_goal');
        formData.append('goal_id', goalId);
        formData.append('current_value', newProgress);
        
        const response = await fetch('api/progress.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert('Failed to update goal: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        alert('An error occurred while updating the goal');
    }
}

// Delete goal
async function deleteGoal(goalId) {
    if (!confirm('Are you sure you want to delete this goal?')) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete_goal');
        formData.append('goal_id', goalId);
        
        const response = await fetch('api/progress.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert('Failed to delete goal: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        alert('An error occurred while deleting the goal');
    }
}

// Initialize progress chart if data exists
<?php if (!empty($progressEntries)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('progressChart').getContext('2d');
    const progressData = <?php echo json_encode($progressEntries); ?>;
    
    const labels = progressData.map(entry => entry.measurement_date).reverse();
    const weights = progressData.map(entry => entry.weight).reverse();
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Weight (kg)',
                data: weights,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
});
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>