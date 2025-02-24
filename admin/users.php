<?php
session_start();
include '../config.php';

// Set page specific variables
$page_title = "Manage Users";
$current_page = "users";

// Handle user creation
if(isset($_POST['create_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Check if username or email already exists
    $check_query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
    $check_result = mysqli_query($conn, $check_query);
    
    if(mysqli_num_rows($check_result) > 0) {
        $_SESSION['error'] = "Username or email already exists";
    } else {
        $query = "INSERT INTO users (username, email, password, role) 
                  VALUES ('$username', '$email', '$password', '$role')";
        
        if(mysqli_query($conn, $query)) {
            $_SESSION['success'] = "User created successfully!";
        } else {
            $_SESSION['error'] = "Error creating user: " . mysqli_error($conn);
        }
    }
    header("Location: users.php");
    exit();
}

// Get all users
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");

// Start output buffering
ob_start();
?>

<style>
/* Users specific styles */
.users-container {
    padding: 20px;
}

.users-header {
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
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
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

/* Table Styles */
.table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
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

/* User Info Cell */
.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

/* Role Badge */
.role-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.role-badge.admin {
    background: #fef3c7;
    color: #92400e;
}

.role-badge.user {
    background: #e0e7ff;
    color: #3730a3;
}

/* Action Buttons */
.actions {
    display: flex;
    gap: 10px;
}

.btn-edit, .btn-delete {
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

.current-user {
    background: #f8f9fa;
}

.current-user-badge {
    background: #e9ecef;
    color: #495057;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
}

@media (max-width: 768px) {
    .users-header {
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
    
    .user-info {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<div class="users-container">
    <div class="users-header">
        <button class="add-btn" onclick="showModal()">
            <i class="fas fa-plus"></i> Add New User
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined Date</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($users)): ?>
                    <tr class="<?php echo ($row['id'] == $_SESSION['user_id']) ? 'current-user' : ''; ?>">
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <?php echo htmlspecialchars($row['username']); ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <span class="role-badge <?php echo $row['role']; ?>">
                                <?php echo ucfirst($row['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        <td><?php echo $row['last_login'] ? date('M d, Y H:i', strtotime($row['last_login'])) : 'Never'; ?></td>
                        <td class="actions">
                            <?php if($row['id'] != $_SESSION['admin_id']): ?>
                                <a href="delete_user.php?id=<?php echo $row['id']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php else: ?>
                                <span class="current-user-badge">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New User</h2>
            <span class="close">&times;</span>
        </div>
        <form action="" method="POST" class="admin-form">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" name="create_user" class="submit-btn">Create User</button>
        </form>
    </div>
</div>

<script>
// Modal functionality
function showModal() {
    document.getElementById('addUserModal').style.display = 'block';
}

document.querySelector('.close').onclick = function() {
    document.getElementById('addUserModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('addUserModal')) {
        document.getElementById('addUserModal').style.display = 'none';
    }
}
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
