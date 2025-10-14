<?php
/**
 * Test Authentication System
 * This script tests the login and signup functionality
 */

require_once 'includes/config.php';

echo "<h1>FitTrack Authentication Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .success { background: #d4edda; border-color: #c3e6cb; }
    .error { background: #f8d7da; border-color: #f5c6cb; }
    .info { background: #d1ecf1; border-color: #bee5eb; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

// Test 1: Database Connection
echo "<div class='test-section'>";
echo "<h2>Test 1: Database Connection</h2>";
try {
    if (isDatabaseAvailable()) {
        echo "<div class='success'>✅ Database connection successful</div>";
    } else {
        echo "<div class='error'>❌ Database connection failed</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Database error: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 2: Check if demo user exists
echo "<div class='test-section'>";
echo "<h2>Test 2: Demo User Check</h2>";
try {
    $demoUser = fetchOne("SELECT * FROM users WHERE username = 'demo_user'");
    if ($demoUser) {
        echo "<div class='success'>✅ Demo user exists</div>";
        echo "<pre>Username: " . htmlspecialchars($demoUser['username']) . "</pre>";
        echo "<pre>Email: " . htmlspecialchars($demoUser['email']) . "</pre>";
        echo "<pre>Name: " . htmlspecialchars($demoUser['first_name'] . ' ' . $demoUser['last_name']) . "</pre>";
    } else {
        echo "<div class='error'>❌ Demo user not found</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error checking demo user: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 3: Test password verification
echo "<div class='test-section'>";
echo "<h2>Test 3: Password Verification</h2>";
try {
    $demoUser = fetchOne("SELECT * FROM users WHERE username = 'demo_user'");
    if ($demoUser) {
        $testPassword = 'demo123';
        if (verifyPassword($testPassword, $demoUser['password_hash'])) {
            echo "<div class='success'>✅ Password verification successful</div>";
        } else {
            echo "<div class='error'>❌ Password verification failed</div>";
        }
    } else {
        echo "<div class='error'>❌ Cannot test password - demo user not found</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error testing password: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 4: Test user creation
echo "<div class='test-section'>";
echo "<h2>Test 4: User Creation Test</h2>";
try {
    $testUsername = 'test_user_' . time();
    $testEmail = 'test' . time() . '@example.com';
    $testPassword = 'test123';
    
    // Check if user already exists
    $existingUser = fetchOne("SELECT id FROM users WHERE username = ? OR email = ?", [$testUsername, $testEmail]);
    if ($existingUser) {
        echo "<div class='info'>ℹ️ Test user already exists, skipping creation</div>";
    } else {
        // Create test user
        $passwordHash = hashPassword($testPassword);
        $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        executeQuery($sql, [$testUsername, $testEmail, $passwordHash, 'Test', 'User']);
        $userId = getLastInsertId();
        
        echo "<div class='success'>✅ Test user created successfully</div>";
        echo "<pre>User ID: $userId</pre>";
        echo "<pre>Username: $testUsername</pre>";
        echo "<pre>Email: $testEmail</pre>";
        
        // Clean up test user
        executeQuery("DELETE FROM users WHERE id = ?", [$userId]);
        echo "<div class='info'>ℹ️ Test user cleaned up</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error creating test user: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 5: Session functionality
echo "<div class='test-section'>";
echo "<h2>Test 5: Session Functionality</h2>";
try {
    // Test CSRF token generation
    $csrfToken = generateCSRFToken();
    if ($csrfToken && strlen($csrfToken) > 0) {
        echo "<div class='success'>✅ CSRF token generation successful</div>";
        echo "<pre>Token: " . htmlspecialchars($csrfToken) . "</pre>";
    } else {
        echo "<div class='error'>❌ CSRF token generation failed</div>";
    }
    
    // Test CSRF token validation
    if (validateCSRFToken($csrfToken)) {
        echo "<div class='success'>✅ CSRF token validation successful</div>";
    } else {
        echo "<div class='error'>❌ CSRF token validation failed</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error testing session functionality: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 6: API endpoints availability
echo "<div class='test-section'>";
echo "<h2>Test 6: API Endpoints</h2>";
$apiFile = 'api/auth.php';
if (file_exists($apiFile)) {
    echo "<div class='success'>✅ Auth API file exists</div>";
    echo "<pre>File: $apiFile</pre>";
} else {
    echo "<div class='error'>❌ Auth API file not found</div>";
}
echo "</div>";

// Test 7: HTML pages availability
echo "<div class='test-section'>";
echo "<h2>Test 7: HTML Pages</h2>";
$pages = ['login.html', 'signup.html', 'login.php', 'register.php'];
foreach ($pages as $page) {
    if (file_exists($page)) {
        echo "<div class='success'>✅ $page exists</div>";
    } else {
        echo "<div class='error'>❌ $page not found</div>";
    }
}
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>Summary</h2>";
echo "<p>Authentication system test completed. Check the results above to ensure all components are working correctly.</p>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ul>";
echo "<li>If all tests pass, you can use the login and signup pages</li>";
echo "<li>Demo credentials: username 'demo_user', password 'demo123'</li>";
echo "<li>Access login page: <a href='login.html'>login.html</a> or <a href='login.php'>login.php</a></li>";
echo "<li>Access signup page: <a href='signup.html'>signup.html</a> or <a href='register.php'>register.php</a></li>";
echo "</ul>";
echo "</div>";
?>
