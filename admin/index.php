<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Welcome Admin <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
    <p>Role: <?php echo htmlspecialchars($_SESSION['role']); ?></p>
    <a href="../logout.php">Logout</a>
</body>
</html> 