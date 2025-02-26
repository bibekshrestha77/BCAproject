<?php
session_start();
include '../config.php';

// Set page specific variables
$page_title = "Manage Elections";
$current_page = "elections";


//election creation
if (isset($_POST['create_election'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $course = mysqli_real_escape_string($conn, $_POST['course']);

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

        // Insert election with dynamic status
        $query = "INSERT INTO elections (title, description, start_date, end_date, status, created_by, course_id) 
                  VALUES ('$title', '$description', '$start_date', '$end_date', '$status', {$_SESSION['user_id']}, '$course')";

        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Election created successfully!";
        } else {
            $_SESSION['error'] = "Error creating election: " . mysqli_error($conn);
        }
    }
    header("Location: elections.php");
    exit();

}

// Handle election deletion
if (isset($_GET['delete'])) {
    $election_id = mysqli_real_escape_string($conn, $_GET['delete']);

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Delete related votes first
        $delete_votes = "DELETE FROM votes WHERE election_id = $election_id";
        if (!mysqli_query($conn, $delete_votes)) {
            throw new Exception(mysqli_error($conn));
        }

        // Delete election candidates
        $delete_candidates = "DELETE FROM election_candidates WHERE election_id = $election_id";
        if (!mysqli_query($conn, $delete_candidates)) {
            throw new Exception(mysqli_error($conn));
        }

        // Finally delete the election
        $delete_election = "DELETE FROM elections WHERE id = $election_id";
        if (!mysqli_query($conn, $delete_election)) {
            throw new Exception(mysqli_error($conn));
        }

        // If everything is successful, commit the transaction
        mysqli_commit($conn);
        $_SESSION['success'] = "Election and all related data deleted successfully!";

    } catch (Exception $e) {
        // If there's an error, rollback the changes
        mysqli_rollback($conn);
        $_SESSION['error'] = "Failed to delete election: " . $e->getMessage();
    }

    header("Location: elections.php");
    exit();
}

// Update election statuses based on current date
$current_date = date('Y-m-d H:i:s');
$update_status_query = "
    UPDATE elections 
    SET status = CASE 
        WHEN '$current_date' >= start_date AND '$current_date' <= end_date THEN 'active'
        WHEN '$current_date' > end_date THEN 'completed'
        ELSE 'upcoming'
    END
";
mysqli_query($conn, $update_status_query);


// Fetch elections with updated statuses
// Fetch elections with updated statuses and course names
$sql = "
    SELECT e.*, c.name AS course_name 
    FROM elections e 
    LEFT JOIN courses c ON e.course_id = c.id 
    ORDER BY e.created_at DESC
";
$elections = mysqli_query($conn, $sql);


// Start output buffering
ob_start();
?>

<style>
    /* Elections specific styles */
    .elections-container {
        padding: 20px;
    }

    .elections-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .add-btn {
        background: #4CAF50;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .add-btn:hover {
        background: #45a049;
        transform: translateY(-1px);
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    .modal-content {
        background: white;
        width: 90%;
        max-width: 600px;
        margin: 50px auto;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateY(-100px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h2 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.5rem;
    }

    .close {
        font-size: 24px;
        color: #666;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .close:hover {
        color: #333;
    }

    /* Form Styles */
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

    /* Table Styles */
    .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
    }

    tr:hover {
        background-color: #f8f9fa;
    }

    /* Status Badge */
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-badge.active {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .status-badge.upcoming {
        background: #fff3e0;
        color: #ef6c00;
    }

    .status-badge.completed {
        background: #eceff1;
        color: #546e7a;
    }

    /* Action Buttons */
    .actions {
        display: flex;
        gap: 10px;
    }

    .btn-edit,
    .btn-delete {
        padding: 6px 10px;
        border-radius: 4px;
        color: white;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .btn-edit {
        background: #3498db;
    }

    .btn-edit:hover {
        background: #2980b9;
    }

    .btn-delete {
        background: #e74c3c;
    }

    .btn-delete:hover {
        background: #c0392b;
    }

    @media (max-width: 768px) {
        .elections-header {
            flex-direction: column;
            gap: 10px;
        }

        .add-btn {
            width: 100%;
            justify-content: center;
        }

        .table-container {
            overflow-x: auto;
        }

        .actions {
            flex-direction: column;
        }

        .modal-content {
            margin: 20px;
            width: auto;
        }
    }
</style>

<div class="elections-container">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message">
            <?php
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message">
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <div class="elections-header">
        <button class="add-btn" onclick="showModal()">
            <i class="fas fa-plus"></i> Add New Election
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Course</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($elections)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($row['start_date'])); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($row['end_date'])); ?></td>
                        <td>
                            <?php
                            $status_class = strtolower($row['status']); // Convert status to lowercase for CSS
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>

                        </td>
                        <td class="actions">
                            <a href="edit_election.php?id=<?php echo $row['id']; ?>" class="btn-edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="elections.php?delete=<?php echo $row['id']; ?>" class="btn-delete"
                                onclick="return confirm('Are you sure you want to delete this election?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Election Modal -->
<div id="addElectionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Election</h2>
            <span class="close">&times;</span>
        </div>
        <form action="" method="POST" class="admin-form">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
            <label>Course</label>
    <select name="course" required>
        <option value="">Select Course</option>
        <?php
        $courses_query = mysqli_query($conn, "SELECT id, name FROM courses");
        while ($course = mysqli_fetch_assoc($courses_query)) {
            echo "<option value='{$course['id']}'>{$course['name']}</option>";
        }
        ?>
    </select>
    </div>
            <div class="form-group">
                <label>Start Date</label>
                <input type="datetime-local" name="start_date" required>
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input type="datetime-local" name="end_date" required>
            </div>
            <button type="submit" name="create_election" class="submit-btn">Create Election</button>
        </form>
    </div>
</div>

<script>
    // Modal functionality
    function showModal() {
        document.getElementById('addElectionModal').style.display = 'block';
    }

    document.querySelector('.close').onclick = function () {
        document.getElementById('addElectionModal').style.display = 'none';
    }

    window.onclick = function (event) {
        if (event.target == document.getElementById('addElectionModal')) {
            document.getElementById('addElectionModal').style.display = 'none';
        }
    }
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>