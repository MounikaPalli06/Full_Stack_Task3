<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/security.php';

require_login();
require_admin();

$user_id = sanitize_input($_GET['id'] ?? '');
$errors = [];
$success = false;

// Fetch user data
if (empty($user_id) || !is_numeric($user_id)) {
    die("Invalid user ID");
}

$stmt = $conn->prepare("SELECT users.user_id, users.username, users.email, users.first_name, users.last_name, users.role_id, users.is_active, roles.role_name 
                       FROM users 
                       JOIN roles ON users.role_id = roles.role_id 
                       WHERE users.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found");
}

$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "CSRF token validation failed";
    }
    
    // Sanitize inputs
    $email = sanitize_input($_POST['email'] ?? '');
    $first_name = sanitize_input($_POST['first_name'] ?? '');
    $last_name = sanitize_input($_POST['last_name'] ?? '');
    $role_id = sanitize_input($_POST['role_id'] ?? $user['role_id']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate inputs
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!validate_email($email)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($first_name)) {
        $errors[] = "First name is required";
    }
    
    if (empty($last_name)) {
        $errors[] = "Last name is required";
    }
    
    // Check if email is changed and already exists
    if (empty($errors) && $email !== $user['email']) {
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Email already exists";
        }
        $check_stmt->close();
    }
    
    // Update user if no errors
    if (empty($errors)) {
        $update_stmt = $conn->prepare("UPDATE users SET email = ?, first_name = ?, last_name = ?, role_id = ?, is_active = ? WHERE user_id = ?");
        $update_stmt->bind_param("sssii", $email, $first_name, $last_name, $role_id, $is_active, $user_id);
        
        if ($update_stmt->execute()) {
            $success = true;
            $_SESSION['success_message'] = "User updated successfully!";
            header('Location: manage_users.php');
            exit();
        } else {
            $errors[] = "Error updating user. Please try again.";
        }
        $update_stmt->close();
    }
}

$csrf_token = generate_csrf_token();

// Fetch roles for dropdown
$roles_result = $conn->query("SELECT role_id, role_name FROM roles");
$roles = $roles_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - User Management System</title>
    <link rel="stylesheet" href="../css/style.css">
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
            <h2>Edit User: <?php echo htmlspecialchars($user['username']); ?></h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <div class="form-group">
                    <label for="username">Username (Read-only):</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="role_id">Role:</label>
                    <select id="role_id" name="role_id" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['role_id']; ?>" 
                                <?php echo $user['role_id'] == $role['role_id'] ? 'selected' : ''; ?>>
                                <?php echo ucfirst($role['role_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="is_active">
                        <input type="checkbox" id="is_active" name="is_active" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                        Active
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>
