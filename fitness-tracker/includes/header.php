<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Fitness Tracker'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">💪 FitTrack</a>
                </div>
                <nav class="main-nav">
                    <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a>
                    <a href="calculators.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'calculators.php' ? 'active' : ''; ?>">Calculators</a>
                    <a href="workouts.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'workouts.php' ? 'active' : ''; ?>">Workouts</a>
                    <a href="pages/challenges.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'challenges.php' ? 'active' : ''; ?>">Challenges</a>
                    <a href="tracker.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'tracker.php' ? 'active' : ''; ?>">Tracker</a>
                    <a href="progress.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'progress.php' ? 'active' : ''; ?>">Progress</a>
                    <a href="pages/blog.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'blog.php' ? 'active' : ''; ?>">Blog</a>
                    <a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">About</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="logout.php" class="btn-login">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn-login">Login/Signup</a>
                    <?php endif; ?>
                </nav>
                <button class="mobile-menu-toggle" onclick="toggleMenu()">☰</button>
            </div>
        </div>
    </header>
    <div class="mobile-nav" id="mobileNav">
        <a href="index.php">Home</a>
        <a href="calculators.php">Calculators</a>
        <a href="workouts.php">Workouts</a>
        <a href="pages/challenges.php">Challenges</a>
        <a href="tracker.php">Tracker</a>
        <a href="progress.php">Progress</a>
        <a href="pages/blog.php">Blog</a>
        <a href="about.php">About</a>
        <?php if (isLoggedIn()): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login/Signup</a>
        <?php endif; ?>
    </div>