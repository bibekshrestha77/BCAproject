<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Get user's voting history
$votes_query = "SELECT v.*, e.title, c.name as candidate_name 
                FROM votes v 
                JOIN elections e ON v.election_id = e.id 
                JOIN candidates c ON v.candidate_id = c.id 
                WHERE v.user_id = $user_id";
$votes_result = mysqli_query($conn, $votes_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Voting System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <img src="assets/default-avatar.png" alt="Profile" class="profile-avatar">
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <div class="profile-content">
                <h3>My Voting History</h3>
                <div class="voting-history">
                    <?php if(mysqli_num_rows($votes_result) > 0): ?>
                        <?php while($vote = mysqli_fetch_assoc($votes_result)): ?>
                            <div class="vote-card">
                                <h4><?php echo htmlspecialchars($vote['title']); ?></h4>
                                <p>Voted for: <?php echo htmlspecialchars($vote['candidate_name']); ?></p>
                                <p class="vote-date">Date: <?php echo date('F j, Y', strtotime($vote['voted_at'])); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-votes">You haven't voted in any elections yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    function toggleDropdown() {
        document.getElementById('profileDropdown').classList.toggle('show');
    }

    // Close dropdown when clicking outside
    window.onclick = function(event) {
        if (!event.target.matches('.profile-icon') && !event.target.matches('.profile-icon *')) {
            var dropdowns = document.getElementsByClassName('dropdown-content');
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