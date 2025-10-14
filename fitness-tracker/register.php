<?php
$pageTitle = 'Sign Up - Fitness Tracker';
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
                <h2>Join FitTrack</h2>
                <p style="text-align: center; color: var(--text-light); margin-bottom: 2rem;">Create your account and start your fitness journey today.</p>
                
                <form id="registerForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required>
                        <small style="color: var(--text-light);">3+ characters, letters, numbers, and underscores only</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required>
                        <small style="color: var(--text-light);">At least 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Create Account</button>
                </form>
                
                <div class="auth-switch">
                    <p>Already have an account? <a href="login.php">Sign in here</a></p>
                </div>
                
                <div id="registerMessage" style="margin-top: 1rem;"></div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const messageDiv = document.getElementById('registerMessage');
    
    // Client-side validation
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        messageDiv.innerHTML = '<div class="alert alert-danger">Passwords do not match</div>';
        return;
    }
    
    if (password.length < 6) {
        messageDiv.innerHTML = '<div class="alert alert-danger">Password must be at least 6 characters long</div>';
        return;
    }
    
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
            let errorMessage = 'Registration failed';
            if (result.errors && result.errors.length > 0) {
                errorMessage = result.errors.join('<br>');
            } else if (result.error) {
                errorMessage = result.error;
            }
            messageDiv.innerHTML = '<div class="alert alert-danger">' + errorMessage + '</div>';
        }
    } catch (error) {
        messageDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
    }
});

// Real-time password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && password !== confirmPassword) {
        this.style.borderColor = '#ef4444';
    } else {
        this.style.borderColor = '#e5e7eb';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
