<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? $_SESSION['role'] ?? '', ['Admin', 'UpperBody'])) {
    header('Location: /index.php');
    exit;
}

require_once '../Database/db-config.php';

$message = "";
$messageType = "";

// Handle status updates, edits and deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
    if ($action === 'update_user') {
        $role = isset($_POST['role']) ? trim($_POST['role']) : '';
        $escRoles = ['DeptAdmin', 'Coordinator', 'HOD', 'Dean'];
        $assignedCategory = isset($_POST['assignedCategory']) && in_array($role, $escRoles) ? intval($_POST['assignedCategory']) : null;

        $stmt = $conn->prepare("UPDATE users SET role = ?, assigned_category = ? WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("sii", $role, $assignedCategory, $userId);
            if ($stmt->execute()) {
                // If the user's role is changed to something other than 'User', clear batch & program
                if ($role !== 'User') {
                    $clearStmt = $conn->prepare("UPDATE users SET batch = NULL, program = NULL WHERE user_id = ?");
                    if ($clearStmt) {
                        $clearStmt->bind_param("i", $userId);
                        $clearStmt->execute();
                        $clearStmt->close();
                    }
                }
                $_SESSION['message'] = "User updated successfully!";
                $_SESSION['messageType'] = "success";
            } else {
                $_SESSION['message'] = "Error updating user: " . $conn->error;
                $_SESSION['messageType'] = "error";
            }
            $stmt->close();
        }
    } elseif ($action === 'delete_user') {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            if ($stmt->execute()) {
                $_SESSION['message'] = "User deleted successfully!";
                $_SESSION['messageType'] = "success";
            } else {
                $_SESSION['message'] = "Error deleting user: " . $conn->error;
                $_SESSION['messageType'] = "error";
            }
            $stmt->close();
        }
    }

    header("Location: user-management.php");
    exit;
}

// Fetch session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
}

// Fetch categories for DeptAdmin assignment
$categories = [];
$res_cats = $conn->query("SELECT category_id, category_name FROM complaint_categories ORDER BY category_name ASC");
if ($res_cats) {
    while ($row = $res_cats->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch users
$users = [];
$sql = "SELECT u.*, c.category_name 
        FROM users u 
        LEFT JOIN complaint_categories c ON u.assigned_category = c.category_id 
        WHERE u.role != 'Admin' 
        ORDER BY u.created_at DESC";
$res_users = $conn->query($sql);
if ($res_users) {
    while ($row = $res_users->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Complaint System</title>
    <link rel="stylesheet" href="../Assets/Css/admin-dashboard.css">
    <link rel="stylesheet" href="../Assets/Css/complaint-category.css">
    <link rel="stylesheet" href="../Assets/Css/user-management.css">
</head>
<body>
    <?php include '../Includes/header.php'; ?>
    <div class="main-container">
        <?php include '../Includes/sidebar.php'; ?>
        <!-- Main Content Area -->
        <main class="main-content">
            <h2 class="page-title">User Management</h2>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; color: white; background-color: <?php echo $messageType === 'success' ? '#2ecc71' : '#e74c3c'; ?>;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Data Table Card -->
            <div class="table-card">
                <div class="table-header">
                    <h3 class="table-title">User Management Table</h3>
                </div>

                <!-- Table Controls -->
                <div class="table-controls">
                    <div class="entries-control">
                        <label>Show</label>
                        <select id="entriesSelect">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>Entries</span>
                    </div>

                    <div class="search-control">
                        <label>Search:</label>
                        <input type="text" id="searchInput" placeholder="Search...">
                    </div>
                </div>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="data-table" id="userTable">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No users found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $index => $u): ?>
                                    <?php 
                                        $fullName = trim($u['first_name'] . ' ' . $u['middle_name'] . ' ' . $u['last_name']);
                                        $statusClass = (isset($u['status']) && $u['status'] === 'Inactive') ? 'status-inactive' : 'status-active';
                                        $displayStatus = $u['status'] ?? 'Active';
                                    ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($fullName); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><?php echo htmlspecialchars($u['contact'] ?? ''); ?></td>
                                        <td>
                                            <?php 
                                                echo htmlspecialchars($u['role']); 
                                                $escRoles = ['DeptAdmin', 'Coordinator', 'HOD', 'Dean'];
                                                if (in_array($u['role'], $escRoles) && $u['category_name']) {
                                                    echo ' <small style="color:#666;">(' . htmlspecialchars($u['category_name']) . ')</small>';
                                                }
                                            ?>
                                        </td>
                                        <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($displayStatus); ?></span></td>
                                        <td>
                                            <div class="action-btns">
                                            <button class="edit-btn" title="Edit" 
                                                    data-id="<?php echo $u['user_id']; ?>"
                                                    data-first="<?php echo htmlspecialchars($u['first_name']); ?>"
                                                    data-middle="<?php echo htmlspecialchars($u['middle_name']); ?>"
                                                    data-last="<?php echo htmlspecialchars($u['last_name']); ?>"
                                                    data-email="<?php echo htmlspecialchars($u['email']); ?>"
                                                    data-contact="<?php echo htmlspecialchars($u['contact'] ?? ''); ?>"
                                                    data-role="<?php echo htmlspecialchars($u['role']); ?>"
                                                    data-category="<?php echo $u['assigned_category'] ?? ''; ?>"
                                                    data-status="<?php echo htmlspecialchars($displayStatus); ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>
                                            <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                                                <button class="delete-btn" title="Delete" data-id="<?php echo $u['user_id']; ?>">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer -->
                <div class="table-footer">
                    <div class="entries-info" id="entriesInfo">Showing <?php echo count($users); ?> entries</div>
                    <div class="pagination">
                        <button class="page-btn" id="prevBtn">Previous</button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn" id="nextBtn">Next</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit User Modal -->
    <div class="modal" id="editUserModal" style="z-index: 99999;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit User</h3>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            <form id="editUserForm" method="POST" action="">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="userId" id="editUserId">
                <div style="padding: 20px;">
                    <div class="form-row" style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>First Name</label>
                            <input type="text" id="firstName" readonly style="background-color: #f9fafb; cursor: not-allowed;">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Middle Name</label>
                            <input type="text" id="middleName" readonly style="background-color: #f9fafb; cursor: not-allowed;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" id="lastName" readonly style="background-color: #f9fafb; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="userEmail" readonly style="background-color: #f9fafb; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Contact</label>
                        <input type="text" id="userContact" readonly style="background-color: #f9fafb; cursor: not-allowed;">
                    </div>
                    <div class="form-row" style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Role</label>
                            <select name="role" id="userRole" required>
                                <option value="User">User</option>
                                <option value="Admin">Admin</option>
                                <option value="DeptAdmin">DeptAdmin</option>
                                <option value="Coordinator">Coordinator</option>
                                <option value="HOD">HOD</option>
                                <option value="Dean">Dean</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Status</label>
                            <input type="text" id="userStatus" readonly style="background-color: #f9fafb; cursor: not-allowed;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="cancelBtn">Close</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Custom Delete Modal Overlay -->
    <div id="deleteModal" class="custom-modal-overlay" style="z-index: 99999;">
        <div class="custom-modal-box">
            <h3>Are you sure you want to delete this user?</h3>
            <div class="custom-modal-actions">
                <button class="modal-btn-cancel" id="deleteCancel">Cancel</button>
                <button class="modal-btn-confirm" id="deleteConfirm">Yes, Delete</button>
            </div>
        </div>
    </div>

    <!-- Hidden form for deletion -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_user">
        <input type="hidden" name="userId" id="deleteUserId">
    </form>

    <script src="../Assets/JS/admin-dashboard.js"></script>
    <script src="../Assets/JS/table-pagination.js"></script>
    <script src="../Assets/JS/user-management.js"></script>
</body>
</html>