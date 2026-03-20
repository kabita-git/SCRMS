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
            $_SESSION['role'] = $user['role'];
            $_SESSION['contact'] = $user['contact'];    
            
            // Determine redirect based on role (PHP dashboard placeholders)
            $redirect = in_array($user['role'], ['Admin', 'DeptAdmin', 'UpperBody', 'Coordinator', 'HOD', 'Dean']) ? 'Admin/admin-dashboard.php' : 'User/user-dashboard.php';
            
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
    $redirect = in_array($_SESSION['user_role'], ['Admin', 'DeptAdmin', 'UpperBody', 'Coordinator', 'HOD', 'Dean']) ? 'Admin/admin-dashboard.php' : 'User/user-dashboard.php';
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
    <div class="split-container">
        <!-- Left Panel: Brand & Visuals -->
        <div class="left-panel">
            <div class="overlay"></div>
            <div class="left-content">
                <div class="logo-area">
                    <img src="Assets/images/logo-KU.png" alt="KU Logo" class="brand-logo">
                    <div class="brand-text">
                        <h2>Kathmandu University</h2>
                        <h3>School of Education</h3>
                        <p>SCRMS System</p>
                    </div>
                </div>
                <div class="left-footer"></div>
            </div>
        </div>

        <!-- Right Panel: Login Form -->
        <div class="right-panel">
            <div class="form-container">
                <h1 class="welcome-title">Welcome to SCRMS</h1>
                <p class="welcome-subtitle">Student Complaint Registration and Management System</p>

                <div class="google-login-section" style="width: 100%; max-width: 440px;">
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
                        data-text="signin_with"
                        data-size="large"
                        data-logo_alignment="left"
                        data-width="440">
                    </div>
                </div>

                <div class="divider">
                    <span>OR</span>
                </div>

                <form id="loginForm" class="modern-form">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </span>
                            <input type="email" id="email" name="email" placeholder="name@example.com" required>
                        </div>
                        <span class="error-text" id="emailError"></span>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" placeholder="••••••••" required>
                        </div>
                        <span class="error-text" id="passwordError"></span>
                    </div>

                    <div class="form-options"></div>

                    <button type="submit" class="submit-btn primary-btn">Sign In</button>
                    
                    <div class="form-footer">
                        <p>Don't have an account? <a href="registration.php" class="auth-link">Sign Up Here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="Assets/js/login.js"></script>
</body>
</html>
