<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/security.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ../index.php');
    exit();
}

$errors = [];
$success_message = '';

// Check for registration success message
if (isset($_SESSION['registration_success'])) {
    $success_message = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "CSRF token validation failed";
    }
    
    // Sanitize inputs
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // Authenticate user
    if (empty($errors)) {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT users.user_id, users.username, users.password, users.first_name, users.last_name, users.is_active, roles.role_name 
                                FROM users 
                                JOIN roles ON users.role_id = roles.role_id 
                                WHERE users.username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check if user is active
            if ($user['is_active'] != 1) {
                $errors[] = "Your account has been deactivated";
            } elseif (verify_password($password, $user['password'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['role'] = $user['role_name'];
                
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                // Redirect based on role
                if ($user['role_name'] === 'admin') {
                    header('Location: ../crud/admin_dashboard.php');
                } else {
                    header('Location: ../index.php');
                }
                exit();
            } else {
                $errors[] = "Invalid username or password";
            }
        } else {
            $errors[] = "Invalid username or password";
        }
        
        $stmt->close();
    }
}

$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - User Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-page">
    <div class="container">
        <div class="auth-form">
            <h2>Login</h2>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
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
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="password-toggle" data-target="password">Show</button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <p class="auth-link">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
    <script>
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.dataset.target);
                if (!target) return;
                if (target.type === 'password') {
                    target.type = 'text';
                    button.textContent = 'Hide';
                } else {
                    target.type = 'password';
                    button.textContent = 'Show';
                }
            });
        });
    </script>
</body>
</html>
