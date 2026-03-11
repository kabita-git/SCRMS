<?php
// Include database configuration
include 'Database/db-config.php';

// Handle AJAX login request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'validate') {
    header('Content-Type: application/json');
    
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit;
    }
    
    // Query database for user
    $stmt = $conn->prepare("SELECT user_id, first_name, middle_name, last_name, password, role, contact FROM users WHERE email = ?");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password (assumes password is hashed with password_hash)
        if (password_verify($password, $user['password'])) {
            // Set session or return success
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['middle_name'] = $user['middle_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['contact'] = $user['contact'];    
            
            // Determine redirect based on role (PHP dashboard placeholders)
            $redirect = in_array($user['role'], ['Admin', 'DeptAdmin', 'UpperBody']) ? 'Admin/admin-dashboard.php' : 'User/user-dashboard.php';
            
            echo json_encode([
                'success' => true, 
                'message' => 'Login successful',
                'redirect' => $redirect
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }
    
    $stmt->close();
    $conn->close();
    exit;
}

// Start session for login check
session_start();
if (isset($_SESSION['user_id'])) {
    // Redirect if already logged in to PHP dashboards
    $redirect = in_array($_SESSION['user_role'], ['Admin', 'DeptAdmin', 'UpperBody']) ? 'Admin/admin-dashboard.php' : 'User/user-dashboard.php';
    header("Location: " . $redirect);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Complaint System</title>
    <link rel="stylesheet" href="Assets/css/login.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="user-icon">
                    <svg width="60" height="60" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="40" cy="40" r="40" fill="#2D1B69"/>
                        <circle cx="40" cy="30" r="12" fill="white"/>
                        <path d="M20 60C20 52 28 46 40 46C52 46 60 52 60 60" stroke="white" stroke-width="4" stroke-linecap="round"/>
                        <circle cx="55" cy="55" r="10" fill="white"/>
                        <path d="M55 55L60 60" stroke="#2D1B69" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <h1 class="login-title">Log in to Complaint System</h1>
            </div>

            <!-- Login Form -->
            <form id="loginForm" class="login-form">
                <!-- Email Input -->
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email"
                        required
                    >
                    <span class="error-message" id="emailError"></span>
                </div>

                <!-- Password Input -->
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                    >
                    <span class="error-message" id="passwordError"></span>
                </div>

                <!-- Login Button -->
                <button type="submit" class="login-btn">Login</button>

                <!-- Forgot Password Link -->
                <a href="#" class="forgot-password">Forgot Password?</a>

                <div class="google-signin-container" style="margin: 10px 0; display: flex; justify-content: center;">
                    <div id="g_id_onload"
                        data-client_id="265525982720-19tf8tergo216hsf0pfikc7uvo9ii1ok.apps.googleusercontent.com"
                        data-context="signin"
                        data-ux_mode="popup"
                        data-callback="handleCredentialResponse"
                        data-auto_prompt="false">
                    </div>

                    <div class="g_id_signin"
                        data-type="standard"
                        data-shape="rectangular"
                        data-theme="outline"
                        data-text="continue_with"
                        data-size="large"
                        data-logo_alignment="left">
                    </div>
                </div>
                <!-- Register Link -->
                <p class="register-text">
                    Don't have an account? <a href="./registration.php" class="register-link">Register here.</a>
                </p>
            </form>
        </div>
    </div>

    <script src="Assets/js/login.js"></script>
</body>
</html>
