<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';
$token = sanitize_input($_GET['token']);

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_token = sanitize_input($_POST['token']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($post_token) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Validate token and get user
        $sql = "SELECT t.user_id, t.expires_at, u.username 
                FROM password_reset_tokens t 
                JOIN users u ON t.user_id = u.id 
                WHERE t.token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $post_token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = "Invalid or expired reset token";
        } else {
            $token_data = $result->fetch_assoc();
            $expires_at = $token_data['expires_at'];
            
            if (strtotime($expires_at) < time()) {
                $error = "Reset token has expired";
            } else {
                // Update password
                $hashed_password = hash_password($password);
                $user_id = $token_data['user_id'];
                
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($stmt->execute()) {
                    // Delete the token
                    $sql = "DELETE FROM password_reset_tokens WHERE token = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $post_token);
                    $stmt->execute();
                    
                    $success = "Password reset successful! You can now login.";
                    
                    // Redirect to login after 3 seconds
                    header("refresh:3;url=index.php");
                } else {
                    $error = "Password reset failed. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Planner - Reset Password</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Theme Toggle -->
            <div class="theme-toggle">
                <button class="theme-btn" id="themeToggle">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
            
            <div class="auth-header">
                <div class="logo">
                    <i class="fas fa-calendar-alt"></i>
                    <h1>Schedule Planner</h1>
                </div>
                <p>Step 5: Change Password</p>
            </div>
            
            <?php if (isset($error) && !empty($error)): ?>
                <div class="auth-form">
                    <div class="alert alert-error"><?php echo $error; ?></div>
                    <div class="auth-links">
                        <a href="index.php">Back to Login</a>
                    </div>
                </div>
            <?php elseif (isset($success) && !empty($success)): ?>
                <div class="auth-form">
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <div class="auth-links">
                        <a href="index.php">Go to Login</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-form">
                    <h2>Reset Password</h2>
                    <p>Token validated! Enter your new password below</p>
                    
                    <form method="POST">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                    </form>
                    
                    <div class="auth-links">
                        <a href="index.php">Back to Login</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Theme toggle
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        
        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme') || 'light';
        body.classList.toggle('dark-theme', savedTheme === 'dark');
        updateThemeIcon();
        
        themeToggle.addEventListener('click', function() {
            body.classList.toggle('dark-theme');
            const currentTheme = body.classList.contains('dark-theme') ? 'dark' : 'light';
            localStorage.setItem('theme', currentTheme);
            updateThemeIcon();
        });
        
        function updateThemeIcon() {
            const icon = themeToggle.querySelector('i');
            if (body.classList.contains('dark-theme')) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        }
        
        // Show alerts using SweetAlert
        <?php if (isset($error) && !empty($error)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo addslashes($error); ?>',
                confirmButtonColor: '#3498db'
            });
        <?php endif; ?>
        
        <?php if (isset($success) && !empty($success)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?php echo addslashes($success); ?>',
                confirmButtonColor: '#3498db',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        <?php endif; ?>
        
        // Password confirmation validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        password.addEventListener('input', function() {
            if (confirmPassword.value && this.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        });
        
        confirmPassword.addEventListener('input', function() {
            if (this.value !== password.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
