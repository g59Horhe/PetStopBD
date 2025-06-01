<?php
// Authentication functions for PetStopBD

// Start session if not already started
function start_session_if_not_started() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Generate random OTP
function generate_otp($length = 6) {
    $characters = '0123456789';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $otp;
}

// Register a new user
function register_user($conn, $username, $email, $password, $first_name, $last_name) {
    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Username already exists'];
    }
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Email already exists'];
    }
    
    // Generate OTP
    $otp = generate_otp();
    date_default_timezone_set('Asia/Dhaka'); // Use your local timezone here
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Create temporary user record
    $stmt = $conn->prepare("INSERT INTO user_verification (username, email, password, first_name, last_name, otp, otp_expiry) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $username, $email, $hashed_password, $first_name, $last_name, $otp, $otp_expiry);
    
    if ($stmt->execute()) {
        return [
            'success' => true, 
            'message' => 'Verification code sent to your email', 
            'email' => $email,
            'otp' => $otp  // In a production environment, you'd never return this, just for testing
        ];
    } else {
        return ['success' => false, 'message' => 'Registration failed: ' . $conn->error];
    }
}

// Verify OTP and complete registration
function verify_otp($conn, $email, $otp) {
    // Get user verification data
    $stmt = $conn->prepare("SELECT * FROM user_verification WHERE email = ? AND otp = ? AND otp_expiry > NOW()");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        return ['success' => false, 'message' => 'Invalid or expired verification code'];
    }
    
    $user_data = $result->fetch_assoc();
    
    // Create permanent user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", 
                     $user_data['username'], 
                     $user_data['email'], 
                     $user_data['password'], 
                     $user_data['first_name'], 
                     $user_data['last_name']);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        // Delete verification record
        $stmt = $conn->prepare("DELETE FROM user_verification WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        // Set session
        start_session_if_not_started();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $user_data['username'];
        $_SESSION['first_name'] = $user_data['first_name'];
        $_SESSION['last_name'] = $user_data['last_name'];
        
        return ['success' => true, 'message' => 'Registration completed successfully'];
    } else {
        return ['success' => false, 'message' => 'Registration failed: ' . $conn->error];
    }
}

// Login user
function login_user($conn, $username_or_email, $password) {
    // Check if input is email or username
    $is_email = filter_var($username_or_email, FILTER_VALIDATE_EMAIL);
    
    if ($is_email) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    }
    
    $stmt->bind_param("s", $username_or_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        return ['success' => false, 'message' => 'User not found'];
    }
    
    $user = $result->fetch_assoc();
    
    // Verify password
    if (password_verify($password, $user['password'])) {
        // Set session
        start_session_if_not_started();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        
        // Check if remember me was checked
        if (isset($_POST['remember_me']) && $_POST['remember_me'] == 'on') {
            // Generate token
            $token = bin2hex(random_bytes(32));
            $hashed_token = password_hash($token, PASSWORD_DEFAULT);
            
            // Set token expiry to 30 days
            $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            // Store token in database
            $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expiry) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user['id'], $hashed_token, $expiry);
            $stmt->execute();
            
            // Set cookie
            setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 days
            setcookie('remember_user', $user['id'], time() + (86400 * 30), "/"); // 30 days
        }
        
        return ['success' => true, 'message' => 'Login successful'];
    } else {
        return ['success' => false, 'message' => 'Invalid password'];
    }
}

// Check if user is logged in
function is_logged_in() {
    start_session_if_not_started();
    return isset($_SESSION['user_id']);
}

// Get current user data
function get_user_data($conn) {
    if (!is_logged_in()) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT id, username, email, first_name, last_name, profile_image, user_type FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        return null;
    }
    
    return $result->fetch_assoc();
}

// Logout user
function logout_user() {
    start_session_if_not_started();
    
    // Unset all session variables
    $_SESSION = array();
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Delete remember me cookie
    setcookie('remember_token', '', time() - 3600, "/");
    setcookie('remember_user', '', time() - 3600, "/");
    
    // Destroy the session
    session_destroy();
}

// Reset password request
function request_password_reset($conn, $email) {
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        return ['success' => false, 'message' => 'Email not found'];
    }
    
    $user_id = $result->fetch_assoc()['id'];
    
    // Generate reset token
    $token = bin2hex(random_bytes(32));
    
    // Set timezone to match your server
    date_default_timezone_set('Asia/Dhaka'); // Use your local timezone
    
    // Calculate expiry time (1 hour in the future)
    $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Delete any existing reset tokens for this user
    $stmt = $conn->prepare("DELETE FROM password_reset WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Store token in database
    $stmt = $conn->prepare("INSERT INTO password_reset (user_id, token, expiry) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $token, $token_expiry);
    
    if ($stmt->execute()) {
        return [
            'success' => true, 
            'message' => 'Password reset link sent to your email',
            'token' => $token // In a production environment, you'd never return this
        ];
    } else {
        return ['success' => false, 'message' => 'Password reset request failed: ' . $conn->error];
    }
}

// Reset password
function reset_password($conn, $token, $password) {
    // Check if token exists and is valid
    $stmt = $conn->prepare("SELECT user_id FROM password_reset WHERE token = ? AND expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        return ['success' => false, 'message' => 'Invalid or expired reset token'];
    }
    
    $user_id = $result->fetch_assoc()['user_id'];
    
    // Hash new password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    if ($stmt->execute()) {
        // Delete reset token
        $stmt = $conn->prepare("DELETE FROM password_reset WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        
        return ['success' => true, 'message' => 'Password reset successful'];
    } else {
        return ['success' => false, 'message' => 'Password reset failed: ' . $conn->error];
    }
}
?>
