<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-vote-yea"></i>
                <span>Admin Panel</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="<?php echo ($current_page === 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="elections.php" class="<?php echo ($current_page === 'elections') ? 'active' : ''; ?>">
                    <i class="fas fa-poll"></i> Elections
                </a>
                <a href="candidates.php" class="<?php echo ($current_page === 'candidates') ? 'active' : ''; ?>">
                    <i class="fas fa-user-tie"></i> Candidates
                </a>
                <a href="users.php" class="<?php echo ($current_page === 'users') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="results.php" class="<?php echo ($current_page === 'results') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Results
                </a>
                <a href="../logout.php" class="logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <h1><?php echo $page_title; ?></h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    
                </div>
            </div>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert success">
                    <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert error">
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Page specific content will go here -->
            <?php echo $content; ?>

        </div>
    </div>
</body>
</html> 