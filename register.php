<?php
session_start();
include 'config.php';

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $course = mysqli_real_escape_string($conn, $_POST['course']); // Add course field

    // Validation
    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } else if ($password != $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if username or email exists
        $check = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' OR email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Username or email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Set default role as 'user'
            $role = 'user';

            // Include course in the query
            $query = "INSERT INTO users (username, email, password, role, course) 
                     VALUES ('$username', '$email', '$hashed_password', '$role', '$course')";

            if (mysqli_query($conn, $query)) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Registration failed: " . mysqli_error($conn);
            }
        }
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
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Create Account</h2>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message"><?php echo $_SESSION['success']; ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Course & Semester</label>
                    <select name="course" required class="form-control">
                        <option value="">Select your course and semester</option>
                        <option value="BCA 1st Semester" <?php echo (isset($_POST['course']) && $_POST['course'] == 'BCA 1st Semester') ? 'selected' : ''; ?>>BCA 1st Semester</option>
                        <option value="BCA 2nd Semester" <?php echo (isset($_POST['course']) && $_POST['course'] == 'BCA 2nd Semester') ? 'selected' : ''; ?>>BCA 2nd Semester</option>
                        <option value="BCA 3rd Semester" <?php echo (isset($_POST['course']) && $_POST['course'] == 'BCA 3rd Semester') ? 'selected' : ''; ?>>BCA 3rd Semester</option>
                        <option value="BCA 4th Semester" <?php echo (isset($_POST['course']) && $_POST['course'] == 'BCA 4th Semester') ? 'selected' : ''; ?>>BCA 4th Semester</option>
                        <option value="BCA 5th Semester" <?php echo (isset($_POST['course']) && $_POST['course'] == 'BCA 5th Semester') ? 'selected' : ''; ?>>BCA 5th Semester</option>
                        <option value="BCA 6th Semester" <?php echo (isset($_POST['course']) && $_POST['course'] == 'BCA 6th Semester') ? 'selected' : ''; ?>>BCA 6th Semester</option>
                        <option value="BCA 7th Semester" <?php echo (isset($_POST['course']) && $_POST['course'] == 'BCA 7th Semester') ? 'selected' : ''; ?>>BCA 7th Semester</option>
                        <option value="BCA 8th Semester" <?php echo (isset($_POST['course']) && $_POST['course'] == 'BCA 8th Semester') ? 'selected' : ''; ?>>BCA 8th Semester</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                    <small class="password-hint">Must be at least 6 characters long</small>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
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

<style>
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 15px;
    font-size: 14px;
    background-color: #fff;
}

.form-group select:focus {
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.2);
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #333;
    font-weight: 500;
}

/* Style for the dropdown arrow */
.form-group select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 1em;
    padding-right: 30px;
}
</style>