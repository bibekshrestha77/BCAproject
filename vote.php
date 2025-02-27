<?php
session_start();
require_once 'config.php'; // Ensure this file initializes $conn as a mysqli connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if election ID is provided
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$election_id = intval($_GET['id']); // Sanitize election ID

// Get user's course
$user_query = "SELECT course_id FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_course = $user_result->fetch_assoc();

if (!$user_course) {
    $_SESSION['error'] = "User course not found!";
    header("Location: index.php");
    exit();
}

// Verify election belongs to user's course
$election_query = "
    SELECT e.*, c.course AS course_name 
    FROM elections e 
    JOIN courses c ON e.course_id = c.id 
    WHERE e.id = ? AND e.course_id = ?
";
$election_stmt = $conn->prepare($election_query);
$election_stmt->bind_param("ii", $election_id, $user_course['course_id']);
$election_stmt->execute();
$election_result = $election_stmt->get_result();
$election = $election_result->fetch_assoc();

if (!$election) {
    $_SESSION['error'] = "You can only vote in elections for your own course!";
    header("Location: index.php");
    exit();
}

// Check if user already voted
$vote_check_query = "SELECT id FROM votes WHERE user_id = ? AND election_id = ?";
$vote_check_stmt = $conn->prepare($vote_check_query);
$vote_check_stmt->bind_param("ii", $user_id, $election_id);
$vote_check_stmt->execute();
$vote_check_result = $vote_check_stmt->get_result();

if ($vote_check_result->num_rows > 0) {
    $_SESSION['error'] = "You have already voted in this election!";
    header("Location: index.php");
    exit();
}

// Get candidates for this election (from same course)
$candidate_query = "
    SELECT * FROM candidates 
    WHERE election_id = ? AND course_id = ?
    ORDER BY name
";
$candidate_stmt = $conn->prepare($candidate_query);
$candidate_stmt->bind_param("ii", $election_id, $user_course['course_id']);
$candidate_stmt->execute();
$candidates = $candidate_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote - <?php echo htmlspecialchars($election['title']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="vote.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="voting-container">
        <div class="election-header">
            <h1><?php echo htmlspecialchars($election['title']); ?></h1>
            <p><?php echo htmlspecialchars($election['description']); ?></p>
            <div class="election-timer" id="electionTimer" 
                 data-end="<?php echo $election['end_date']; ?>">
                Time remaining: <span id="timer"></span>
            </div>
        </div>

        <form action="cast_vote.php" method="POST" class="voting-form" id="votingForm">
            <input type="hidden" name="election_id" value="<?php echo $election_id; ?>">
            
            <div class="candidates-grid">
                <?php foreach ($candidates as $candidate): ?>
                    <div class="candidate-card">
                        <div class="candidate-image">
                            <?php if ($candidate['photo_url']): ?>
                                <img src="<?php echo htmlspecialchars($candidate['photo_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($candidate['name']); ?>">
                            <?php else: ?>
                                <div class="default-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="candidate-info">
                            <h3><?php echo htmlspecialchars($candidate['name']); ?></h3>
                            <p class="position"><?php echo htmlspecialchars($candidate['position']); ?></p>
                            <p class="bio"><?php echo htmlspecialchars($candidate['bio']); ?></p>
                        </div>
                        <div class="vote-selection">
                            <input type="radio" name="candidate_id" 
                                   id="candidate_<?php echo $candidate['id']; ?>" 
                                   value="<?php echo $candidate['id']; ?>" required>
                            <label for="candidate_<?php echo $candidate['id']; ?>" class="vote-button">
                                Select Candidate
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="voting-actions">
                <button type="submit" class="submit-vote" id="submitVote">
                    Cast Your Vote
                </button>
            </div>
        </form>

        <!-- Confirmation Modal -->
        <div id="confirmationModal" class="modal">
            <div class="modal-content">
                <h2>Confirm Your Vote</h2>
                <p>Are you sure you want to vote for <span id="selectedCandidate"></span>?</p>
                <p class="warning">This action cannot be undone!</p>
                <div class="modal-actions">
                    <button id="confirmVote" class="confirm-btn">Yes, Confirm Vote</button>
                    <button id="cancelVote" class="cancel-btn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Timer functionality
        function updateTimer() {
            const endDate = new Date(document.getElementById('electionTimer').dataset.end);
            const now = new Date();
            const timeLeft = endDate - now;

            if (timeLeft <= 0) {
                document.getElementById('timer').innerHTML = "Election has ended";
                document.getElementById('votingForm').style.display = 'none';
                return;
            }

            const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
            const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

            document.getElementById('timer').innerHTML = 
                `${days}d ${hours}h ${minutes}m ${seconds}s`;
        }

        setInterval(updateTimer, 1000);
        updateTimer();

        // Voting confirmation
        const form = document.getElementById('votingForm');
        const modal = document.getElementById('confirmationModal');
        const confirmBtn = document.getElementById('confirmVote');
        const cancelBtn = document.getElementById('cancelVote');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const selectedCandidate = document.querySelector('input[name="candidate_id"]:checked');
            if (selectedCandidate) {
                const candidateName = selectedCandidate.closest('.candidate-card')
                    .querySelector('h3').textContent;
                document.getElementById('selectedCandidate').textContent = candidateName;
                modal.style.display = 'flex';
            }
        });

        confirmBtn.addEventListener('click', function() {
            form.submit();
        });

        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        window.addEventListener('click', function(e) {
            if (e.target == modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html>