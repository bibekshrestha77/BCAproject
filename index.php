<?php
session_start();
include 'config.php';

// Fetch user's course_id from the session or database
$user_course_id = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT course_id FROM users WHERE id = $user_id";
    $user_result = mysqli_query($conn, $user_query);
    if ($user_result && mysqli_num_rows($user_result) > 0) {
        $user_row = mysqli_fetch_assoc($user_result);
        $user_course_id = $user_row['course_id'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Voting System</title>
    <link rel="stylesheet" href="style.css">
   
</head>

<body>
    <nav class="navbar">
        <div class="logo">Voting System</div>
        <div class="nav-buttons">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="nav-btn">Login</a>
                <a href="register.php" class="nav-btn">Register</a>
            <?php else: ?>
                <div class="profile-container">
                    <div class="profile-icon" onclick="toggleProfileDropdown()">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="dropdown-header">
                            <?php if ($user_data): ?>
                                <span class="user-name"><?php echo $user_data['first_name'] . ' ' . $user_data['last_name']; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a>
                       
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <?php if (isset($_SESSION['vote_success'])): ?>
        <div class="alert success-alert">
            <div class="alert-content">
                <i class="fas fa-check-circle"></i>
                <p><?php echo $_SESSION['vote_success']; ?></p>
            </div>
            <button class="close-alert" onclick="this.parentElement.style.display='none';">&times;</button>
        </div>
        <?php unset($_SESSION['vote_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['vote_error'])): ?>
        <div class="alert error-alert">
            <div class="alert-content">
                <i class="fas fa-exclamation-circle"></i>
                <p><?php echo $_SESSION['vote_error']; ?></p>
            </div>
            <button class="close-alert" onclick="this.parentElement.style.display='none';">&times;</button>
        </div>
        <?php unset($_SESSION['vote_error']); ?>
    <?php endif; ?>

    <div class="hero-section">
        <h1>Welcome to Online Voting System</h1>
        <p>Make your voice heard - Vote for a better tomorrow</p>
    </div>

    <div class="features-section">
        <h2>Available Elections</h2>
        <div class="election-cards">
            <?php
            $current_date = date('Y-m-d H:i:s');

            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
                // Modified query to get all active elections for the user's course and check if user has voted
                $query = "SELECT e.*, 
                        CASE 
                            WHEN '$current_date' >= e.start_date AND '$current_date' <= e.end_date THEN 'active'
                            WHEN '$current_date' > e.end_date THEN 'completed'
                            ELSE 'upcoming'
                        END AS status,
                        v.election_id AS has_voted
                    FROM elections e
                    LEFT JOIN votes v ON e.id = v.election_id AND v.user_id = $user_id
                    WHERE e.course_id = $user_course_id
                    HAVING status = 'active'
                    ORDER BY e.end_date ASC";
            } else {
                // If user not logged in, show all active elections
                $query = "SELECT *, 
              CASE 
                  WHEN '$current_date' >= start_date AND '$current_date' <= end_date THEN 'active'
                  WHEN '$current_date' > end_date THEN 'completed'
                  ELSE 'upcoming'
              END AS status
              FROM elections
              HAVING status = 'active'
              ORDER BY end_date ASC";
            }

            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $end_date = strtotime($row['end_date']);
                    $now = time();
                    $time_left = $end_date - $now;

                    if ($time_left > 0) {
                        echo "<div class='election-card'>";
                        echo "<span class='status active'>Active</span>";
                        echo "<h3>{$row['title']}</h3>";
                        echo "<p>{$row['description']}</p>";

                        // Format remaining time
                        $days = floor($time_left / (60 * 60 * 24));
                        $hours = floor(($time_left % (60 * 60 * 24)) / (60 * 60));
                        echo "<p class='date'>Ends in: {$days}d {$hours}h</p>";

                        if (isset($_SESSION['user_id'])) {
                            // Get vote counts for this election
                            $election_id = $row['id'];
                            $vote_query = "SELECT c.name, COUNT(v.id) as vote_count 
                                         FROM candidates c 
                                         LEFT JOIN votes v ON c.id = v.candidate_id 
                                         WHERE c.election_id = $election_id
                                         GROUP BY c.id, c.name 
                                         ORDER BY vote_count DESC";
                            $vote_result = mysqli_query($conn, $vote_query);

                            // Get total votes for percentage calculation
                            $total_votes = 0;
                            $vote_counts = array();
                            while ($vote_row = mysqli_fetch_assoc($vote_result)) {
                                $total_votes += $vote_row['vote_count'];
                                $vote_counts[] = $vote_row;
                            }

                            echo "<div class='vote-counts'>";
                            echo "<h4>Current Results:</h4>";
                            echo "<ul class='vote-list'>";
                            foreach ($vote_counts as $vote_row) {
                                $percentage = $total_votes > 0 ? round(($vote_row['vote_count'] / $total_votes) * 100, 1) : 0;
                                echo "<li>";
                                echo "<div class='vote-count-info'>";
                                echo "<span>{$vote_row['name']}</span>";
                                echo "<span class='vote-percentage'>{$vote_row['vote_count']} votes ({$percentage}%)</span>";
                                echo "</div>";
                                echo "<div class='vote-progress-container'>";
                                echo "<div class='vote-progress-bar' style='width: {$percentage}%'></div>";
                                echo "</div>";
                                echo "</li>";
                            }
                            echo "</ul>";
                            echo "</div>";

                            if (!$row['has_voted']) {
                                echo "<a href='vote.php?id={$row['id']}' class='vote-btn'>Vote Now</a>";
                            } else {
                                echo "<span class='voted-badge'>You have voted</span>";
                            }
                        } else {
                            echo "<a href='login.php' class='vote-btn'>Login to Vote</a>";
                        }
                        echo "</div>";
                    } else {
                        // Election has ended
                        echo "<div class='election-card completed'>";
                        echo "<span class='status completed'>Completed</span>";
                        echo "<h3>{$row['title']}</h3>";
                        echo "<p>{$row['description']}</p>";

                        if (isset($_SESSION['user_id'])) {
                            // Get winner information
                            $election_id = $row['id'];
                            $winner_query = "SELECT c.name, COUNT(v.id) as vote_count,
                                           (SELECT COUNT(DISTINCT user_id) FROM votes WHERE election_id = $election_id) as total_voters
                                           FROM candidates c 
                                           LEFT JOIN votes v ON c.id = v.candidate_id 
                                           WHERE c.election_id = $election_id
                                           GROUP BY c.id, c.name 
                                           ORDER BY vote_count DESC
                                           LIMIT 1";
                            $winner_result = mysqli_query($conn, $winner_query);
                            $winner = mysqli_fetch_assoc($winner_result);

                            if ($winner && $winner['vote_count'] > 0) {
                                echo "<div class='winner-announcement'>";
                                echo "<div class='winner-crown'><i class='fas fa-crown'></i></div>";
                                echo "<div class='winner-details'>";
                                echo "<h4>Winner</h4>";
                                echo "<p class='winner-name'>{$winner['name']}</p>";
                                echo "<div class='winner-stats'>";
                                echo "<span class='votes'><i class='fas fa-check-circle'></i> {$winner['vote_count']} votes</span>";
                                echo "<span class='total-voters'><i class='fas fa-users'></i> {$winner['total_voters']} total voters</span>";
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";
                            }
                        }
                        echo "<p class='date completed-date'>Ended on " . date('M d, Y', strtotime($row['end_date'])) . "</p>";
                        echo "</div>";
                    }
                }
            } else {
                echo "<div class='no-elections'>";
                echo "<i class='fas fa-check-circle'></i>";
                if (isset($_SESSION['user_id'])) {
                    echo "<p>You have voted in all available elections!</p>";
                    echo "<a href='profile.php' class='view-history-btn'>View Your Voting History</a>";
                } else {
                    echo "<p>No active elections at the moment.</p>";
                }
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <div class="info-section">
        <div class="info-card">
            <h3>How to Vote</h3>
            <ol>
                <li>Register an account</li>
                <li>Login to your account</li>
                <li>Select active election</li>
                <li>Cast your vote</li>
            </ol>
        </div>
        <div class="info-card">
            <h3>Important Dates</h3>
            <ul>
                <li>Registration Deadline: [Date]</li>
                <li>Voting Starts: [Date]</li>
                <li>Results Announcement: [Date]</li>
            </ul>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#elections">Elections</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contact Info</h4>
                <ul>
                    <li>Email: info@votesystem.com</li>
                    <li>Phone: (123) 456-7890</li>
                    <li>Address: 123 Vote Street</li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Follow Us</h4>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Online Voting System. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 5500); // 5.5 seconds (allowing for fade animation)
            });

            // Initialize dropdown functionality
            initializeDropdown();
        });

        function initializeDropdown() {
            const profileIcon = document.querySelector('.profile-icon');
            const dropdown = document.getElementById('profileDropdown');
            let isDropdownOpen = false;

            if (profileIcon && dropdown) {
                profileIcon.addEventListener('click', function(event) {
                    event.stopPropagation();
                    isDropdownOpen = !isDropdownOpen;
                    
                    if (isDropdownOpen) {
                        dropdown.classList.add('show');
                        dropdown.style.opacity = '1';
                        dropdown.style.transform = 'translateY(0)';
                    } else {
                        closeDropdown();
                    }
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(event) {
                    if (isDropdownOpen && !dropdown.contains(event.target)) {
                        closeDropdown();
                    }
                });

                // Prevent dropdown from closing when clicking inside it
                dropdown.addEventListener('click', function(event) {
                    event.stopPropagation();
                });
            }

            function closeDropdown() {
                isDropdownOpen = false;
                dropdown.style.opacity = '0';
                dropdown.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    dropdown.classList.remove('show');
                }, 300); // Match the transition duration
            }
        }
    </script>

    <style>
        .profile-container {
            position: relative;
            display: inline-block;
        }

        .profile-icon {
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 120%;
            background-color: white;
            min-width: 300px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            z-index: 1000;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            visibility: hidden;
        }

        .profile-dropdown.show {
            display: block;
            visibility: visible;
        }

        .profile-dropdown a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .profile-dropdown a:hover {
            background-color: #f1f1f1;
        }

        /* Vote counts styling */
        .vote-counts {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .vote-counts h4 {
            color: #2c3e50;
            margin: 0 0 10px 0;
            font-size: 1.1em;
            font-weight: 600;
        }

        .vote-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .vote-list li {
            display: block;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
            color: #495057;
            font-size: 0.95em;
        }

        .vote-list li:last-child {
            border-bottom: none;
        }

        .voted-badge {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9em;
            margin-top: 10px;
        }

        /* Progress bar styling */
        .vote-progress-container {
            margin-top: 6px;
            background-color: #e9ecef;
            border-radius: 10px;
            height: 8px;
            width: 100%;
            overflow: hidden;
        }

        .vote-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #4CAF50, #45a049);
            border-radius: 10px;
            transition: width 0.6s ease;
        }

        .vote-count-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }

        .vote-percentage {
            color: #6c757d;
            font-size: 0.9em;
            font-weight: 500;
        }

        /* Winner announcement styling */
        .winner-announcement {
            background: linear-gradient(to right, #f3f4f6, #ffffff);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
            position: relative;
            overflow: hidden;
        }

        .winner-crown {
            background: linear-gradient(135deg, #ffd700 0%, #ffec8b 100%);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #b8860b;
            font-size: 1.2em;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin: 0 auto 15px;
        }

        .winner-details {
            text-align: center;
        }

        .winner-details h4 {
            text-transform: uppercase;
            color: #6b7280;
            font-size: 0.8em;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        .winner-name {
            color: #28a745;
            font-size: 1.4em;
            font-weight: 600;
            margin: 5px 0;
        }

        .winner-stats {
            display: flex;
            justify-content: center;
            gap: 20px;
            color: #6b7280;
            font-size: 0.9em;
            margin-top: 10px;
        }

        .winner-stats i {
            color: #28a745;
            margin-right: 6px;
        }

        .completed-date {
            color: #6c757d;
            font-style: italic;
        }

        .election-card.completed {
            background-color: #f8f9fa;
        }

        .status.completed {
            background-color: #6c757d;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8em;
            display: inline-block;
            margin-bottom: 10px;
        }
    </style>
</body>

</html>