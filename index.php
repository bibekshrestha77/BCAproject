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
                        <a href="profile.php">My Profile</a>
                        <a href="edit-profile.php">Edit Profile</a>
                        <a href="logout.php">Logout</a>
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

                            echo "<div class='vote-counts'>";
                            echo "<h4>Current Results:</h4>";
                            echo "<ul class='vote-list'>";
                            while ($vote_row = mysqli_fetch_assoc($vote_result)) {
                                echo "<li>{$vote_row['name']}: {$vote_row['vote_count']} votes</li>";
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
        });

        function toggleProfileDropdown() {
            document.getElementById("profileDropdown").classList.toggle("show");
        }

        // Close the dropdown if the user clicks outside of it
        window.onclick = function (event) {
            if (!event.target.matches('.profile-icon')) {
                var dropdowns = document.getElementsByClassName("profile-dropdown");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
</body>

</html>
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
            background-color: #f9f9f9;
            min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1;
            border-radius: 5px;
        }

        .profile-dropdown.show {
            display: block;
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
    </style>