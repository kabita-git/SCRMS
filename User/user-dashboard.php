<?php
session_start();
// Require logged-in user and appropriate role
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? $_SESSION['role'] ?? '') !== 'User') {
    header('Location: /index.php');
    exit;
}

require_once '../Database/db-config.php';

$userId = $_SESSION['user_id'];
$first = htmlspecialchars($_SESSION['first_name'] ?? '');
$middle = htmlspecialchars($_SESSION['middle_name'] ?? '');
$last = htmlspecialchars($_SESSION['last_name'] ?? '');
$fullName = trim("$first $middle $last");

// Fetch Total Complaints for this user
$total_complaints = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM complaints WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $total_complaints = $row['count'];
    }
    $stmt->close();
}

// Fetch Solved Complaints for this user
$solved_complaints = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM complaints c JOIN complaint_statuses s ON c.status_id = s.status_id WHERE c.user_id = ? AND s.status_label = 'Solved'");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $solved_complaints = $row['count'];
    }
    $stmt->close();
}
// Fetch Pending Complaints for this user
$pending_complaints = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM complaints c JOIN complaint_statuses s ON c.status_id = s.status_id WHERE c.user_id = ? AND s.status_label = 'Pending'");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $pending_complaints = $row['count'];
    }
    $stmt->close();
}

// Fetch Anonymous Complaints for this user
$anon_complaints = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM complaints WHERE user_id = ? AND is_anonymous = 1");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $anon_complaints = $row['count'];
    }
    $stmt->close();
}

// Fetch Non-Anonymous Complaints for this user
$named_complaints = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM complaints WHERE user_id = ? AND is_anonymous = 0");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $named_complaints = $row['count'];
    }
    $stmt->close();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Student Complaint System</title>
    <link rel="stylesheet" href="../Assets/Css/user-dashboard.css?v=<?php echo time(); ?>">

</head>
<body>
    <?php include '../Includes/header.php'; ?>
    <?php include '../Includes/sidebar.php'; ?>

    <div class="main-container">

        <main class="main-content">
            <h1 class="welcome-title">Welcome to SCRMS!!</h1>

            <div class="welcome-message">
                Hello <?php echo $fullName; ?>, this dashboard allows you to submit new complaints, track the progress of existing ones, and receive updates from the administration. The system is designed to ensure that your concerns are properly recorded and addressed in a timely manner, keeping you informed at every step and making the process transparent and easy to follow.
            </div>

            <div class="cards-container">
                <!-- Total Complaints Card -->
                <div class="stat-card stat-card-blue">
                    <div class="stat-icon">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $total_complaints; ?></h3>
                        <p class="stat-label">Total Complaints</p>
                    </div>
                    <button class="stat-more-btn" onclick="window.location.href='user-complaints.php'">
                        More info
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 16 16 12 12 8"></polyline>
                        </svg>
                    </button>
                </div>

                <!-- Solved Complaints Card -->
                <div class="stat-card stat-card-orange">
                    <div class="stat-icon">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $solved_complaints; ?></h3>
                        <p class="stat-label">Total Complaint Solve</p>
                    </div>
                    <button class="stat-more-btn" onclick="window.location.href='user-complaints.php'">
                        More info
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 16 16 12 12 8"></polyline>
                        </svg>
                    </button>
                </div>

                <!-- Pending Complaints Card -->
                <div class="stat-card stat-card-green">
                    <div class="stat-icon">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $pending_complaints; ?></h3>
                        <p class="stat-label">Total Pending</p>
                    </div>
                    <button class="stat-more-btn" onclick="window.location.href='user-complaints.php'">
                        More info
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 16 16 12 12 8"></polyline>
                        </svg>
                    </button>
                </div>

                <!-- Anonymous Posts Card -->
                <div class="stat-card stat-card-purple">
                    <div class="stat-icon">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $anon_complaints; ?></h3>
                        <p class="stat-label">Anonymous Posts</p>
                    </div>
                    <button class="stat-more-btn" onclick="window.location.href='user-complaints.php'">
                        More info
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 16 16 12 12 8"></polyline>
                        </svg>
                    </button>
                </div>

                <!-- Non-Anonymous (Named) Card -->
                <div class="stat-card stat-card-indigo">
                    <div class="stat-icon">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <polyline points="17 11 19 13 23 9"></polyline>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $named_complaints; ?></h3>
                        <p class="stat-label">Named Posts</p>
                    </div>
                    <button class="stat-more-btn" onclick="window.location.href='user-complaints.php'">
                        More info
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 16 16 12 12 8"></polyline>
                        </svg>
                    </button>
                </div>
            </div>


        </main>
    </div>

    <script src="../Assets/js/user-dashboard.js"></script>
</body>
</html>
