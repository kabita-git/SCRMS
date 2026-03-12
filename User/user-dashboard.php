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

// Fetch Detailed Complaint Tracking for this user
$complaint_tracking = [];
$stmt = $conn->prepare("
    SELECT c.complaint_id, c.title, c.assigned_role, c.updated_at, c.created_at,
           cat.category_name, s.status_label
    FROM complaints c
    LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
    LEFT JOIN complaint_statuses s ON c.status_id = s.status_id
    WHERE c.user_id = ?
    ORDER BY c.updated_at DESC, c.created_at DESC
    LIMIT 1
");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $complaint_tracking[] = $row;
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
                Hello <?php echo $fullName; ?>, this dashboard allows you to submit new complaints, track the progress of existing ones, and receive updates from the administration. The system is designed to ensure that your concerns are properly recorded and addressed in a timely manner, keeping you informed at every step and making the process transparent and easy to follow."
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
            </div>

            <!-- Complaint Tracking Section -->
            <div style="margin-top: 30px; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); overflow: hidden; max-width: 1200px; margin-left: auto; margin-right: auto;">
                <div style="background: linear-gradient(135deg, #2D1B69, #4a3f7a); padding: 14px 20px;">
                    <h3 style="font-size: 16px; color: #fff; font-weight: 700; margin: 0;">Track Your Complaints</h3>
                </div>

                <div style="padding: 18px 20px;">
                <?php if (empty($complaint_tracking)): ?>
                    <div style="text-align: center; padding: 25px; color: #999; font-size: 14px;">
                        <p>No complaints yet. <a href="complaints-submission.php" style="color: #2D1B69; font-weight: 600; text-decoration: none;">Submit one &rarr;</a></p>
                    </div>
                <?php else: ?>
                    <?php 
                    $levels = ['DeptAdmin', 'Coordinator', 'HOD', 'Dean'];
                    $statuses_x = ['Pending', 'In Progress', 'Solved'];
                    foreach ($complaint_tracking as $comp): 
                        $current_level = $comp['assigned_role'] ?: 'DeptAdmin';
                        $current_status = $comp['status_label'];
                    ?>
                        <div style="margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #f0f1f4;">
                            <span style="font-size: 11px; color: #a0aec0; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Your Latest Complaint</span>
                            <h4 style="font-size: 15px; color: #1a202c; font-weight: 700; margin: 4px 0 3px 0;"><?php echo htmlspecialchars($comp['title']); ?></h4>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 12px; color: #718096;"><?php echo htmlspecialchars($comp['category_name']); ?></span>
                                <span style="font-size: 11px; color: #a0aec0;">Updated <?php echo date('M d, Y', strtotime($comp['updated_at'] ?: $comp['created_at'])); ?></span>
                            </div>
                        </div>

                        <!-- Compact Graph -->
                        <div style="position: relative; height: 160px; margin-left: 80px; margin-right: 10px;">
                            <!-- Y-Axis Labels (absolute, matching grid positions) -->
                            <?php foreach ($levels as $idx => $l): 
                                $yPos = ($idx / (count($levels) - 1)) * 100;
                            ?>
                                <span style="position: absolute; left: -80px; bottom: <?php echo $yPos; ?>%; transform: translateY(50%); font-size: 11px; color: #4a5568; font-weight: 600; width: 70px; text-align: right;"><?php echo $l; ?></span>
                            <?php endforeach; ?>

                            <!-- Grid Box -->
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; border-left: 2px solid #e2e8f0; border-bottom: 2px solid #e2e8f0;">
                                <!-- Horizontal grid lines -->
                                <?php for ($i = 1; $i < count($levels); $i++): ?>
                                    <div style="position: absolute; bottom: <?php echo ($i / (count($levels) - 1)) * 100; ?>%; left: 0; right: 0; height: 1px; background: #edf2f7;"></div>
                                <?php endfor; ?>
                                <!-- Vertical grid lines -->
                                <?php for ($i = 1; $i < count($statuses_x); $i++): ?>
                                    <div style="position: absolute; left: <?php echo ($i / (count($statuses_x) - 1)) * 100; ?>%; top: 0; bottom: 0; width: 1px; background: #edf2f7;"></div>
                                <?php endfor; ?>

                                <?php 
                                $level_idx = array_search($current_level, $levels);
                                $status_idx = array_search($current_status, $statuses_x);
                                if ($level_idx === false) $level_idx = 0;
                                if ($status_idx === false) $status_idx = 0;
                                $bottom_pct = ($level_idx / (count($levels) - 1)) * 100;
                                $left_pct = ($status_idx / (count($statuses_x) - 1)) * 100;
                                ?>
                                <!-- Point -->
                                <div style="position: absolute; bottom: <?php echo $bottom_pct; ?>%; left: <?php echo $left_pct; ?>%; transform: translate(-50%, 50%); z-index: 10;">
                                    <div style="position: absolute; width: 30px; height: 30px; background: rgba(45,27,105,0.15); border-radius: 50%; animation: mp 2s infinite; transform: translate(-50%,-50%); left: 50%; top: 50%;"></div>
                                    <div style="width: 14px; height: 14px; background: linear-gradient(135deg,#2D1B69,#5B4FA0); border: 3px solid white; border-radius: 50%; box-shadow: 0 3px 10px rgba(45,27,105,0.4);"></div>
                                    <div style="position: absolute; top: -30px; left: 50%; transform: translateX(-50%); background: #2D1B69; color: white; padding: 3px 8px; border-radius: 5px; font-size: 10px; font-weight: 700; white-space: nowrap;">
                                        Currently Here
                                        <div style="position: absolute; bottom: -4px; left: 50%; transform: translateX(-50%); width: 0; height: 0; border-left: 4px solid transparent; border-right: 4px solid transparent; border-top: 4px solid #2D1B69;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- X-Axis Labels (absolute aligned) -->
                        <div style="position: relative; height: 20px; margin-left: 80px; margin-right: 10px; margin-top: 8px;">
                            <?php foreach ($statuses_x as $idx => $s): 
                                $xPos = ($idx / (count($statuses_x) - 1)) * 100;
                            ?>
                                <span style="position: absolute; left: <?php echo $xPos; ?>%; transform: translateX(-50%); font-size: 11px; color: #4a5568; font-weight: 600;"><?php echo $s; ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>

                    <style>
                        @keyframes mp {
                            0% { transform: translate(-50%,-50%) scale(0.5); opacity: 1; }
                            100% { transform: translate(-50%,-50%) scale(2.5); opacity: 0; }
                        }
                    </style>
                <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../Assets/js/user-dashboard.js"></script>
</body>
</html>
