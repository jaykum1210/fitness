<?php 
$pageTitle = 'Progress - Fitness Tracker';
include 'includes/header.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getLoggedInUser();
$entries = readJSON(TRACKER_FILE);

$userEntries = array_filter($entries, function($e) use ($user) {
    return $e['user_id'] == $user['id'];
});

$totalWorkouts = count($userEntries);
$totalCalories = array_sum(array_column($userEntries, 'calories'));
$totalDuration = array_sum(array_column($userEntries, 'duration'));

$uniqueDates = array_unique(array_column($userEntries, 'date'));
$activeDays = count($uniqueDates);

$badges = [
    ['name' => 'First Workout', 'icon' => '🎯', 'earned' => $totalWorkouts >= 1],
    ['name' => '10 Workouts', 'icon' => '💪', 'earned' => $totalWorkouts >= 10],
    ['name' => '500 Calories', 'icon' => '🔥', 'earned' => $totalCalories >= 500],
    ['name' => '1000 Calories', 'icon' => '🔥', 'earned' => $totalCalories >= 1000],
    ['name' => '7 Day Streak', 'icon' => '📅', 'earned' => $activeDays >= 7],
    ['name' => '30 Day Streak', 'icon' => '🏆', 'earned' => $activeDays >= 30],
];

$last7Days = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayEntries = array_filter($userEntries, function($e) use ($date) {
        return $e['date'] == $date;
    });
    $last7Days[$date] = [
        'active' => !empty($dayEntries),
        'calories' => array_sum(array_column($dayEntries, 'calories'))
    ];
}
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Your Progress</h1>

        <div class="cards-grid" style="margin-bottom: 3rem;">
            <div class="card" style="border-left: 4px solid var(--primary);">
                <div class="card-icon">💪</div>
                <h3><?php echo $totalWorkouts; ?></h3>
                <p>Total Workouts</p>
            </div>
            <div class="card" style="border-left: 4px solid var(--secondary);">
                <div class="card-icon">🔥</div>
                <h3><?php echo $totalCalories; ?></h3>
                <p>Calories Burned</p>
            </div>
            <div class="card" style="border-left: 4px solid #f59e0b;">
                <div class="card-icon">⏱️</div>
                <h3><?php echo $totalDuration; ?> min</h3>
                <p>Total Duration</p>
            </div>
            <div class="card" style="border-left: 4px solid #8b5cf6;">
                <div class="card-icon">📅</div>
                <h3><?php echo $activeDays; ?></h3>
                <p>Active Days</p>
            </div>
        </div>

        <div class="chart-container">
            <h2 style="margin-bottom: 1.5rem;">Last 7 Days Calories</h2>
            <div style="display: flex; gap: 1rem; align-items: flex-end; justify-content: space-around; padding: 2rem; background: var(--bg-gray); border-radius: 8px; min-height: 300px;">
                <?php foreach ($last7Days as $date => $data): ?>
                <div style="text-align: center; flex: 1;">
                    <div style="background: var(--primary); border-radius: 8px 8px 0 0; height: <?php echo max(20, ($data['calories'] / 10)); ?>px; margin-bottom: 0.5rem;"></div>
                    <div style="font-weight: 600; margin-bottom: 0.25rem;"><?php echo $data['calories']; ?></div>
                    <div style="font-size: 0.75rem; color: var(--text-light);"><?php echo date('D', strtotime($date)); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="chart-container">
            <h2 style="margin-bottom: 1.5rem;">Workout Calendar (Last 7 Days)</h2>
            <div class="calendar-grid">
                <?php foreach ($last7Days as $date => $data): ?>
                <div class="calendar-day <?php echo $data['active'] ? 'active' : ''; ?>" title="<?php echo $date; ?>">
                    <?php echo date('j', strtotime($date)); ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="chart-container">
            <h2 style="margin-bottom: 1.5rem;">🏆 Achievements</h2>
            <div class="badge-grid">
                <?php foreach ($badges as $badge): ?>
                <div class="badge <?php echo $badge['earned'] ? 'earned' : ''; ?>">
                    <div class="badge-icon"><?php echo $badge['icon']; ?></div>
                    <div style="font-weight: 600;"><?php echo $badge['name']; ?></div>
                    <?php if (!$badge['earned']): ?>
                    <div style="font-size: 0.75rem; margin-top: 0.25rem; opacity: 0.7;">Locked</div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>