<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';

// Redirect if not logged in
if (!is_logged_in()) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Timetable";
$current_user = get_logged_in_user();

// Handle CRUD operations
$message = '';
$message_type = '';
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

// Add timetable entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_timetable'])) {
    $subject = sanitize_input($_POST['subject']);
    $day_of_week = sanitize_input($_POST['day_of_week']);
    $start_time = sanitize_input($_POST['start_time']);
    $end_time = sanitize_input($_POST['end_time']);
    $teacher = sanitize_input($_POST['teacher']);
    $room = sanitize_input($_POST['room']);
    $color = sanitize_input($_POST['color']);
    
    // Validate inputs
    if (empty($subject) || empty($day_of_week) || empty($start_time) || empty($end_time)) {
        $message = "Please fill in all required fields";
        $message_type = "error";
    } elseif ($start_time >= $end_time) {
        $message = "End time must be after start time";
        $message_type = "error";
    } else {
        // Check for time conflicts
        $conflict_sql = "SELECT * FROM timetable 
                        WHERE user_id = ? AND day_of_week = ? 
                        AND start_time < ? AND end_time > ?";
        $stmt = $conn->prepare($conflict_sql);
        $stmt->bind_param("isss", $current_user['id'], $day_of_week, $end_time, $start_time);
        $stmt->execute();
        $conflict_result = $stmt->get_result();
        
        if ($conflict_result->num_rows > 0) {
            $message = "Time slot overlaps with another class";
            $message_type = "error";
        } else {
            // Insert new timetable entry
            $insert_sql = "INSERT INTO timetable (user_id, subject, day_of_week, start_time, end_time, teacher, room, color) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("isssssss", $current_user['id'], $subject, $day_of_week, $start_time, $end_time, $teacher, $room, $color);
            
            if ($stmt->execute()) {
                $message = "Class added successfully!";
                $message_type = "success";
                
                // Return JSON response for AJAX
                if ($is_ajax) {
                    echo json_encode(['success' => true, 'message' => $message]);
                    exit();
                }
            } else {
                $message = "Failed to add class";
                $message_type = "error";
                
                // Return JSON response for AJAX
                if ($is_ajax) {
                    echo json_encode(['success' => false, 'message' => $message]);
                    exit();
                }
            }
        }
    }
    
    // Return JSON response for AJAX errors
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
    }
}

// Edit timetable entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_timetable'])) {
    $id = sanitize_input($_POST['id']);
    $subject = sanitize_input($_POST['subject']);
    $day_of_week = sanitize_input($_POST['day_of_week']);
    $start_time = sanitize_input($_POST['start_time']);
    $end_time = sanitize_input($_POST['end_time']);
    $teacher = sanitize_input($_POST['teacher']);
    $room = sanitize_input($_POST['room']);
    $color = sanitize_input($_POST['color']);
    
    // Validate inputs
    if (empty($subject) || empty($day_of_week) || empty($start_time) || empty($end_time)) {
        $message = "Please fill in all required fields";
        $message_type = "error";
    } elseif ($start_time >= $end_time) {
        $message = "End time must be after start time";
        $message_type = "error";
    } else {
        // Check for time conflicts (excluding current entry)
        $conflict_sql = "SELECT * FROM timetable 
                        WHERE user_id = ? AND day_of_week = ? 
                        AND start_time < ? AND end_time > ? AND id != ?";
        $stmt = $conn->prepare($conflict_sql);
        $stmt->bind_param("isssi", $current_user['id'], $day_of_week, $end_time, $start_time, $id);
        $stmt->execute();
        $conflict_result = $stmt->get_result();
        
        if ($conflict_result->num_rows > 0) {
            $message = "Time slot overlaps with another class";
            $message_type = "error";
        } else {
            // Update timetable entry
            $update_sql = "UPDATE timetable 
                          SET subject=?, day_of_week=?, start_time=?, end_time=?, teacher=?, room=?, color=?
                          WHERE id=? AND user_id=?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("sssssssii", $subject, $day_of_week, $start_time, $end_time, $teacher, $room, $color, $id, $current_user['id']);
            
            if ($stmt->execute()) {
                $message = "Class updated successfully!";
                $message_type = "success";
                
                // Return JSON response for AJAX
                if ($is_ajax) {
                    echo json_encode(['success' => true, 'message' => $message]);
                    exit();
                }
            } else {
                $message = "Failed to update class";
                $message_type = "error";
                
                // Return JSON response for AJAX
                if ($is_ajax) {
                    echo json_encode(['success' => false, 'message' => $message]);
                    exit();
                }
            }
        }
    }
    
    // Return JSON response for AJAX errors
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
    }
}

// Delete timetable entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_timetable'])) {
    $id = sanitize_input($_POST['id']);
    
    $delete_sql = "DELETE FROM timetable WHERE id=? AND user_id=?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $id, $current_user['id']);
    
    if ($stmt->execute()) {
        $message = "Class deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to delete class";
        $message_type = "error";
    }
}

// Fetch timetable data
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$day_filter = isset($_GET['day']) ? sanitize_input($_GET['day']) : '';

$sql = "SELECT * FROM timetable WHERE user_id = ?";
$params = ["i", $current_user['id']];

if (!empty($day_filter)) {
    $sql .= " AND day_of_week = ?";
    $params[0] .= "s";
    $params[] = $day_filter;
}

if (!empty($search)) {
    $sql .= " AND (subject LIKE ? OR teacher LIKE ? OR room LIKE ?)";
    $params[0] .= "sss";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY day_of_week, start_time";

$stmt = $conn->prepare($sql);
$stmt->bind_param(...$params);
$stmt->execute();
$timetable_data = $stmt->get_result();

// Get single entry for editing
$edit_entry = null;
if (isset($_GET['edit_id'])) {
    $edit_id = sanitize_input($_GET['edit_id']);
    $edit_sql = "SELECT * FROM timetable WHERE id=? AND user_id=?";
    $stmt = $conn->prepare($edit_sql);
    $stmt->bind_param("ii", $edit_id, $current_user['id']);
    $stmt->execute();
    $edit_result = $stmt->get_result();
    if ($edit_result->num_rows === 1) {
        $edit_entry = $edit_result->fetch_assoc();
    }
}

// Days of week
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

// Time slots (7:00 AM to 7:30 PM)
$time_slots = [];
$start_time = strtotime('07:00');
$end_time = strtotime('19:30');

while ($start_time < $end_time) {
    $time_slots[] = [
        'display' => date('h:i A', $start_time),
        'compare' => date('H:i:s', $start_time)
    ];
    $start_time = strtotime('+30 minutes', $start_time);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Planner - Timetable</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
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
                <a href="timetable.php" class="nav-item active">
                    <i class="fas fa-table"></i>
                    <span>Timetable</span>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Controls -->
            <div class="timetable-controls">
                <div class="controls-left">
                    <div class="current-date">
                        <i class="fas fa-calendar-week"></i>
                        <span>Weekly Timetable</span>
                    </div>
                </div>
                
                <div class="controls-middle">
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search by subject, teacher, room..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                
                <div class="controls-right">
                    <select id="dayFilter" class="day-filter">
                        <option value="">All Days</option>
                        <?php foreach ($days as $day): ?>
                            <option value="<?php echo $day; ?>" <?php echo $day_filter === $day ? 'selected' : ''; ?>>
                                <?php echo $day; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary" id="addClassBtn">
                        <i class="fas fa-plus"></i> Add Class
                    </button>
                </div>
            </div>
            
            <!-- Message Display -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Timetable Grid -->
            <div class="timetable-container">
                <div class="timetable-grid">
                    <!-- Header Row -->
                    <div class="grid-header">Time</div>
                    <?php foreach ($days as $day): ?>
                        <div class="grid-header"><?php echo $day; ?></div>
                    <?php endforeach; ?>
                    
                    <!-- Time Slot Rows -->
                    <?php foreach ($time_slots as $time_slot): ?>
                        <div class="grid-time"><?php echo $time_slot['display']; ?></div>
                        <?php foreach ($days as $day): ?>
                            <div class="grid-cell" data-day="<?php echo $day; ?>" data-time="<?php echo $time_slot['display']; ?>">
                                <?php 
                                // Find class for this time slot and day
                                $class_found = false;
                                $timetable_data->data_seek(0);
                                while ($row = $timetable_data->fetch_assoc()) {
                                    if ($row['day_of_week'] === $day && $row['start_time'] <= $time_slot['compare'] && $row['end_time'] > $time_slot['compare']) {
                                        $class_found = true;
                                        ?>
                                        <div class="class-card" style="background-color: <?php echo htmlspecialchars($row['color']); ?>;">
                                            <div class="class-subject"><?php echo htmlspecialchars($row['subject']); ?></div>
                                            <div class="class-teacher"><?php echo htmlspecialchars($row['teacher']); ?></div>
                                            <div class="class-room"><?php echo htmlspecialchars($row['room']); ?></div>
                                            <div class="class-actions">
                                                <button class="btn-edit" onclick="editClass(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn-delete" onclick="deleteClass(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <?php
                                        break;
                                    }
                                }
                                if (!$class_found) {
                                    echo '<div class="empty-cell"></div>';
                                }
                                ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Add/Edit Class Modal -->
    <div id="classModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add Class</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form id="classForm" method="POST">
                <input type="hidden" id="classId" name="id">
                <input type="hidden" name="add_timetable" value="1">
                
                <div class="form-group">
                    <label for="subject">Subject *</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                
                <div class="form-group">
                    <label for="day_of_week">Day *</label>
                    <select id="day_of_week" name="day_of_week" required>
                        <option value="">Select Day</option>
                        <?php foreach ($days as $day): ?>
                            <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_time">Start Time *</label>
                        <input type="time" id="start_time" name="start_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_time">End Time *</label>
                        <input type="time" id="end_time" name="end_time" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="teacher">Teacher</label>
                    <input type="text" id="teacher" name="teacher">
                </div>
                
                <div class="form-group">
                    <label for="room">Room</label>
                    <input type="text" id="room" name="room">
                </div>
                
                <div class="form-group">
                    <label for="color">Color</label>
                    <div class="color-picker">
                        <input type="color" id="color" name="color" value="#3498db">
                        <div class="color-preview" id="colorPreview"></div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Class</button>
                    <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Modal functionality
        const modal = document.getElementById('classModal');
        const form = document.getElementById('classForm');
        const modalTitle = document.getElementById('modalTitle');
        const colorInput = document.getElementById('color');
        const colorPreview = document.getElementById('colorPreview');
        
        // Color preview
        colorInput.addEventListener('input', function() {
            colorPreview.style.backgroundColor = this.value;
        });
        
        // Open modal for adding
        document.getElementById('addClassBtn').addEventListener('click', function() {
            openModal();
        });
        
        // Open modal for editing
        function editClass(id) {
            // Fetch class data
            fetch('api/get_timetable_entry.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        openModal(data.entry);
                    } else {
                        showQuickMessage(data.message || 'Failed to load class data', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showQuickMessage('Failed to load class data', 'error');
                });
        }
        
        // Delete class
        function deleteClass(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = '<input type="hidden" name="delete_timetable" value="1"><input type="hidden" name="id" value="' + id + '">';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
        
        // Modal functions
        function openModal(entry = null) {
            if (entry) {
                modalTitle.textContent = 'Edit Class';
                document.getElementById('classId').value = entry.id;
                document.getElementById('subject').value = entry.subject || '';
                document.getElementById('day_of_week').value = entry.day_of_week || '';
                document.getElementById('start_time').value = entry.start_time || '';
                document.getElementById('end_time').value = entry.end_time || '';
                document.getElementById('teacher').value = entry.teacher || '';
                document.getElementById('room').value = entry.room || '';
                document.getElementById('color').value = entry.color || '#3498db';
                colorPreview.style.backgroundColor = entry.color || '#3498db';
                
                // Change form action for editing
                const hiddenInput = form.querySelector('input[name="add_timetable"], input[name="edit_timetable"]');
                if (hiddenInput) {
                    hiddenInput.name = 'edit_timetable';
                } else {
                    // Create edit input if not found
                    const editInput = document.createElement('input');
                    editInput.type = 'hidden';
                    editInput.name = 'edit_timetable';
                    editInput.value = '1';
                    form.appendChild(editInput);
                }
            } else {
                modalTitle.textContent = 'Add Class';
                form.reset();
                colorPreview.style.backgroundColor = '#3498db';
                
                // Reset to add mode
                const hiddenInput = form.querySelector('input[name="add_timetable"], input[name="edit_timetable"]');
                if (hiddenInput) {
                    hiddenInput.name = 'add_timetable';
                    hiddenInput.value = '1';
                }
            }
            modal.style.display = 'block';
        }
        
        function closeModal() {
            modal.style.display = 'none';
            form.reset();
        }
        
        // Form submission handling
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const isEdit = formData.has('edit_timetable');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    closeModal();
                    
                    // Show success message briefly
                    const message = isEdit ? 'Class updated successfully!' : 'Class added successfully!';
                    showQuickMessage(message, 'success');
                    
                    // Refresh page immediately to show new data
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } else {
                    showQuickMessage(data.message || 'Failed to save class', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showQuickMessage('Failed to save class', 'error');
            });
        });
        
        // Quick message function
        function showQuickMessage(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.padding = '12px 20px';
            alertDiv.style.borderRadius = '8px';
            alertDiv.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            
            if (type === 'success') {
                alertDiv.style.backgroundColor = '#d4edda';
                alertDiv.style.color = '#155724';
                alertDiv.style.border = '1px solid #c3e6cb';
            } else {
                alertDiv.style.backgroundColor = '#f8d7da';
                alertDiv.style.color = '#721c24';
                alertDiv.style.border = '1px solid #f5c6cb';
            }
            
            document.body.appendChild(alertDiv);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 3000);
        }
        
        // Search functionality
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const search = this.value;
            
            // Debounce search - wait 500ms after user stops typing
            searchTimeout = setTimeout(() => {
                const url = new URL(window.location);
                if (search.trim()) {
                    url.searchParams.set('search', search.trim());
                } else {
                    url.searchParams.delete('search');
                }
                window.location.href = url.toString();
            }, 500);
        });
        
        // Also search when user presses Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                const search = this.value;
                const url = new URL(window.location);
                if (search.trim()) {
                    url.searchParams.set('search', search.trim());
                } else {
                    url.searchParams.delete('search');
                }
                window.location.href = url.toString();
            }
        });
        
        // Day filter functionality
        document.getElementById('dayFilter').addEventListener('change', function() {
            const day = this.value;
            const url = new URL(window.location);
            if (day) {
                url.searchParams.set('day', day);
            } else {
                url.searchParams.delete('day');
            }
            window.location.href = url.toString();
        });
        
        // Close modal on outside click
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeModal();
            }
        });
        
        <?php if ($edit_entry): ?>
            // Open edit modal if edit_id is set
            openModal(<?php echo json_encode($edit_entry); ?>);
        <?php endif; ?>
    </script>
</body>
</html>
