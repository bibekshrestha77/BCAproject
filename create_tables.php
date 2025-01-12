<?php
include 'config.php';

$tables = [
    "CREATE TABLE IF NOT EXISTS elections (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        start_date DATETIME NOT NULL,
        end_date DATETIME NOT NULL,
        status ENUM('upcoming', 'active', 'completed') DEFAULT 'upcoming',
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS election_candidates (
        id INT PRIMARY KEY AUTO_INCREMENT,
        election_id INT,
        candidate_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (election_id) REFERENCES elections(id),
        FOREIGN KEY (candidate_id) REFERENCES users(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS votes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        election_id INT,
        voter_id INT,
        candidate_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (election_id) REFERENCES elections(id),
        FOREIGN KEY (voter_id) REFERENCES users(id),
        FOREIGN KEY (candidate_id) REFERENCES users(id)
    )"
];

foreach ($tables as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Table created successfully<br>";
    } else {
        echo "Error creating table: " . mysqli_error($conn) . "<br>";
    }
}

echo "All done!";
?> 