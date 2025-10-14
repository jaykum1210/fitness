<?php
/**
 * Database Installation Script
 * Run this once to set up the database and initial data
 */

require_once 'includes/database.php';

// Check if database connection is working
if (!testConnection()) {
    die("Database connection failed. Please check your database configuration in includes/database.php");
}

echo "<h1>Fitness Tracker Database Installation</h1>";
echo "<p>Setting up database tables and initial data...</p>";

try {
    // Read and execute schema
    $schema = file_get_contents('database/schema.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    $pdo = getDB();
    $pdo->beginTransaction();
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE)/i', $statement)) {
            $pdo->exec($statement);
        }
    }
    
    $pdo->commit();
    
    echo "<div style='color: green; padding: 1rem; background: #d1fae5; border-radius: 8px; margin: 1rem 0;'>";
    echo "✅ Database installation completed successfully!";
    echo "</div>";
    
    echo "<h2>Next Steps:</h2>";
    echo "<ol>";
    echo "<li>Delete or rename this install.php file for security</li>";
    echo "<li>Visit <a href='register.php'>register.php</a> to create your first user account</li>";
    echo "<li>Start using the fitness tracker!</li>";
    echo "</ol>";
    
    echo "<h2>Database Tables Created:</h2>";
    echo "<ul>";
    echo "<li>users - User accounts and profiles</li>";
    echo "<li>workout_categories - Workout categories</li>";
    echo "<li>workouts - Workout routines</li>";
    echo "<li>exercises - Individual exercises</li>";
    echo "<li>workout_exercises - Workout-exercise relationships</li>";
    echo "<li>workout_sessions - User workout sessions</li>";
    echo "<li>exercise_logs - Individual exercise performance</li>";
    echo "<li>user_progress - Progress tracking data</li>";
    echo "<li>user_goals - User fitness goals</li>";
    echo "<li>challenges - Fitness challenges</li>";
    echo "<li>user_challenges - User challenge participation</li>";
    echo "<li>blog_posts - Blog articles</li>";
    echo "<li>user_favorites - User favorite workouts</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 1rem; background: #fee2e2; border-radius: 8px; margin: 1rem 0;'>";
    echo "❌ Installation failed: " . $e->getMessage();
    echo "</div>";
    
    if (isset($pdo)) {
        $pdo->rollback();
    }
}

echo "<hr>";
echo "<p><small>Installation completed at " . date('Y-m-d H:i:s') . "</small></p>";
?>
