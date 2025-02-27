<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();
require_once 'config.php'; // Ensure this initializes $conn as a MySQLi connection

if (!isset($_SESSION['user_id']) || !isset($_POST['candidate_id']) || !isset($_POST['election_id'])) {
    $_SESSION['error'] = "Invalid request!";
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$candidate_id = intval($_POST['candidate_id']);
$election_id = intval($_POST['election_id']);

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // Get user's course
    $user_query = "SELECT course_id FROM users WHERE id = ?";
    $user_stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($user_stmt, "i", $user_id);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    $user_course = mysqli_fetch_assoc($user_result);

    if (!$user_course) {
        throw new Exception("User course not found!");
    }

    // Check if user already voted in this election
    $check_query = "SELECT id FROM votes WHERE user_id = ? AND election_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $election_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {
        throw new Exception("You have already voted in this election!");
    }

    // Verify that the candidate belongs to the election and the user's course
    $verify_query = "
        SELECT c.id 
        FROM candidates c 
        JOIN elections e ON c.election_id = e.id 
        WHERE c.id = ? AND e.id = ? AND c.course_id = ? AND e.course_id = ?
    ";
    $verify_stmt = mysqli_prepare($conn, $verify_query);
    mysqli_stmt_bind_param($verify_stmt, "iiii", $candidate_id, $election_id, $user_course['course_id'], $user_course['course_id']);
    mysqli_stmt_execute($verify_stmt);
    $verify_result = mysqli_stmt_get_result($verify_stmt);

    if (mysqli_num_rows($verify_result) === 0) {
        throw new Exception("Invalid voting attempt!");
    }

    // Cast vote
    $vote_query = "INSERT INTO votes (user_id, candidate_id, election_id) VALUES (?, ?, ?)";
    $vote_stmt = mysqli_prepare($conn, $vote_query);
    mysqli_stmt_bind_param($vote_stmt, "iii", $user_id, $candidate_id, $election_id);

    if (!mysqli_stmt_execute($vote_stmt)) {
        throw new Exception("Error casting vote: " . mysqli_error($conn));
    }

    // Commit transaction
    mysqli_commit($conn);
    $_SESSION['message'] = "Vote cast successfully!";
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = $e->getMessage();
}

// Close statements
mysqli_stmt_close($user_stmt);
mysqli_stmt_close($check_stmt);
mysqli_stmt_close($verify_stmt);
mysqli_stmt_close($vote_stmt);

// Redirect back
header("Location: index.php");
exit();
?>
