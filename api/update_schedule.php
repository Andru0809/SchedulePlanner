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
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $schedule_date = $_POST['schedule_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $color = sanitize_input($_POST['color']);
    $category = sanitize_input($_POST['category']);
    
    // Validation
    if (empty($title) || empty($schedule_date) || empty($start_time) || empty($end_time) || empty($schedule_id)) {
        $response['message'] = 'Please fill in all required fields';
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
                // Update schedule (conflict checking is now handled on frontend)
                $sql = "UPDATE schedules 
                        SET title = ?, description = ?, schedule_date = ?, start_time = ?, end_time = ?, color = ?, category = ?
                        WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssii", $title, $description, $schedule_date, $start_time, $end_time, $color, $category, $schedule_id, $user_id);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Schedule updated successfully';
                } else {
                    $response['message'] = 'Failed to update schedule. Please try again.';
                }
            }
        }
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
