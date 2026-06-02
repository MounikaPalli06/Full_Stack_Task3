<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/security.php';

require_login();
require_admin();

$user_id = sanitize_input($_GET['id'] ?? '');

// Validate user ID
if (empty($user_id) || !is_numeric($user_id)) {
    $_SESSION['error_message'] = "Invalid user ID";
    header('Location: manage_users.php');
    exit();
}

// Prevent admin from deleting themselves
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error_message'] = "You cannot delete your own account";
    header('Location: manage_users.php');
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT user_id, username, profile_picture FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "User not found";
    header('Location: manage_users.php');
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Delete profile picture if exists
if (!empty($user['profile_picture']) && file_exists(UPLOAD_DIR . $user['profile_picture'])) {
    unlink(UPLOAD_DIR . $user['profile_picture']);
}

// Delete user from database
$delete_stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$delete_stmt->bind_param("i", $user_id);

if ($delete_stmt->execute()) {
    $_SESSION['success_message'] = "User '" . htmlspecialchars($user['username']) . "' deleted successfully!";
} else {
    $_SESSION['error_message'] = "Error deleting user. Please try again.";
}

$delete_stmt->close();

header('Location: manage_users.php');
exit();
?>
