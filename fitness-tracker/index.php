<?php
$pageTitle = 'Home - Fitness Tracker';
include 'includes/header.php';

// Get user data if logged in
$user = null;
$userStats = null;
if (isLoggedIn()) {
    $user = getLoggedInUser();
    if ($user && isDatabaseAvailable()) {
        try {
            // Get user's recent workout stats
            $recentWorkouts = fetchAll("SELECT COUNT(*) as count FROM workout_sessions WHERE user_id = ? AND completed_at IS NOT NULL", [$user['id']]);
            $totalCalories = fetchOne("SELECT SUM(calories_burned) as total FROM workout_sessions WHERE user_id = ? AND completed_at IS NOT NULL", [$user['id']]);
            $userStats = [
                'total_workouts' => $recentWorkouts[0]['count'] ?? 0,
                'total_calories' => $totalCalories['total'] ?? 0
            ];
        } catch (Exception $e) {
            // Fallback if database query fails
            $userStats = ['total_workouts' => 0, 'total_calories' => 0];
        }
    }
}

// Get featured workouts from database
$featuredWorkouts = [];
if (isDatabaseAvailable()) {
    try {
        $featuredWorkouts = fetchAll("SELECT w.*, wc.name as category_name, wc.icon as category_icon 
                                     FROM workouts w 
                                     LEFT JOIN workout_categories wc ON w.category_id = wc.id 
                                     WHERE w.is_active = 1 
                                     ORDER BY w.created_at DESC 
                                     LIMIT 4");
    } catch (Exception $e) {
        // Fallback to empty array if database fails
        $featuredWorkouts = [];
    }
}

// Get latest blog posts
$blogPosts = [];
if (isDatabaseAvailable()) {
    try {
        $blogPosts = fetchAll("SELECT * FROM blog_posts WHERE is_published = 1 ORDER BY published_at DESC LIMIT 3");
    } catch (Exception $e) {
        // Fallback to empty array if database fails
        $blogPosts = [];
    }
}
?>

<section class="hero">
    <div class="container">
        <?php if ($user): ?>
            <h1 class="gradient-text">Welcome back, <?php echo htmlspecialchars($user['first_name'] ?: $user['username']); ?>!</h1>
            <p>Ready to continue your fitness journey? You've completed <?php echo $userStats['total_workouts']; ?> workouts and burned <?php echo $userStats['total_calories']; ?> calories!</p>
        <?php else: ?>
            <h1 class="gradient-text">Track Your Fitness. Transform Your Life.</h1>
            <p>Build sustainable habits with smart tracking, curated workouts, and everyday motivation.</p>
        <?php endif; ?>
        
        <img src="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=1600&auto=format&fit=crop" alt="Athlete training in a modern gym" class="hero-image" />
        <div class="hero-buttons">
            <?php if ($user): ?>
                <a href="tracker.php" class="btn btn-primary">Continue Tracking</a>
                <a href="progress.php" class="btn btn-secondary">View Progress</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary">Get Started</a>
                <a href="workouts.php" class="btn btn-secondary">Explore Workouts</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Stay Inspired Every Day</h2>
        <div class="quote-card">
            <p id="motivationalQuote">"The only bad workout is the one that didn't happen."</p>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <h2 class="section-title">Quick Calculators</h2>
        <div class="cards-grid">
            <div class="card">
                <div class="card-icon">📊</div>
                <h3>BMI Calculator</h3>
                <p>Check your Body Mass Index</p>
                <a href="calculators.php#bmi" class="btn btn-primary">Calculate</a>
            </div>
            <div class="card">
                <div class="card-icon">🔥</div>
                <h3>Calories Calculator</h3>
                <p>Find your daily calorie needs</p>
                <a href="calculators.php#calories" class="btn btn-primary">Calculate</a>
            </div>
            <div class="card">
                <div class="card-icon">💧</div>
                <h3>Water Intake</h3>
                <p>Calculate daily water requirement</p>
                <a href="calculators.php#water" class="btn btn-primary">Calculate</a>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Featured Workouts</h2>
        <div class="cards-grid">
            <?php if (!empty($featuredWorkouts)): ?>
                <?php foreach ($featuredWorkouts as $workout): ?>
                    <div class="card">
                        <div class="card-icon"><?php echo htmlspecialchars($workout['category_icon'] ?? '💪'); ?></div>
                        <h3><?php echo htmlspecialchars($workout['name']); ?></h3>
                        <p><?php echo htmlspecialchars($workout['description']); ?></p>
                        <div class="workout-meta">
                            <span><?php echo $workout['duration_minutes']; ?> min</span>
                            <span><?php echo ucfirst($workout['difficulty_level']); ?></span>
                            <span><?php echo $workout['calories_burned']; ?> cal</span>
                        </div>
                        <a href="workouts.php?id=<?php echo $workout['id']; ?>" class="btn btn-primary">Start Workout</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback content if no workouts available -->
                <div class="card">
                    <div class="card-icon">💪</div>
                    <h3>Full Body</h3>
                    <p>Complete workout for all muscle groups</p>
                    <a href="workouts.php" class="btn btn-primary">View Workouts</a>
                </div>
                <div class="card">
                    <div class="card-icon">🦵</div>
                    <h3>Legs Day</h3>
                    <p>Build powerful lower body strength</p>
                    <a href="workouts.php" class="btn btn-primary">View Workouts</a>
                </div>
                <div class="card">
                    <div class="card-icon">💪</div>
                    <h3>Upper Body</h3>
                    <p>Chest, back, shoulders & arms</p>
                    <a href="workouts.php" class="btn btn-primary">View Workouts</a>
                </div>
                <div class="card">
                    <div class="card-icon">🎯</div>
                    <h3>Abs & Core</h3>
                    <p>Strengthen your core muscles</p>
                    <a href="workouts.php" class="btn btn-primary">View Workouts</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <h2 class="section-title">📚 Latest from Our Blog</h2>
        <div class="cards-grid">
            <?php if (!empty($blogPosts)): ?>
                <?php foreach ($blogPosts as $post): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                        <div class="blog-meta">
                            <span><?php echo formatDate($post['published_at'], 'M j, Y'); ?></span>
                            <span><?php echo htmlspecialchars($post['category']); ?></span>
                        </div>
                        <a href="pages/blog.php?id=<?php echo $post['id']; ?>" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">Read Article</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback content if no blog posts available -->
                <div class="card">
                    <h3>10 Beginner Mistakes to Avoid</h3>
                    <p>Learn the most common gym mistakes and how to prevent them from slowing your progress.</p>
                    <a href="pages/blog.php" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">Read Article</a>
                </div>
                <div class="card">
                    <h3>The Science Behind Muscle Growth</h3>
                    <p>Understanding hypertrophy will help you train smarter and see better results.</p>
                    <a href="pages/blog.php" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">Read Article</a>
                </div>
                <div class="card">
                    <h3>Nutrition Timing Tips</h3>
                    <p>When to eat for optimal performance and recovery throughout your day.</p>
                    <a href="pages/blog.php" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">Read Article</a>
                </div>
            <?php endif; ?>
        </div>
        <div style="text-align: center; margin-top: 2rem;">
            <a href="pages/blog.php" class="btn btn-primary">Visit Blog</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container" style="text-align: center;">
        <h2 class="section-title">⭐ Success Stories</h2>
        <p style="color: var(--text-light); max-width: 700px; margin: 0 auto 2rem;">
            Real people. Real results. Get inspired by transformations from our community of everyday athletes.
        </p>
        <div class="testimonial-section">
            <p class="testimonial-quote">"I've never felt stronger or more consistent. The tracker made it simple."</p>
            <p class="testimonial-author">— Alex, lost 18 lbs in 12 weeks</p>
        </div>
    </div>
</section>

<section class="section surprise-section">
    <div class="container">
        <h2 class="section-title">🎉 Surprise Me!</h2>
        <button onclick="surpriseMe()" class="btn btn-surprise">Give Me Something Random!</button>
        <div id="surpriseResult" class="surprise-result"></div>
    </div>
</section>

<script>
// Motivational quotes
const quotes = [
    "The only bad workout is the one that didn't happen.",
    "Strength doesn't come from what you can do. It comes from overcoming the things you once thought you couldn't.",
    "The body achieves what the mind believes.",
    "Don't wish for it, work for it.",
    "Success starts with self-discipline.",
    "The only way to do great work is to love what you do.",
    "Push yourself because no one else is going to do it for you.",
    "The pain you feel today will be the strength you feel tomorrow.",
    "Your body can stand almost anything. It's your mind you have to convince.",
    "The difference between try and triumph is just a little umph!"
];

// Update quote every 5 seconds
let quoteIndex = 0;
setInterval(() => {
    quoteIndex = (quoteIndex + 1) % quotes.length;
    document.getElementById('motivationalQuote').textContent = `"${quotes[quoteIndex]}"`;
}, 5000);

// Surprise function
function surpriseMe() {
    const surprises = [
        "🎯 Today's challenge: Do 20 push-ups!",
        "💪 Fun fact: Your heart is the strongest muscle in your body!",
        "🏃‍♀️ Did you know? Regular exercise can improve your memory!",
        "🥗 Nutrition tip: Drink a glass of water before every meal!",
        "🧘‍♀️ Mindfulness moment: Take 5 deep breaths right now!",
        "🔥 Motivation: You're stronger than you think!",
        "⚡ Energy boost: Stand up and stretch for 30 seconds!",
        "🌟 Remember: Progress, not perfection!",
        "💡 Tip: Consistency beats intensity every time!",
        "🎉 You're doing great! Keep up the amazing work!"
    ];
    
    const randomSurprise = surprises[Math.floor(Math.random() * surprises.length)];
    const resultDiv = document.getElementById('surpriseResult');
    
    resultDiv.innerHTML = `<p>${randomSurprise}</p>`;
    resultDiv.classList.add('show');
    
    setTimeout(() => {
        resultDiv.classList.remove('show');
    }, 5000);
}

// Check authentication status and update UI
async function checkAuthStatus() {
    try {
        const response = await fetch('api/auth.php?action=check');
        const result = await response.json();
        
        if (result.success && result.data) {
            // User is logged in, update UI if needed
            console.log('User is authenticated:', result.data.username);
        }
    } catch (error) {
        console.log('Auth check failed:', error);
    }
}

// Run auth check on page load
document.addEventListener('DOMContentLoaded', checkAuthStatus);
</script>

<?php include 'includes/footer.php'; ?>