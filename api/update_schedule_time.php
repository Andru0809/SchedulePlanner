<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/functions.php';

// Check if user is logged in
require_login();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = get_current_user_id();
    $schedule_id = $_POST['schedule_id'];
    $schedule_date = $_POST['schedule_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    if (empty($schedule_id) || empty($schedule_date) || empty($start_time) || empty($end_time)) {
        $response['message'] = 'All fields are required';
    } elseif ($start_time >= $end_time) {
        $response['message'] = 'End time must be after start time';
    } else {
        // Check if schedule belongs to current user
        $sql = "SELECT user_id FROM schedules WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $schedule_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $response['message'] = 'Schedule not found';
        } else {
            $schedule = $result->fetch_assoc();
            if ($schedule['user_id'] != $user_id) {
                $response['message'] = 'Unauthorized access';
            } else {
                // Check for time conflicts (excluding current schedule)
                if (check_time_conflict($user_id, $schedule_date, $start_time, $end_time, $schedule_id)) {
                    $response['message'] = 'Schedule conflicts with existing schedule. Please choose a different time.';
                } else {
                    // Update schedule time
                    $sql = "UPDATE schedules 
                            SET schedule_date = ?, start_time = ?, end_time = ?
                            WHERE id = ? AND user_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssii", $schedule_date, $start_time, $end_time, $schedule_id, $user_id);
                    
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Schedule time updated successfully';
                    } else {
                        $response['message'] = 'Failed to update schedule time. Please try again.';
                    }
                }
            }
        }
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
