<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php';

// Set page-specific variables
$page_title = "Edit User";
$current_page = "users";

// Check if user ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No user specified";
    header("Location: users.php");
    exit();
}

$user_id = mysqli_real_escape_string($conn, $_GET['id']);

// Handle user update
if (isset($_POST['update_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $course_id = mysqli_real_escape_string($conn, $_POST['course']);

    // Update user information
    $query = "UPDATE users 
    SET username = '$username', 
        email = '$email', 
        course_id = '$course_id'
    WHERE id = $user_id";


    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "User updated successfully!";
        header("Location: users.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating user: " . mysqli_error($conn);
    }
}

// Get user details
$result = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($result);

if (!$user) {
    $_SESSION['error'] = "User not found";
    header("Location: users.php");
    exit();
}

// Get all available courses
$courses_query = "SELECT id, course FROM courses ORDER BY course ASC";
$courses = mysqli_query($conn, $courses_query);

if (!$courses) {
    die("Query Failed: " . mysqli_error($conn)); // Debugging line
}


// Start output buffering
ob_start();
?>

<div class="users-container">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message">
            <?php
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="modal-content" style="margin: 20px auto;">
        <div class="modal-header">
            <h2>Edit User</h2>
        </div>
        <form action="" method="POST" class="admin-form">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label>Course</label>
                <select name="course" required>
                    <option value="">Select Course</option>
                    <?php while ($course = mysqli_fetch_assoc($courses)): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo ($user['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['course']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

            </div>

            <button type="submit" name="update_user" class="submit-btn">Update User</button>
            <a href="manage_user.php" class="submit-btn"
                style="display: block; text-align: center; margin-top: 10px; text-decoration: none; background: #666;">Cancel</a>
        </form>
    </div>
</div>

<style>
    .users-container {
        padding: 20px;
    }

    .modal-content {
        background: white;
        width: 90%;
        max-width: 600px;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
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
    .form-group select {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
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

    .error-message {
        background: #fee;
        color: #e74c3c;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 20px;
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