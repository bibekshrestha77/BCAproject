<?php
session_start();
include '../config.php';

// Set page specific variables
$page_title = "Election Results";
$current_page = "results";

// Get all elections with their results
$elections_query = "SELECT e.*, 
                          COUNT(DISTINCT v.id) as total_votes,
                          COUNT(DISTINCT c.id) as total_candidates
                   FROM elections e
                   LEFT JOIN votes v ON e.id = v.election_id
                   LEFT JOIN candidates c ON e.id = c.election_id
                   GROUP BY e.id
                   ORDER BY e.end_date DESC";
$elections = mysqli_query($conn, $elections_query);

// Start output buffering
ob_start();
?>
<link rel="stylesheet" href="css/results.css">

<div class="results-container">
    <?php while($election = mysqli_fetch_assoc($elections)): ?>
        <div class="election-result-card">
            <div class="election-header">
                <h2><?php echo htmlspecialchars($election['title']); ?></h2>
                <div class="election-meta">
                    <span class="status-badge <?php echo $election['status']; ?>">
                        <?php echo ucfirst($election['status']); ?>
                    </span>
                    <span class="total-votes">
                        <i class="fas fa-vote-yea"></i>
                        <?php echo $election['total_votes']; ?> votes
                    </span>
                </div>
            </div>

            <?php
            // Get candidates and their votes for this election
            $candidates_query = "SELECT c.*, 
                                      COUNT(v.id) as vote_count,
                                      (COUNT(v.id) / {$election['total_votes']}) * 100 as vote_percentage
                               FROM candidates c
                               LEFT JOIN votes v ON c.id = v.candidate_id
                               WHERE c.election_id = {$election['id']}
                               GROUP BY c.id
                               ORDER BY vote_count DESC";
            $candidates = mysqli_query($conn, $candidates_query);
            ?>

            <div class="candidates-results">
                <?php while($candidate = mysqli_fetch_assoc($candidates)): ?>
                    <div class="candidate-result">
                        <div class="candidate-info">
                            <?php if($candidate['photo_url']): ?>
                                <img src="../<?php echo $candidate['photo_url']; ?>" 
                                     alt="<?php echo htmlspecialchars($candidate['name']); ?>"
                                     class="candidate-photo">
                            <?php else: ?>
                                <div class="candidate-photo-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                            <div class="candidate-details">
                                <h3><?php echo htmlspecialchars($candidate['name']); ?></h3>
                                <p class="position"><?php echo htmlspecialchars($candidate['position']); ?></p>
                            </div>
                            <div class="vote-count">
                                <span class="number"><?php echo $candidate['vote_count']; ?></span>
                                <span class="percentage">
                                    <?php echo number_format($candidate['vote_percentage'], 1); ?>%
                                </span>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $candidate['vote_percentage']; ?>%"></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="election-footer">
                <div class="election-dates">
                    <span>Started: <?php echo date('M d, Y', strtotime($election['start_date'])); ?></span>
                    <span>Ended: <?php echo date('M d, Y', strtotime($election['end_date'])); ?></span>
                </div>
                <button class="export-btn" onclick="exportResults(<?php echo $election['id']; ?>)">
                    <i class="fas fa-download"></i> Export Results
                </button>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<script>
function exportResults(electionId) {
    // Implement PDF or Excel export functionality
    window.location.href = 'export_results.php?election_id=' + electionId;
}
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?> 