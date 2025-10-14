<?php
/**
 * Authentication Functions
 * Handles user registration, login, and session management
 */

require_once __DIR__ . '/database.php';

/**
 * Register a new user
 */
function registerUser($username, $email, $password, $firstName, $lastName) {
    try {
        // Check if user already exists
        $existingUser = fetchOne("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
        if ($existingUser) {
            throw new Exception('Username or email already exists');
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate email verification token
        $verificationToken = bin2hex(random_bytes(32));
        $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Begin transaction
        beginTransaction();
        
        // Insert user
        $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        executeQuery($sql, [$username, $email, $passwordHash, $firstName, $lastName]);
        $userId = getLastInsertId();
        
        // Insert verification token
        $sql = "INSERT INTO email_verification_tokens (user_id, token, expires_at) 
                VALUES (?, ?, ?)";
        executeQuery($sql, [$userId, $verificationToken, $tokenExpiry]);
        
        // Log activity
        logUserActivity($userId, 'register');
        
        commit();
        
        return [
            'user_id' => $userId,
            'username' => $username,
            'email' => $email,
            'verification_token' => $verificationToken
        ];
        
    } catch (Exception $e) {
        rollback();
        throw $e;
    }
}

/**
 * Authenticate user login
 */
function authenticateUser($username, $password, $rememberMe = false) {
    try {
        // Get user by username or email
        $user = fetchOne("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1", [$username, $username]);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new Exception('Invalid username or password');
        }
        
        // Update last login
        executeQuery("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
        
        // Create session
        $sessionToken = createUserSession($user['id'], $rememberMe);
        
        // Log activity
        logUserActivity($user['id'], 'login');
        
        // Remove sensitive data
        unset($user['password_hash']);
        
        return [
            'user' => $user,
            'session_token' => $sessionToken
        ];
        
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Create user session
 */
function createUserSession($userId, $rememberMe = false) {
    $sessionToken = bin2hex(random_bytes(32));
    $expiresAt = $rememberMe ? 
        date('Y-m-d H:i:s', strtotime('+30 days')) : 
        date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    $sql = "INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)";
    executeQuery($sql, [$userId, $sessionToken, $expiresAt]);
    
    // Set session cookie
    $cookieExpiry = $rememberMe ? time() + (30 * 24 * 60 * 60) : 0;
    setcookie('session_token', $sessionToken, $cookieExpiry, '/', '', false, true);
    
    return $sessionToken;
}

/**
 * Validate session token
 */
function validateSession($sessionToken) {
    try {
        $session = fetchOne(
            "SELECT s.*, u.* FROM user_sessions s 
             JOIN users u ON s.user_id = u.id 
             WHERE s.session_token = ? AND s.expires_at > NOW() AND u.is_active = 1",
            [$sessionToken]
        );
        
        if (!$session) {
            return null;
        }
        
        // Remove sensitive data
        unset($session['password_hash']);
        
        return $session;
        
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Logout user
 */
function logoutUser($sessionToken = null) {
    try {
        if ($sessionToken) {
            // Delete specific session
            executeQuery("DELETE FROM user_sessions WHERE session_token = ?", [$sessionToken]);
        } else {
            // Delete all sessions for current user
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId) {
                executeQuery("DELETE FROM user_sessions WHERE user_id = ?", [$userId]);
                logUserActivity($userId, 'logout');
            }
        }
        
        // Clear session cookie
        setcookie('session_token', '', time() - 3600, '/', '', false, true);
        
        // Destroy PHP session
        session_destroy();
        
        return true;
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get current user from session
 */
function getCurrentUser() {
    $sessionToken = $_COOKIE['session_token'] ?? null;
    
    if (!$sessionToken) {
        return null;
    }
    
    $session = validateSession($sessionToken);
    
    if ($session) {
        $_SESSION['user_id'] = $session['user_id'];
        $_SESSION['username'] = $session['username'];
        $_SESSION['email'] = $session['email'];
        return $session;
    }
    
    return null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return getCurrentUser() !== null;
}

/**
 * Log user activity
 */
function logUserActivity($userId, $activityType, $ipAddress = null, $userAgent = null) {
    try {
        $ipAddress = $ipAddress ?: $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $userAgent ?: $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $sql = "INSERT INTO user_activity_log (user_id, activity_type, ip_address, user_agent) 
                VALUES (?, ?, ?, ?)";
        executeQuery($sql, [$userId, $activityType, $ipAddress, $userAgent]);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Failed to log user activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate password reset token
 */
function generatePasswordResetToken($email) {
    try {
        $user = fetchOne("SELECT id FROM users WHERE email = ? AND is_active = 1", [$email]);
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Delete existing tokens for this user
        executeQuery("DELETE FROM password_reset_tokens WHERE user_id = ?", [$user['id']]);
        
        // Insert new token
        $sql = "INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)";
        executeQuery($sql, [$user['id'], $token, $expiresAt]);
        
        return $token;
        
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Reset password with token
 */
function resetPasswordWithToken($token, $newPassword) {
    try {
        // Validate token
        $resetToken = fetchOne(
            "SELECT * FROM password_reset_tokens 
             WHERE token = ? AND expires_at > NOW() AND used = 0",
            [$token]
        );
        
        if (!$resetToken) {
            throw new Exception('Invalid or expired token');
        }
        
        // Hash new password
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Begin transaction
        beginTransaction();
        
        // Update password
        executeQuery("UPDATE users SET password_hash = ? WHERE id = ?", [$passwordHash, $resetToken['user_id']]);
        
        // Mark token as used
        executeQuery("UPDATE password_reset_tokens SET used = 1 WHERE id = ?", [$resetToken['id']]);
        
        // Log activity
        logUserActivity($resetToken['user_id'], 'password_change');
        
        commit();
        
        return true;
        
    } catch (Exception $e) {
        rollback();
        throw $e;
    }
}

/**
 * Clean expired sessions and tokens
 */
function cleanExpiredSessions() {
    try {
        // Delete expired sessions
        executeQuery("DELETE FROM user_sessions WHERE expires_at < NOW()");
        
        // Delete expired password reset tokens
        executeQuery("DELETE FROM password_reset_tokens WHERE expires_at < NOW()");
        
        // Delete expired email verification tokens
        executeQuery("DELETE FROM email_verification_tokens WHERE expires_at < NOW()");
        
        return true;
        
    } catch (Exception $e) {
        error_log("Failed to clean expired sessions: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user statistics
 */
function getUserStats($userId) {
    try {
        $stats = fetchOne("SELECT * FROM user_stats WHERE id = ?", [$userId]);
        return $stats;
        
    } catch (Exception $e) {
        return null;
    }
}
?>
