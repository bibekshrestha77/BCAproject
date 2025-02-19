<?php
session_start();
include '../config.php';

// Get statistics
$stats = [
    'total_users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'],
    'total_elections' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM elections"))['count'],
    'active_elections' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM elections WHERE status='active'"))['count'],
    'total_votes' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM votes"))['count']
];

$current_date = date('Y-m-d H:i:s');
$recent_elections = mysqli_query(
    $conn,
    "SELECT *, 
        CASE 
            WHEN '$current_date' >= start_date AND '$current_date' <= end_date THEN 'active'
            WHEN '$current_date' > end_date THEN 'completed'
            ELSE 'upcoming'
        END AS status
     FROM elections 
     ORDER BY created_at DESC 
     LIMIT 5"
);

// Get recent votes
$recent_votes = mysqli_query(
    $conn,
    "SELECT v.*, u.username, e.title as election_title, c.name as candidate_name 
     FROM votes v 
     JOIN users u ON v.user_id = u.id 
     JOIN elections e ON v.election_id = e.id 
     JOIN candidates c ON v.candidate_id = c.id 
     ORDER BY v.voted_at DESC LIMIT 5"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Voting System</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-vote-yea"></i>
                <span>Admin Panel</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="elections.php">
                    <i class="fas fa-poll"></i> Elections
                </a>
                <a href="candidates.php">
                    <i class="fas fa-user-tie"></i> Candidates
                </a>
                <a href="users.php">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="results.php">
                    <i class="fas fa-chart-bar"></i> Results
                </a>
                <a href="../logout.php" class="logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <img src="../assets/default-avatar.png" alt="Admin" class="admin-avatar">
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Total Users</h3>
                        <p><?php echo $stats['total_users']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon elections">
                        <i class="fas fa-poll"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Total Elections</h3>
                        <p><?php echo $stats['total_elections']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon active">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Active Elections</h3>
                        <p><?php echo $stats['active_elections']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon votes">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Total Votes</h3>
                        <p><?php echo $stats['total_votes']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="activity-grid">
                <!-- Recent Elections -->
                <div class="activity-card">
                    <div class="card-header">
                        <h2>Recent Elections</h2>
                        <a href="elections.php" class="view-all">View All</a>
                    </div>
                    <div class="card-content">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>End Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($election = mysqli_fetch_assoc($recent_elections)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($election['title']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $election['status']; ?>">
                                                <?php echo ucfirst($election['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($election['end_date'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Votes -->
                <div class="activity-card">
                    <div class="card-header">
                        <h2>Recent Votes</h2>
                        <a href="votes.php" class="view-all">View All</a>
                    </div>
                    <div class="card-content">
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Election</th>
                                    <th>Candidate</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($vote = mysqli_fetch_assoc($recent_votes)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($vote['username']); ?></td>
                                        <td><?php echo htmlspecialchars($vote['election_title']); ?></td>
                                        <td><?php echo htmlspecialchars($vote['candidate_name']); ?></td>
                                        <td><?php echo date('M d, H:i', strtotime($vote['voted_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>