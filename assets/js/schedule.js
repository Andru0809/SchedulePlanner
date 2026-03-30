// Theme Management
const themeToggle = document.getElementById('themeToggle');
const themeToggleSidebar = document.getElementById('themeToggleSidebar');
const body = document.body;

// Check for saved theme preference
const savedTheme = localStorage.getItem('theme') || 'light';
body.classList.toggle('dark-theme', savedTheme === 'dark');
updateThemeIcons();

function updateThemeIcons() {
    const icons = document.querySelectorAll('.theme-btn i, #themeToggleSidebar i');
    icons.forEach(icon => {
        if (body.classList.contains('dark-theme')) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    });
}

// Theme toggle handlers
if (themeToggle) {
    themeToggle.addEventListener('click', toggleTheme);
}
if (themeToggleSidebar) {
    themeToggleSidebar.addEventListener('click', toggleTheme);
}

function toggleTheme() {
    body.classList.toggle('dark-theme');
    const currentTheme = body.classList.contains('dark-theme') ? 'dark' : 'light';
    localStorage.setItem('theme', currentTheme);
    updateThemeIcons();
}

// Mobile Menu Toggle
const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const sidebar = document.getElementById('sidebar');

if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('active');
    });
}

// User Profile Dropdown
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
        <a href="settings.php" style="display: block; padding: 10px; color: inherit; text-decoration: none; border-radius: 5px; transition: background 0.3s;">
            <i class="fas fa-cog"></i> Settings
        </a>
        <hr style="border: none; border-top: 1px solid #e1e8ed; margin: 10px 0;">
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

// Week Navigation
const prevWeekBtn = document.getElementById('prevWeek');
const nextWeekBtn = document.getElementById('nextWeek');

if (prevWeekBtn) {
    prevWeekBtn.addEventListener('click', function() {
        const date = this.getAttribute('data-date');
        window.location.href = `dashboard.php?date=${date}`;
    });
}

if (nextWeekBtn) {
    nextWeekBtn.addEventListener('click', function() {
        const date = this.getAttribute('data-date');
        window.location.href = `dashboard.php?date=${date}`;
    });
}

// Modal Management
const scheduleModal = document.getElementById('scheduleModal');
const addScheduleBtn = document.getElementById('addScheduleBtn');
const closeModalBtn = document.getElementById('closeModal');
const cancelBtn = document.getElementById('cancelBtn');
const scheduleForm = document.getElementById('scheduleForm');
const modalTitle = document.getElementById('modalTitle');

// Open modal for adding schedule
if (addScheduleBtn) {
    addScheduleBtn.addEventListener('click', function() {
        openModal();
    });
}

// Close modal handlers
if (closeModalBtn) {
    closeModalBtn.addEventListener('click', closeModal);
}
if (cancelBtn) {
    cancelBtn.addEventListener('click', closeModal);
}

// Close modal when clicking outside
scheduleModal.addEventListener('click', function(e) {
    if (e.target === scheduleModal) {
        closeModal();
    }
});

function openModal(scheduleId = null) {
    if (scheduleId) {
        // Edit mode
        modalTitle.textContent = 'Edit Schedule';
        loadScheduleData(scheduleId);
    } else {
        // Add mode
        modalTitle.textContent = 'Add Schedule';
        scheduleForm.reset();
        document.getElementById('scheduleId').value = '';
        
        // Set default date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('scheduleDate').value = today;
        
        // Reset color selection
        document.querySelectorAll('.color-option').forEach(option => {
            option.classList.remove('selected');
        });
        document.querySelector('.color-option').classList.add('selected');
        document.getElementById('scheduleColor').value = '#3498db';
    }
    
    scheduleModal.classList.add('active');
}

function closeModal() {
    scheduleModal.classList.remove('active');
    scheduleForm.reset();
}

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
if (scheduleForm) {
    scheduleForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(scheduleForm);
        const scheduleId = document.getElementById('scheduleId').value;
        
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
        if (scheduleId) {
            conflictData.append('schedule_id', scheduleId);
        }
        
        fetch('api/check_conflicts.php', {
            method: 'POST',
            body: conflictData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Conflict check response:', data); // Debug logging
            if (data.success) {
                if (data.conflicts && data.conflicts.length > 0) {
                    // Show conflict warning dialog
                    showConflictWarning(data.conflicts, formData, scheduleId);
                } else {
                    // No conflicts, proceed with saving
                    saveSchedule(formData, scheduleId);
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

function showConflictWarning(conflicts, formData, scheduleId) {
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
            saveSchedule(formData, scheduleId);
        } else {
            // User cancelled, reopen the modal with form data
            if (modal) {
                modal.style.display = 'flex';
            }
        }
    });
}

function saveSchedule(formData, scheduleId) {
    const url = scheduleId ? 'api/update_schedule.php' : 'api/add_schedule.php';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal on success
            const modal = document.getElementById('scheduleModal');
            if (modal) {
                modal.style.display = 'none';
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message,
                confirmButtonColor: '#3498db',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                location.reload();
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

// Edit Schedule
const editButtons = document.querySelectorAll('.edit-schedule');
editButtons.forEach(button => {
    button.addEventListener('click', function(e) {
        e.stopPropagation();
        const scheduleId = this.getAttribute('data-id');
        openModal(scheduleId);
    });
});

// Delete Schedule
const deleteButtons = document.querySelectorAll('.delete-schedule');
deleteButtons.forEach(button => {
    button.addEventListener('click', function(e) {
        e.stopPropagation();
        const scheduleId = this.getAttribute('data-id');
        
        Swal.fire({
            icon: 'warning',
            title: 'Delete Schedule',
            text: 'Are you sure you want to delete this schedule?',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteSchedule(scheduleId);
            }
        });
    });
});

function deleteSchedule(scheduleId) {
    const formData = new FormData();
    formData.append('schedule_id', scheduleId);
    
    fetch('api/delete_schedule.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted',
                text: data.message,
                confirmButtonColor: '#3498db',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                location.reload();
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

// Load Schedule Data for Editing
function loadScheduleData(scheduleId) {
    fetch(`api/get_schedule.php?id=${scheduleId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const schedule = data.schedule;
            document.getElementById('scheduleId').value = schedule.id;
            document.getElementById('scheduleTitle').value = schedule.title;
            document.getElementById('scheduleDescription').value = schedule.description || '';
            document.getElementById('scheduleDate').value = schedule.schedule_date;
            document.getElementById('scheduleCategory').value = schedule.category;
            document.getElementById('startTime').value = schedule.start_time;
            document.getElementById('endTime').value = schedule.end_time;
            document.getElementById('scheduleColor').value = schedule.color;
            
            // Update color selection
            colorOptions.forEach(option => {
                option.classList.remove('selected');
                if (option.getAttribute('data-color') === schedule.color) {
                    option.classList.add('selected');
                }
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

// Drag and Drop Functionality
let draggedElement = null;

document.addEventListener('dragstart', function(e) {
    if (e.target.classList.contains('schedule-card')) {
        draggedElement = e.target;
        e.target.style.opacity = '0.5';
    }
});

document.addEventListener('dragend', function(e) {
    if (e.target.classList.contains('schedule-card')) {
        e.target.style.opacity = '';
    }
});

document.addEventListener('dragover', function(e) {
    e.preventDefault();
    
    const cell = e.target.closest('.grid-cell');
    if (cell && draggedElement) {
        cell.style.background = 'rgba(102, 126, 234, 0.1)';
    }
});

document.addEventListener('dragleave', function(e) {
    const cell = e.target.closest('.grid-cell');
    if (cell) {
        cell.style.background = '';
    }
});

document.addEventListener('drop', function(e) {
    e.preventDefault();
    
    const cell = e.target.closest('.grid-cell');
    if (cell && draggedElement) {
        cell.style.background = '';
        
        const scheduleId = draggedElement.getAttribute('data-id');
        const newDate = cell.getAttribute('data-date');
        const newTime = cell.getAttribute('data-time');
        
        // Calculate new start time based on drop position
        const rect = cell.getBoundingClientRect();
        const y = e.clientY - rect.top;
        const hourHeight = rect.height;
        const hourOffset = Math.floor(y / (hourHeight / 60)); // Convert to minutes
        const newStartTime = addMinutesToTime(newTime, hourOffset);
        
        // Get schedule duration
        const startTime = draggedElement.querySelector('.schedule-card-time').textContent.split(' - ')[0];
        const endTime = draggedElement.querySelector('.schedule-card-time').textContent.split(' - ')[1];
        const duration = calculateDuration(startTime, endTime);
        const newEndTime = addMinutesToTime(newStartTime, duration);
        
        // Update schedule
        updateScheduleTime(scheduleId, newDate, newStartTime, newEndTime);
    }
});

function addMinutesToTime(time, minutes) {
    const [hours, mins] = time.split(':').map(Number);
    const totalMinutes = hours * 60 + mins + minutes;
    const newHours = Math.floor(totalMinutes / 60);
    const newMins = totalMinutes % 60;
    return `${String(newHours).padStart(2, '0')}:${String(newMins).padStart(2, '0')}`;
}

function calculateDuration(startTime, endTime) {
    const start = convertToMinutes(startTime);
    const end = convertToMinutes(endTime);
    return end - start;
}

function convertToMinutes(timeStr) {
    const [time, period] = timeStr.split(' ');
    const [hours, mins] = time.split(':').map(Number);
    let totalMinutes = hours * 60 + mins;
    
    if (period === 'PM' && hours !== 12) {
        totalMinutes += 12 * 60;
    } else if (period === 'AM' && hours === 12) {
        totalMinutes -= 12 * 60;
    }
    
    return totalMinutes;
}

function updateScheduleTime(scheduleId, newDate, newStartTime, newEndTime) {
    const formData = new FormData();
    formData.append('schedule_id', scheduleId);
    formData.append('schedule_date', newDate);
    formData.append('start_time', newStartTime);
    formData.append('end_time', newEndTime);
    
    fetch('api/update_schedule_time.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
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

// Add click to empty cells for quick schedule creation
document.addEventListener('click', function(e) {
    const cell = e.target.closest('.grid-cell');
    if (cell && !e.target.closest('.schedule-card')) {
        const date = cell.getAttribute('data-date');
        const time = cell.getAttribute('data-time');
        
        // Open modal with pre-filled date and time
        openModal();
        document.getElementById('scheduleDate').value = date;
        document.getElementById('startTime').value = time;
        
        // Set end time to 1 hour later
        const endTime = addMinutesToTime(time, 60);
        document.getElementById('endTime').value = endTime;
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth transitions
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all items
            document.querySelectorAll('.nav-item').forEach(nav => {
                nav.classList.remove('active');
            });
            // Add active class to clicked item
            this.classList.add('active');
        });
    });
});
