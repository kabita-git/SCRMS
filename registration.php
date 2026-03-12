<?php
// Include database configuration
include 'Database/db-config.php';

// Handle AJAX registration request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    header('Content-Type: application/json');
    
    $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
    $middleName = isset($_POST['middleName']) ? trim($_POST['middleName']) : '';
    $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';
    $userRoleChoice = isset($_POST['userRole']) ? trim($_POST['userRole']) : 'Student';
    $batch = isset($_POST['batch']) ? trim($_POST['batch']) : '';
    $program = isset($_POST['program']) ? trim($_POST['program']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? trim($_POST['confirmPassword']) : '';
    
    
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
    $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, email, contact, batch, program, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    // Set role based on choice
    $role = ($userRoleChoice === 'Faculty') ? 'DeptAdmin' : 'User';
    
    // Clear batch/program for faculty
    $batchVal = ($role === 'DeptAdmin') ? null : $batch;
    $programVal = ($role === 'DeptAdmin') ? null : $program;
    
    $stmt->bind_param("sssssssss", $firstName, $middleName, $lastName, $email, $contact, $batchVal, $programVal, $hashedPassword, $role);
    
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
    <title>Create Account - Student Complaint System</title>
    <link rel="stylesheet" href="Assets/css/registration.css">
</head>
<body>
    <div class="registration-container">
        <div class="registration-header">
            <div class="icon-container">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
            </div>
            <h1>Create Your Account</h1>
        </div>
        
        <form id="registrationForm">
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Register As <span class="required">*</span></label>
                <div style="display: flex; gap: 20px; margin-top: 5px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; border: none; padding: 0;">
                        <input type="radio" name="userRole" value="Student" checked style="width: auto;"> Student
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; border: none; padding: 0;">
                        <input type="radio" name="userRole" value="Faculty" style="width: auto;"> Faculty
                    </label>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                <div class="form-group">
                    <label for="firstName">First Name <span class="required">*</span></label>
                    <input type="text" id="firstName" name="firstName" required>
                    <span class="error-message" id="firstNameError"></span>
                </div>

                <div class="form-group">
                    <label for="middleName">Middle Name</label>
                    <input type="text" id="middleName" name="middleName">
                    <span class="error-message" id="middleNameError"></span>
                </div>

                <div class="form-group">
                    <label for="lastName">Last Name <span class="required">*</span></label>
                    <input type="text" id="lastName" name="lastName" required>
                    <span class="error-message" id="lastNameError"></span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required>
                    <span class="error-message" id="emailError"></span>
                </div>
                
                <div class="form-group">
                    <label for="contact">Contact Number <span class="required">*</span></label>
                    <input type="text" id="contact" name="contact" required>
                    <span class="error-message" id="contactError"></span>
                </div>
            </div>

            <div id="studentFields" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label for="batch">Batch <span class="required">*</span></label>
                    <select id="batch" name="batch">
                        <option value="">Select Batch</option>
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                        <option value="2027">2027</option>
                    </select>
                    <span class="error-message" id="batchError"></span>
                </div>

                <div class="form-group">
                    <label for="program">Program <span class="required">*</span></label>
                    <select id="program" name="program">
                        <option value="">Select Program</option>
                        <option value="B.Tech.Ed.IT & BIT">B.Tech.Ed.IT & BIT</option>
                        <option value="B. Tech.Ed. in Civil">B. Tech.Ed. in Civil</option>
                        <option value="BA.BED.TESOL">BA.BED.TESOL</option>
                        <option value="BED in TCSOL">BED in TCSOL</option>
                        <option value="B. Mathmatics Education">B. Mathmatics Education</option>
                    </select>
                    <span class="error-message" id="programError"></span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
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
