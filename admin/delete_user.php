<?php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Don't allow deleting the current admin
    if ($id == $_SESSION['admin_id']) {
        $_SESSION['error'] = "You cannot delete your own account!";
        header("Location: users.php");
        exit();
    }

    // Delete user's votes first (due to foreign key constraints)
    mysqli_query($conn, "DELETE FROM votes WHERE user_id = $id");
    
    // Delete the user
    if (mysqli_query($conn, "DELETE FROM users WHERE id = $id")) {
        $_SESSION['success'] = "User deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting user: " . mysqli_error($conn);
    }
}

header("Location: users.php");
exit();
?> 