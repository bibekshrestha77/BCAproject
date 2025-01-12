<?php
include 'config.php';

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'voter') DEFAULT 'voter',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(mysqli_query($conn, $sql)) {
    echo "Users table created successfully";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}
?>
