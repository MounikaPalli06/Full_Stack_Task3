<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/security.php';

require_login();
require_admin();

// Get statistics
$total_users_result = $conn->query("SELECT COUNT(*) as count FROM users");
$total_users = $total_users_result->fetch_assoc()['count'];

$active_users_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
$active_users = $active_users_result->fetch_assoc()['count'];

$admin_users_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role_id = (SELECT role_id FROM roles WHERE role_name = 'admin')");
$admin_users = $admin_users_result->fetch_assoc()['count'];

$inactive_users_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_active = 0");
$inactive_users = $inactive_users_result->fetch_assoc()['count'];

// Get recent users
$recent_users_result = $conn->query("SELECT user_id, username, email, first_name, last_name, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $recent_users_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - User Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <h1>User Management System</h1>
            <nav>
                <a href="../index.php">Dashboard</a>
                <a href="manage_users.php">Manage Users</a>
                <a href="add_user.php">Add User</a>
                <a href="../profile/edit_profile.php">Profile</a>
                <a href="../auth/logout.php">Logout</a>
            </nav>
        </div>
        
        <div class="main-content">
            <h2>Admin Dashboard</h2>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! Here is an overview of your user management system.</p>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?php echo $total_users; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Active Users</h3>
                    <p class="stat-number"><?php echo $active_users; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Administrators</h3>
                    <p class="stat-number"><?php echo $admin_users; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Inactive Users</h3>
                    <p class="stat-number"><?php echo $inactive_users; ?></p>
                </div>
            </div>
            
            <h3 style="margin-top: 30px;">Recent Users</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-info">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
