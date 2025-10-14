<?php
$pageTitle = 'Workouts - Fitness Tracker';
include 'includes/header.php';

// Get workout categories
$categories = [];
if (isDatabaseAvailable()) {
    try {
        $categories = fetchAll("SELECT * FROM workout_categories ORDER BY name");
    } catch (Exception $e) {
        $categories = [];
    }
}

// Get workouts based on filters
$workouts = [];
$selectedCategory = $_GET['category'] ?? '';
$selectedDifficulty = $_GET['difficulty'] ?? '';
$search = $_GET['search'] ?? '';

if (isDatabaseAvailable()) {
    try {
        $sql = "SELECT w.*, wc.name as category_name, wc.icon as category_icon 
                FROM workouts w 
                LEFT JOIN workout_categories wc ON w.category_id = wc.id 
                WHERE w.is_active = 1";
        $params = [];
        
        if (!empty($selectedCategory)) {
            $sql .= " AND wc.name = ?";
            $params[] = $selectedCategory;
        }
        
        if (!empty($selectedDifficulty)) {
            $sql .= " AND w.difficulty_level = ?";
            $params[] = $selectedDifficulty;
        }
        
        if (!empty($search)) {
            $sql .= " AND (w.name LIKE ? OR w.description LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY w.created_at DESC";
        
        $workouts = fetchAll($sql, $params);
        
        // Get exercise count for each workout
        foreach ($workouts as &$workout) {
            $exerciseCount = fetchOne("SELECT COUNT(*) as count FROM workout_exercises WHERE workout_id = ?", [$workout['id']]);
            $workout['exercise_count'] = $exerciseCount['count'];
        }
    } catch (Exception $e) {
        $workouts = [];
    }
}

// Get single workout if ID is provided
$singleWorkout = null;
$workoutId = $_GET['id'] ?? '';
if (!empty($workoutId) && is_numeric($workoutId) && isDatabaseAvailable()) {
    try {
        $singleWorkout = fetchOne("SELECT w.*, wc.name as category_name, wc.icon as category_icon 
                                  FROM workouts w 
                                  LEFT JOIN workout_categories wc ON w.category_id = wc.id 
                                  WHERE w.id = ? AND w.is_active = 1", [$workoutId]);
        
        if ($singleWorkout) {
            // Get exercises for this workout
            $exercises = fetchAll("SELECT e.*, we.sets, we.reps, we.weight, we.rest_seconds, we.order_index
                                  FROM workout_exercises we
                                  JOIN exercises e ON we.exercise_id = e.id
                                  WHERE we.workout_id = ? AND e.is_active = 1
                                  ORDER BY we.order_index", [$workoutId]);
            
            $singleWorkout['exercises'] = $exercises;
        }
    } catch (Exception $e) {
        $singleWorkout = null;
    }
}
?>

<!-- Hero Section -->
<section class="workout-hero">
    <div class="container">
        <h1 class="fade-in">Transform Your Body</h1>
        <p class="fade-in stagger-1">Discover professional workout routines designed to help you achieve your fitness goals. From strength training to cardio, find the perfect workout for your level.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Search and Filters -->
        <div class="search-bar fade-in stagger-2">
            <form method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <input type="text" name="search" placeholder="Search workouts..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; min-width: 200px;">
                
                <select name="category" style="padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['name']); ?>" 
                                <?php echo $selectedCategory === $category['name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="difficulty" style="padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px;">
                    <option value="">All Levels</option>
                    <option value="beginner" <?php echo $selectedDifficulty === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                    <option value="intermediate" <?php echo $selectedDifficulty === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                    <option value="advanced" <?php echo $selectedDifficulty === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                    <option value="expert" <?php echo $selectedDifficulty === 'expert' ? 'selected' : ''; ?>>Expert</option>
                </select>
                
                <button type="submit" class="btn btn-primary">Filter</button>
                <?php if (!empty($search) || !empty($selectedCategory) || !empty($selectedDifficulty)): ?>
                    <a href="workouts.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if ($singleWorkout): ?>
            <!-- Single Workout View -->
            <div class="workout-detail-page">
                <div class="workout-header fade-in stagger-1">
                    <div class="workout-info">
                        <h1><?php echo htmlspecialchars($singleWorkout['name']); ?></h1>
                        <div class="workout-meta-modern">
                            <span class="workout-meta-item duration">⏱️ <?php echo $singleWorkout['duration_minutes']; ?> min</span>
                            <span class="workout-meta-item level">🎯 <?php echo ucfirst($singleWorkout['difficulty_level']); ?></span>
                            <span class="workout-meta-item sets">🔥 <?php echo $singleWorkout['calories_burned']; ?> cal</span>
                            <span class="workout-meta-item">📋 <?php echo count($singleWorkout['exercises']); ?> exercises</span>
                        </div>
                        <p class="workout-description"><?php echo htmlspecialchars($singleWorkout['description']); ?></p>
                    </div>
                    <div class="workout-actions">
                        <?php if (isLoggedIn()): ?>
                            <button onclick="startWorkout(<?php echo $singleWorkout['id']; ?>)" class="workout-btn-modern">Start Workout</button>
                        <?php else: ?>
                            <a href="login.php" class="workout-btn-modern">Login to Start</a>
                        <?php endif; ?>
                        <button onclick="toggleFavorite(<?php echo $singleWorkout['id']; ?>)" class="btn btn-secondary" id="favoriteBtn">
                            <span id="favoriteText">Add to Favorites</span>
                        </button>
                    </div>
                </div>
                
                <?php if (!empty($singleWorkout['exercises'])): ?>
                    <div class="exercises-list fade-in stagger-2">
                        <h2>Exercise Breakdown</h2>
                        <?php foreach ($singleWorkout['exercises'] as $exercise): ?>
                            <div class="exercise-detail">
                                <div class="exercise-header">
                                    <img src="<?php echo htmlspecialchars($exercise['image_url'] ?: 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?q=80&w=400&auto=format&fit=crop'); ?>" 
                                         alt="<?php echo htmlspecialchars($exercise['name']); ?>" class="exercise-image">
                                    <div class="exercise-info">
                                        <h3><?php echo htmlspecialchars($exercise['name']); ?></h3>
                                        <div class="exercise-meta">
                                            <span><?php echo $exercise['sets']; ?> sets</span>
                                            <span><?php echo $exercise['reps']; ?> reps</span>
                                            <?php if ($exercise['weight']): ?>
                                                <span><?php echo $exercise['weight']; ?> kg</span>
                                            <?php endif; ?>
                                            <span><?php echo $exercise['rest_seconds']; ?>s rest</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="exercise-description"><?php echo htmlspecialchars($exercise['description']); ?></p>
                                <?php if (!empty($exercise['instructions'])): ?>
                                    <div class="exercise-instructions">
                                        <h4>📋 Instructions</h4>
                                        <p><?php echo nl2br(htmlspecialchars($exercise['instructions'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($exercise['tips'])): ?>
                                    <div class="exercise-tips">
                                        <h4>💡 Tips</h4>
                                        <p><?php echo nl2br(htmlspecialchars($exercise['tips'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div style="text-align: center; margin-top: 3rem;">
                    <a href="workouts.php" class="workout-btn-modern">← Back to Workouts</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Workout Grid -->
            <div class="workout-grid-modern">
                <?php if (!empty($workouts)): ?>
                    <?php foreach ($workouts as $index => $workout): ?>
                        <div class="workout-card-modern fade-in stagger-<?php echo ($index % 5) + 1; ?>" data-category="<?php echo strtolower($workout['category_name'] ?? 'strength'); ?>">
                            <div class="workout-image-modern">
                                <img src="<?php echo htmlspecialchars($workout['image_url'] ?: 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=1200&auto=format&fit=crop'); ?>" 
                                     alt="<?php echo htmlspecialchars($workout['name']); ?>">
                                <div class="workout-overlay">
                                    <div class="workout-overlay-content">
                                        <h4><?php echo htmlspecialchars($workout['name']); ?></h4>
                                        <p><?php echo htmlspecialchars($workout['category_name'] ?? 'Workout'); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="workout-content">
                                <h3 class="workout-title"><?php echo htmlspecialchars($workout['name']); ?></h3>
                                <p class="workout-description"><?php echo htmlspecialchars($workout['description']); ?></p>
                                <div class="workout-meta-modern">
                                    <span class="workout-meta-item duration">⏱️ <?php echo $workout['duration_minutes']; ?> min</span>
                                    <span class="workout-meta-item level">🎯 <?php echo ucfirst($workout['difficulty_level']); ?></span>
                                    <span class="workout-meta-item sets">📋 <?php echo $workout['exercise_count']; ?> exercises</span>
                                </div>
                                <div class="exercise-tags-modern">
                                    <span class="exercise-tag-modern"><?php echo htmlspecialchars($workout['category_name'] ?? 'Workout'); ?></span>
                                    <span class="exercise-tag-modern"><?php echo $workout['calories_burned']; ?> cal</span>
                                </div>
                                <a href="workouts.php?id=<?php echo $workout['id']; ?>" class="workout-btn-modern">View Workout</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results fade-in">
                        <h3>No workouts found</h3>
                        <p>Try adjusting your search criteria or browse all workouts.</p>
                        <a href="workouts.php" class="btn btn-primary">View All Workouts</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Start workout functionality
async function startWorkout(workoutId) {
    if (!workoutId) return;
    
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
            // Redirect to tracker with session ID
            window.location.href = `tracker.php?session_id=${result.data.session_id}`;
        } else {
            alert('Failed to start workout: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        alert('An error occurred while starting the workout');
        console.error('Start workout error:', error);
    }
}

// Toggle favorite functionality
async function toggleFavorite(workoutId) {
    if (!workoutId) return;
    
    const favoriteBtn = document.getElementById('favoriteBtn');
    const favoriteText = document.getElementById('favoriteText');
    const isCurrentlyFavorited = favoriteText.textContent.includes('Remove');
    
    try {
        const formData = new FormData();
        formData.append('action', isCurrentlyFavorited ? 'unfavorite' : 'favorite');
        formData.append('workout_id', workoutId);
        
        const response = await fetch('api/workouts.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (isCurrentlyFavorited) {
                favoriteText.textContent = 'Add to Favorites';
                favoriteBtn.classList.remove('btn-primary');
                favoriteBtn.classList.add('btn-secondary');
            } else {
                favoriteText.textContent = 'Remove from Favorites';
                favoriteBtn.classList.remove('btn-secondary');
                favoriteBtn.classList.add('btn-primary');
            }
        } else {
            alert('Failed to update favorites: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        alert('An error occurred while updating favorites');
        console.error('Toggle favorite error:', error);
    }
}

// Enhanced category filtering with animations
document.querySelectorAll('.category-btn').forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons
        document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        
        const filter = button.getAttribute('data-filter');
        const cards = document.querySelectorAll('.workout-card-modern');
        
        cards.forEach((card, index) => {
            const cat = card.getAttribute('data-category');
            const shouldShow = filter === 'all' || cat === filter;
            
            if (shouldShow) {
                card.style.display = 'flex';
                card.style.animation = `fadeInUp 0.6s ease-out ${index * 0.1}s both`;
            } else {
                card.style.display = 'none';
            }
        });
    });
});

// Add scroll animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe all workout cards
document.querySelectorAll('.workout-card-modern').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';
    card.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
    observer.observe(card);
});
</script>

<?php include 'includes/footer.php'; ?>