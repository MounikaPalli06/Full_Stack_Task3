<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/security.php';

require_login();

$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Fetch user data
$stmt = $conn->prepare("SELECT user_id, username, email, first_name, last_name, profile_picture, bio FROM users WHERE user_id = ?");
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
    $bio = sanitize_input($_POST['bio'] ?? '');
    
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
    
    // Handle profile picture upload
    $profile_picture = $user['profile_picture'];
    
    if (!empty($_FILES['profile_picture']['name'])) {
        $file_errors = validate_file_upload($_FILES['profile_picture']);
        
        if (empty($file_errors)) {
            // Delete old profile picture if exists
            if (!empty($user['profile_picture']) && file_exists(UPLOAD_DIR . $user['profile_picture'])) {
                unlink(UPLOAD_DIR . $user['profile_picture']);
            }
            
            // Generate unique filename
            $file_ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $profile_picture = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], UPLOAD_DIR . $profile_picture)) {
                // File moved successfully
            } else {
                $errors[] = "Error uploading file";
                $profile_picture = $user['profile_picture'];
            }
        } else {
            $errors = array_merge($errors, $file_errors);
        }
    }
    
    // Update user if no errors
    if (empty($errors)) {
        $update_stmt = $conn->prepare("UPDATE users SET email = ?, first_name = ?, last_name = ?, bio = ?, profile_picture = ? WHERE user_id = ?");
        $update_stmt->bind_param("sssssi", $email, $first_name, $last_name, $bio, $profile_picture, $user_id);
        
        if ($update_stmt->execute()) {
            $success = true;
            // Update session data
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $user = array_merge($user, ['email' => $email, 'first_name' => $first_name, 'last_name' => $last_name, 'bio' => $bio, 'profile_picture' => $profile_picture]);
            $_SESSION['success_message'] = "Profile updated successfully!";
        } else {
            $errors[] = "Error updating profile. Please try again.";
        }
        $update_stmt->close();
    }
}

$csrf_token = generate_csrf_token();

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
    <title>Edit Profile - User Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <h1>User Management System</h1>
            <nav>
                <a href="../index.php">Dashboard</a>
                <?php if (is_admin()): ?>
                    <a href="../crud/manage_users.php">Manage Users</a>
                    <a href="../crud/add_user.php">Add User</a>
                <?php endif; ?>
                <a href="edit_profile.php" class="active">Profile</a>
                <a href="../auth/logout.php">Logout</a>
            </nav>
        </div>
        
        <div class="main-content profile-page">
            <div class="profile-header">
                <h2>Edit Your Profile</h2>
                <p>Manage your account information and upload your profile picture</p>
            </div>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="profile-wrapper">
                <div class="profile-card">
                    <h3>Your Photo</h3>
                    <div class="profile-picture-container">
                        <?php if (!empty($user['profile_picture']) && file_exists(UPLOAD_DIR . $user['profile_picture'])): ?>
                            <img src="/myproject/Task3/uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-img">
                        <?php else: ?>
                            <div class="profile-placeholder">
                                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="100" cy="80" r="40" fill="#ffffff" opacity="0.3"/>
                                    <ellipse cx="100" cy="150" rx="60" ry="50" fill="#ffffff" opacity="0.3"/>
                                </svg>
                                <p>No Photo Yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <form method="POST" action="" enctype="multipart/form-data" class="profile-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    
                    <div class="form-section">
                        <h3>Account Information</h3>
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small>Your username cannot be changed</small>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>Personal Details</h3>
                        
                        <div class="form-group">
                            <label for="bio">About You</label>
                            <textarea id="bio" name="bio" rows="4" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>Upload Photo</h3>
                        
                        <div class="form-group file-upload-group">
                            <label for="profile_picture">Choose Profile Picture</label>
                            <div class="file-input-wrapper" onclick="document.getElementById('profile_picture').click();">
                                <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                                <span class="file-label">📁 Click to upload or drag and drop</span>
                            </div>
                            <small>JPG, PNG, or GIF (Max 2MB)</small>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="../index.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
