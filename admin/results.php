<?php
session_start();
include '../config.php';
// Set the time zone
date_default_timezone_set('Asia/Kathmandu'); 
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

            // Check if the election has ended
            $current_date = date('Y-m-d H:i:s');
            $election_ended = ($current_date > $election['end_date']);

            // Determine the winner if the election has ended
            $winner = null;
            if ($election_ended) {
                $winner_query = "SELECT c.*, 
                                       COUNT(v.id) as vote_count
                                FROM candidates c
                                LEFT JOIN votes v ON c.id = v.candidate_id
                                WHERE c.election_id = {$election['id']}
                                GROUP BY c.id
                                ORDER BY vote_count DESC
                                LIMIT 1";
                $winner_result = mysqli_query($conn, $winner_query);
                $winner = mysqli_fetch_assoc($winner_result);
            }
            ?>

            <!-- Display the winner if the election has ended -->
            <?php if ($election_ended && $winner): ?>
                <div class="winner-card">
                    <div class="winner-header">
                        <h3>Winner Announcement</h3>
                    </div>
                    <div class="winner-details">
                        <?php if($winner['photo_url']): ?>
                            <img src="../<?php echo $winner['photo_url']; ?>" 
                                 alt="<?php echo htmlspecialchars($winner['name']); ?>"
                                 class="winner-photo">
                        <?php else: ?>
                            <div class="winner-photo-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <div class="winner-info">
                            <h3><?php echo htmlspecialchars($winner['name']); ?></h3>
                            <p class="position"><?php echo htmlspecialchars($winner['position']); ?></p>
                            <p class="vote-count">
                                <i class="fas fa-trophy"></i>
                                <?php echo $winner['vote_count']; ?> votes
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Display all candidates' results -->
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
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
<style>
 /* Winner Card Styles */
.winner-card {
    background-color: #fff;
    border: 2px solid #4CAF50; /* Green border */
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.winner-card:hover {
    transform: translateY(-5px); /* Slight lift on hover */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover */
}

.winner-header {
    text-align: center;
    margin-bottom: 15px;
}

.winner-header h3 {
    font-size: 24px;
    color: #4CAF50; /* Green color */
    margin: 0;
    transition: color 0.3s ease;
}

.winner-card:hover .winner-header h3 {
    color: #45a049; /* Darker green on hover */
}

.winner-details {
    display: flex;
    align-items: center;
    gap: 20px;
}

.winner-photo {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.winner-card:hover .winner-photo {
    transform: scale(1.1); /* Slight zoom on hover */
}

.winner-photo-placeholder {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background-color: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    color: #ccc;
    transition: background-color 0.3s ease;
}

.winner-card:hover .winner-photo-placeholder {
    background-color: #e0e0e0; /* Slightly darker background on hover */
}

.winner-info h3 {
    font-size: 22px;
    margin: 0;
    transition: color 0.3s ease;
}

.winner-card:hover .winner-info h3 {
    color: #45a049; /* Darker green on hover */
}

.winner-info .position {
    font-size: 16px;
    color: #666;
    margin: 5px 0;
    transition: color 0.3s ease;
}

.winner-card:hover .winner-info .position {
    color: #555; /* Slightly darker text on hover */
}

.winner-info .vote-count {
    font-size: 18px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: color 0.3s ease;
}

.winner-card:hover .winner-info .vote-count {
    color: #45a049; /* Darker green on hover */
}

.winner-info .vote-count i {
    color: #4CAF50; /* Green color */
    transition: color 0.3s ease;
}

.winner-card:hover .winner-info .vote-count i {
    color: #45a049; /* Darker green on hover */
}
</style>