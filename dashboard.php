<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';

// Check if user is logged in
require_login();

// Get current user
$current_user = get_logged_in_user();

// Get current week dates
$current_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$week_dates = get_week_dates($current_date);

// Get schedules for the current week
$user_id = get_current_user_id();
$sql = "SELECT * FROM schedules 
        WHERE user_id = ? AND schedule_date BETWEEN ? AND ? 
        ORDER BY schedule_date, start_time";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $week_dates['start'], $week_dates['end']);
$stmt->execute();
$schedules_result = $stmt->get_result();
$schedules = [];

while ($row = $schedules_result->fetch_assoc()) {
    $schedules[] = $row;
}

// Get week navigation dates
$prev_week = get_week_navigation($current_date, 'prev');
$next_week = get_week_navigation($current_date, 'next');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Planner - Schedule</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <a href="dashboard.php" class="nav-item active">
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
            
            <!-- Toolbar -->
            <div class="toolbar">
                <div class="week-navigation">
                    <button class="nav-btn" id="prevWeek" data-date="<?php echo $prev_week['start']; ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <div class="week-display">
                        <?php echo $week_dates['month']; ?> <?php echo $week_dates['start_day']; ?> - <?php echo $week_dates['end_day']; ?>, <?php echo $week_dates['year']; ?>
                    </div>
                    <button class="nav-btn" id="nextWeek" data-date="<?php echo $next_week['start']; ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <button class="btn btn-primary" id="addScheduleBtn">
                    <i class="fas fa-plus"></i> Add Schedule
                </button>
            </div>
            
            <!-- Content -->
            <div class="content">
                <div class="schedule-container">
                    <div class="schedule-grid">
                        <!-- Header Row -->
                        <div class="grid-header">Time</div>
                        <?php 
                        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                        $week_start = new DateTime($week_dates['start']);
                        
                        for ($i = 0; $i < 7; $i++): 
                            $current_day = clone $week_start;
                            $current_day->add(new DateInterval('P' . $i . 'D'));
                            $day_number = $current_day->format('j');
                        ?>
                            <div class="grid-header">
                                <?php echo $days[$i]; ?> <?php echo $day_number; ?>
                            </div>
                        <?php endfor; ?>
                        
                        <!-- Time Slots -->
                        <?php 
                        $time_slots = [];
                        for ($hour = 6; $hour <= 22; $hour++) {
                            $time_slots[] = sprintf('%02d:00', $hour);
                        }
                        
                        foreach ($time_slots as $time): ?>
                            <div class="grid-time-slot">
                                <?php echo date('h:i A', strtotime($time)); ?>
                            </div>
                            
                            <?php for ($day = 0; $day < 7; $day++): 
                                $current_day = clone $week_start;
                                $current_day->add(new DateInterval('P' . $day . 'D'));
                                $date_str = $current_day->format('Y-m-d');
                                
                                // Find schedules for this time slot and day
                                $cell_schedules = [];
                                foreach ($schedules as $schedule) {
                                    if ($schedule['schedule_date'] == $date_str) {
                                        $schedule_start = date('H:i', strtotime($schedule['start_time']));
                                        $schedule_end = date('H:i', strtotime($schedule['end_time']));
                                        
                                        // Check if schedule falls within this hour slot
                                        $next_hour = date('H:i', strtotime($time . ':00 +1 hour'));
                                        if (($schedule_start >= $time && $schedule_start < $next_hour) ||
                                            ($schedule_start < $time && $schedule_end > $time)) {
                                            $cell_schedules[] = $schedule;
                                        }
                                    }
                                }
                            ?>
                                <div class="grid-cell" data-time="<?php echo $time; ?>" data-date="<?php echo $date_str; ?>">
                                    <?php foreach ($cell_schedules as $schedule): ?>
                                        <div class="schedule-card" style="border-left-color: <?php echo $schedule['color']; ?>; background: <?php echo $schedule['color']; ?>20;" 
                                             data-id="<?php echo $schedule['id']; ?>"
                                             draggable="true">
                                            <div class="schedule-card-title"><?php echo htmlspecialchars($schedule['title']); ?></div>
                                            <div class="schedule-card-time">
                                                <?php echo format_time_12hour($schedule['start_time']); ?> - <?php echo format_time_12hour($schedule['end_time']); ?>
                                            </div>
                                            <div class="schedule-card-category"><?php echo htmlspecialchars($schedule['category']); ?></div>
                                            <div class="schedule-card-actions">
                                                <button class="card-action-btn edit-schedule" data-id="<?php echo $schedule['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="card-action-btn delete-schedule" data-id="<?php echo $schedule['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endfor; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Add/Edit Schedule Modal -->
    <div class="modal" id="scheduleModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add Schedule</h2>
                <button class="modal-close" id="closeModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="scheduleForm">
                <input type="hidden" id="scheduleId" name="schedule_id">
                
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
                        <textarea id="scheduleDescription" name="description" rows="3" style="padding: 12px 15px 12px 45px; border: 2px solid #e1e8ed; border-radius: 10px; font-size: 16px; width: 100%; resize: vertical;"></textarea>
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
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i> Save Schedule
                    </button>
                    <button type="button" class="btn" id="cancelBtn" style="flex: 1; background: #e1e8ed; color: #333;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/schedule.js"></script>
</body>
</html>
