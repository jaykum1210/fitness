<?php 
$pageTitle = 'Community - Fitness Tracker';
include '../includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">👥 Fitness Community</h1>
        <p style="text-align: center; color: var(--text-light); max-width: 700px; margin: 0 auto 3rem;">
            Connect with fellow fitness enthusiasts, share your progress, and stay motivated together!
        </p>

        <div class="cards-grid" style="margin-bottom: 3rem;">
            <div class="card stats-card">
                <div class="stat-icon">👥</div>
                <h3>15,847</h3>
                <p>Active Members</p>
            </div>
            <div class="card stats-card">
                <div class="stat-icon">🏋️</div>
                <h3>2.3M+</h3>
                <p>Workouts Logged</p>
            </div>
            <div class="card stats-card">
                <div class="stat-icon">🔥</div>
                <h3>45M+</h3>
                <p>Calories Burned</p>
            </div>
        </div>

        <div class="community-section">
            <h2 style="margin-bottom: 2rem;">🌟 Success Stories</h2>
            <div class="cards-grid">
                <div class="card success-story-card">
                    <div class="story-avatar">👤</div>
                    <h3>Sarah M.</h3>
                    <p class="story-achievement">Lost 30 lbs in 4 months</p>
                    <p>"FitTrack helped me stay consistent. The daily tracking and progress charts kept me motivated every single day!"</p>
                    <div class="story-stats">
                        <span>💪 180 workouts</span>
                        <span>🔥 35,000 cal burned</span>
                    </div>
                </div>

                <div class="card success-story-card">
                    <div class="story-avatar">👤</div>
                    <h3>Mike R.</h3>
                    <p class="story-achievement">Gained 15 lbs muscle</p>
                    <p>"The workout library and nutrition guides made it so easy to follow a structured plan. Best decision ever!"</p>
                    <div class="story-stats">
                        <span>💪 220 workouts</span>
                        <span>📈 6 month streak</span>
                    </div>
                </div>

                <div class="card success-story-card">
                    <div class="story-avatar">👤</div>
                    <h3>Emma L.</h3>
                    <p class="story-achievement">Ran first marathon</p>
                    <p>"Started from barely running 5K. The challenges pushed me further than I thought possible!"</p>
                    <div class="story-stats">
                        <span>🏃 350 workouts</span>
                        <span>🏆 12 challenges completed</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="community-section" style="margin-top: 4rem;">
            <h2 style="margin-bottom: 2rem;">💬 Community Forums</h2>
            <div class="forum-categories">
                <a href="#" class="forum-card">
                    <div class="forum-icon">💪</div>
                    <div class="forum-info">
                        <h3>Workout Tips & Advice</h3>
                        <p>Share techniques, ask questions, get expert advice</p>
                        <span class="forum-count">2,543 discussions</span>
                    </div>
                </a>

                <a href="#" class="forum-card">
                    <div class="forum-icon">🥗</div>
                    <div class="forum-info">
                        <h3>Nutrition & Recipes</h3>
                        <p>Healthy recipes, meal prep tips, diet advice</p>
                        <span class="forum-count">1,876 discussions</span>
                    </div>
                </a>

                <a href="#" class="forum-card">
                    <div class="forum-icon">🎯</div>
                    <div class="forum-info">
                        <h3>Motivation & Accountability</h3>
                        <p>Stay motivated, find workout buddies, share wins</p>
                        <span class="forum-count">3,102 discussions</span>
                    </div>
                </a>

                <a href="#" class="forum-card">
                    <div class="forum-icon">📈</div>
                    <div class="forum-info">
                        <h3>Progress & Transformations</h3>
                        <p>Share your journey, celebrate milestones</p>
                        <span class="forum-count">4,231 discussions</span>
                    </div>
                </a>
            </div>
        </div>

        <div class="community-cta" style="margin-top: 4rem;">
            <div class="card" style="background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%); color: white; text-align: center; padding: 3rem;">
                <h2 style="color: white; margin-bottom: 1rem;">Join Our Community Today!</h2>
                <p style="opacity: 0.95; margin-bottom: 2rem; font-size: 1.125rem;">
                    Get support, share your progress, and achieve your goals together.
                </p>
                <?php if (!isLoggedIn()): ?>
                <a href="../login.php?signup=1" class="btn btn-primary" style="font-size: 1.125rem;">Sign Up Now</a>
                <?php else: ?>
                <a href="../tracker.php" class="btn btn-primary" style="font-size: 1.125rem;">Start Tracking</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>