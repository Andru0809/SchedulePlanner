<?php
require_once 'database.php';

// Create timetable table
$sql = "CREATE TABLE IF NOT EXISTS timetable (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    day_of_week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    teacher VARCHAR(100),
    room VARCHAR(50),
    color VARCHAR(20) DEFAULT '#3498db',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql)) {
    echo "Timetable table created successfully or already exists.\n";
} else {
    echo "Error creating timetable table: " . $conn->error . "\n";
}

$conn->close();
?>
