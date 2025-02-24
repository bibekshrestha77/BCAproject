<?php
session_start();
include '../config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);  // Show all errors for debugging

if(isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    // Prevent admin self-deletion
    if ($user_id == $_SESSION['admin_id']) {
        $_SESSION['error'] = "You cannot delete your own account.";
        header("Location: users.php");
        exit();
    }

    // Check if user exists
    $check_query = "SELECT * FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $check_query);
    
    if (!$result) {
        error_log("Error checking user: " . mysqli_error($conn));
        $_SESSION['error'] = "Error checking user: " . mysqli_error($conn);
        header("Location: users.php");
        exit();
    }

    if (mysqli_num_rows($result) > 0) {
        
        // Proceed to delete user
        $query = "DELETE FROM users WHERE id = $user_id";
        if(mysqli_query($conn, $query)) {
            $_SESSION['success'] = "User deleted successfully!";
        } else {
            error_log("Error deleting user: " . mysqli_error($conn));
            $_SESSION['error'] = "Error deleting user: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "User not found!";
    }
}

header("Location: users.php");
exit();
?>
