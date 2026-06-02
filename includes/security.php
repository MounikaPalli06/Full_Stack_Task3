<?php
// Security Functions

// Sanitize user input to prevent SQL injection and XSS
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Validate email format
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate password strength (minimum 8 characters, at least one uppercase, one lowercase, one number)
function validate_password($password) {
    if (strlen($password) < 8) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    return true;
}

// Hash password using password_hash
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verify password
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Generate CSRF token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect to login if not logged in
function require_login() {
    if (!is_logged_in()) {
        header('Location: /myproject/Task3/auth/login.php');
        exit();
    }
}

// Redirect to home if admin access required
function require_admin() {
    if (!is_admin()) {
        header('Location: /myproject/Task3/index.php');
        exit();
    }
}

// Validate file upload
function validate_file_upload($file) {
    $errors = [];
    
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = "File size must not exceed 2MB";
    }
    
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
        $errors[] = "Only " . implode(', ', ALLOWED_EXTENSIONS) . " files are allowed";
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($mime_type, $allowed_mimes)) {
        $errors[] = "Invalid file type";
    }
    
    return $errors;
}
?>
