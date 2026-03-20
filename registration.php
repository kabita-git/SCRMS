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

        <!-- Right Panel: Registration Form -->
        <div class="right-panel">
            <div class="form-container registration-width">
                <h1 class="welcome-title">Welcome to SCRMS</h1>
                <p class="welcome-subtitle">Student Complaint Registration and Management System</p>

                <form id="registrationForm" class="modern-form">
                    <div class="form-group role-selector">
                        <label>Register As</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="userRole" value="Student" checked> 
                                <span class="radio-custom">Student</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="userRole" value="Faculty"> 
                                <span class="radio-custom">Faculty</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-row three-cols">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input type="text" id="firstName" name="firstName" placeholder="First" required>
                            <span class="error-text" id="firstNameError"></span>
                        </div>
                        <div class="form-group">
                            <label for="middleName">Middle Name</label>
                            <input type="text" id="middleName" name="middleName" placeholder="Middle">
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <input type="text" id="lastName" name="lastName" placeholder="Last" required>
                            <span class="error-text" id="lastNameError"></span>
                        </div>
                    </div>

                    <div class="form-row two-cols">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="name@example.com" required>
                            <span class="error-text" id="emailError"></span>
                        </div>
                        <div class="form-group">
                            <label for="contact">Contact Number</label>
                            <input type="text" id="contact" name="contact" placeholder="98XXXXXXXX" required>
                            <span class="error-text" id="contactError"></span>
                        </div>
                    </div>

                    <div id="studentFields" class="form-row two-cols">
                        <div class="form-group">
                            <label for="batch">Batch</label>
                            <select id="batch" name="batch">
                                <option value="">Select Batch</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                            </select>
                            <span class="error-text" id="batchError"></span>
                        </div>
                        <div class="form-group">
                            <label for="program">Program</label>
                            <select id="program" name="program">
                                <option value="">Select Program</option>
                                <option value="B.Tech.Ed.IT & BIT">B.Tech.Ed.IT & BIT</option>
                                <option value="B. Tech.Ed. in Civil">B. Tech.Ed. in Civil</option>
                                <option value="BA.BED.TESOL">BA.BED.TESOL</option>
                                <option value="BED in TCSOL">BED in TCSOL</option>
                                <option value="B. Mathmatics Education">B. Mathmatics Education</option>
                            </select>
                            <span class="error-text" id="programError"></span>
                        </div>
                    </div>

                    <div class="form-row two-cols">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="••••••••" required>
                            <span class="error-text" id="passwordError"></span>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Confirm Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="••••••••" required>
                            <span class="error-text" id="confirmPasswordError"></span>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn primary-btn">Create Account</button>
                    
                    <div class="form-footer">
                        <p>Already have an account? <a href="index.php" class="auth-link">Login Here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="Assets/JS/registration.js"></script>
</body>
</html>
