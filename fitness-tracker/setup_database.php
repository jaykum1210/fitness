<?php
/**
 * Database Setup Script
 * Run this script to initialize the database and create tables
 */

require_once 'includes/database.php';

echo "<h1>FitTrack Database Setup</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 10px 0; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

try {
    // Test database connection
    echo "<div class='info'>Testing database connection...</div>";
    
    if (!testConnection()) {
        throw new Exception("Database connection failed. Please check your database configuration.");
    }
    
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Initialize database
    echo "<div class='info'>Initializing database tables...</div>";
    
    if (initializeDatabase()) {
        echo "<div class='success'>✅ Database tables created successfully</div>";
    } else {
        throw new Exception("Failed to create database tables");
    }
    
    // Test user creation
    echo "<div class='info'>Testing user creation...</div>";
    
    try {
        // Check if demo user exists
        $demoUser = fetchOne("SELECT * FROM users WHERE username = 'demo_user'");
        
        if ($demoUser) {
            echo "<div class='success'>✅ Demo user already exists</div>";
            echo "<pre>Username: " . htmlspecialchars($demoUser['username']) . "</pre>";
            echo "<pre>Email: " . htmlspecialchars($demoUser['email']) . "</pre>";
            echo "<pre>Name: " . htmlspecialchars($demoUser['first_name'] . ' ' . $demoUser['last_name']) . "</pre>";
        } else {
            // Create demo user
            $passwordHash = password_hash('demo123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, is_active, email_verified) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            executeQuery($sql, ['demo_user', 'demo@fittrack.com', $passwordHash, 'Demo', 'User', TRUE, TRUE]);
            
            echo "<div class='success'>✅ Demo user created successfully</div>";
        }
        
        // Test password verification
        $testPassword = 'demo123';
        if (password_verify($testPassword, $demoUser['password_hash'])) {
            echo "<div class='success'>✅ Password verification successful</div>";
        } else {
            echo "<div class='error'>❌ Password verification failed</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error with demo user: " . $e->getMessage() . "</div>";
    }
    
    // Test session creation
    echo "<div class='info'>Testing session management...</div>";
    
    try {
        $sessionToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $sql = "INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)";
        executeQuery($sql, [$demoUser['id'], $sessionToken, $expiresAt]);
        
        echo "<div class='success'>✅ Session created successfully</div>";
        
        // Test session validation
        $session = fetchOne(
            "SELECT s.*, u.* FROM user_sessions s 
             JOIN users u ON s.user_id = u.id 
             WHERE s.session_token = ? AND s.expires_at > NOW() AND u.is_active = 1",
            [$sessionToken]
        );
        
        if ($session) {
            echo "<div class='success'>✅ Session validation successful</div>";
        } else {
            echo "<div class='error'>❌ Session validation failed</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error with session management: " . $e->getMessage() . "</div>";
    }
    
    // Test activity logging
    echo "<div class='info'>Testing activity logging...</div>";
    
    try {
        $sql = "INSERT INTO user_activity_log (user_id, activity_type, ip_address, user_agent) 
                VALUES (?, ?, ?, ?)";
        executeQuery($sql, [$demoUser['id'], 'login', '127.0.0.1', 'Test User Agent']);
        
        echo "<div class='success'>✅ Activity logging successful</div>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error with activity logging: " . $e->getMessage() . "</div>";
    }
    
    // Clean up test data
    echo "<div class='info'>Cleaning up test data...</div>";
    
    try {
        executeQuery("DELETE FROM user_sessions WHERE user_id = ?", [$demoUser['id']]);
        executeQuery("DELETE FROM user_activity_log WHERE user_id = ?", [$demoUser['id']]);
        
        echo "<div class='success'>✅ Test data cleaned up</div>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error cleaning up test data: " . $e->getMessage() . "</div>";
    }
    
    echo "<div class='success'>";
    echo "<h2>🎉 Database Setup Complete!</h2>";
    echo "<p>Your FitTrack database has been successfully initialized.</p>";
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li>✅ Database connection is working</li>";
    echo "<li>✅ All tables have been created</li>";
    echo "<li>✅ Demo user is ready for testing</li>";
    echo "<li>✅ Session management is functional</li>";
    echo "<li>✅ Activity logging is working</li>";
    echo "</ul>";
    echo "<h3>Demo Credentials:</h3>";
    echo "<p><strong>Username:</strong> demo_user</p>";
    echo "<p><strong>Password:</strong> demo123</p>";
    echo "<h3>API Endpoints:</h3>";
    echo "<ul>";
    echo "<li><strong>Register:</strong> POST api/auth.php?action=register</li>";
    echo "<li><strong>Login:</strong> POST api/auth.php?action=login</li>";
    echo "<li><strong>Logout:</strong> POST api/auth.php?action=logout</li>";
    echo "<li><strong>Check Auth:</strong> GET api/auth.php?action=check</li>";
    echo "<li><strong>Profile:</strong> GET api/auth.php?action=profile</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Database Setup Failed</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Check your database configuration in includes/database.php</li>";
    echo "<li>Ensure MySQL is running</li>";
    echo "<li>Verify database credentials</li>";
    echo "<li>Check file permissions</li>";
    echo "</ul>";
    echo "</div>";
}
?>
