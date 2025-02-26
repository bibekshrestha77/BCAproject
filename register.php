<?php
session_start();
include 'config.php';

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $course = $_POST['course'];

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($course)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if username or email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username or email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';

            // Insert user data
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, course_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $username, $email, $hashed_password, $role, $course);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Registration failed: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Voting System</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="style.css">
    <style>
    /* Add this CSS after your existing styles or in login.css */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }

    .form-group input,
    .form-group select { /* Add select here to match input styling */
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
        background-color: white; /* Add this to match input background */
        color: #333; /* Add this to match input text color */
        appearance: none; /* Remove default select styling */
        -webkit-appearance: none; /* For Safari */
        -moz-appearance: none; /* For Firefox */
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #4CAF50;
        box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        outline: none;
    }

    /* Add a custom arrow for the select element */
    .form-group select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: calc(100% - 12px) center;
        padding-right: 35px; /* Make room for the arrow */
    }

    /* Remove default arrow in IE */
    .form-group select::-ms-expand {
        display: none;
    }

    /* Style the select options */
    .form-group select option {
        padding: 12px;
        font-size: 14px;
    }

    .password-hint {
        display: block;
        margin-top: 5px;
        font-size: 12px;
        color: #666;
    }

    /* Keep your existing button and other styles */
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Create Account</h2>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="course">Course & Semester</label>
                    <select name="course" id="course" required class="form-control">
                        <option value="">Select your course and semester</option>
                        <option value="1">BCA 1st Semester</option>
                        <option value="2">BCA 2nd Semester</option>
                        <option value="3">BCA 3rd Semester</option>
                        <option value="4">BCA 4th Semester</option>
                        <option value="5">BCA 5th Semester</option>
                        <option value="6">BCA 6th Semester</option>
                        <option value="7">BCA 7th Semester</option>
                        <option value="8">BCA 8th Semester</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                    <small class="password-hint">Must be at least 6 characters long</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>

                <button type="submit" name="register">Create Account</button>
            </form>

            <div class="login-link">
                <p>Already have an account?</p>
                <a href="login.php">Login Now</a>
            </div>
        </div>
    </div>
</body>
</html>