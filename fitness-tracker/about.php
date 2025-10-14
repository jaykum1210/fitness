<?php 
$pageTitle = 'About - Fitness Tracker';
include 'includes/header.php'; 
?>

<section class="section">
    <div class="container about-section">
        <h1 class="section-title">About FitTrack</h1>

        <div style="text-align: center; margin-bottom: 3rem;">
            <div style="font-size: 5rem; margin-bottom: 1rem;">💪</div>
            <p style="font-size: 1.25rem; color: var(--text-light);">Track Your Fitness. Transform Your Life.</p>
        </div>

        <div class="card" style="padding: 3rem; margin-bottom: 2rem;">
            <h2>Why We Built This Platform</h2>
            <p>In a world where fitness information is overwhelming and tracking is complicated, we wanted to create something different. FitTrack was born from a simple idea: fitness tracking should be simple, motivating, and accessible to everyone.</p>
            
            <p>We believe that consistent small steps lead to transformative results. Whether you're just starting your fitness journey or you're a seasoned athlete, FitTrack provides the tools you need to stay accountable and motivated.</p>
        </div>

        <div class="card" style="padding: 3rem; margin-bottom: 2rem; background: var(--bg-gray);">
            <h2>Our Mission</h2>
            <p>Our goal is to make fitness simple and trackable. We want to empower individuals to take control of their health by providing:</p>
            <ul style="margin-left: 2rem; margin-top: 1rem; line-height: 2;">
                <li>Easy-to-use tracking tools for daily workouts</li>
                <li>Accurate fitness calculators for personalized insights</li>
                <li>A library of workouts for all fitness levels</li>
                <li>Progress visualization to celebrate your wins</li>
                <li>Daily motivation to keep you inspired</li>
            </ul>
        </div>

        <div class="team-member">
            <div class="team-member-icon">👤</div>
            <h3>Jay</h3>
            <p style="color: var(--text-light);">Creator & Developer</p>
            <p style="margin-top: 1rem;">Passionate about fitness and technology, bringing the two together to help people achieve their goals.</p>
        </div>

        <div class="contact-info">
            <h2 style="text-align: center; margin-bottom: 1.5rem;">Get In Touch</h2>
            <div style="text-align: center;">
                <p><strong>Email:</strong> support@fittrack.com</p>
                <p><strong>Phone:</strong> (555) 123-4567</p>
                <div style="margin-top: 1.5rem;">
                    <a href="#" style="font-size: 2rem; margin: 0 0.5rem;">📘</a>
                    <a href="#" style="font-size: 2rem; margin: 0 0.5rem;">📷</a>
                    <a href="#" style="font-size: 2rem; margin: 0 0.5rem;">🐦</a>
                    <a href="#" style="font-size: 2rem; margin: 0 0.5rem;">📺</a>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 3rem; padding: 2rem; background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%); color: white; border-radius: 12px;">
            <h2 style="color: white; margin-bottom: 1rem;">Ready to Start Your Journey?</h2>
            <p style="margin-bottom: 2rem; opacity: 0.95;">Join thousands of users transforming their lives, one workout at a time.</p>
            <a href="login.php?signup=1" class="btn btn-primary">Sign Up Now</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>