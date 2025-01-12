<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'voter') {
    header("Location: ../login.php?error=unauthorized");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css"> <!-- Optional: Link to CSS file -->
</head>
<body>
    <div class="dashboard">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <p>Role: <?php echo htmlspecialchars($_SESSION['role']); ?></p>
        <a href="../logout.php">Logout</a>
    </div>
</body>
</html>
