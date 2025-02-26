<td>
    <a href="edit_user.php?id=<?php echo $user['id']; ?>" 
       class="btn-edit">
        <i class="fas fa-edit"></i> Edit
    </a>
    <a href="manage_user.php?delete=<?php echo $user['id']; ?>" 
       class="btn-delete"
       onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
        <i class="fas fa-trash"></i> Delete
    </a>
</td> 

<style>
.btn-edit {
    padding: 6px 12px;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    font-size: 0.9em;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-right: 5px;
}

.btn-edit:hover {
    background: #2980b9;
}
</style> 