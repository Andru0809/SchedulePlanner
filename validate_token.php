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

// Handle token validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = sanitize_input($_POST['token']);
    
    if (empty($token)) {
        $error = "Please enter the reset token";
    } else {
        // Validate token and get user
        $sql = "SELECT t.user_id, t.expires_at, u.username 
                FROM password_reset_tokens t 
                JOIN users u ON t.user_id = u.id 
                WHERE t.token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
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
                // Token is valid, redirect to reset password page with token
                header("Location: reset_password.php?token=" . urlencode($token));
                exit();
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
    <title>Schedule Planner - Validate Token</title>
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
                <p>Enter your reset token</p>
            </div>
            
            <div class="auth-form">
                <h2>Step 4: Validate Token</h2>
                <p>Copy the token you received and paste it below</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="token">Reset Token</label>
                        <div class="input-group">
                            <i class="fas fa-key"></i>
                            <input type="text" id="token" name="token" required 
                                   placeholder="Enter your 10-character token" 
                                   maxlength="10" style="text-transform: uppercase; letter-spacing: 2px;">
                        </div>
                        <small style="color: #999; font-size: 12px;">Enter the 10-character token you received</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-check"></i> Validate Token
                    </button>
                </form>
                
                <div class="auth-links">
                    <a href="forgot_password.php">← Back to Generate Token</a>
                    <a href="index.php">Back to Login</a>
                </div>
            </div>
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
        
        // Auto-format token input
        const tokenInput = document.getElementById('token');
        tokenInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // Show alerts using SweetAlert
        <?php if ($error): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo addslashes($error); ?>',
                confirmButtonColor: '#3498db'
            });
        <?php endif; ?>
    </script>
</body>
</html>
