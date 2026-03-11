<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? $_SESSION['role'] ?? '') !== 'User') {
    header('Location: /index.php');
    exit;
}

require_once '../Database/db-config.php';

$userId = $_SESSION['user_id'];
$message = "";
$messageType = "";

// Handle user actions: Edit and Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $compId = isset($_POST['complaintId']) ? intval($_POST['complaintId']) : 0;

    // Verify ownership before any action
    $checkStmt = $conn->prepare("SELECT user_id FROM complaints WHERE complaint_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $compId, $userId);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        $_SESSION['message'] = "Unauthorized action.";
        $_SESSION['messageType'] = "error";
    } else {
        if ($action === 'delete_complaint') {
            $stmt = $conn->prepare("DELETE FROM complaints WHERE complaint_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $compId);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Complaint deleted successfully!";
                    $_SESSION['messageType'] = "success";
                } else {
                    $_SESSION['message'] = "Error deleting: " . $conn->error;
                    $_SESSION['messageType'] = "error";
                }
                $stmt->close();
            }
        }
    }
    $checkStmt->close();
    header("Location: user-complaints.php");
    exit;
}

// Fetch session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
}

// Fetch user's complaints with attachment counts
$complaints = [];
$sql = "SELECT c.*, cat.category_name, s.status_label, s.status_message,
               CONCAT(u.first_name, ' ', u.last_name) as assigned_head
        FROM complaints c
        LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
        LEFT JOIN complaint_statuses s ON c.status_id = s.status_id
        LEFT JOIN users u ON u.assigned_category = c.category_id AND u.role = 'DeptAdmin'
        WHERE c.user_id = ? 
        ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        // Fetch specific attachments for each complaint
        $compId = $row['complaint_id'];
        $row['attachments'] = [];
        $attStmt = $conn->prepare("SELECT attachment_id, file_name FROM complaint_attachments WHERE complaint_id = ?");
        $attStmt->bind_param("i", $compId);
        $attStmt->execute();
        $attRes = $attStmt->get_result();
        while ($attRow = $attRes->fetch_assoc()) {
            $row['attachments'][] = $attRow;
        }
        $attStmt->close();
        
        $complaints[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints - Student Complaint System</title>
    <link rel="stylesheet" href="../Assets/Css/admin-dashboard.css">
    <link rel="stylesheet" href="../Assets/css/user-complaints.css">
</head>
<body>
    <?php include '../Includes/header.php'; ?>
    <?php include '../Includes/sidebar.php'; ?>
    <div class="main-container">

        <main class="main-content">
            <h1 class="page-title">Complaints</h1>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; color: white; background-color: <?php echo $messageType === 'success' ? '#2ecc71' : '#e74c3c'; ?>;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="complaints-container">
                <h2 class="section-title">List of Complaints</h2>

                <div class="table-controls">
                    <div class="entries-control">
                        <span>Show</span>
                        <select id="entriesPerPage">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>Entries</span>
                    </div>

                    <div class="search-control">
                        <span>Search:</span>
                        <input type="text" id="searchInput" placeholder="">
                    </div>
                </div>

                <div class="table-wrapper">
                    <table id="complaintsTable">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th>Admin Message</th>
                                <th>Evidence</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($complaints)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center;">You haven't submitted any complaints yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($complaints as $index => $c): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($c['title']); ?></td>
                                        <td class="description-text" title="<?php echo htmlspecialchars($c['description']); ?>">
                                            <?php echo htmlspecialchars($c['description']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($c['category_name'] ?? 'Other'); ?></td>
                                        <td>
                                            <?php 
                                                $assigned = trim($c['assigned_head'] ?? '');
                                                echo !empty($assigned) ? htmlspecialchars($assigned) : '<span style="color: #999; font-style: italic;">Not Assigned</span>'; 
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $statusClass = 'status-pending';
                                            $status = $c['status_label'] ?? 'Pending';
                                            if (stripos($status, 'Progress') !== false) $statusClass = 'status-progress';
                                            if (stripos($status, 'Solved') !== false || stripos($status, 'Fixed') !== false) $statusClass = 'status-solved';
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>" title="<?php echo htmlspecialchars($c['status_message'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($status); ?>
                                            </span>
                                        </td>
                                        <td style="font-size: 13px; color: #555; max-width: 250px;">
                                            <?php if (!empty($c['final_status_message'])): ?>
                                                <div style="background: #f8f9fa; padding: 8px; border-radius: 4px; border-left: 3px solid #6c757d;">
                                                    <?php echo htmlspecialchars($c['final_status_message']); ?>
                                                </div>
                                            <?php else: ?>
                                                <span style="font-style: italic; color: #aaa;">No message yet</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($c['attachments'])): ?>
                                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                                    <?php foreach ($c['attachments'] as $att): ?>
                                                        <a href="../Includes/view-attachment.php?id=<?php echo $att['attachment_id']; ?>" target="_blank" style="font-size: 12px; color: #2D1B69; text-decoration: underline;">
                                                            <?php echo htmlspecialchars($att['file_name']); ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <span style="font-size: 12px; color: #999;">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view-btn" title="View Details" 
                                                        data-id="<?php echo $c['complaint_id']; ?>"
                                                        data-title="<?php echo htmlspecialchars($c['title']); ?>"
                                                        data-desc="<?php echo htmlspecialchars($c['description']); ?>"
                                                        data-cat="<?php echo htmlspecialchars($c['category_name'] ?? 'Other'); ?>"
                                                        data-batch="<?php echo htmlspecialchars($c['batch'] ?? ''); ?>"
                                                        data-date="<?php echo date('M d, Y', strtotime($c['created_at'])); ?>"
                                                        data-status="<?php echo htmlspecialchars($status); ?>"
                                                        data-message="<?php echo htmlspecialchars($c['final_status_message'] ?? 'No message yet'); ?>"
                                                        data-assigned="<?php echo !empty($c['assigned_head']) ? htmlspecialchars($c['assigned_head']) : 'Not Assigned'; ?>">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                </button>
                                                <button class="action-btn delete-btn" title="Delete" data-id="<?php echo $c['complaint_id']; ?>">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <div class="pagination-info">
                        Showing <?php echo count($complaints); ?> entries
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- View Complaint Modal -->
    <div class="modal modal-large" id="viewModal" style="z-index: 99999;">
        <div class="modal-content" style="max-width: 800px; width: 90%;">
            <div class="modal-header">
                <h3 class="modal-title">Complaint Details</h3>
                <button class="modal-close" id="viewClose">&times;</button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #e5e7eb;">
                    <div>
                        <p style="font-size: 13px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Title</p>
                        <p id="viewTitle" style="font-size: 16px; font-weight: 600; color: #111827;"></p>
                    </div>
                    <div>
                        <p style="font-size: 13px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Date Submitted</p>
                        <p id="viewDate" style="font-size: 15px; color: #374151;"></p>
                    </div>
                    <div>
                        <p style="font-size: 13px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Category</p>
                        <p id="viewCat" style="font-size: 15px; color: #374151;"></p>
                    </div>
                    <div>
                        <p style="font-size: 13px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Batch</p>
                        <p id="viewBatch" style="font-size: 15px; color: #374151;"></p>
                    </div>
                    <div>
                        <p style="font-size: 13px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Status</p>
                        <p id="viewStatus" style="font-size: 15px; font-weight: 600; padding: 4px 10px; display: inline-block; border-radius: 6px;"></p>
                    </div>
                    <div>
                        <p style="font-size: 13px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Assigned To</p>
                        <p id="viewAssigned" style="font-size: 15px; color: #374151;"></p>
                    </div>
                </div>

                <div style="margin-bottom: 24px;">
                    <p style="font-size: 13px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">Description</p>
                    <div id="viewDesc" style="font-size: 15px; color: #374151; background: #f9fafb; padding: 16px; border-radius: 8px; border: 1px solid #e5e7eb; white-space: pre-wrap; word-wrap: break-word;"></div>
                </div>

                <div>
                    <p style="font-size: 13px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">Admin Message</p>
                    <div id="viewMessage" style="font-size: 15px; color: #374151; background: #f3f4f6; padding: 16px; border-radius: 8px; border-left: 4px solid #6b7280;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="viewCancel">Close</button>
            </div>
        </div>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <div class="custom-modal-overlay" id="deleteModal" style="z-index: 99999;">
        <div class="custom-modal-box">
            <h3>Are you sure you want to delete this complaint?</h3>
            <p style="margin-bottom: 25px; color: #666;">This action cannot be undone.</p>
            <div class="custom-modal-actions">
                <button type="button" class="modal-btn-cancel" id="deleteCancel">Cancel</button>
                <button type="button" class="modal-btn-confirm" id="deleteConfirm">Yes, Delete</button>
            </div>
        </div>
    </div>

    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete_complaint">
        <input type="hidden" name="complaintId" id="deleteCompId">
    </form>

    <script src="../Assets/JS/table-pagination.js"></script>
    <script src="../Assets/JS/user-complaints.js"></script>
    <script src="../Assets/JS/alerts.js"></script>
</body>
</html>