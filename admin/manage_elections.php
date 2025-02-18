<?php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_loggedin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle Delete Operation
if (isset($_GET['delete_id'])) {
    $election_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // First delete related votes
    mysqli_query($conn, "DELETE FROM votes WHERE election_id = $election_id");
    
    // Then delete related candidates
    mysqli_query($conn, "DELETE FROM candidates WHERE election_id = $election_id");
    
    // Finally delete the election
    $delete_query = "DELETE FROM elections WHERE id = $election_id";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success'] = "Election deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting election";
    }
    header("Location: manage_elections.php");
    exit();
}

// Fetch all elections
$sql = "SELECT * FROM elections ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Elections - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Manage Elections</h2>
            <div class="nav-buttons">
                <a href="add_election.php" class="btn-primary">Add New Election</a>
                <a href="admin_dashboard.php" class="btn-secondary">Back to Dashboard</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])) { ?>
            <div class="alert-success">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php } ?>

        <?php if (isset($_SESSION['error'])) { ?>
            <div class="alert-danger">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php } ?>

        <div class="elections-list">
            <?php if (mysqli_num_rows($result) > 0) { ?>
                <?php while ($election = mysqli_fetch_assoc($result)) { ?>
                    <div class="election-item">
                        <div class="election-details">
                            <h3><?php echo htmlspecialchars($election['title']); ?></h3>
                            <p><?php echo htmlspecialchars($election['description']); ?></p>
                            <span class="status-badge <?php echo $election['status']; ?>">
                                <?php echo ucfirst($election['status']); ?>
                            </span>
                        </div>
                        <div class="election-actions">
                            <a href="edit_election.php?id=<?php echo $election['id']; ?>" 
                               class="btn-secondary">Edit</a>
                            <a href="#" onclick="confirmDelete(<?php echo $election['id']; ?>)" 
                               class="btn-danger">Delete</a>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="no-elections">
                    <p>No elections found</p>
                </div>
            <?php } ?>
        </div>
    </div>

    <script>
    function confirmDelete(electionId) {
        if (confirm('Are you sure you want to delete this election? This action cannot be undone.')) {
            window.location.href = 'manage_elections.php?delete_id=' + electionId;
        }
    }
    </script>
</body>
</html> 