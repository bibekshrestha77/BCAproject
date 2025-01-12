<?php
session_start();
include '../config.php';

// Set page specific variables
$page_title = "Manage Candidates";
$current_page = "candidates";

// Handle candidate creation
if(isset($_POST['create_candidate'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $position = mysqli_real_escape_string($conn, $_POST['position']);
    $election_id = mysqli_real_escape_string($conn, $_POST['election_id']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    
    // Handle file upload
    $photo_url = '';
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "../uploads/candidates/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
        $file_name = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;
        
        if(move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $photo_url = 'uploads/candidates/' . $file_name;
        }
    }
    
    $query = "INSERT INTO candidates (name, position, election_id, bio, photo_url) 
              VALUES ('$name', '$position', '$election_id', '$bio', '$photo_url')";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Candidate added successfully!";
    } else {
        $_SESSION['error'] = "Error adding candidate: " . mysqli_error($conn);
    }
    header("Location: candidates.php");
    exit();
}

// Handle candidate deletion
if(isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Get photo URL before deleting
    $photo_query = mysqli_query($conn, "SELECT photo_url FROM candidates WHERE id = $id");
    $candidate = mysqli_fetch_assoc($photo_query);
    
    if($candidate && $candidate['photo_url']) {
        unlink("../" . $candidate['photo_url']); // Delete photo file
    }
    
    mysqli_query($conn, "DELETE FROM candidates WHERE id = $id");
    $_SESSION['success'] = "Candidate deleted successfully!";
    header("Location: candidates.php");
    exit();
}

// Get all candidates with election names
$candidates = mysqli_query($conn, 
    "SELECT c.*, e.title as election_name 
     FROM candidates c 
     JOIN elections e ON c.election_id = e.id 
     ORDER BY e.title, c.name"
);

// Get elections for dropdown
$elections = mysqli_query($conn, "SELECT id, title FROM elections WHERE status != 'completed'");

// Start output buffering
ob_start();
?>

<style>
/* Candidates specific styles */
.candidates-container {
    padding: 20px;
}

.candidates-header {
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
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
    outline: none;
}

/* File Upload Styles */
.file-upload {
    border: 2px dashed #e0e0e0;
    padding: 30px;
    text-align: center;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.file-upload:hover {
    border-color: #4CAF50;
    background-color: #f9f9f9;
}

.file-upload i {
    font-size: 40px;
    color: #999;
    margin-bottom: 10px;
}

.file-upload p {
    margin: 0;
    color: #666;
}

#photo-preview {
    margin-top: 10px;
    text-align: center;
}

#photo-preview img {
    max-width: 200px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

/* Candidate Photo */
.candidate-photo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.candidate-photo-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
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

@media (max-width: 768px) {
    .candidates-header {
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

<div class="candidates-container">
    <div class="candidates-header">
        <button class="add-btn" onclick="showModal()">
            <i class="fas fa-plus"></i> Add New Candidate
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Election</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($candidates)): ?>
                    <tr>
                        <td>
                            <?php if($row['photo_url']): ?>
                                <img src="../<?php echo $row['photo_url']; ?>" 
                                     alt="<?php echo htmlspecialchars($row['name']); ?>"
                                     class="candidate-photo">
                            <?php else: ?>
                                <div class="candidate-photo-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['position']); ?></td>
                        <td><?php echo htmlspecialchars($row['election_name']); ?></td>
                        <td class="actions">
                            <a href="edit_candidate.php?id=<?php echo $row['id']; ?>" class="btn-edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="candidates.php?delete=<?php echo $row['id']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this candidate?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Candidate Modal -->
<div id="addCandidateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Candidate</h2>
            <span class="close">&times;</span>
        </div>
        <form action="" method="POST" class="admin-form" enctype="multipart/form-data">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Position</label>
                <input type="text" name="position" required>
            </div>
            <div class="form-group">
                <label>Election</label>
                <select name="election_id" required>
                    <option value="">Select Election</option>
                    <?php while($election = mysqli_fetch_assoc($elections)): ?>
                        <option value="<?php echo $election['id']; ?>">
                            <?php echo htmlspecialchars($election['title']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Bio</label>
                <textarea name="bio" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label>Photo</label>
                <div class="file-upload" onclick="document.getElementById('photo').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click to upload photo</p>
                    <input type="file" id="photo" name="photo" accept="image/*" style="display: none">
                </div>
                <div id="photo-preview"></div>
            </div>
            <button type="submit" name="create_candidate" class="submit-btn">Add Candidate</button>
        </form>
    </div>
</div>

<script>
// Modal functionality
function showModal() {
    document.getElementById('addCandidateModal').style.display = 'block';
}

document.querySelector('.close').onclick = function() {
    document.getElementById('addCandidateModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('addCandidateModal')) {
        document.getElementById('addCandidateModal').style.display = 'none';
    }
}

// Photo preview
document.getElementById('photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('photo-preview');
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
