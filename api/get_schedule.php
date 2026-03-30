<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/functions.php';

// Check if user is logged in
require_login();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'schedule' => null];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = get_current_user_id();
    $schedule_id = $_GET['id'];
    
    if (empty($schedule_id)) {
        $response['message'] = 'Schedule ID is required';
    } else {
        // Get schedule details
        $sql = "SELECT * FROM schedules WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $schedule_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $response['message'] = 'Schedule not found';
        } else {
            $schedule = $result->fetch_assoc();
            $response['success'] = true;
            $response['schedule'] = $schedule;
        }
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
