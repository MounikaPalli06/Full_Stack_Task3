<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/security.php';

require_login();
require_admin();

// Fetch all users with their role information
$query = "SELECT users.user_id, users.username, users.email, users.first_name, users.last_name, 
                  users.created_at, users.is_active, roles.role_name 
           FROM users 
           JOIN roles ON users.role_id = roles.role_id 
           ORDER BY users.created_at DESC";

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$users = $result->fetch_all(MYSQLI_ASSOC);

// Check for success message
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - User Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        function confirmDelete(username) {
            if (confirm("Are you sure you want to delete user '" + username + "'? This action cannot be undone.")) {
                return true;
            }
            return false;
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <h1>User Management System</h1>
            <nav>
                <a href="../index.php">Dashboard</a>
                <a href="manage_users.php" class="active">Manage Users</a>
                <a href="add_user.php">Add User</a>
                <a href="../profile/edit_profile.php">Profile</a>
                <a href="../auth/logout.php">Logout</a>
            </nav>
        </div>
        
        <div class="main-content">
            <h2>Manage Users</h2>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <a href="add_user.php" class="btn btn-primary">Add New User</a>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['role_name'] === 'admin' ? 'danger' : 'info'; ?>">
                                        <?php echo ucfirst($user['role_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $user['is_active'] ? 'success' : 'warning'; ?>">
                                        <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                    <a href="delete_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('<?php echo htmlspecialchars($user['username']); ?>')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
