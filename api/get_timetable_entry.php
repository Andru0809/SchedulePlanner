<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/functions.php';

// Redirect if not logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = sanitize_input($_GET['id']);
    $current_user = get_logged_in_user();
    
    $sql = "SELECT * FROM timetable WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $current_user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $entry = $result->fetch_assoc();
        echo json_encode(['success' => true, 'entry' => $entry]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Entry not found']);
    }
}
?>
