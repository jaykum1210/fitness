<?php 
$pageTitle = 'Tracker - Fitness Tracker';
include 'includes/header.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getLoggedInUser();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_entry'])) {
        $entries = readJSON(TRACKER_FILE);
        
        $entry = [
            'id' => uniqid(),
            'user_id' => $user['id'],
            'exercise' => $_POST['exercise'],
            'sets' => (int)$_POST['sets'],
            'reps' => (int)$_POST['reps'],
            'duration' => (int)$_POST['duration'],
            'calories' => (int)$_POST['calories'],
            'date' => date('Y-m-d'),
            'timestamp' => time()
        ];
        
        $entries[] = $entry;
        writeJSON(TRACKER_FILE, $entries);
        
        $message = 'Workout logged successfully!';
        $messageType = 'success';
    }
    
    if (isset($_POST['delete_entry'])) {
        $entries = readJSON(TRACKER_FILE);
        $entryId = $_POST['entry_id'];
        
        $entries = array_filter($entries, function($e) use ($entryId) {
            return $e['id'] !== $entryId;
        });
        
        writeJSON(TRACKER_FILE, array_values($entries));
        
        $message = 'Entry deleted successfully!';
        $messageType = 'success';
    }
}

$entries = readJSON(TRACKER_FILE);
$userEntries = array_filter($entries, function($e) use ($user) {
    return $e['user_id'] == $user['id'] && $e['date'] == date('Y-m-d');
});

$todayStats = [
    'exercises' => count($userEntries),
    'duration' => array_sum(array_column($userEntries, 'duration')),
    'calories' => array_sum(array_column($userEntries, 'calories'))
];

$prefilledWorkout = isset($_GET['workout']) ? htmlspecialchars($_GET['workout']) : '';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Daily Workout Tracker</h1>
        
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <div class="cards-grid" style="margin-bottom: 3rem;">
            <div class="card summary-card">
                <p style="font-size: 0.875rem; opacity: 0.9;">Today's Exercises</p>
                <h3><?php echo $todayStats['exercises']; ?></h3>
            </div>
            <div class="card summary-card" style="background: linear-gradient(135deg, var(--secondary) 0%, #059669 100%);">
                <p style="font-size: 0.875rem; opacity: 0.9;">Total Duration</p>
                <h3><?php echo $todayStats['duration']; ?> min</h3>
            </div>
            <div class="card summary-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <p style="font-size: 0.875rem; opacity: 0.9;">Calories Burned</p>
                <h3><?php echo $todayStats['calories']; ?> cal</h3>
            </div>
        </div>

        <?php if ($todayStats['exercises'] >= 3): ?>
        <div class="alert alert-success" style="text-align: center; font-size: 1.125rem;">
            🔥 Amazing! You've logged <?php echo $todayStats['exercises']; ?> exercises today. Keep it up!
        </div>
        <?php endif; ?>

        <div class="tracker-form">
            <h2 style="margin-bottom: 1.5rem;">Log Today's Workout</h2>
            
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Exercise Name</label>
                        <input type="text" name="exercise" value="<?php echo $prefilledWorkout; ?>" required placeholder="e.g., Push-ups">
                    </div>
                    
                    <div class="form-group">
                        <label>Sets</label>
                        <input type="number" name="sets" required placeholder="e.g., 3">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Reps</label>
                        <input type="number" name="reps" required placeholder="e.g., 12">
                    </div>
                    
                    <div class="form-group">
                        <label>Duration (minutes)</label>
                        <input type="number" name="duration" required placeholder="e.g., 20">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Calories Burned (estimate)</label>
                    <input type="number" name="calories" required placeholder="e.g., 150">
                </div>
                
                <button type="submit" name="add_entry" class="btn btn-success">Add Workout</button>
            </form>
        </div>

        <div style="margin-top: 3rem;">
            <h2 style="margin-bottom: 1.5rem;">Today's Log</h2>
            
            <?php if (empty($userEntries)): ?>
            <p style="text-align: center; color: var(--text-light); padding: 2rem;">No workouts logged today. Start tracking!</p>
            <?php else: ?>
            <?php foreach ($userEntries as $entry): ?>
            <div class="log-entry">
                <div class="log-entry-info">
                    <h4><?php echo htmlspecialchars($entry['exercise']); ?></h4>
                    <p>
                        <?php echo $entry['sets']; ?> sets × <?php echo $entry['reps']; ?> reps | 
                        <?php echo $entry['duration']; ?> min | 
                        <?php echo $entry['calories']; ?> cal
                    </p>
                </div>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="entry_id" value="<?php echo $entry['id']; ?>">
                    <button type="submit" name="delete_entry" class="btn-delete" onclick="return confirm('Delete this entry?')">Delete</button>
                </form>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>