<?php 
$pageTitle = 'Challenges - Fitness Tracker';
include '../includes/header.php';

$challenges = readJSON(DATA_DIR . 'challenges.json');

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $challengeId = $_POST['challenge_id'];
    $message = 'Challenge joined successfully! Track your progress in the Tracker page.';
}
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">🏆 Fitness Challenges</h1>
        <p style="text-align: center; color: var(--text-light); max-width: 700px; margin: 0 auto 3rem;">
            Push yourself to the next level with our structured fitness challenges. Join thousands of users transforming their lives!
        </p>

        <?php if ($message): ?>
        <div class="alert alert-success" style="max-width: 600px; margin: 0 auto 2rem;">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <div class="cards-grid">
            <?php foreach ($challenges as $challenge): ?>
            <div class="card challenge-detail-card">
                <div class="challenge-badge">
                    <?php 
                    $badges = ['🔥', '💪', '🏆', '🎯', '⚡', '🌟'];
                    echo $badges[$challenge['id'] % 6]; 
                    ?>
                </div>
                <h3><?php echo htmlspecialchars($challenge['name']); ?></h3>
                <div class="challenge-meta">
                    <span class="badge-pill badge-<?php echo $challenge['difficulty']; ?>">
                        <?php echo ucfirst($challenge['difficulty']); ?>
                    </span>
                    <span class="badge-pill">📅 <?php echo $challenge['duration']; ?> Days</span>
                </div>
                <p style="margin: 1rem 0;"><?php echo htmlspecialchars($challenge['description']); ?></p>
                
                <div class="challenge-goal">
                    <strong>🎯 Goal:</strong> <?php echo htmlspecialchars($challenge['goal']); ?>
                </div>

                <div class="challenge-exercises">
                    <strong>Includes:</strong>
                    <div class="exercise-tags">
                        <?php foreach (array_slice($challenge['exercises'], 0, 3) as $exercise): ?>
                        <span class="exercise-tag"><?php echo $exercise; ?></span>
                        <?php endforeach; ?>
                        <?php if (count($challenge['exercises']) > 3): ?>
                        <span class="exercise-tag">+<?php echo count($challenge['exercises']) - 3; ?> more</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="challenge-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $challenge['participants']; ?></div>
                        <div class="stat-label">Participants</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $challenge['completionRate']; ?>%</div>
                        <div class="stat-label">Success Rate</div>
                    </div>
                </div>

                <?php if (isLoggedIn()): ?>
                <form method="POST" style="margin-top: 1rem;">
                    <input type="hidden" name="challenge_id" value="<?php echo $challenge['id']; ?>">
                    <button type="submit" class="btn btn-success" style="width: 100%;">Join Challenge</button>
                </form>
                <?php else: ?>
                <a href="../login.php" class="btn btn-primary" style="width: 100%; display: inline-block; text-align: center; margin-top: 1rem;">Login to Join</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>