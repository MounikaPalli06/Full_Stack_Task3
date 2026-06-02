<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/security.php';

require_login();
require_admin();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "CSRF token validation failed";
    }
    
    // Sanitize inputs
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $first_name = sanitize_input($_POST['first_name'] ?? '');
    $last_name = sanitize_input($_POST['last_name'] ?? '');
    $role_id = sanitize_input($_POST['role_id'] ?? 1);
    
    // Validate inputs
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!validate_email($email)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (!validate_password($password)) {
        $errors[] = "Password must be at least 8 characters with uppercase, lowercase, and number";
    } elseif ($password !== $password_confirm) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($first_name)) {
        $errors[] = "First name is required";
    }
    
    if (empty($last_name)) {
        $errors[] = "Last name is required";
    }
    
    // Check if username already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Username already exists";
        }
        $stmt->close();
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists";
        }
        $stmt->close();
    }
    
    // Add user if no errors
    if (empty($errors)) {
        $hashed_password = hash_password($password);
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, role_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $username, $email, $hashed_password, $first_name, $last_name, $role_id);
        
        if ($stmt->execute()) {
            $success = true;
            $_SESSION['success_message'] = "User added successfully!";
            header('Location: manage_users.php');
            exit();
        } else {
            $errors[] = "Error adding user. Please try again.";
        }
        $stmt->close();
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
    <title>Add User - User Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <h1>User Management System</h1>
            <nav>
                <a href="../index.php">Dashboard</a>
                <a href="manage_users.php">Manage Users</a>
                <a href="add_user.php" class="active">Add User</a>
                <a href="../profile/edit_profile.php">Profile</a>
                <a href="../auth/logout.php">Logout</a>
            </nav>
        </div>
        
        <div class="main-content">
            <h2>Add New User</h2>
            
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
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="role_id">Role:</label>
                    <select id="role_id" name="role_id" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['role_id']; ?>" 
                                <?php echo isset($_POST['role_id']) && $_POST['role_id'] == $role['role_id'] ? 'selected' : ''; ?>>
                                <?php echo ucfirst($role['role_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                    <small>Must be at least 8 characters with uppercase, lowercase, and number</small>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Confirm Password:</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Add User</button>
                <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>
