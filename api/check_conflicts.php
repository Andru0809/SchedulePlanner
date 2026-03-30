<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/functions.php';

// Check if user is logged in
require_login();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'conflicts' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = get_current_user_id();
    $title = sanitize_input($_POST['title']);
    $schedule_date = $_POST['schedule_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $schedule_id = isset($_POST['schedule_id']) ? $_POST['schedule_id'] : null;
    
    // Validation
    if (empty($title) || empty($schedule_date) || empty($start_time) || empty($end_time)) {
        $response['message'] = 'Please fill in all required fields';
    } elseif ($start_time >= $end_time) {
        $response['message'] = 'End time must be after start time';
    } else {
        // Check for conflicts
        $conflicts = check_time_conflict_details($user_id, $schedule_date, $start_time, $end_time, $schedule_id);
        
        // Debug logging
        error_log("Checking conflicts for user $user_id on $schedule_date from $start_time to $end_time");
        error_log("Found " . count($conflicts) . " conflicts");
        
        if (!empty($conflicts)) {
            $response['success'] = true;
            $response['conflicts'] = [];
            
            foreach ($conflicts as $conflict) {
                $response['conflicts'][] = [
                    'id' => $conflict['id'],
                    'title' => $conflict['title'],
                    'start_time' => format_time_12hour($conflict['start_time']),
                    'end_time' => format_time_12hour($conflict['end_time']),
                    'category' => $conflict['category'],
                    'color' => $conflict['color']
                ];
                error_log("Conflict: " . $conflict['title'] . " " . $conflict['start_time'] . "-" . $conflict['end_time']);
            }
        } else {
            $response['success'] = true;
            $response['conflicts'] = [];
            error_log("No conflicts found");
        }
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
