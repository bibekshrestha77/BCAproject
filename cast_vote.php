<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['candidate_id']) || !isset($_POST['election_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$candidate_id = $_POST['candidate_id'];
$election_id = $_POST['election_id'];

try {
    $pdo->beginTransaction();
    
    // Get user's class
    $user_stmt = $pdo->prepare("SELECT class_id FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user_class = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verify candidate and election belong to user's class
    $verify_stmt = $pdo->prepare("
        SELECT c.id 
        FROM candidates c 
        JOIN elections e ON c.election_id = e.id 
        WHERE c.id = ? AND e.id = ? AND c.class_id = ? AND e.class_id = ?
    ");
    $verify_stmt->execute([$candidate_id, $election_id, $user_class['class_id'], $user_class['class_id']]);
    
    if ($verify_stmt->rowCount() === 0) {
        throw new Exception("Invalid voting attempt!");
    }
    
    // Check for duplicate votes
    $check_stmt = $pdo->prepare("SELECT id FROM votes WHERE user_id = ? AND election_id = ?");
    $check_stmt->execute([$user_id, $election_id]);
    if ($check_stmt->rowCount() > 0) {
        throw new Exception("You have already voted in this election!");
    }
    
    // Cast vote
    $vote_stmt = $pdo->prepare("INSERT INTO votes (user_id, candidate_id, election_id) VALUES (?, ?, ?)");
    $vote_stmt->execute([$user_id, $candidate_id, $election_id]);
    
    $pdo->commit();
    $_SESSION['message'] = "Vote cast successfully!";
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

header("Location: index.php");
exit();
?> 