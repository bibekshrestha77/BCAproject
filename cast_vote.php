<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['candidate_id']) || !isset($_POST['election_id'])) {
    header("Location: index.php?error=invalid_request");
    exit();
}

$user_id = $_SESSION['user_id'];
$candidate_id = mysqli_real_escape_string($conn, $_POST['candidate_id']);
$election_id = mysqli_real_escape_string($conn, $_POST['election_id']);

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Check if user has already voted
    $check_vote = mysqli_query($conn, "SELECT id FROM votes WHERE user_id = $user_id AND election_id = $election_id");
    if (mysqli_num_rows($check_vote) > 0) {
        throw new Exception("Already voted");
    }

    // Check if election is still active
    $check_election = mysqli_query($conn, "SELECT title FROM elections WHERE id = $election_id AND status = 'active'");
    if (mysqli_num_rows($check_election) == 0) {
        throw new Exception("Election not active");
    }
    
    $election = mysqli_fetch_assoc($check_election);

    // Record the vote
    $insert_vote = mysqli_query($conn, 
        "INSERT INTO votes (user_id, election_id, candidate_id) VALUES ($user_id, $election_id, $candidate_id)"
    );

    // Update candidate vote count
    $update_count = mysqli_query($conn, 
        "UPDATE candidates SET votes = votes + 1 WHERE id = $candidate_id"
    );

    if (!$insert_vote || !$update_count) {
        throw new Exception("Vote recording failed");
    }

    mysqli_commit($conn);
    $_SESSION['vote_success'] = "Your vote for " . htmlspecialchars($election['title']) . " has been successfully recorded!";
    header("Location: index.php");
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['vote_error'] = $e->getMessage();
    header("Location: index.php");
    exit();
}
?> 