<?php
session_start();
include 'config.php';

if(isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if(strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    }
    else if($password != $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if username or email exists
        $check = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' OR email = '$email'");
        if(mysqli_num_rows($check) > 0) {
            $error = "Username or email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Set default role as 'voter'
            $role = 'user';
            
            $query = "INSERT INTO users (username, email, password, role) 
                     VALUES ('$username', '$email', '$hashed_password', '$role')";
            
            if(mysqli_query($conn, $query)) {
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
            <?php if(isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['success'])): ?>
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