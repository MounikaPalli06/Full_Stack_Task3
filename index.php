<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/security.php';

// Redirect to login if not logged in
if (!is_logged_in()) {
    header('Location: auth/login.php');
    exit();
}

// Redirect admin to admin dashboard
if (is_admin()) {
    header('Location: crud/admin_dashboard.php');
    exit();
}

// Get user profile data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT user_id, username, email, first_name, last_name, profile_picture, bio, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - User Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <h1>User Management System</h1>
            <nav>
                <a href="index.php" class="active">Dashboard</a>
                <a href="profile/edit_profile.php">Profile</a>
                <a href="auth/logout.php">Logout</a>
            </nav>
        </div>
        
        <div class="main-content">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h2>
            <p>You are logged in as a <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong> user.</p>
            
            <div class="profile-container">
                <div class="profile-section">
                    <h3>Your Profile Picture</h3>
                    <?php if (!empty($user['profile_picture']) && file_exists('uploads/' . $user['profile_picture'])): ?>
                        <div class="profile-picture">
                            <img src="uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                        </div>
                    <?php else: ?>
                        <div class="profile-picture">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'%3E%3Crect fill='%23ddd' width='200' height='200'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-size='18' fill='%23666'%3ENo Image%3C/text%3E%3C/svg%3E" alt="No Profile Picture">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="profile-section">
                    <h3>Your Information</h3>
                    <div class="dashboard-card">
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                        <p><strong>Role:</strong> <span class="badge badge-info"><?php echo ucfirst($_SESSION['role']); ?></span></p>
                        <p><strong>Member Since:</strong> <?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                        
                        <?php if (!empty($user['bio'])): ?>
                            <p><strong>Bio:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                        <?php endif; ?>
                        
                        <a href="profile/edit_profile.php" class="btn btn-primary">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
