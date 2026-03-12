<?php
session_start();
// Require admin-like roles
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? $_SESSION['role'] ?? '', ['Admin', 'DeptAdmin', 'UpperBody', 'Coordinator', 'HOD', 'Dean'])) {
    header('Location: /index.php');
    exit;
}

include_once '../Database/db-config.php';
include_once '../Includes/AutoEscalator.php';
AutoEscalator::runEscalation($conn);

// Fetch current user info for Dept-specific filtering
$assigned_category = null;
$role_in_session = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';
$departmental_roles = ['DeptAdmin', 'Coordinator', 'HOD', 'Dean'];
if (in_array($role_in_session, $departmental_roles)) {
    $stmt = $conn->prepare("SELECT assigned_category FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($assigned_category);
    $stmt->fetch();
    $stmt->close();
}

// Fetch Total Users
$total_users = 0;
$res_users = $conn->query("SELECT COUNT(*) as count FROM users");
if ($res_users && $row = $res_users->fetch_assoc()) {
    $total_users = $row['count'];
}

// Build where clauses for filtering based on role
$role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';
$comp_where = "";
$assigned_category_sql = $assigned_category !== null ? intval($assigned_category) : -1;

if (in_array($role, $departmental_roles)) {
    $comp_where = " WHERE assigned_role = '$role'";
    if ($assigned_category !== null) {
        $comp_where .= " AND category_id = $assigned_category_sql";
    }
}

// Fetch Total Complaints
$total_complaints = 0;
$res_comp = $conn->query("SELECT COUNT(*) as count FROM complaints" . $comp_where);
if ($res_comp && $row = $res_comp->fetch_assoc()) {
    $total_complaints = $row['count'];
}

// Fetch Solved Complaints
$solved_complaints = 0;
// Note: using complaint_statuses as specified in the schema
$solved_where = " WHERE s.status_label = 'Solved'";
if (in_array($role, $departmental_roles)) {
    $solved_where .= " AND c.assigned_role = '$role'";
    if ($assigned_category !== null) {
        $solved_where .= " AND c.category_id = $assigned_category_sql";
    }
}
$res_solved = $conn->query("SELECT COUNT(*) as count FROM complaints c JOIN complaint_statuses s ON c.status_id = s.status_id" . $solved_where);
if ($res_solved && $row = $res_solved->fetch_assoc()) {
    $solved_complaints = $row['count'];
}

// Fetch Pending Complaints
$pending_complaints = 0;
$pending_where = " WHERE s.status_label = 'Pending'";
if (in_array($role, $departmental_roles)) {
    $pending_where .= " AND c.assigned_role = '$role'";
    if ($assigned_category !== null) {
        $pending_where .= " AND c.category_id = $assigned_category_sql";
    }
}
$res_pending = $conn->query("SELECT COUNT(*) as count FROM complaints c JOIN complaint_statuses s ON c.status_id = s.status_id" . $pending_where);
if ($res_pending && $row = $res_pending->fetch_assoc()) {
    $pending_complaints = $row['count'];
}

// Fetch Complaints by Category for Chart
$category_stats = [];
$cat_sql = "SELECT cc.category_name, COUNT(c.complaint_id) as count 
            FROM complaint_categories cc 
            LEFT JOIN complaints c ON cc.category_id = c.category_id 
            GROUP BY cc.category_id 
            ORDER BY count DESC";
$res_cat_stats = $conn->query($cat_sql);
if ($res_cat_stats) {
    while ($row = $res_cat_stats->fetch_assoc()) {
        $category_stats[] = $row;
    }
}

$first = htmlspecialchars($_SESSION['first_name'] ?? '');
$middle = htmlspecialchars($_SESSION['middle_name'] ?? '');
$last = htmlspecialchars($_SESSION['last_name'] ?? '');

// Fetch Status Breakdown for DeptAdmin graph
$status_breakdown = [];
$assigned_category_name = '';
if (in_array($role, $departmental_roles) && $assigned_category !== null) {
    // Get category name
    $cat_stmt = $conn->prepare("SELECT category_name FROM complaint_categories WHERE category_id = ?");
    if ($cat_stmt) {
        $cat_stmt->bind_param("i", $assigned_category);
        $cat_stmt->execute();
        $cat_stmt->bind_result($assigned_category_name);
        $cat_stmt->fetch();
        $cat_stmt->close();
    }

    // Get status breakdown for all complaints in this category
    $sb_sql = "SELECT s.status_label, COUNT(c.complaint_id) as count 
               FROM complaint_statuses s 
               LEFT JOIN complaints c ON s.status_id = c.status_id 
                   AND c.category_id = $assigned_category_sql
               GROUP BY s.status_id 
               ORDER BY s.status_id ASC";
    $res_sb = $conn->query($sb_sql);
    if ($res_sb) {
        while ($row = $res_sb->fetch_assoc()) {
            $status_breakdown[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Complaint System</title>
    <link rel="stylesheet" href="../Assets/Css/admin-dashboard.css?v=1.1">
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
                <!-- Total Complaints Card (Always Shown) -->
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
                    <button class="stat-more-btn" onclick="window.location.href='complaint-management.php'">
                        More info
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 16 16 12 12 8"></polyline>
                        </svg>
                    </button>
                </div>

                <?php if (in_array($role, $departmental_roles)): ?>
                    <!-- Solved Complaints Card for Dept Roles -->
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
                        <button class="stat-more-btn" onclick="window.location.href='reports.php'">
                            More info
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 16 16 12 12 8"></polyline>
                            </svg>
                        </button>
                    </div>

                    <!-- Pending Complaints Card for Dept Roles -->
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
                        <button class="stat-more-btn" onclick="window.location.href='complaint-management.php'">
                            More info
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 16 16 12 12 8"></polyline>
                            </svg>
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Total Users Card for Admin/UpperBody -->
                    <div class="stat-card stat-card-green">
                        <div class="stat-icon">
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?php echo $total_users; ?></h3>
                            <p class="stat-label">Total Users</p>
                        </div>
                        <button class="stat-more-btn" onclick="window.location.href='user-management.php'">
                            More info
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 16 16 12 12 8"></polyline>
                            </svg>
                        </button>
                    </div>

                    <!-- Solved Complaints Card for Admin/UpperBody -->
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
                        <button class="stat-more-btn" onclick="window.location.href='reports.php'">
                            More info
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 16 16 12 12 8"></polyline>
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!in_array($role, $departmental_roles)): ?>
            <!-- Category Stats Chart (Admin only) -->
            <div class="chart-section" style="margin-top: 50px; background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <div>
                        <h3 style="font-size: 24px; color: #2D1B69; font-weight: 700; margin-bottom: 5px;">Complaints by Category</h3>
                        <p style="color: #666; font-size: 14px;">Complaint requiring review</p>
                    </div>
                </div>

                <div class="category-bars" style="display: flex; flex-direction: column; gap: 20px;">
                    <?php 
                    $max_count = 1;
                    foreach ($category_stats as $stat) {
                        if ($stat['count'] > $max_count) $max_count = $stat['count'];
                    }
                    
                    foreach ($category_stats as $stat): 
                        $percentage = ($stat['count'] / $max_count) * 100;
                    ?>
                        <div class="category-item">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span style="font-weight: 600; color: #444; font-size: 15px;"><?php echo htmlspecialchars($stat['category_name']); ?></span>
                                <span style="font-weight: 700; color: #2D1B69;"><?php echo $stat['count']; ?></span>
                            </div>
                            <div class="progress-bg" style="height: 12px; background: #eee; border-radius: 10px; overflow: hidden; position: relative;">
                                <div class="progress-fill" 
                                     style="width: 0; height: 100%; border-radius: 10px; transition: width 1.5s cubic-bezier(0.1, 0.42, 0.41, 1);
                                            background: linear-gradient(90deg, #4A3F7A, #2D1B69);"
                                     data-width="<?php echo $percentage; ?>%">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="../Assets/JS/admin-dashboard.js"></script>
</body>
</html>