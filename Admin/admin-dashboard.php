<?php
session_start();
// Require admin role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? $_SESSION['role'] ?? '', ['Admin', 'DeptAdmin', 'UpperBody'])) {
    header('Location: /index.php');
    exit;
}

include_once '../Database/db-config.php';

// Fetch Total Users
$total_users = 0;
$res_users = $conn->query("SELECT COUNT(*) as count FROM users");
if ($res_users && $row = $res_users->fetch_assoc()) {
    $total_users = $row['count'];
}

// Fetch Total Complaints
$total_complaints = 0;
$res_comp = $conn->query("SELECT COUNT(*) as count FROM complaints");
if ($res_comp && $row = $res_comp->fetch_assoc()) {
    $total_complaints = $row['count'];
}

// Fetch Solved Complaints
$solved_complaints = 0;
// Note: using complaint_statuses as specified in the schema
$res_solved = $conn->query("SELECT COUNT(*) as count FROM complaints c JOIN complaint_statuses s ON c.status_id = s.status_id WHERE s.status_label = 'Solved'");
if ($res_solved && $row = $res_solved->fetch_assoc()) {
    $solved_complaints = $row['count'];
}

$first = htmlspecialchars($_SESSION['first_name'] ?? '');
$middle = htmlspecialchars($_SESSION['middle_name'] ?? '');
$last = htmlspecialchars($_SESSION['last_name'] ?? '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Complaint System</title>
    <link rel="stylesheet" href="../Assets/Css/admin-dashboard.css">
</head>
<body>
    <?php include '../Includes/header.php'; ?>

    <div class="main-container">
        <?php include '../Includes/sidebar.php'; ?>
        <!-- Main Content Area -->
        <main class="main-content">
            <h2 class="page-title">Admin Dashboard</h2>

            <!-- Stats Cards -->
            <div class="stats-container">
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
                    <button class="stat-more-btn">
                        More info
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 16 16 12 12 8"></polyline>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg>
                    </button>
                </div>

                <!-- Total Users Card -->
                <div class="stat-card stat-card-green">
                    <div class="stat-icon">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $total_users; ?></h3>
                        <p class="stat-label">Total User</p>
                    </div>
                    <button class="stat-more-btn">
                        More info
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 16 16 12 12 8"></polyline>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg>
                    </button>
                </div>

                <!-- Complaints Solved Card -->
                <div class="stat-card stat-card-orange">
                    <div class="stat-icon">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $solved_complaints; ?></h3>
                        <p class="stat-label">Complaints Solve</p>
                    </div>
                    <button class="stat-more-btn">
                        More info
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 16 16 12 12 8"></polyline>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg>
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script src="../Assets/JS/admin-dashboard.js"></script>
</body>
</html>