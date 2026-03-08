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

// Fetch Latest Complaint Status
$latestTitle = "No Complaints";
$latestStatus = "N/A";
$sql = "SELECT c.title, s.status_label 
        FROM complaints c 
        LEFT JOIN complaint_statuses s ON c.status_id = s.status_id 
        WHERE c.user_id = ? 
        ORDER BY c.created_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $latestTitle = $row['title'];
        $latestStatus = $row['status_label'];
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
    <link rel="stylesheet" href="../Assets/css/user-dashboard.css">

</head>
<body>
    <?php include '../Includes/header.php'; ?>
    <?php include '../Includes/sidebar.php'; ?>

    <div class="main-container">

        <main class="main-content">
            <h1 class="welcome-title">Welcome to SCRMS!!</h1>

            <div class="welcome-message">
                Hello <?php echo $fullName; ?>, this dashboard allows you to submit new complaints, track the progress of existing ones, and receive updates from the administration. The system is designed to ensure that your concerns are properly recorded and addressed in a timely manner, keeping you informed at every step and making the process transparent and easy to follow."
            </div>

            <div class="cards-container">
                <div class="info-card blue">
                    <svg class="card-icon" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <div class="card-number"><?php echo $total_complaints; ?></div>
                    <div class="card-label">Total Complaints</div>
                    <button class="more-info-btn" onclick="window.location.href='user-complaints.php'">
                        More info
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>

                <div class="info-card green">
                    <div class="card-label" style="font-size: 28px; font-weight: bold; margin-bottom: 15px;"><?php echo htmlspecialchars($latestTitle); ?></div>
                    <div class="card-label" style="font-size: 18px; margin-bottom: 25px;"><?php echo htmlspecialchars($latestStatus); ?></div>
                    <button class="more-info-btn" onclick="window.location.href='user-complaints.php'">
                        More info
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>

            <button class="submit-complaint-btn" id="submitComplaintBtn" onclick="window.location.href='complaints-submission.php'">
                Submit Complaint
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </button>
        </main>
    </div>

    <script src="../Assets/js/user-dashboard.js"></script>
</body>
</html>
