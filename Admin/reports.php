<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? $_SESSION['role'] ?? '', ['Admin', 'UpperBody'])) {
    header('Location: /index.php');
    exit;
}

require_once '../Database/db-config.php';

$message = "";
$messageType = "";

// Handle GET for attachment fetching
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_attachments') {
    $complaintId = isset($_GET['complaintId']) ? intval($_GET['complaintId']) : 0;
    $attachments = [];
    $stmt = $conn->prepare("SELECT attachment_id, file_name FROM complaint_attachments WHERE complaint_id = ?");
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

// Fetch reports data
$reports = [];
$sql = "SELECT c.*, cat.category_name, s.status_label, u.first_name, u.middle_name, u.last_name, u.email 
        FROM complaints c
        LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
        LEFT JOIN users u ON c.user_id = u.user_id
        LEFT JOIN complaint_statuses s ON c.status_id = s.status_id
        ORDER BY c.created_at DESC";
$res = $conn->query($sql);
if ($res) {
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
    <link rel="stylesheet" href="../Assets/Css/complaint-category.css">
    <link rel="stylesheet" href="../Assets/Css/user-management.css">
    <style>
        .description-text {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
                    <h3 class="table-title">Reports Table</h3>
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
                                <th>Name</th>
                                <th>Email</th>
                                <th>Batch</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>File</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php if (empty($reports)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center;">No reports found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reports as $index => $r): ?>
                                    <?php 
                                        $fullName = trim($r['first_name'] . ' ' . $r['middle_name'] . ' ' . $r['last_name']);
                                        $date = date('d/m/Y', strtotime($r['created_at']));
                                    ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($fullName); ?></td>
                                        <td><?php echo htmlspecialchars($r['email'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($r['batch'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($r['category_name'] ?? 'Other'); ?></td>
                                        <td class="description-text" title="<?php echo htmlspecialchars($r['description']); ?>">
                                            <?php echo htmlspecialchars($r['description']); ?>
                                        </td>
                                        <td><?php echo $date; ?></td>
                                        <td>
                                            <?php if (!empty($r['attachments'])): ?>
                                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                                    <?php foreach ($r['attachments'] as $att): ?>
                                                        <a href="../Includes/view-attachment.php?id=<?php echo $att['attachment_id']; ?>" target="_blank" class="file-link" style="font-size: 11px;">
                                                            <?php echo htmlspecialchars($att['file_name']); ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                No File
                                            <?php endif; ?>
                                        </td>
                                        <td class="action-btns">
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

    </div>



    <!-- Custom Delete Modal Overlay -->
    <div id="deleteModal" class="custom-modal-overlay">
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
    <script src="../Assets/JS/reports.js"></script>
</body>
</html>