<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? $_SESSION['role'] ?? '', ['Admin', 'DeptAdmin', 'UpperBody'])) {
    header('Location: /index.php');
    exit;
}

require_once '../Database/db-config.php';

$message = "";
$messageType = "";

// Handle status updates and deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $complaintId = isset($_POST['complaintId']) ? intval($_POST['complaintId']) : 0;

    if ($action === 'update_status') {
        $statusId = isset($_POST['statusId']) ? intval($_POST['statusId']) : 0;
        $stmt = $conn->prepare("UPDATE complaints SET status_id = ? WHERE complaint_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $statusId, $complaintId);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Complaint status updated successfully!";
                $_SESSION['messageType'] = "success";
            } else {
                $_SESSION['message'] = "Error updating status: " . $conn->error;
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
    } elseif ($action === 'get_attachments') {
        $complaintId = isset($_GET['complaintId']) ? intval($_GET['complaintId']) : (isset($_POST['complaintId']) ? intval($_POST['complaintId']) : 0);
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
        echo json_encode($attachments);
        exit;
    } elseif ($action === 'delete_attachment') {
        $attachmentId = isset($_POST['attachmentId']) ? intval($_POST['attachmentId']) : 0;
        $stmt = $conn->prepare("DELETE FROM complaint_attachments WHERE attachment_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $attachmentId);
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            $stmt->close();
        }
        exit;
    }

    header("Location: complaint-management.php");
    exit;
}

// Fetch session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
}

// Fetch statuses for dropdown
$statuses = [];
$res_status = $conn->query("SELECT * FROM complaint_statuses ORDER BY status_id ASC");
if ($res_status) {
    while ($row = $res_status->fetch_assoc()) {
        $statuses[] = $row;
    }
}

// Fetch complaints
$complaints = [];
$sql = "SELECT c.*, cat.category_name, s.status_label, u.first_name, u.last_name 
        FROM complaints c
        LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
        LEFT JOIN complaint_statuses s ON c.status_id = s.status_id
        LEFT JOIN users u ON c.user_id = u.user_id
        ORDER BY c.created_at DESC";
$res_comp = $conn->query($sql);
if ($res_comp) {
    while ($row = $res_comp->fetch_assoc()) {
        $complaints[] = $row;
    }
}
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
                                <th>Description</th>
                                <th>Date</th>
                                <th>Complainant</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php if (empty($complaints)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No complaints found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($complaints as $index => $comp): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($comp['category_name']); ?></td>
                                        <td><?php echo htmlspecialchars($comp['title']); ?></td>
                                        <td>
                                            <div class="text-truncate">
                                                <?php echo htmlspecialchars($comp['description']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($comp['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($comp['first_name'] . ' ' . $comp['last_name']); ?></td>
                                        <td>
                                            <?php 
                                                $statusClass = 'status-pending';
                                                if ($comp['status_label'] === 'Solved') $statusClass = 'status-solved';
                                                elseif ($comp['status_label'] === 'In Progress') $statusClass = 'status-progress';
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($comp['status_label']); ?>
                                            </span>
                                        </td>
                                        <td class="action-btns">
                                            <button class="edit-btn" title="Update Status" 
                                                    data-id="<?php echo $comp['complaint_id']; ?>"
                                                    data-status="<?php echo $comp['status_id']; ?>"
                                                    data-title="<?php echo htmlspecialchars($comp['title']); ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>
                                            <button class="delete-btn" title="Delete" data-id="<?php echo $comp['complaint_id']; ?>">
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

    <!-- Edit Status Modal -->
    <div class="modal" id="statusModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="statusModalTitle">Update Status</h3>
                <button class="modal-close" id="statusModalClose">&times;</button>
            </div>
            <form id="statusForm" method="POST" action="">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="complaintId" id="statusComplaintId">
                <div class="form-group" style="padding: 20px;">
                    <label>Complaint Title</label>
                    <p id="displayTitle" style="font-weight: 600; margin-bottom: 15px; color: #333;"></p>
                    
                    <label>Select Status <span class="required">*</span></label>
                    <select name="statusId" id="statusId" required>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status['status_id']; ?>"><?php echo htmlspecialchars($status['status_label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="statusCancelBtn">Close</button>
                    <button type="submit" class="btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <div id="deleteModal" class="custom-modal-overlay">
        <div class="custom-modal-box">
            <h3 id="deleteMessage">Are you sure you want to delete this complaint?</h3>
            <div class="custom-modal-actions" id="deleteActions">
                <button class="modal-btn-cancel" id="deleteCancel">Cancel</button>
                <button class="modal-btn-confirm" id="deleteConfirm">Yes, Delete</button>
            </div>
        </div>
    </div>

    <!-- File Removal Confirmation Modal (Admin) -->
    <div id="fileDeleteModal" class="custom-modal-overlay">
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
    <script src="../Assets/JS/complaint-management.js"></script>
</body>
</html>