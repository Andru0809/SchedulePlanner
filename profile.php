<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';

// Check if user is logged in
require_login();

// Get current user
$current_user = get_logged_in_user();

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($full_name) || empty($email)) {
        $error = "Please fill in all required fields";
    } elseif (!validate_email($email)) {
        $error = "Invalid email format";
    } else {
        // Check if email is being changed and if it's already taken
        if ($email !== $current_user['email']) {
            $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $email, $current_user['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Email is already taken by another user";
            }
        }
        
        if (empty($error)) {
            // Update basic info
            $sql = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $full_name, $email, $current_user['id']);
            
            if ($stmt->execute()) {
                // Handle password change if provided
                if (!empty($current_password) && !empty($new_password)) {
                    if (strlen($new_password) < 6) {
                        $error = "New password must be at least 6 characters long";
                    } elseif ($new_password !== $confirm_password) {
                        $error = "New passwords do not match";
                    } else {
                        // Verify current password
                        $sql = "SELECT password FROM users WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $current_user['id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                        
                        if (verify_password($current_password, $user['password'])) {
                            // Update password
                            $hashed_password = hash_password($new_password);
                            $sql = "UPDATE users SET password = ? WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("si", $hashed_password, $current_user['id']);
                            
                            if (!$stmt->execute()) {
                                $error = "Failed to update password";
                            }
                        } else {
                            $error = "Current password is incorrect";
                        }
                    }
                }
                
                if (empty($error)) {
                    $success = "Profile updated successfully";
                    // Refresh user data
                    $current_user = get_logged_in_user();
                }
            } else {
                $error = "Failed to update profile";
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
    <title>Schedule Planner - Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedule Planner</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-calendar"></i>
                    <span>Schedule</span>
                </a>
                <a href="add_schedule.php" class="nav-item">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Schedule</span>
                </a>
                <a href="timetable.php" class="nav-item">
                    <i class="fas fa-table"></i>
                    <span>Timetable</span>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <!-- Content -->
            <div class="content">
                <div class="schedule-container">
                    <div style="padding: 30px;">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px;">
                            <div class="user-avatar" style="width: 80px; height: 80px; font-size: 32px; display: flex; align-items: center; justify-content: center;">
                                <?php echo strtoupper(substr($current_user['full_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <h2 class="profile-name">
                                    <?php echo htmlspecialchars($current_user['full_name']); ?>
                                </h2>
                                <p class="profile-username">
                                    @<?php echo htmlspecialchars($current_user['username']); ?>
                                </p>
                                <p class="profile-member-date">
                                    Member since <?php echo format_date($current_user['created_at']); ?>
                                </p>
                            </div>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-error"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <h3 class="profile-section-title">Edit Profile</h3>
                            
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <div class="input-group">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="full_name" name="full_name" required 
                                           value="<?php echo htmlspecialchars($current_user['full_name']); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <div class="input-group">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" id="email" name="email" required 
                                           value="<?php echo htmlspecialchars($current_user['email']); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="username">Username</label>
                                <div class="input-group">
                                    <i class="fas fa-user-tag"></i>
                                    <input type="text" id="username" value="<?php echo htmlspecialchars($current_user['username']); ?>" readonly
                                           class="readonly-input">
                                </div>
                                <small class="form-help-text">Username cannot be changed</small>
                            </div>
                            
                            <hr class="profile-divider">
                            
                            <h3 class="profile-section-title">Change Password</h3>
                            <p class="profile-section-desc">Leave blank if you don't want to change your password</p>
                            
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="current_password" name="current_password">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="new_password" name="new_password">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }
        
        // User profile dropdown
        const userProfile = document.getElementById('userProfile');
        let profileDropdown = null;
        
        if (userProfile) {
            userProfile.addEventListener('click', function(e) {
                e.stopPropagation();
                
                if (profileDropdown) {
                    profileDropdown.remove();
                    profileDropdown = null;
                } else {
                    createProfileDropdown();
                }
            });
        }
        
        function createProfileDropdown() {
            profileDropdown = document.createElement('div');
            profileDropdown.className = 'profile-dropdown';
            profileDropdown.style.cssText = `
                position: absolute;
                top: 100%;
                right: 0;
                background: white;
                border-radius: 10px;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
                padding: 10px;
                min-width: 200px;
                z-index: 1000;
                margin-top: 10px;
            `;
            
            if (body.classList.contains('dark-theme')) {
                profileDropdown.style.background = '#2c3e50';
                profileDropdown.style.color = '#fff';
            }
            
            profileDropdown.innerHTML = `
                <a href="profile.php" style="display: block; padding: 10px; color: inherit; text-decoration: none; border-radius: 5px; transition: background 0.3s;">
                    <i class="fas fa-user"></i> Profile
                </a>
                <a href="logout.php" style="display: block; padding: 10px; color: #e74c3c; text-decoration: none; border-radius: 5px; transition: background 0.3s;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            `;
            
            userProfile.appendChild(profileDropdown);
            
            // Add hover effects
            const links = profileDropdown.querySelectorAll('a');
            links.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.background = body.classList.contains('dark-theme') ? '#34495e' : '#f8f9fa';
                });
                link.addEventListener('mouseleave', function() {
                    this.style.background = 'transparent';
                });
            });
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            if (profileDropdown) {
                profileDropdown.remove();
                profileDropdown = null;
            }
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
        
        <?php if ($success): ?>
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
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        
        newPassword.addEventListener('input', function() {
            if (confirmPassword.value && this.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        });
        
        confirmPassword.addEventListener('input', function() {
            if (this.value !== newPassword.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
