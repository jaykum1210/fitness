<?php 
$pageTitle = 'Workouts - Fitness Tracker';
include 'includes/header.php';

$workouts = readJSON(WORKOUTS_FILE);
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Workout Library</h1>
        <p style="text-align: center; color: var(--text-light); margin-bottom: 3rem;">Choose a workout that fits your level and goal.</p>

        <div class="search-bar">
            <input type="text" id="workoutSearch" onkeyup="searchWorkouts()" placeholder="Search workouts...">
        </div>

        <div class="workout-tabs">
            <button class="tab-btn active" onclick="filterWorkouts('all')">All</button>
            <button class="tab-btn" onclick="filterWorkouts('beginner')">Beginner</button>
            <button class="tab-btn" onclick="filterWorkouts('intermediate')">Intermediate</button>
            <button class="tab-btn" onclick="filterWorkouts('advanced')">Advanced</button>
        </div>

        <div class="cards-grid">
            <?php foreach ($workouts as $workout): ?>
            <div class="card workout-card" data-category="<?php echo $workout['category']; ?>">
                <div class="card-icon">💪</div>
                <h3><?php echo htmlspecialchars($workout['name']); ?></h3>
                <p><?php echo htmlspecialchars($workout['description']); ?></p>
                <div class="workout-meta">
                    <span>📋 <?php echo $workout['sets']; ?> sets</span>
                    <span>🔁 <?php echo $workout['reps']; ?> reps</span>
                    <span>🎯 <?php echo ucfirst($workout['bodyPart']); ?></span>
                </div>
                <?php if (isLoggedIn()): ?>
                <a href="tracker.php?workout=<?php echo $workout['name']; ?>" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">Add to Tracker</a>
                <?php else: ?>
                <a href="login.php" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">Login to Track</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>