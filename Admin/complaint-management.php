<?php
session_start();
$role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';
if (!isset($_SESSION['user_id']) || !in_array($role, ['Admin', 'DeptAdmin', 'UpperBody', 'Coordinator', 'HOD', 'Dean'])) {
    header('Location: /index.php');
    exit;
}

require_once '../Database/db-config.php';
require_once '../Includes/AutoEscalator.php';
AutoEscalator::runEscalation($conn);

$message = "";
$messageType = "";

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $complaintId = isset($_POST['complaintId']) ? intval($_POST['complaintId']) : 0;

    if ($action === 'update_status') {
        $statusId = isset($_POST['statusId']) ? intval($_POST['statusId']) : 0;
        $statusMessage = isset($_POST['statusMessage']) ? trim($_POST['statusMessage']) : '';

        // We no longer manually update assigned_to here as it's automatic based on category
        $stmt = $conn->prepare("UPDATE complaints SET status_id = ?, final_status_message = ?, updated_at = NOW() WHERE complaint_id = ?");
        if ($stmt) {
            $stmt->bind_param("isi", $statusId, $statusMessage, $complaintId);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Complaint updated successfully!";
                $_SESSION['messageType'] = "success";
            } else {
                $_SESSION['message'] = "Error updating complaint: " . $conn->error;
                $_SESSION['messageType'] = "error";
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM complaints WHERE complaint_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $complaintId);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Complaint deleted successfully!";
                $_SESSION['messageType'] = "success";
            } else {
                $_SESSION['message'] = "Error deleting complaint: " . $conn->error;
                $_SESSION['messageType'] = "error";
            }
            $stmt->close();
        }
    }

    header("Location: complaint-management.php");
    exit;
}

// Handle GET actions (AJAX)
if (isset($_GET['action']) && $_GET['action'] === 'get_attachments') {
    $complaintId = isset($_GET['complaintId']) ? intval($_GET['complaintId']) : 0;
    $attachments = [];
    $stmt = $conn->prepare("SELECT attachment_id, file_name, file_type FROM complaint_attachments WHERE complaint_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $complaintId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $attachments[] = $row;
        }
        $stmt->close();
    }
    header('Content-Type: application/json');
    echo json_encode($attachments);
    exit;
}

// Fetch session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
}

// Fetch current user info for Dept-specific filtering
$assigned_category = null;
$departmental_roles = ['DeptAdmin', 'Coordinator', 'HOD', 'Dean'];
if (in_array($role, $departmental_roles)) {
    $stmt = $conn->prepare("SELECT assigned_category FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($assigned_category);
    $stmt->fetch();
    $stmt->close();
}

// Fetch statuses for dropdown
$statuses = [];
$res_status = $conn->query("SELECT * FROM complaint_statuses ORDER BY status_id ASC");
if ($res_status) {
    while ($row = $res_status->fetch_assoc()) {
        $statuses[] = $row;
    }
}

// Fetch DeptAdmins for assignment dropdown
$deptAdmins = [];
$res_admins = $conn->query("SELECT user_id, first_name, last_name FROM users WHERE role = 'DeptAdmin' AND status = 'Active'");
if ($res_admins) {
    while ($row = $res_admins->fetch_assoc()) {
        $deptAdmins[] = $row;
    }
}

// Fetch complaints with assignment info
$complaints = [];
$whereClause = "";

if (in_array($role, $departmental_roles)) {
    $assigned_category_sql = $assigned_category !== null ? intval($assigned_category) : -1;
    $whereClause = "WHERE c.assigned_role = '$role'";
    if ($assigned_category !== null) {
        $whereClause .= " AND c.category_id = $assigned_category_sql";
    }
}

$sql = "SELECT c.*, cat.category_name, s.status_label, 
               u.first_name, u.last_name, u.email,
               dept_head.first_name as assign_first, dept_head.last_name as assign_last
        FROM complaints c
        LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
        LEFT JOIN complaint_statuses s ON c.status_id = s.status_id
        LEFT JOIN users u ON c.user_id = u.user_id
        LEFT JOIN users dept_head ON dept_head.assigned_category = c.category_id AND dept_head.role = 'DeptAdmin'
        $whereClause
        ORDER BY c.created_at DESC";
$res_comp = $conn->query($sql);
if ($res_comp) {
    while ($row = $res_comp->fetch_assoc()) {
        $complaints[] = $row;
    }
}

// Pass status messages to JS
echo "<script>const statusMessages = " . json_encode($statuses) . ";</script>";
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Management - Complaint System</title>
    <link rel="stylesheet" href="../Assets/Css/admin-dashboard.css">
    <link rel="stylesheet" href="../Assets/Css/complaint-management.css">
</head>
<body>
    <?php include '../Includes/header.php'; ?>

    <div class="main-container">
        <?php include '../Includes/sidebar.php'; ?>
        <!-- Main Content Area -->
        <main class="main-content">
            <h2 class="page-title">Complaints <span class="page-subtitle">Management</span></h2>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; color: white; background-color: <?php echo $messageType === 'success' ? '#2ecc71' : '#e74c3c'; ?>;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Data Table Card -->
            <div class="table-card">
                <div class="table-header">
                    <h3 class="table-title">Complaint Management Table</h3>
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
                    <table class="data-table" id="complaintTable">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Category</th>
                                <th>Title</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Complainant</th>
                                <?php if (in_array($role, ['Admin', 'UpperBody', 'DeptAdmin', 'Coordinator', 'HOD', 'Dean'])): ?>
                                    <th>Assigned To</th>
                                    <th>Current Level</th>
                                <?php endif; ?>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php if (empty($complaints)): ?>
                                    <td colspan="10" style="text-align: center;">No complaints found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($complaints as $index => $comp): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($comp['category_name']); ?></td>
                                        <td><?php echo htmlspecialchars($comp['title']); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($comp['created_at'])); ?></td>
                                        <td><?php echo $comp['updated_at'] ? date('Y-m-d H:i', strtotime($comp['updated_at'])) : '---'; ?></td>
                                        <td>
                                            <?php 
                                                if ($comp['is_anonymous']) {
                                                    echo '<span class="status-badge" style="background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb;">Anonymous</span>';
                                                } else {
                                                    echo htmlspecialchars($comp['first_name'] . ' ' . $comp['last_name']);
                                                }
                                            ?>
                                        </td>
                                        <?php if (in_array($role, ['Admin', 'UpperBody', 'DeptAdmin', 'Coordinator', 'HOD', 'Dean'])): ?>
                                            <td>
                                                <?php 
                                                    if ($comp['assign_first']) {
                                                        echo htmlspecialchars($comp['assign_first'] . ' ' . $comp['assign_last']);
                                                    } else {
                                                        echo '<span style="color: #999; font-style: italic;">Not Assigned</span>';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <span style="font-weight: 600; color: #4b5563;">
                                                    <?php echo htmlspecialchars($comp['assigned_role'] ?? 'DeptAdmin'); ?>
                                                </span>
                                            </td>
                                        <?php endif; ?>
                                        <td>
                                            <?php 
                                                $statusClass = 'status-pending';
                                                if ($comp['status_label'] === 'Solved') $statusClass = 'status-solved';
                                                elseif ($comp['status_label'] === 'In Progress') $statusClass = 'status-progress';
                                                elseif ($comp['status_label'] === 'Unresolved') $statusClass = 'status-unresolved';
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($comp['status_label']); ?>
                                            </span>
                                        </td>
                                        <td class="action-btns">
                                            <button class="view-btn" title="View Details"
                                                    data-id="<?php echo $comp['complaint_id']; ?>"
                                                    data-category="<?php echo htmlspecialchars($comp['category_name']); ?>"
                                                    data-title="<?php echo htmlspecialchars($comp['title']); ?>"
                                                    data-desc="<?php echo htmlspecialchars($comp['description']); ?>"
                                                    data-date="<?php echo date('Y-m-d H:i', strtotime($comp['created_at'])); ?>"
                                                    data-updated="<?php echo $comp['updated_at'] ? date('Y-m-d H:i', strtotime($comp['updated_at'])) : ''; ?>"
                                                    data-complainant="<?php echo $comp['is_anonymous'] ? 'Anonymous' : htmlspecialchars($comp['first_name'] . ' ' . $comp['last_name']); ?>"
                                                    data-email="<?php echo $comp['is_anonymous'] ? '---' : htmlspecialchars($comp['email']); ?>"
                                                    data-batch="<?php echo htmlspecialchars($comp['batch']); ?>"
                                                    data-incident-date="<?php echo $comp['incident_date'] ? date('Y-m-d', strtotime($comp['incident_date'])) : '---'; ?>"
                                                    data-assigned-name="<?php echo $comp['assign_first'] ? htmlspecialchars($comp['assign_first'] . ' ' . $comp['assign_last']) : 'Not Assigned'; ?>"
                                                    data-status-label="<?php echo htmlspecialchars($comp['status_label']); ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </button>

                                            <button class="edit-btn" title="<?php echo (in_array($role, $departmental_roles)) ? 'Write Remarks' : 'Update Status & Remarks'; ?>" 
                                                    data-id="<?php echo $comp['complaint_id']; ?>"
                                                    data-status="<?php echo $comp['status_id']; ?>"
                                                    data-message="<?php echo htmlspecialchars($comp['final_status_message'] ?? ''); ?>"
                                                    data-title="<?php echo htmlspecialchars($comp['title']); ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>

                                            <?php if (!in_array($role, $departmental_roles)): ?>
                                                <button class="delete-btn" title="Delete" data-id="<?php echo $comp['complaint_id']; ?>">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer -->
                <div class="table-footer">
                    <div class="entries-info" id="entriesInfo">Showing <?php echo count($complaints); ?> entries</div>
                    <div class="pagination">
                        <button class="page-btn" id="prevBtn">Previous</button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn" id="nextBtn">Next</button>
                    </div>
                </div>
            </div>
        </main>
    </div>


    <!-- Update Status & Assignment Modal -->
    <div class="modal" id="statusModal" style="z-index: 99999;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="statusModalTitle"><?php echo (in_array($role, $departmental_roles)) ? 'Write Remarks' : 'Update Status & Remarks'; ?></h3>
                <button class="modal-close" id="statusModalClose">&times;</button>
            </div>
            <form id="statusForm" method="POST" action="">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="complaintId" id="statusComplaintId">
                <div style="padding: 20px;">
                    <div class="form-group" style="padding: 0 0 15px 0;">
                        <label>Complaint Title</label>
                        <p id="displayTitle" style="font-weight: 600; margin-top: 5px; color: #333;"></p>
                    </div>
                    
                    <div class="form-group" style="padding: 0 0 15px 0;">
                        <label>Select Status <span class="required">*</span></label>
                        <select name="statusId" id="statusId" required>
                            <option value="">Select Status</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo $status['status_id']; ?>"><?php echo htmlspecialchars($status['status_label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p id="statusDefaultMessage" style="font-size: 12px; color: #666; font-style: italic; margin-top: 5px;"></p>
                    </div>

                    <div class="form-group" style="padding: 0;">
                        <label>Remarks</label>
                        <textarea name="statusMessage" id="statusMessage" rows="4" placeholder="Update the student about the progress..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="statusCancelBtn">Close</button>
                    <button type="submit" class="btn-primary">Update Complaint</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Details Modal (Enhanced) -->
    <div class="modal" id="viewModal" style="z-index: 99999;">
        <div class="modal-content" style="max-width: 800px; width: 95%;">
            <div class="modal-header">
                <h3 class="modal-title">Complaint Details</h3>
                <button class="modal-close" id="viewModalClose">&times;</button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <div class="view-details-container">
                    <!-- Left Sidebar / Info Summary -->
                    <div class="view-sidebar">
                        <div class="sidebar-info-group">
                            <label>Status</label>
                            <div id="viewStatus" class="status-badge-container"></div>
                        </div>
                        <div class="sidebar-info-group">
                            <label>Complaint ID</label>
                            <p id="viewId" style="font-weight: 600;"></p>
                        </div>
                        <div class="sidebar-info-group">
                            <label>Assigned To</label>
                            <p id="viewAssignedName"></p>
                        </div>
                        <div class="sidebar-info-group">
                            <label>Created On</label>
                            <p id="viewDate"></p>
                        </div>
                        <div class="sidebar-info-group">
                            <label>Last Updated</label>
                            <p id="viewUpdated"></p>
                        </div>
                    </div>

                    <!-- Right Top Content -->
                    <div class="view-main">
                        <div class="view-section">
                            <h4 class="section-title">General Information</h4>
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Category</label>
                                    <p id="viewCategory"></p>
                                </div>
                                <div class="info-item">
                                    <label>Incident Date</label>
                                    <p id="viewIncidentDate"></p>
                                </div>
                                <div class="info-item">
                                    <label>Complainant</label>
                                    <p id="viewComplainant"></p>
                                </div>
                                <div class="info-item">
                                    <label>Batch</label>
                                    <p id="viewBatch"></p>
                                </div>
                                <div class="info-item" style="grid-column: span 2;">
                                    <label>Email Address</label>
                                    <p id="viewEmail"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Full Width Content -->
                <div class="view-full-content">
                    <div class="view-section">
                        <h4 class="section-title">Complaint Statement</h4>
                        <div class="statement-box">
                            <h5 id="viewTitle" class="statement-title"></h5>
                            <p id="viewDesc" class="statement-text"></p>
                        </div>
                    </div>

                    <div class="view-section">
                        <h4 class="section-title">Evidence & Attachments</h4>
                        <div id="viewAttachments" class="attachments-list">
                            <!-- Loaded via JS -->
                            <p class="no-attachments">Loading attachments...</p>
                        </div>
                    </div>
                </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="viewCancelBtn">Close</button>
            </div>
        </div>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <div id="deleteModal" class="custom-modal-overlay" style="z-index: 99999;">
        <div class="custom-modal-box">
            <h3 id="deleteMessage">Are you sure you want to delete this complaint?</h3>
            <div class="custom-modal-actions" id="deleteActions">
                <button class="modal-btn-cancel" id="deleteCancel">Cancel</button>
                <button class="modal-btn-confirm" id="deleteConfirm">Yes, Delete</button>
            </div>
        </div>
    </div>

    <!-- File Removal Confirmation Modal (Admin) -->
    <div id="fileDeleteModal" class="custom-modal-overlay" style="z-index: 99999;">
        <div class="custom-modal-box">
            <h3 style="margin-bottom: 20px;">Are you sure you want to remove this evidence file?</h3>
            <div class="custom-modal-actions">
                <button type="button" class="modal-btn-cancel" id="fileDeleteCancel">Cancel</button>
                <button type="button" class="modal-btn-confirm" id="fileDeleteConfirm">Yes, Remove</button>
            </div>
        </div>
    </div>

    <!-- Hidden form for deletion -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="complaintId" id="deleteComplaintId">
    </form>

    <script src="../Assets/JS/admin-dashboard.js"></script>
    <script src="../Assets/JS/table-pagination.js"></script>
    <script src="../Assets/JS/complaint-management.js"></script>
</body>
</html>