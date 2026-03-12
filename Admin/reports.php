<?php
session_start();
$role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';
if (!isset($_SESSION['user_id']) || !in_array($role, ['Admin', 'UpperBody', 'Coordinator', 'HOD', 'Dean'])) {
    header('Location: /index.php');
    exit;
}

require_once '../Database/db-config.php';
require_once '../Includes/AutoEscalator.php';
AutoEscalator::runEscalation($conn);

$message = "";
$messageType = "";

// Handle GET for attachment fetching
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_attachments') {
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

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $compId = isset($_POST['complaintId']) ? intval($_POST['complaintId']) : 0;

    if ($action === 'delete_report') {
        $stmt = $conn->prepare("DELETE FROM complaints WHERE complaint_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $compId);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Report deleted successfully!";
                $_SESSION['messageType'] = "success";
            } else {
                $_SESSION['message'] = "Error deleting report: " . $conn->error;
                $_SESSION['messageType'] = "error";
            }
            $stmt->close();
        }
    }

    // Redirect to clear POST data (PRG pattern)
    header('Location: reports.php');
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

// Fetch reports data with all requested fields
$reports = [];
$whereClause = "";
if (in_array($role, $departmental_roles)) {
    $whereClause = "WHERE c.assigned_role = '$role'";
    if ($assigned_category !== null) {
        $assigned_category_sql = intval($assigned_category);
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
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $reports[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Complaint System</title>
    <link rel="stylesheet" href="../Assets/Css/admin-dashboard.css">
    <link rel="stylesheet" href="../Assets/Css/complaint-management.css">
    <link rel="stylesheet" href="../Assets/Css/user-management.css">
    <style>
        .data-table th, .data-table td {
            white-space: nowrap;
            font-size: 13px;
        }
        .truncate-text {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <?php include '../Includes/header.php'; ?>

    <div class="main-container">
        <?php include '../Includes/sidebar.php'; ?>

        <!-- Main Content Area -->
        <main class="main-content">
            <h2 class="page-title">Reports</h2>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; color: white; background-color: <?php echo $messageType === 'success' ? '#2ecc71' : '#e74c3c'; ?>;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Data Table Card -->
            <div class="table-card">
                <div class="table-header">
                    <h3 class="table-title">Complaints Report</h3>
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
                    <table class="data-table" id="reportsTable">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Title</th>
                                <th>Anonymous</th>
                                <th>Assigned To</th>
                                <th>Current Level</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php if (empty($reports)): ?>
                                <tr>
                                    <td colspan="10" style="text-align: center;">No reports found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reports as $index => $r): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($r['category_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php 
                                                $statusClass = 'status-pending';
                                                if ($r['status_label'] === 'Solved') $statusClass = 'status-solved';
                                                elseif ($r['status_label'] === 'In Progress') $statusClass = 'status-progress';
                                                elseif ($r['status_label'] === 'Unresolved') $statusClass = 'status-unresolved';
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($r['status_label'] ?? 'Pending'); ?>
                                            </span>
                                        </td>
                                        <td class="truncate-text" title="<?php echo htmlspecialchars($r['title']); ?>">
                                            <?php echo htmlspecialchars($r['title']); ?>
                                        </td>
                                        <td><?php echo $r['is_anonymous'] ? 'Yes' : 'No'; ?></td>
                                        <td>
                                            <?php 
                                                if ($r['assign_first']) echo htmlspecialchars($r['assign_first'] . ' ' . $r['assign_last']);
                                                else echo '<span style="color: #999;">Not Assigned</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <span style="font-weight: 600; color: #4b5563;">
                                                <?php echo htmlspecialchars($r['assigned_role'] ?? 'DeptAdmin'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?></td>
                                        <td><?php echo $r['updated_at'] ? date('d/m/Y H:i', strtotime($r['updated_at'])) : '---'; ?></td>
                                        <td class="action-btns">
                                            <button class="view-btn" title="View Details"
                                                    data-id="<?php echo $r['complaint_id']; ?>"
                                                    data-category="<?php echo htmlspecialchars($r['category_name'] ?? 'N/A'); ?>"
                                                    data-title="<?php echo htmlspecialchars($r['title']); ?>"
                                                    data-desc="<?php echo htmlspecialchars($r['description']); ?>"
                                                    data-date="<?php echo date('Y-m-d H:i', strtotime($r['created_at'])); ?>"
                                                    data-updated="<?php echo $r['updated_at'] ? date('Y-m-d H:i', strtotime($r['updated_at'])) : ''; ?>"
                                                    data-complainant="<?php echo $r['is_anonymous'] ? 'Anonymous' : htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?>"
                                                    data-email="<?php echo $r['is_anonymous'] ? '---' : htmlspecialchars($r['email']); ?>"
                                                    data-batch="<?php echo htmlspecialchars($r['batch'] ?? 'N/A'); ?>"
                                                    data-incident-date="<?php echo $r['incident_date'] ? date('Y-m-d', strtotime($r['incident_date'])) : '---'; ?>"
                                                    data-assigned-name="<?php echo $r['assign_first'] ? htmlspecialchars($r['assign_first'] . ' ' . $r['assign_last']) : 'Not Assigned'; ?>"
                                                    data-status-label="<?php echo htmlspecialchars($r['status_label'] ?? 'Pending'); ?>"
                                                    data-message="<?php echo htmlspecialchars($r['final_status_message'] ?? 'No closure message yet.'); ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </button>
                                            <button class="delete-btn" title="Delete" data-id="<?php echo $r['complaint_id']; ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer -->
                <div class="table-footer">
                    <div class="entries-info" id="entriesInfo">Showing <?php echo count($reports); ?> entries</div>
                    <div class="pagination">
                        <button class="page-btn" id="prevBtn">Previous</button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn" id="nextBtn">Next</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- View Details Modal -->
    <div class="modal" id="viewModal" style="z-index: 99999;">
        <div class="modal-content" style="max-width: 800px; width: 95%;">
            <div class="modal-header">
                <h3 class="modal-title">Report Details</h3>
                <button class="modal-close" id="viewModalClose">&times;</button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <div class="view-details-container">
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

                        <div class="view-section">
                            <h4 class="section-title">Complaint Statement</h4>
                            <div class="statement-box">
                                <h5 id="viewTitle" class="statement-title"></h5>
                                <p id="viewDesc" class="statement-text"></p>
                            </div>
                        </div>

                        <div class="view-section" id="closureSection">
                            <h4 class="section-title">Closure Message / Remarks</h4>
                            <div class="statement-box" style="background: #f0f7ff; border-left-color: #3498db;">
                                <p id="viewClosureMessage" class="statement-text"></p>
                            </div>
                        </div>

                        <div class="view-section">
                            <h4 class="section-title">Evidence & Attachments</h4>
                            <div id="viewAttachments" class="attachments-list">
                                <p class="no-attachments">No attachments found.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="viewCancelBtn">Close</button>
            </div>
        </div>
    </div>

    <!-- Custom Delete Modal Overlay -->
    <div id="deleteModal" class="custom-modal-overlay" style="z-index: 99999;">
        <div class="custom-modal-box">
            <h3 id="deleteMessage">Are you sure you want to delete this report?</h3>
            <div class="custom-modal-actions">
                <button class="modal-btn-cancel" id="deleteCancel">Cancel</button>
                <button class="modal-btn-confirm" id="deleteConfirm">Yes, Delete</button>
            </div>
        </div>
    </div>

    <!-- Hidden form for deletion -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_report">
        <input type="hidden" name="complaintId" id="deleteCompId">
    </form>

    <script src="../Assets/JS/admin-dashboard.js"></script>
    <script src="../Assets/JS/alerts.js"></script>
    <script src="../Assets/JS/table-pagination.js"></script>
    <script src="../Assets/JS/reports.js"></script>
</body>
</html>