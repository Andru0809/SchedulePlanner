<?php
// Common functions

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to hash password
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Function to verify password
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Function to generate random token
function generate_token($length = 5) {
    return bin2hex(random_bytes($length));
}

// Function to send email (basic implementation)
function send_email($to, $subject, $message) {
    $headers = "From: noreply@scheduleplanner.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Function to format time to 12-hour format
function format_time_12hour($time) {
    $timestamp = strtotime($time);
    return date("h:i A", $timestamp);
}

// Function to format date
function format_date($date) {
    $timestamp = strtotime($date);
    return date("M j, Y", $timestamp);
}

// Function to get week start and end dates
function get_week_dates($date = null) {
    if ($date === null) {
        $date = date('Y-m-d');
    }
    
    $timestamp = strtotime($date);
    $day_of_week = date('N', $timestamp);
    
    // Get Monday (start of week)
    $monday_timestamp = strtotime($date . ' -' . ($day_of_week - 1) . ' days');
    $sunday_timestamp = strtotime($date . ' +' . (7 - $day_of_week) . ' days');
    
    return [
        'start' => date('Y-m-d', $monday_timestamp),
        'end' => date('Y-m-d', $sunday_timestamp),
        'start_day' => date('j', $monday_timestamp),
        'end_day' => date('j', $sunday_timestamp),
        'month' => date('F', $monday_timestamp),
        'year' => date('Y', $monday_timestamp)
    ];
}

// Function to check time conflict (returns conflicting schedules)
function check_time_conflict_details($user_id, $schedule_date, $start_time, $end_time, $exclude_id = null) {
    global $conn;
    
    if ($exclude_id) {
        $sql = "SELECT * FROM schedules 
                WHERE user_id = ? AND schedule_date = ? AND id != ?
                ORDER BY start_time";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $user_id, $schedule_date, $exclude_id);
    } else {
        $sql = "SELECT * FROM schedules 
                WHERE user_id = ? AND schedule_date = ?
                ORDER BY start_time";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $schedule_date);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $conflicts = [];
    while ($schedule = $result->fetch_assoc()) {
        // Check for overlap: new_start < existing_end AND existing_start < new_end
        if ($start_time < $schedule['end_time'] && $schedule['start_time'] < $end_time) {
            $conflicts[] = $schedule;
        }
    }
    
    return $conflicts;
}

// Function to check if there are any conflicts (boolean)
function has_time_conflict($user_id, $schedule_date, $start_time, $end_time, $exclude_id = null) {
    $conflicts = check_time_conflict_details($user_id, $schedule_date, $start_time, $end_time, $exclude_id);
    return !empty($conflicts);
}

// Function to get week navigation dates
function get_week_navigation($current_date, $direction = 'next') {
    if ($direction === 'next') {
        $new_date = date('Y-m-d', strtotime($current_date . ' +7 days'));
    } else {
        $new_date = date('Y-m-d', strtotime($current_date . ' -7 days'));
    }
    
    return get_week_dates($new_date);
}

?>
