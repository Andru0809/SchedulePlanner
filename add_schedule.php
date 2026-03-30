<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';

// Check if user is logged in
require_login();

// Get current user
$current_user = get_logged_in_user();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Planner - Add Schedule</title>
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
                <a href="add_schedule.php" class="nav-item active">
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
                        <form id="addScheduleForm">
                            <div class="form-group">
                                <label for="scheduleTitle">Title</label>
                                <div class="input-group">
                                    <i class="fas fa-heading"></i>
                                    <input type="text" id="scheduleTitle" name="title" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="scheduleDescription">Description</label>
                                <div class="input-group">
                                    <i class="fas fa-align-left"></i>
                                    <textarea id="scheduleDescription" name="description" rows="4" style="padding: 12px 15px 12px 45px; border: 2px solid #e1e8ed; border-radius: 10px; font-size: 16px; width: 100%; resize: vertical;"></textarea>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="scheduleDate">Date</label>
                                    <div class="input-group">
                                        <i class="fas fa-calendar"></i>
                                        <input type="date" id="scheduleDate" name="schedule_date" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="scheduleCategory">Category</label>
                                    <div class="input-group">
                                        <i class="fas fa-tag"></i>
                                        <select id="scheduleCategory" name="category" style="padding: 12px 15px 12px 45px; border: 2px solid #e1e8ed; border-radius: 10px; font-size: 16px; width: 100%;">
                                            <option value="general">General</option>
                                            <option value="study">Study</option>
                                            <option value="work">Work</option>
                                            <option value="personal">Personal</option>
                                            <option value="meeting">Meeting</option>
                                            <option value="assignment">Assignment</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="startTime">Start Time</label>
                                    <div class="input-group">
                                        <i class="fas fa-clock"></i>
                                        <input type="time" id="startTime" name="start_time" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="endTime">End Time</label>
                                    <div class="input-group">
                                        <i class="fas fa-clock"></i>
                                        <input type="time" id="endTime" name="end_time" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Color</label>
                                <div class="color-picker">
                                    <div class="color-option selected" style="background: #3498db;" data-color="#3498db"></div>
                                    <div class="color-option" style="background: #e74c3c;" data-color="#e74c3c"></div>
                                    <div class="color-option" style="background: #2ecc71;" data-color="#2ecc71"></div>
                                    <div class="color-option" style="background: #f39c12;" data-color="#f39c12"></div>
                                    <div class="color-option" style="background: #9b59b6;" data-color="#9b59b6"></div>
                                    <div class="color-option" style="background: #1abc9c;" data-color="#1abc9c"></div>
                                    <div class="color-option" style="background: #34495e;" data-color="#34495e"></div>
                                    <div class="color-option" style="background: #e67e22;" data-color="#e67e22"></div>
                                </div>
                                <input type="hidden" id="scheduleColor" name="color" value="#3498db">
                            </div>
                            
                            <div style="display: flex; gap: 10px; margin-top: 30px;">
                                <button type="submit" class="btn btn-primary" style="flex: 1;">
                                    <i class="fas fa-save"></i> Add Schedule
                                </button>
                                <a href="dashboard.php" class="btn" style="flex: 1; background: #e1e8ed; color: #333; text-align: center; text-decoration: none;">
                                    Cancel
                                </a>
                            </div>
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
        
        if (mobileMenuToggle && sidebar) {
            mobileMenuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
            
            // Close sidebar when clicking outside
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768 && 
                    !sidebar.contains(e.target) && 
                    !mobileMenuToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
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
        
        // Color Picker
        const colorOptions = document.querySelectorAll('.color-option');
        colorOptions.forEach(option => {
            option.addEventListener('click', function() {
                colorOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('scheduleColor').value = this.getAttribute('data-color');
            });
        });
        
        // Form Submission
        const addScheduleForm = document.getElementById('addScheduleForm');
        
        if (addScheduleForm) {
            addScheduleForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(addScheduleForm);
                
                // Validate time
                const startTime = formData.get('start_time');
                const endTime = formData.get('end_time');
                
                if (startTime >= endTime) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Time',
                        text: 'End time must be after start time',
                        confirmButtonColor: '#3498db'
                    });
                    return;
                }
                
                // Check for conflicts first
                const conflictData = new FormData();
                conflictData.append('title', formData.get('title'));
                conflictData.append('schedule_date', formData.get('schedule_date'));
                conflictData.append('start_time', formData.get('start_time'));
                conflictData.append('end_time', formData.get('end_time'));
                
                fetch('api/check_conflicts.php', {
                    method: 'POST',
                    body: conflictData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.conflicts && data.conflicts.length > 0) {
                            // Show conflict warning dialog
                            showConflictWarning(data.conflicts, formData);
                        } else {
                            // No conflicts, proceed with saving
                            saveSchedule(formData);
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                            confirmButtonColor: '#3498db'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong. Please try again.',
                        confirmButtonColor: '#3498db'
                    });
                });
            });
        }
        
        function showConflictWarning(conflicts, formData) {
            // Close the modal first
            const modal = document.getElementById('scheduleModal');
            if (modal) {
                modal.style.display = 'none';
            }
            
            let conflictHtml = '<div style="text-align: left;">';
            conflicts.forEach(conflict => {
                conflictHtml += `
                    <div style="margin-bottom: 15px; padding: 10px; background: ${conflict.color}20; border-left: 4px solid ${conflict.color}; border-radius: 5px;">
                        <strong style="color: ${conflict.color};">${conflict.title}</strong><br>
                        <span style="color: #666;">${conflict.start_time} – ${conflict.end_time}</span><br>
                        <small style="color: #999;">${conflict.category}</small>
                    </div>
                `;
            });
            conflictHtml += '</div>';
            
            Swal.fire({
                icon: 'warning',
                title: '⚠ Schedule Conflict Detected',
                html: `
                    <p style="margin-bottom: 15px;">This event overlaps with existing schedules:</p>
                    ${conflictHtml}
                    <p style="margin-top: 15px; color: #666;">Do you want to continue?</p>
                `,
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Add Anyway',
                cancelButtonText: 'Cancel',
                width: '450px'
            }).then((result) => {
                if (result.isConfirmed) {
                    // User chose to add anyway
                    saveSchedule(formData);
                } else {
                    // User cancelled, reopen the modal with form data
                    if (modal) {
                        modal.style.display = 'flex';
                    }
                }
            });
        }

        function saveSchedule(formData) {
            fetch('api/add_schedule.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message,
                        confirmButtonColor: '#3498db',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'dashboard.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#3498db'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong. Please try again.',
                    confirmButtonColor: '#3498db'
                });
            });
        }
        
        // Set default date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('scheduleDate').value = today;
    </script>
</body>
</html>
