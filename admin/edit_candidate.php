<?php
session_start();
include '../config.php';

// Set page-specific variables
$page_title = "Edit Candidate";
$current_page = "candidates";

// Check if candidate ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No candidate specified";
    header("Location: candidates.php");
    exit();
}

$candidate_id = mysqli_real_escape_string($conn, $_GET['id']);

// Handle candidate update
if (isset($_POST['update_candidate'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $position = mysqli_real_escape_string($conn, $_POST['position']);
    $election_id = mysqli_real_escape_string($conn, $_POST['election_id']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);

    // Update candidate information including course
    $query = "UPDATE candidates 
             SET name = '$name', 
                 position = '$position', 
                 election_id = '$election_id',
                 course = '$course',
                 bio = '$bio'
             WHERE id = $candidate_id";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Candidate updated successfully!";
        header("Location: candidates.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating candidate: " . mysqli_error($conn);
    }
}

// Get candidate details
$result = mysqli_query($conn, "SELECT * FROM candidates WHERE id = $candidate_id");
$candidate = mysqli_fetch_assoc($result);

if (!$candidate) {
    $_SESSION['error'] = "Candidate not found";
    header("Location: candidates.php");
    exit();
}

// Get all elections for dropdown
$elections_query = "SELECT id, title FROM elections ORDER BY created_at DESC";
$elections = mysqli_query($conn, $elections_query);

// Get all available courses (assuming a courses table exists)
$courses_query = "SELECT DISTINCT course FROM candidates ORDER BY course ASC";
$courses = mysqli_query($conn, $courses_query);

// Start output buffering
ob_start();
?>

<div class="candidates-container">
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
            <h2>Edit Candidate</h2>
        </div>
        <form action="" method="POST" class="admin-form">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($candidate['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Position</label>
                <input type="text" name="position" value="<?php echo htmlspecialchars($candidate['position']); ?>" required>
            </div>
            <div class="form-group">
                <label>Election</label>
                <select name="election_id" required>
                    <?php while($election = mysqli_fetch_assoc($elections)): ?>
                        <option value="<?php echo $election['id']; ?>" 
                            <?php echo ($election['id'] == $candidate['election_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($election['title']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Course</label>
                <select name="course" required>
                    <?php while($course = mysqli_fetch_assoc($courses)): ?>
                        <option value="<?php echo htmlspecialchars($course['course']); ?>" 
                            <?php echo ($course['course'] == $candidate['course']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['course']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Bio</label>
                <textarea name="bio" rows="4"><?php echo htmlspecialchars($candidate['bio']); ?></textarea>
            </div>
            <button type="submit" name="update_candidate" class="submit-btn">Update Candidate</button>
            <a href="candidates.php" class="submit-btn" style="display: block; text-align: center; margin-top: 10px; text-decoration: none; background: #666;">Cancel</a>
        </form>
    </div>
</div>

<style>
.candidates-container {
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
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
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
