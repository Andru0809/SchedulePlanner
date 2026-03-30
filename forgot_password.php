<?php
$pageTitle = "Forgot Password";
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
$token = '';

// Handle token generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_token'])) {
    $identifier = sanitize_input($_POST['identifier']);
    
    if (empty($identifier)) {
        $error = "Please enter your username or email";
    } else {
        // Find user by username or email
        $sql = "SELECT id, username, email FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = "User not found";
        } else {
            $user = $result->fetch_assoc();
            $token = generate_token();
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Delete any existing tokens for this user
            $sql = "DELETE FROM password_reset_tokens WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            
            // Insert new token
            $sql = "INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $user['id'], $token, $expires_at);
            $stmt->execute();
            
            $success = "Password reset token generated successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Planner - Forgot Password</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="auth-page">
    <?php include 'includes/header.php'; ?>
    
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo">
                    <i class="fas fa-calendar-alt"></i>
                    <h1>Schedule Planner</h1>
                </div>
                <p>Step 2: Generate Reset Token</p>
            </div>
            
            <div class="auth-form">
                <h2>Step 1: Enter Identifier</h2>
                <p>Enter your username or email to generate a reset token</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (empty($success)): ?>
                    <form method="POST">
                        <div class="form-group">
                            <label for="identifier">Username or Email</label>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" id="identifier" name="identifier" required>
                            </div>
                        </div>
                        
                        <button type="submit" name="generate_token" class="btn btn-primary btn-block">
                            <i class="fas fa-key"></i> Generate Reset Token
                        </button>
                    </form>
                    
                    <div class="auth-links">
                        <a href="index.php">Back to Login</a>
                        <a href="register.php">Don't have an account? Register</a>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($token)): ?>
                    <div class="token-display" style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0; text-align: center;">
                        <h3 style="margin-bottom: 15px; color: #333;">Your Reset Token</h3>
                        <div style="background: #fff; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 18px; letter-spacing: 2px; border: 2px solid #3498db; color: #3498db;">
                            <?php echo $token; ?>
                        </div>
                        <p style="margin-top: 15px; color: #666; font-size: 14px;">
                            <strong>Important:</strong> Copy this token and use it on the reset password page. It will expire in 1 hour.
                        </p>
                        <div style="margin-top: 20px;">
                            <button onclick="copyToken()" class="btn btn-primary" style="background: #27ae60;">
                                <i class="fas fa-copy"></i> Copy Token
                            </button>
                            <a href="validate_token.php" class="btn" style="background: #3498db; color: white; text-decoration: none; margin-left: 10px;">
                                <i class="fas fa-arrow-right"></i> Next Step: Validate Token
                            </a>
                        </div>
                    </div>
                    
                    <div class="auth-links">
                        <a href="index.php">Back to Login</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Copy token function
        function copyToken() {
            const tokenText = document.querySelector('.token-display div[style*="monospace"]').textContent;
            navigator.clipboard.writeText(tokenText).then(function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Token copied to clipboard',
                    confirmButtonColor: '#3498db',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            }).catch(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to copy token',
                    confirmButtonColor: '#3498db'
                });
            });
        }
        
        // Show alerts using SweetAlert
        <?php if ($error): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo addslashes($error); ?>',
                confirmButtonColor: '#3498db'
            });
        <?php endif; ?>
        
        <?php if ($success): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?php echo addslashes($success); ?>',
                confirmButtonColor: '#3498db'
            });
        <?php endif; ?>
    </script>
</body>
</html>
