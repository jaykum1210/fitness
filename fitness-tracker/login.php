<?php
$pageTitle = 'Login - Fitness Tracker';
include 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$csrfToken = generateCSRFToken();
?>

<section class="section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-box">
                <h2>Welcome Back!</h2>
                <p style="text-align: center; color: var(--text-light); margin-bottom: 2rem;">Sign in to your account to continue your fitness journey.</p>
                
                <form id="loginForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="username">Username or Email</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember" style="margin: 0;">Remember me</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Sign In</button>
                </form>
                
                <div class="auth-switch">
                    <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                </div>
                
                <div id="loginMessage" style="margin-top: 1rem;"></div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const messageDiv = document.getElementById('loginMessage');
    
    try {
        const response = await fetch('api/auth.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            messageDiv.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1000);
        } else {
            messageDiv.innerHTML = '<div class="alert alert-danger">' + (result.error || 'Login failed') + '</div>';
        }
    } catch (error) {
        messageDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
    }
});
</script>

<?php include 'includes/footer.php'; ?>