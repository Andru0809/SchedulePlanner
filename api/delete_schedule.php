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
    
    if (empty($schedule_id)) {
        $response['message'] = 'Schedule ID is required';
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
                // Delete schedule
                $sql = "DELETE FROM schedules WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $schedule_id, $user_id);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Schedule deleted successfully';
                } else {
                    $response['message'] = 'Failed to delete schedule. Please try again.';
                }
            }
        }
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
