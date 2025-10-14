<?php 
$pageTitle = 'Login - Fitness Tracker';
include 'includes/header.php';

if (isLoggedIn()) {
    redirect('tracker.php');
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        $users = readJSON(USERS_FILE);
        $found = false;
        
        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                redirect('tracker.php');
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $message = 'Invalid email or password';
            $messageType = 'danger';
        }
    }
    
    if (isset($_POST['signup'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if ($password !== $confirmPassword) {
            $message = 'Passwords do not match';
            $messageType = 'danger';
        } else {
            $users = readJSON(USERS_FILE);
            
            $emailExists = false;
            foreach ($users as $user) {
                if ($user['email'] === $email) {
                    $emailExists = true;
                    break;
                }
            }
            
            if ($emailExists) {
                $message = 'Email already registered';
                $messageType = 'danger';
            } else {
                $newUser = [
                    'id' => uniqid(),
                    'name' => $name,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'created_at' => time()
                ];
                
                $users[] = $newUser;
                writeJSON(USERS_FILE, $users);
                
                $_SESSION['user_id'] = $newUser['id'];
                redirect('tracker.php');
            }
        }
    }
}

$showSignup = isset($_GET['signup']);
?>

<div class="auth-container">
    <div class="container">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>" style="max-width: 450px; margin: 0 auto 2rem;">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <?php if (!$showSignup): ?>
        <div class="auth-box">
            <h2>Login to Your Account</h2>
            
            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="your@email.com">
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>
            
            <div class="auth-switch">
                <p>Don't have an account? <a href="?signup=1">Create one</a></p>
            </div>
        </div>
        <?php else: ?>
        <div class="auth-box">
            <h2>Create Your Account</h2>
            
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="John Doe">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="your@email.com">
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Choose a strong password">
                </div>
                
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required placeholder="Re-enter your password">
                </div>
                
                <button type="submit" name="signup" class="btn btn-success" style="width: 100%;">Sign Up</button>
            </form>
            
            <div class="auth-switch">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>