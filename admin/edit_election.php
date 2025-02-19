<?php
session_start();
include '../config.php';

// Set page specific variables
$page_title = "Edit Election";
$current_page = "elections";

// Check if election ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No election specified";
    header("Location: elections.php");
    exit();
}

$election_id = mysqli_real_escape_string($conn, $_GET['id']);

// Handle election update
if (isset($_POST['update_election'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    // Validate dates
    if (strtotime($end_date) <= strtotime($start_date)) {
        $_SESSION['error'] = "End date must be after start date";
    } else {
             // Calculate status based on current date
        $current_date = date('Y-m-d H:i:s');
        $status = 'upcoming'; // Default status

        if ($current_date >= $start_date && $current_date <= $end_date) {
            $status = 'active';
        } elseif ($current_date > $end_date) {
            $status = 'completed';
        }

        $query = "UPDATE elections 
                  SET title = '$title', 
                      description = '$description', 
                      start_date = '$start_date', 
                      end_date = '$end_date' 
                  WHERE id = $election_id";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Election updated successfully!";
            header("Location: elections.php");
            exit();
        } else {
            $_SESSION['error'] = "Error updating election: " . mysqli_error($conn);
        }
    }
}

// Get election details
$result = mysqli_query($conn, "SELECT * FROM elections WHERE id = $election_id");
$election = mysqli_fetch_assoc($result);

if (!$election) {
    $_SESSION['error'] = "Election not found";
    header("Location: elections.php");
    exit();
}

// Start output buffering
ob_start();
?>

<div class="elections-container">
    <?php if(isset($_SESSION['error'])): ?>
        <div class="error-message">
            <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="modal-content" style="margin: 20px auto;">
        <div class="modal-header">
            <h2>Edit Election</h2>
        </div>
        <form action="" method="POST" class="admin-form">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($election['title']); ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" required><?php echo htmlspecialchars($election['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label>Start Date</label>
                <input type="datetime-local" name="start_date" 
                       value="<?php echo date('Y-m-d\TH:i', strtotime($election['start_date'])); ?>" required>
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input type="datetime-local" name="end_date" 
                       value="<?php echo date('Y-m-d\TH:i', strtotime($election['end_date'])); ?>" required>
            </div>
            <button type="submit" name="update_election" class="submit-btn">Update Election</button>
            <a href="elections.php" class="submit-btn" style="display: block; text-align: center; margin-top: 10px; text-decoration: none; background: #666;">Cancel</a>
        </form>
    </div>
</div>

<style>
/* Reuse existing styles from elections.php */
.elections-container {
    padding: 20px;
}

.modal-content {
    background: white;
    width: 90%;
    max-width: 600px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.modal-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.5rem;
}

.admin-form {
    padding: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 500;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
    outline: none;
}

.submit-btn {
    background: #4CAF50;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s ease;
}

.submit-btn:hover {
    background: #45a049;
}

@media (max-width: 768px) {
    .modal-content {
        margin: 20px;
        width: auto;
    }
}
</style>

<?php
$content = ob_get_clean();
include 'layout.php';
?> 