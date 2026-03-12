<?php
// Determine current page for highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Ensure we have a session and database connection to lookup user info
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../Database/db-config.php';

$user_id   = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? null;
$first     = htmlspecialchars($_SESSION['first_name'] ?? '');
$middle    = htmlspecialchars($_SESSION['middle_name'] ?? '');
$last      = htmlspecialchars($_SESSION['last_name'] ?? '');

// If the session doesn't yet have a role but we have an ID, fetch from DB
if (!$role && $user_id) {
    $stmt = $conn->prepare('SELECT role, first_name, middle_name, last_name FROM users WHERE user_id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($db_role, $db_first, $db_middle, $db_last);
        if ($stmt->fetch()) {
            $role = $_SESSION['role'] = $db_role;
            $first     = $_SESSION['first_name'] = $db_first;
            $middle    = $_SESSION['middle_name'] = $db_middle;
            $last      = $_SESSION['last_name'] = $db_last;
        }
        $stmt->close();
    }
}

// Build a display name and escaping again just in case
$full_name = trim("$first $middle $last");
$full_name = htmlspecialchars($full_name);
$display_role = htmlspecialchars(ucfirst($role ?? '')); ?>
<link rel="stylesheet" href="../Assets/Css/sidebar.css">
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($full_name ?: 'User'); ?>&background=4a3f7a&color=fff&size=60" alt="User Avatar" class="user-avatar">
            <div class="user-info">
                <h3 class="user-name"><?php echo $full_name ?: 'Unknown'; ?></h3>
                <p class="role"><?php echo $display_role; ?></p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <?php if (in_array($role, ['Admin', 'DeptAdmin', 'UpperBody', 'Coordinator', 'HOD', 'Dean'])): ?>
                <a href="../Admin/admin-dashboard.php" class="nav-item <?php echo ($current_page == 'admin-dashboard.php') ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                        <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                        <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                        <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                    </svg>
                    Dashboard
                </a>

                <?php if (!in_array($role, ['DeptAdmin', 'Coordinator', 'HOD', 'Dean'])): ?>
                    <a href="../Admin/complaint-category.php" class="nav-item <?php echo ($current_page == 'complaint-category.php') ? 'active' : ''; ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                        </svg>
                        Complaint Category
                    </a>
                <?php endif; ?>

                <a href="../Admin/complaint-management.php" class="nav-item <?php echo ($current_page == 'complaint-management.php') ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Complaint Management
                </a>

                <?php if (!in_array($role, ['DeptAdmin', 'Coordinator', 'HOD', 'Dean'])): ?>
                <a href="../Admin/user-management.php" class="nav-item <?php echo ($current_page == 'user-management.php') ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    User Management
                </a>

                <a href="../Admin/reports.php" class="nav-item <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    Reports
                </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="../User/user-dashboard.php" class="nav-item <?php echo ($current_page == 'user-dashboard.php') ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                        <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                        <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                        <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                    </svg>
                    Dashboard
                </a>

                <a href="../User/user-complaints.php" class="nav-item <?php echo ($current_page == 'user-complaints.php') ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Complaints
                </a>

                <a href="../User/complaints-submission.php" class="nav-item <?php echo ($current_page == 'complaints-submission.php') ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    Submit Complaints
                </a>
            <?php endif; ?>
            <!-- Profile link works from any subdirectory -->
            <?php
            // Resolve correct path to Includes/profile.php from any depth
            $dir = strtolower(basename(dirname($_SERVER['PHP_SELF'])));
            if ($dir === 'includes') {
                $profilePath = 'profile.php';
            } elseif (in_array($dir, ['admin', 'user'])) {
                $profilePath = '../Includes/profile.php';
            } else {
                $profilePath = 'Includes/profile.php';
            }
            ?>
            <a href="<?php echo $profilePath; ?>" class="nav-item <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Profile
            </a>
        </nav>
    </div>
</aside>