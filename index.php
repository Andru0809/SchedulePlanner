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

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        $sql = "SELECT id, username, email, password, full_name FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (verify_password($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['last_activity'] = time();
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }
    }
}

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_request'])) {
    $email = sanitize_input($_POST['email']);
    
    if (empty($email)) {
        $error = "Please enter your email address";
    } elseif (!validate_email($email)) {
        $error = "Invalid email format";
    } else {
        $sql = "SELECT id, username FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
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
            
            // Send reset email (in production, you'd use a proper email service)
            $reset_link = "http://localhost/schedule-planner/reset_password.php?token=" . $token;
            $subject = "Password Reset Request";
            $message = "Hello " . $user['username'] . ",<br><br>";
            $message .= "Click the link below to reset your password:<br>";
            $message .= "<a href='" . $reset_link . "'>" . $reset_link . "</a><br><br>";
            $message .= "This link will expire in 1 hour.<br><br>";
            $message .= "If you didn't request this, please ignore this email.";
            
            if (send_email($email, $subject, $message)) {
                $success = "Password reset link has been sent to your email";
            } else {
                $error = "Failed to send reset email. Please try again.";
            }
        } else {
            $error = "Email not found in our system";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Planner - Login</title>
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
                <p>Welcome back! Please login to your account</p>
            </div>
            
            <div class="auth-form" id="loginForm">
                <h2>Login</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username or Email</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="username" name="username" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>
                </form>
                
                <div class="auth-links">
                    <a href="forgot_password.php">Forgot Password?</a>
                    <a href="register.php">Don't have an account? Register</a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
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
                confirmButtonColor: '#3498db',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                window.location.href = 'index.php';
            });
        <?php endif; ?>
    </script>
</body>
</html>
