<?php
// Include database configuration
include 'Database/db-config.php';

// Handle AJAX registration request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    header('Content-Type: application/json');
    
    $fullName = isset($_POST['fullName']) ? trim($_POST['fullName']) : '';
    $studentId = isset($_POST['studentId']) ? trim($_POST['studentId']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? trim($_POST['confirmPassword']) : '';
    
    // Validate inputs
    if (empty($fullName) || empty($studentId) || empty($email) || empty($password) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Check password match
    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit;
    }
    
    // Check password length
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        exit;
    }
    
    // Parse full name into first, middle, last names
    $nameParts = explode(' ', $fullName);
    $firstName = $nameParts[0];
    $lastName = isset($nameParts[2]) ? $nameParts[2] : (isset($nameParts[1]) ? $nameParts[1] : '');
    $middleName = isset($nameParts[2]) ? $nameParts[1] : '';
    
    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    if (!$checkStmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }
    $checkStmt->close();
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $role = 'user'; // Default role for new registrations
    $stmt->bind_param("ssssss", $firstName, $middleName, $lastName, $email, $hashedPassword, $role);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Please login with your credentials.',
            'redirect' => 'index.php'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
    exit;
}

// Start session for redirect if already logged in
session_start();
if (isset($_SESSION['user_id'])) {
    $redirect = ($_SESSION['user_role'] === 'admin') ? 'Admin/dashboard.html' : 'User/user-dashboard.html';
    header("Location: " . $redirect);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Student Complaint System</title>
    <link rel="stylesheet" href="Assets/Css/registration.css">
</head>
<body>
    <div class="registration-container">
        <div class="icon-container">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
        </div>
        
        <h1>Create Your Account</h1>
        
        <form id="registrationForm">
            <div class="form-group">
                <label for="fullName">Full Name <span class="required">*</span></label>
                <input type="text" id="fullName" name="fullName" required>
                <span class="error-message" id="fullNameError"></span>
            </div>

            <div class="form-group">
                <label for="studentId">Student ID / Roll Number <span class="required">*</span></label>
                <input type="text" id="studentId" name="studentId" required>
                <span class="error-message" id="studentIdError"></span>
            </div>

            <div class="form-group">
                <label for="email">Email Address <span class="required">*</span></label>
                <input type="email" id="email" name="email" required>
                <span class="error-message" id="emailError"></span>
            </div>

            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" required>
                <span class="error-message" id="passwordError"></span>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password <span class="required">*</span></label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
                <span class="error-message" id="confirmPasswordError"></span>
            </div>

            <button type="submit" class="register-btn">Register</button>

            <div class="login-link">
                Already have an account? <a href="index.php">Login here.</a>
            </div>
        </form>
    </div>

    <script src="Assets/JS/registration.js"></script>
</body>
</html>
