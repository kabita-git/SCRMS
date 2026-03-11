<?php
// Includes/google-login.php
include '../Database/db-config.php';
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_token'])) {
    $id_token = $_POST['id_token'];

    // Verify the ID token with Google's API
    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;
    $response = @file_get_contents($url);
    $payload = json_decode($response, true);

    if ($payload && isset($payload['email'])) {
        // Verify audience (client ID)
        if ($payload['aud'] !== GOOGLE_CLIENT_ID) {
            echo json_encode(['success' => false, 'message' => 'Invalid audience']);
            exit;
        }

        $email = $payload['email'];
        $firstName = $payload['given_name'] ?? '';
        $lastName = $payload['family_name'] ?? '';
        $profilePic = $payload['picture'] ?? '';

        // Check if user exists
        $stmt = $conn->prepare("SELECT user_id, first_name, middle_name, last_name, role, contact FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        } else {
            // User doesn't exist, create new account
            $role = 'User';
            $contact = ''; // No contact from Google
            $dummyPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
            
            $insertStmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, email, contact, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $middleName = '';
            $insertStmt->bind_param("sssssss", $firstName, $middleName, $lastName, $email, $contact, $dummyPassword, $role);
            
            if ($insertStmt->execute()) {
                $newUserId = $conn->insert_id;
                $user = [
                    'user_id' => $newUserId,
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'role' => $role,
                    'contact' => $contact
                ];
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create account: ' . $conn->error]);
                exit;
            }
            $insertStmt->close();
        }

        // Login the user (found or newly created)
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['middle_name'] = $user['middle_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['contact'] = $user['contact'];

        $redirect = in_array($user['role'], ['Admin', 'DeptAdmin', 'UpperBody']) ? 'Admin/admin-dashboard.php' : 'User/user-dashboard.php';

        echo json_encode(['success' => true, 'redirect' => $redirect]);
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid ID token']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing ID token']);
}

$conn->close();
?>
